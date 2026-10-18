<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class AdminSupportController extends Controller
{
    /**
     * Landing Page Inquiries Management.
     */
    public function contacts(Request $request): Response
    {
        $status = $request->input('status', 'all');
        $search = $request->input('search');

        $query = ContactMessage::latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $messages = $query->paginate(20)->through(function ($m) {
            return [
                'id' => $m->id,
                'name' => $m->name,
                'email' => $m->email,
                'phone' => $m->phone,
                'subject' => $m->subject,
                'inquiry_type' => $m->inquiry_type,
                'message' => $m->message,
                'status' => $m->status,
                'admin_reply' => $m->admin_reply,
                'replied_at' => $m->replied_at ? $m->replied_at->format('M d, Y h:i A') : null,
                'created_at' => $m->created_at->format('M d, Y h:i A'),
            ];
        });

        $counts = [
            'total' => ContactMessage::count(),
            'unread' => ContactMessage::where('status', 'unread')->count(),
            'replied' => ContactMessage::where('status', 'replied')->count(),
        ];

        return Inertia::render('Admin/Support/Contacts', [
            'messages' => $messages,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
            'counts' => $counts,
        ]);
    }

    /**
     * Mark Landing Page Message Status or Save Reply.
     */
    public function updateContactStatus(Request $request, ContactMessage $contactMessage)
    {
        $validated = $request->validate([
            'status' => 'required|in:unread,read,replied,resolved,pending',
            'admin_reply' => 'nullable|string|max:3000',
        ]);

        $contactMessage->update([
            'status' => $validated['status'],
            'admin_reply' => $validated['admin_reply'] ?? $contactMessage->admin_reply,
            'replied_at' => $validated['status'] === 'replied' ? now() : $contactMessage->replied_at,
        ]);

        return back()->with('success', 'Message status updated successfully.');
    }

    /**
     * Delete Landing Page Message.
     */
    public function destroyContact(ContactMessage $contactMessage)
    {
        $contactMessage->delete();
        return back()->with('success', 'Message deleted successfully.');
    }

    /**
     * Doctor Support Tickets List.
     */
    public function tickets(Request $request): Response
    {
        $status = $request->input('status', 'all');
        $priority = $request->input('priority', 'all');
        $search = $request->input('search');

        $query = SupportTicket::with(['user.doctorProfile', 'replies.user'])->latest('updated_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($priority !== 'all') {
            $query->where('priority', $priority);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->paginate(20)->through(function ($t) {
            return [
                'id' => $t->id,
                'ticket_number' => $t->ticket_number,
                'user' => [
                    'id' => $t->user->id ?? 0,
                    'name' => $t->user->name ?? 'Doctor',
                    'email' => $t->user->email ?? '',
                    'phone' => $t->user->doctorProfile->phone ?? '',
                    'specialty' => $t->user->doctorProfile->specialty ?? 'Physician',
                    'plan' => $t->user->subscription_plan ?? 'free_trial',
                ],
                'category' => $t->category,
                'priority' => $t->priority,
                'subject' => $t->subject,
                'message' => $t->message,
                'status' => $t->status,
                'attachment_path' => $t->attachment_path,
                'replies_count' => $t->replies->count(),
                'last_reply_at' => $t->last_reply_at ? $t->last_reply_at->diffForHumans() : $t->created_at->diffForHumans(),
                'created_at' => $t->created_at->format('M d, Y h:i A'),
                'replies' => $t->replies->map(fn($r) => [
                    'id' => $r->id,
                    'user_name' => $r->user->name ?? 'User',
                    'is_admin' => (bool)$r->is_admin_reply,
                    'message' => $r->message,
                    'attachment_path' => $r->attachment_path,
                    'created_at' => $r->created_at->format('M d, Y h:i A'),
                ]),
            ];
        });

        $counts = [
            'all' => SupportTicket::count(),
            'open' => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'resolved' => SupportTicket::where('status', 'resolved')->count(),
            'closed' => SupportTicket::where('status', 'closed')->count(),
        ];

        return Inertia::render('Admin/Support/Tickets', [
            'tickets' => $tickets,
            'filters' => [
                'status' => $status,
                'priority' => $priority,
                'search' => $search,
            ],
            'counts' => $counts,
        ]);
    }

    /**
     * Admin Reply to Doctor Ticket.
     */
    public function replyTicket(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'status' => 'required|in:open,in_progress,resolved,closed',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('support_attachments', 'public');
        }

        SupportTicketReply::create([
            'support_ticket_id' => $supportTicket->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
            'attachment_path' => $attachmentPath ? '/storage/' . $attachmentPath : null,
            'is_admin_reply' => true,
        ]);

        $supportTicket->update([
            'status' => $validated['status'],
            'last_reply_at' => now(),
        ]);

        return back()->with('success', 'Admin reply posted and ticket status updated.');
    }

    /**
     * Quick status update for support ticket.
     */
    public function updateTicketStatus(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $supportTicket->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', "Ticket #{$supportTicket->ticket_number} marked as {$validated['status']}.");
    }

    /**
     * Official Support Channels & Helpdesk Settings.
     */
    public function settings(): Response
    {
        $settings = [
            'support_email' => SystemSetting::get('support_email', 'contact@cannyapps.com'),
            'support_phone' => SystemSetting::get('support_phone', '+1 (800) 555-0199'),
            'support_whatsapp' => SystemSetting::get('support_whatsapp', '+18005550199'),
            'support_hours' => SystemSetting::get('support_hours', 'Monday - Friday: 9:00 AM - 8:00 PM EST'),
            'support_address' => SystemSetting::get('support_address', '100 Medical Plaza, Suite 400, San Francisco, CA 94143'),
        ];

        return Inertia::render('Admin/Support/Settings', [
            'settings' => $settings,
        ]);
    }

    /**
     * Save Support Channels & Helpdesk Settings.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'support_email' => 'required|email|max:150',
            'support_phone' => 'required|string|max:50',
            'support_whatsapp' => 'nullable|string|max:50',
            'support_hours' => 'required|string|max:200',
            'support_address' => 'nullable|string|max:300',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value);
        }

        return back()->with('success', 'Super Admin support contact details updated successfully.');
    }
}
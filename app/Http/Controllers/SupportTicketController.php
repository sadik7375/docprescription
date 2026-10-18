<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class SupportTicketController extends Controller
{
    /**
     * Display the Doctor / Practice Support Center.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $tickets = SupportTicket::with(['replies.user'])
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->paginate(15)
            ->through(function ($t) {
                return [
                    'id' => $t->id,
                    'ticket_number' => $t->ticket_number,
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

        // Official Super Admin Support Contact Channels
        $supportContacts = [
            'email' => SystemSetting::get('support_email', 'contact@cannyapps.com'),
            'phone' => SystemSetting::get('support_phone', '+1 (800) 555-0199'),
            'whatsapp' => SystemSetting::get('support_whatsapp', '+18005550199'),
            'hours' => SystemSetting::get('support_hours', 'Monday - Friday: 9:00 AM - 8:00 PM EST'),
            'address' => SystemSetting::get('support_address', '100 Medical Plaza, Suite 400, San Francisco, CA 94143'),
        ];

        return Inertia::render('Support/Index', [
            'tickets' => $tickets,
            'supportContacts' => $supportContacts,
        ]);
    }

    /**
     * Submit a new support ticket.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|in:prescription_engine,ai_assistant,billing_subscription,account_chamber,feature_request,general',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:5000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('support_attachments', 'public');
        }

        $ticket = SupportTicket::create([
            'ticket_number' => SupportTicket::generateTicketNumber(),
            'user_id' => $request->user()->id,
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'open',
            'attachment_path' => $attachmentPath ? '/storage/' . $attachmentPath : null,
            'last_reply_at' => now(),
        ]);

        return back()->with('success', "Support ticket #{$ticket->ticket_number} created successfully! Our technical team will review and reply shortly.");
    }

    /**
     * Reply to a support ticket from Doctor Portal.
     */
    public function reply(Request $request, SupportTicket $supportTicket)
    {
        if ($supportTicket->user_id !== $request->user()->id && $request->user()->role !== 'super_admin') {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
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
            'is_admin_reply' => false,
        ]);

        $supportTicket->update([
            'status' => 'open', // Re-open if closed or waiting
            'last_reply_at' => now(),
        ]);

        return back()->with('success', 'Your reply has been posted.');
    }
}
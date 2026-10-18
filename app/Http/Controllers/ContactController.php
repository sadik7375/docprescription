<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    /**
     * Handle public landing page contact / demo request submission.
     */
    public function submitLandingContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'subject' => 'nullable|string|max:200',
            'inquiry_type' => 'required|string|in:general,sales_demo,enterprise,support,billing',
            'message' => 'required|string|max:3000',
        ]);

        $message = ContactMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'] ?? 'General Inquiry',
            'inquiry_type' => $validated['inquiry_type'],
            'message' => $validated['message'],
            'status' => 'unread',
        ]);

        // Attempt optional admin email notification if SMTP is configured
        try {
            $adminEmail = SystemSetting::get('support_email') ?: env('MAIL_FROM_ADDRESS');
            if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                // Sent to super admin
                Mail::raw("New Contact Inquiry Received on Doc Helper AI:\n\nName: {$message->name}\nEmail: {$message->email}\nPhone: {$message->phone}\nType: {$message->inquiry_type}\nSubject: {$message->subject}\n\nMessage:\n{$message->message}", function ($mail) use ($adminEmail, $message) {
                    $mail->to($adminEmail)
                         ->subject("[Doc Helper AI] New Inquiry: {$message->subject}");
                });
            }
        } catch (Throwable $e) {}

        return back()->with('success', 'Thank you! Your message has been sent to our executive team. We will get back to you shortly.');
    }
}
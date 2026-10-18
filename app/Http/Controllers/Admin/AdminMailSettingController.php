<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class AdminMailSettingController extends Controller
{
    /**
     * Display Email / SMTP Settings page for Super Admin.
     */
    public function index(): Response
    {
        $settings = SystemSetting::where('group', 'mail')->pluck('value', 'key')->toArray();

        return Inertia::render('Admin/MailSettings/Index', [
            'settings' => [
                'mail_mailer' => $settings['mail_mailer'] ?? config('mail.default', 'smtp'),
                'mail_host' => $settings['mail_host'] ?? config('mail.mailers.smtp.host', 'smtp.gmail.com'),
                'mail_port' => (string) ($settings['mail_port'] ?? config('mail.mailers.smtp.port', 587)),
                'mail_username' => $settings['mail_username'] ?? config('mail.mailers.smtp.username', ''),
                'mail_password' => $settings['mail_password'] ?? config('mail.mailers.smtp.password', ''),
                'mail_encryption' => $settings['mail_encryption'] ?? config('mail.mailers.smtp.encryption', 'tls'),
                'mail_from_address' => $settings['mail_from_address'] ?? config('mail.from.address', 'noreply@example.com'),
                'mail_from_name' => $settings['mail_from_name'] ?? config('mail.from.name', config('app.name', 'Doc Helper AI')),
            ],
            'status' => session('status'),
            'error' => session('error'),
        ]);
    }

    /**
     * Save dynamic SMTP settings to database.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mail_mailer' => 'required|string|in:smtp,sendmail,log',
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|numeric|between:1,65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl,starttls,null',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value === 'null' ? null : $value,
                    'group' => 'mail',
                    'type' => 'string',
                    'description' => 'SMTP and Mail configuration for sending system emails.',
                ]
            );
        }

        return redirect()->back()->with('status', 'SMTP / Email settings saved successfully!');
    }

    /**
     * Send a real-time Test Email to verify SMTP configuration.
     */
    public function testEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        $testEmail = $request->input('test_email');

        try {
            $settings = SystemSetting::where('group', 'mail')->pluck('value', 'key')->toArray();

            $mailer = $settings['mail_mailer'] ?? config('mail.default', 'smtp');
            $host = $settings['mail_host'] ?? config('mail.mailers.smtp.host');
            $port = (int) ($settings['mail_port'] ?? config('mail.mailers.smtp.port', 587));
            $username = $settings['mail_username'] ?? config('mail.mailers.smtp.username');
            $password = $settings['mail_password'] ?? config('mail.mailers.smtp.password');
            $encryption = $settings['mail_encryption'] ?? config('mail.mailers.smtp.encryption', 'tls');
            $fromAddress = $settings['mail_from_address'] ?? config('mail.from.address', 'noreply@example.com');
            $fromName = $settings['mail_from_name'] ?? config('mail.from.name', 'Doc Helper AI');

            config([
                'mail.default' => $mailer,
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => $port,
                'mail.mailers.smtp.encryption' => ($encryption === 'null' || empty($encryption)) ? null : $encryption,
                'mail.mailers.smtp.username' => $username,
                'mail.mailers.smtp.password' => $password,
                'mail.from.address' => $fromAddress,
                'mail.from.name' => $fromName,
            ]);

            Mail::raw(
                "Hello!\n\nThis is a verification test email from {$fromName}.\n\nYour SMTP server connection is working 100% perfectly. Password reset and system notification emails will be delivered without any issues.\n\nTimestamp: " . now()->toDateTimeString(),
                function ($message) use ($testEmail, $fromName, $fromAddress) {
                    $message->to($testEmail)
                        ->from($fromAddress, $fromName)
                        ->subject("✅ [Test Verified] SMTP Email Connection Working - " . $fromName);
                }
            );

            return redirect()->back()->with('status', "Test email sent successfully to {$testEmail}! Check your inbox or spam folder.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'SMTP Connection Failed: ' . $e->getMessage());
        }
    }
}
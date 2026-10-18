<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Dynamically load SMTP & Mail configuration from database if available
        try {
            if (Schema::hasTable('system_settings')) {
                $mailSettings = SystemSetting::where('group', 'mail')->pluck('value', 'key')->toArray();
                if (!empty($mailSettings['mail_host'])) {
                    $encryption = $mailSettings['mail_encryption'] ?? 'tls';
                    config([
                        'mail.default' => $mailSettings['mail_mailer'] ?? config('mail.default', 'smtp'),
                        'mail.mailers.smtp.host' => $mailSettings['mail_host'],
                        'mail.mailers.smtp.port' => (int) ($mailSettings['mail_port'] ?? 587),
                        'mail.mailers.smtp.encryption' => ($encryption === 'null' || empty($encryption)) ? null : $encryption,
                        'mail.mailers.smtp.username' => $mailSettings['mail_username'] ?? null,
                        'mail.mailers.smtp.password' => $mailSettings['mail_password'] ?? null,
                        'mail.from.address' => $mailSettings['mail_from_address'] ?? config('mail.from.address'),
                        'mail.from.name' => $mailSettings['mail_from_name'] ?? config('mail.from.name'),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Silently fallback to .env configuration during migrations or CLI setup
        }
    }
}

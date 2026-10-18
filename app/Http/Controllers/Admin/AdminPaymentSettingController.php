<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionTransaction;
use App\Models\SystemSetting;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminPaymentSettingController extends Controller
{
    /**
     * Display Payment Gateway Settings & Transaction Logs for Super Admin.
     */
    public function index(): Response
    {
        $settings = SystemSetting::where('group', 'payment')->pluck('value', 'key')->toArray();

        $transactions = SubscriptionTransaction::with('user')
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/PaymentSettings/Index', [
            'settings' => [
                'stripe_enabled' => filter_var($settings['stripe_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'stripe_publishable_key' => $settings['stripe_publishable_key'] ?? '',
                'stripe_secret_key' => $settings['stripe_secret_key'] ?? '',

                'paypal_enabled' => filter_var($settings['paypal_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'paypal_client_id' => $settings['paypal_client_id'] ?? '',
                'paypal_secret' => $settings['paypal_secret'] ?? '',
                'paypal_mode' => $settings['paypal_mode'] ?? 'sandbox', // sandbox | live

                'razorpay_enabled' => filter_var($settings['razorpay_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'razorpay_key_id' => $settings['razorpay_key_id'] ?? '',
                'razorpay_key_secret' => $settings['razorpay_key_secret'] ?? '',

                'bank_transfer_enabled' => filter_var($settings['bank_transfer_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'bank_transfer_instructions' => $settings['bank_transfer_instructions'] ?? "Please wire the subscription fee to:\nBank: International Global Bank\nAccount Name: Doc Helper AI SaaS\nAccount No: 1234567890\nSWIFT/BIC: GLOBUS33\nReference: Your Doctor Email\n\nAfter transferring, please submit your transaction reference number below for instant account verification.",
            ],
            'transactions' => $transactions,
        ]);
    }

    /**
     * Update payment gateway credentials and toggles.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'stripe_enabled' => 'nullable|boolean',
            'stripe_publishable_key' => 'nullable|string|max:255',
            'stripe_key' => 'nullable|string|max:255',
            'stripe_secret_key' => 'nullable|string|max:255',
            'stripe_secret' => 'nullable|string|max:255',

            'paypal_enabled' => 'nullable|boolean',
            'paypal_client_id' => 'nullable|string|max:255',
            'paypal_secret' => 'nullable|string|max:255',
            'paypal_mode' => 'nullable|string|in:sandbox,live',

            'razorpay_enabled' => 'nullable|boolean',
            'razorpay_key_id' => 'nullable|string|max:255',
            'razorpay_key_secret' => 'nullable|string|max:255',

            'bank_transfer_enabled' => 'nullable|boolean',
            'bank_transfer_instructions' => 'nullable|string|max:2000',
        ]);

        foreach ($validated as $key => $val) {
            PaymentService::setSetting($key, is_bool($val) ? ($val ? '1' : '0') : (string) $val);
        }

        return redirect()->back()->with('success', 'Payment gateway settings saved successfully.');
    }

    /**
     * Approve manual/offline wire transaction.
     */
    public function approveTransaction(SubscriptionTransaction $transaction): RedirectResponse
    {
        if ($transaction->payment_status === 'completed') {
            return redirect()->back()->with('error', 'Transaction is already completed.');
        }

        $user = $transaction->user;
        if ($user) {
            $endsAt = $transaction->billing_cycle === 'yearly' ? now()->addYear() : now()->addMonth();

            $user->update([
                'subscription_plan' => $transaction->plan_slug,
                'subscription_status' => 'active',
                'subscription_ends_at' => $endsAt,
                'is_active' => true,
            ]);
        }

        $transaction->update([
            'payment_status' => 'completed',
            'paid_at' => now(),
        ]);

        return redirect()->back()->with('success', "Wire transfer transaction #{$transaction->id} approved and Doctor plan activated.");
    }
}
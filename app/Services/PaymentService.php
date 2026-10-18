<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Get system setting value by key.
     */
    public static function getSetting(string $key, $default = null)
    {
        $setting = SystemSetting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set system setting value.
     */
    public static function setSetting(string $key, $value, string $group = 'payment')
    {
        return SystemSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }

    /**
     * Get public gateway configuration for frontend checkout.
     */
    public static function getPublicConfig(): array
    {
        return [
            'stripe' => [
                'enabled' => filter_var(self::getSetting('stripe_enabled', false), FILTER_VALIDATE_BOOLEAN),
                'publishable_key' => self::getSetting('stripe_publishable_key', ''),
            ],
            'paypal' => [
                'enabled' => filter_var(self::getSetting('paypal_enabled', false), FILTER_VALIDATE_BOOLEAN),
                'client_id' => self::getSetting('paypal_client_id', ''),
                'mode' => self::getSetting('paypal_mode', 'sandbox'), // sandbox | live
            ],
            'razorpay' => [
                'enabled' => filter_var(self::getSetting('razorpay_enabled', false), FILTER_VALIDATE_BOOLEAN),
                'key_id' => self::getSetting('razorpay_key_id', ''),
            ],
            'bank_transfer' => [
                'enabled' => filter_var(self::getSetting('bank_transfer_enabled', true), FILTER_VALIDATE_BOOLEAN),
                'instructions' => self::getSetting('bank_transfer_instructions', "Please wire the subscription fee to:\nBank: International Global Bank\nAccount Name: Doc Helper AI SaaS\nAccount No: 1234567890\nSWIFT/BIC: GLOBUS33\nReference: Your Doctor Email\n\nAfter transferring, please submit your transaction reference number below for instant account verification."),
            ],
        ];
    }

    /* -------------------------------------------------------------------------- */
    /*                               STRIPE METHODS                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Create Stripe Checkout Session.
     */
    public static function createStripeSession(array $params): array
    {
        $secretKey = self::getSetting('stripe_secret_key');
        if (empty($secretKey)) {
            throw new \Exception('Stripe secret key is not configured in Super Admin settings.');
        }

        $amountInCents = (int) round($params['amount'] * 100);
        $currency = strtolower($params['currency'] ?? 'usd');

        $response = Http::withToken($secretKey)
            ->asForm()
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => $currency,
                            'unit_amount' => $amountInCents,
                            'product_data' => [
                                'name' => $params['plan_name'] . ' Plan (' . ucfirst($params['billing_cycle']) . ')',
                                'description' => 'Doc Helper AI Medical Practice Subscription',
                            ],
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'customer_email' => $params['user_email'],
                'client_reference_id' => (string) $params['user_id'],
                'metadata' => [
                    'user_id' => $params['user_id'],
                    'plan_slug' => $params['plan_slug'],
                    'plan_name' => $params['plan_name'],
                    'billing_cycle' => $params['billing_cycle'],
                ],
                'success_url' => $params['success_url'] . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $params['cancel_url'],
            ]);

        if (!$response->successful()) {
            Log::error('Stripe Session Error:', $response->json() ?? ['body' => $response->body()]);
            throw new \Exception($response->json('error.message') ?? 'Failed to initialize Stripe Checkout session.');
        }

        return $response->json();
    }

    /**
     * Verify Stripe Checkout Session.
     */
    public static function verifyStripeSession(string $sessionId): array
    {
        $secretKey = self::getSetting('stripe_secret_key');
        $response = Http::withToken($secretKey)->get("https://api.stripe.com/v1/checkout/sessions/{$sessionId}");

        if (!$response->successful()) {
            throw new \Exception('Failed to verify Stripe Session with API.');
        }

        $data = $response->json();
        return [
            'paid' => ($data['payment_status'] ?? '') === 'paid',
            'transaction_id' => $data['payment_intent'] ?? $data['id'],
            'amount' => ($data['amount_total'] ?? 0) / 100,
            'currency' => strtoupper($data['currency'] ?? 'USD'),
            'metadata' => $data['metadata'] ?? [],
            'raw' => $data,
        ];
    }

    /* -------------------------------------------------------------------------- */
    /*                               PAYPAL METHODS                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Get PayPal Base URL depending on environment mode.
     */
    public static function getPayPalBaseUrl(): string
    {
        $mode = self::getSetting('paypal_mode', 'sandbox');
        return $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Get PayPal OAuth2 Access Token.
     */
    public static function getPayPalAccessToken(): string
    {
        $clientId = self::getSetting('paypal_client_id');
        $secret = self::getSetting('paypal_secret');

        if (empty($clientId) || empty($secret)) {
            throw new \Exception('PayPal Client ID or Secret is missing in Super Admin settings.');
        }

        $baseUrl = self::getPayPalBaseUrl();
        $response = Http::withBasicAuth($clientId, $secret)
            ->asForm()
            ->post("{$baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        if (!$response->successful()) {
            Log::error('PayPal Auth Error:', $response->json() ?? ['body' => $response->body()]);
            throw new \Exception('Failed to authenticate with PayPal API.');
        }

        return $response->json('access_token');
    }

    /**
     * Create PayPal Order (v2 API).
     */
    public static function createPayPalOrder(array $params): array
    {
        $token = self::getPayPalAccessToken();
        $baseUrl = self::getPayPalBaseUrl();
        $amount = number_format($params['amount'], 2, '.', '');
        $currency = strtoupper($params['currency'] ?? 'USD');

        $response = Http::withToken($token)->post("{$baseUrl}/v2/checkout/orders", [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => 'SUB_' . $params['plan_slug'] . '_' . $params['user_id'],
                    'description' => $params['plan_name'] . ' Subscription (' . ucfirst($params['billing_cycle']) . ')',
                    'custom_id' => json_encode([
                        'user_id' => $params['user_id'],
                        'plan_slug' => $params['plan_slug'],
                        'plan_name' => $params['plan_name'],
                        'billing_cycle' => $params['billing_cycle'],
                    ]),
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => $amount,
                    ],
                ]
            ],
            'application_context' => [
                'brand_name' => 'Doc Helper AI',
                'landing_page' => 'NO_PREFERENCE',
                'user_action' => 'PAY_NOW',
                'return_url' => $params['return_url'],
                'cancel_url' => $params['cancel_url'],
            ]
        ]);

        if (!$response->successful()) {
            Log::error('PayPal Create Order Error:', $response->json() ?? ['body' => $response->body()]);
            throw new \Exception($response->json('message') ?? 'Failed to initialize PayPal order.');
        }

        return $response->json();
    }

    /**
     * Capture PayPal Order (v2 API).
     */
    public static function capturePayPalOrder(string $orderId): array
    {
        $token = self::getPayPalAccessToken();
        $baseUrl = self::getPayPalBaseUrl();

        $response = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$baseUrl}/v2/checkout/orders/{$orderId}/capture");

        if (!$response->successful()) {
            Log::error('PayPal Capture Order Error:', $response->json() ?? ['body' => $response->body()]);
            throw new \Exception('Failed to capture PayPal payment order.');
        }

        $data = $response->json();
        $status = $data['status'] ?? '';
        $purchaseUnit = $data['purchase_units'][0] ?? [];
        $capture = $purchaseUnit['payments']['captures'][0] ?? [];
        $customData = json_decode($purchaseUnit['payments']['captures'][0]['custom_id'] ?? $purchaseUnit['custom_id'] ?? '{}', true) ?: [];

        return [
            'paid' => in_array($status, ['COMPLETED', 'APPROVED']),
            'transaction_id' => $capture['id'] ?? $orderId,
            'amount' => $capture['amount']['value'] ?? 0,
            'currency' => $capture['amount']['currency_code'] ?? 'USD',
            'metadata' => $customData,
            'raw' => $data,
        ];
    }

    /* -------------------------------------------------------------------------- */
    /*                              RAZORPAY METHODS                              */
    /* -------------------------------------------------------------------------- */

    /**
     * Create Razorpay Order.
     */
    public static function createRazorpayOrder(array $params): array
    {
        $keyId = self::getSetting('razorpay_key_id');
        $keySecret = self::getSetting('razorpay_key_secret');

        if (empty($keyId) || empty($keySecret)) {
            throw new \Exception('Razorpay API keys are not configured in Super Admin settings.');
        }

        $amountInSubunit = (int) round($params['amount'] * 100);
        $currency = strtoupper($params['currency'] ?? 'USD');

        $response = Http::withBasicAuth($keyId, $keySecret)->post('https://api.razorpay.com/v1/orders', [
            'amount' => $amountInSubunit,
            'currency' => $currency,
            'receipt' => 'rcpt_' . time() . '_' . $params['user_id'],
            'notes' => [
                'user_id' => $params['user_id'],
                'plan_slug' => $params['plan_slug'],
                'plan_name' => $params['plan_name'],
                'billing_cycle' => $params['billing_cycle'],
            ],
        ]);

        if (!$response->successful()) {
            Log::error('Razorpay Order Error:', $response->json() ?? ['body' => $response->body()]);
            throw new \Exception($response->json('error.description') ?? 'Failed to initialize Razorpay Order.');
        }

        return $response->json();
    }

    /**
     * Verify Razorpay Payment Signature.
     */
    public static function verifyRazorpaySignature(string $orderId, string $paymentId, string $signature): bool
    {
        $keySecret = self::getSetting('razorpay_key_secret');
        if (empty($keySecret)) {
            return false;
        }

        $generatedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $keySecret);
        return hash_equals($generatedSignature, $signature);
    }
}
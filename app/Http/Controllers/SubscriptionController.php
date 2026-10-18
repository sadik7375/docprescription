<?php

namespace App\Http\Controllers;

use App\Models\PricingPlan;
use App\Models\SubscriptionTransaction;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\SubscriptionLimitService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    /**
     * Display the Doctor Subscription & Plan Upgrade Dashboard.
     */
    public function index(): Response
    {
        $user = Auth::user();
        $plans = PricingPlan::where('is_active', true)->orderBy('sort_order', 'asc')->get();
        $summary = SubscriptionLimitService::getSubscriptionSummary($user);
        $publicConfig = PaymentService::getPublicConfig();

        $transactions = SubscriptionTransaction::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Subscription/Index', [
            'summary' => $summary,
            'plans' => $plans,
            'paymentConfig' => $publicConfig,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Initiate Stripe Checkout Session.
     */
    public function initiateStripe(Request $request)
    {
        $validated = $request->validate([
            'plan_slug' => 'required|string',
            'billing_cycle' => 'required|string|in:monthly,yearly',
        ]);

        $plan = PricingPlan::where('slug', $validated['plan_slug'])->where('is_active', true)->firstOrFail();
        $user = Auth::user();

        $amount = $validated['billing_cycle'] === 'yearly' ? $plan->annual_price : $plan->monthly_price;
        $currency = $plan->currency === '$' ? 'USD' : 'USD';

        try {
            $session = PaymentService::createStripeSession([
                'user_id' => $user->id,
                'user_email' => $user->email,
                'plan_slug' => $plan->slug,
                'plan_name' => $plan->name,
                'billing_cycle' => $validated['billing_cycle'],
                'amount' => $amount,
                'currency' => $currency,
                'success_url' => route('subscription.stripe.success'),
                'cancel_url' => route('subscription.index'),
            ]);

            return response()->json([
                'status' => 'success',
                'url' => $session['url'] ?? null,
                'id' => $session['id'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Handle Stripe Checkout Success Callback.
     */
    public function stripeSuccess(Request $request): RedirectResponse
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return redirect()->route('subscription.index')->with('error', 'Invalid Stripe session.');
        }

        try {
            $data = PaymentService::verifyStripeSession($sessionId);
            if ($data['paid']) {
                $user = Auth::user();
                $planSlug = $data['metadata']['plan_slug'] ?? 'pro';
                $planName = $data['metadata']['plan_name'] ?? 'Professional Practice';
                $billingCycle = $data['metadata']['billing_cycle'] ?? 'monthly';

                DB::transaction(function () use ($user, $planSlug, $planName, $billingCycle, $data, $sessionId) {
                    $endsAt = $billingCycle === 'yearly' ? now()->addYear() : now()->addMonth();

                    $user->update([
                        'subscription_plan' => $planSlug,
                        'subscription_status' => 'active',
                        'subscription_ends_at' => $endsAt,
                        'is_active' => true,
                    ]);

                    SubscriptionTransaction::updateOrCreate(
                        ['transaction_id' => $data['transaction_id'] ?? $sessionId],
                        [
                            'user_id' => $user->id,
                            'plan_slug' => $planSlug,
                            'plan_name' => $planName,
                            'billing_cycle' => $billingCycle,
                            'amount' => $data['amount'],
                            'currency' => $data['currency'],
                            'gateway' => 'stripe',
                            'payment_status' => 'completed',
                            'gateway_response' => $data['raw'],
                            'paid_at' => now(),
                        ]
                    );
                });

                return redirect()->route('subscription.index')->with('success', "Payment successful! Your {$planName} plan is now active.");
            }
        } catch (\Exception $e) {
            Log::error('Stripe Verification Error: ' . $e->getMessage());
        }

        return redirect()->route('subscription.index')->with('error', 'Payment verification failed.');
    }

    /**
     * Initiate PayPal Order.
     */
    public function initiatePayPal(Request $request)
    {
        $validated = $request->validate([
            'plan_slug' => 'required|string',
            'billing_cycle' => 'required|string|in:monthly,yearly',
        ]);

        $plan = PricingPlan::where('slug', $validated['plan_slug'])->where('is_active', true)->firstOrFail();
        $user = Auth::user();

        $amount = $validated['billing_cycle'] === 'yearly' ? $plan->annual_price : $plan->monthly_price;
        $currency = 'USD';

        try {
            $order = PaymentService::createPayPalOrder([
                'user_id' => $user->id,
                'plan_slug' => $plan->slug,
                'plan_name' => $plan->name,
                'billing_cycle' => $validated['billing_cycle'],
                'amount' => $amount,
                'currency' => $currency,
                'return_url' => route('subscription.paypal.success'),
                'cancel_url' => route('subscription.index'),
            ]);

            $approveUrl = collect($order['links'] ?? [])->firstWhere('rel', 'approve')['href'] ?? null;

            return response()->json([
                'status' => 'success',
                'order_id' => $order['id'] ?? null,
                'approve_url' => $approveUrl,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Handle PayPal Order Capture & Success.
     */
    public function paypalSuccess(Request $request): RedirectResponse
    {
        $token = $request->query('token'); // PayPal Order ID
        if (!$token) {
            return redirect()->route('subscription.index')->with('error', 'Invalid PayPal transaction.');
        }

        try {
            $data = PaymentService::capturePayPalOrder($token);
            if ($data['paid']) {
                $user = Auth::user();
                $planSlug = $data['metadata']['plan_slug'] ?? 'pro';
                $planName = $data['metadata']['plan_name'] ?? 'Professional Practice';
                $billingCycle = $data['metadata']['billing_cycle'] ?? 'monthly';

                DB::transaction(function () use ($user, $planSlug, $planName, $billingCycle, $data, $token) {
                    $endsAt = $billingCycle === 'yearly' ? now()->addYear() : now()->addMonth();

                    $user->update([
                        'subscription_plan' => $planSlug,
                        'subscription_status' => 'active',
                        'subscription_ends_at' => $endsAt,
                        'is_active' => true,
                    ]);

                    SubscriptionTransaction::updateOrCreate(
                        ['transaction_id' => $data['transaction_id'] ?? $token],
                        [
                            'user_id' => $user->id,
                            'plan_slug' => $planSlug,
                            'plan_name' => $planName,
                            'billing_cycle' => $billingCycle,
                            'amount' => $data['amount'],
                            'currency' => $data['currency'],
                            'gateway' => 'paypal',
                            'payment_status' => 'completed',
                            'gateway_response' => $data['raw'],
                            'paid_at' => now(),
                        ]
                    );
                });

                return redirect()->route('subscription.index')->with('success', "PayPal payment verified! Your {$planName} plan is now active.");
            }
        } catch (\Exception $e) {
            Log::error('PayPal Verification Error: ' . $e->getMessage());
        }

        return redirect()->route('subscription.index')->with('error', 'PayPal payment verification failed.');
    }

    /**
     * Create Razorpay Order.
     */
    public function createRazorpayOrder(Request $request)
    {
        $validated = $request->validate([
            'plan_slug' => 'required|string',
            'billing_cycle' => 'required|string|in:monthly,yearly',
        ]);

        $plan = PricingPlan::where('slug', $validated['plan_slug'])->where('is_active', true)->firstOrFail();
        $user = Auth::user();

        $amount = $validated['billing_cycle'] === 'yearly' ? $plan->annual_price : $plan->monthly_price;
        $currency = 'USD';

        try {
            $order = PaymentService::createRazorpayOrder([
                'user_id' => $user->id,
                'plan_slug' => $plan->slug,
                'plan_name' => $plan->name,
                'billing_cycle' => $validated['billing_cycle'],
                'amount' => $amount,
                'currency' => $currency,
            ]);

            return response()->json([
                'status' => 'success',
                'order' => $order,
                'plan' => $plan,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Verify and Capture Razorpay Payment.
     */
    public function verifyRazorpay(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'plan_slug' => 'required|string',
            'billing_cycle' => 'required|string',
        ]);

        $isValid = PaymentService::verifyRazorpaySignature(
            $validated['razorpay_order_id'],
            $validated['razorpay_payment_id'],
            $validated['razorpay_signature']
        );

        if (!$isValid) {
            return redirect()->route('subscription.index')->with('error', 'Razorpay signature verification failed.');
        }

        $user = Auth::user();
        $plan = PricingPlan::where('slug', $validated['plan_slug'])->first();
        $planName = $plan ? $plan->name : ucfirst($validated['plan_slug']);
        $amount = $plan ? ($validated['billing_cycle'] === 'yearly' ? $plan->annual_price : $plan->monthly_price) : 0;

        DB::transaction(function () use ($user, $validated, $planName, $amount) {
            $endsAt = $validated['billing_cycle'] === 'yearly' ? now()->addYear() : now()->addMonth();

            $user->update([
                'subscription_plan' => $validated['plan_slug'],
                'subscription_status' => 'active',
                'subscription_ends_at' => $endsAt,
                'is_active' => true,
            ]);

            SubscriptionTransaction::create([
                'user_id' => $user->id,
                'plan_slug' => $validated['plan_slug'],
                'plan_name' => $planName,
                'billing_cycle' => $validated['billing_cycle'],
                'amount' => $amount,
                'currency' => 'USD',
                'gateway' => 'razorpay',
                'transaction_id' => $validated['razorpay_payment_id'],
                'payment_status' => 'completed',
                'gateway_response' => $validated,
                'paid_at' => now(),
            ]);
        });

        return redirect()->route('subscription.index')->with('success', "Payment successful! Your {$planName} plan is now active.");
    }

    /**
     * Submit Bank Wire / Offline Payment Proof.
     */
    public function submitManualPayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_slug' => 'required|string',
            'billing_cycle' => 'required|string|in:monthly,yearly',
            'transaction_id' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $plan = PricingPlan::where('slug', $validated['plan_slug'])->firstOrFail();
        $amount = $validated['billing_cycle'] === 'yearly' ? $plan->annual_price : $plan->monthly_price;

        SubscriptionTransaction::create([
            'user_id' => $user->id,
            'plan_slug' => $plan->slug,
            'plan_name' => $plan->name,
            'billing_cycle' => $validated['billing_cycle'],
            'amount' => $amount,
            'currency' => 'USD',
            'gateway' => 'bank_wire_transfer',
            'transaction_id' => $validated['transaction_id'],
            'payment_status' => 'pending',
            'gateway_response' => ['notes' => $validated['notes'] ?? null],
            'paid_at' => null,
        ]);

        return redirect()->route('subscription.index')->with('success', 'Wire transfer reference submitted! Administrator will verify and activate your subscription shortly.');
    }
}
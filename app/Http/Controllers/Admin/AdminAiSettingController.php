<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;

class AdminAiSettingController extends Controller
{
    /**
     * Display the AI configuration page in Super Admin.
     */
    public function index(): Response
    {
        // Fetch exact packages strictly from the Pricing Plans database table
        $packages = PricingPlan::orderBy('sort_order', 'asc')->get()->map(function ($plan) {
            return [
                'id' => $plan->slug,
                'name' => $plan->name,
                'description' => $plan->tagline ?: 'Active subscription plan',
                'badge' => ($plan->currency ?? '$') . number_format($plan->monthly_price, 0) . '/mo',
            ];
        })->toArray();

        $validSlugs = array_column($packages, 'id');
        $rawAllowed = SystemSetting::get('ai_allowed_plans', ['pro', 'enterprise']);
        $cleanedAllowed = is_array($rawAllowed) ? array_values(array_intersect($rawAllowed, $validSlugs)) : ['pro', 'enterprise'];

        $settings = [
            'ai_enabled' => SystemSetting::get('ai_enabled', true),
            'ai_provider' => SystemSetting::get('ai_provider', 'gemini'), // 'gemini' | 'openai' | 'offline'
            
            // API Keys
            'gemini_api_key' => SystemSetting::get('gemini_api_key', env('GEMINI_API_KEY', '')),
            'openai_api_key' => SystemSetting::get('openai_api_key', env('OPENAI_API_KEY', '')),

            // Package Permissions (Which plans can use AI)
            'ai_allowed_plans' => $cleanedAllowed,
        ];

        return Inertia::render('Admin/AiSettings/Index', [
            'settings' => $settings,
            'packages' => $packages,
        ]);
    }

    /**
     * Update AI configuration settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ai_enabled' => 'required|boolean',
            'ai_provider' => 'required|in:gemini,openai,offline',
            'gemini_api_key' => 'nullable|string',
            'openai_api_key' => 'nullable|string',
            'ai_allowed_plans' => 'nullable|array',
            'ai_allowed_plans.*' => 'string',
        ]);

        SystemSetting::set('ai_enabled', (bool) $validated['ai_enabled'], 'ai', 'boolean', 'Master toggle for AI features');
        SystemSetting::set('ai_provider', $validated['ai_provider'], 'ai', 'string', 'Active AI Provider (gemini, openai, offline)');
        
        if (isset($validated['gemini_api_key'])) {
            SystemSetting::set('gemini_api_key', trim($validated['gemini_api_key']), 'ai', 'string', 'Google Gemini API Key');
        }

        if (isset($validated['openai_api_key'])) {
            SystemSetting::set('openai_api_key', trim($validated['openai_api_key']), 'ai', 'string', 'OpenAI API Key');
        }

        // Save Allowed Subscription Plans for AI
        $allowedPlans = $validated['ai_allowed_plans'] ?? [];
        SystemSetting::set('ai_allowed_plans', $allowedPlans, 'ai', 'json', 'Subscription plans allowed to use AI');

        return back()->with('success', 'AI settings and plan access permissions updated successfully.');
    }

    /**
     * Live Test Connection with Google Gemini or OpenAI API Key.
     */
    public function testConnection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider' => 'required|in:gemini,openai',
            'api_key' => 'nullable|string',
        ]);

        $provider = $validated['provider'];
        $apiKey = !empty($validated['api_key'])
            ? trim($validated['api_key'])
            : ($provider === 'gemini'
                ? SystemSetting::get('gemini_api_key', env('GEMINI_API_KEY', ''))
                : SystemSetting::get('openai_api_key', env('OPENAI_API_KEY', '')));

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => "No API Key provided for {$provider}. Please enter a valid API Key first.",
            ], 422);
        }

        $startTime = microtime(true);

        try {
            if ($provider === 'gemini') {
                $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
                $response = Http::timeout(10)->post($endpoint, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => 'Respond with strictly: "Doc Helper AI: Google Gemini connection verified successfully."']
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'maxOutputTokens' => 60,
                    ],
                ]);

                $latency = round((microtime(true) - $startTime) * 1000);

                if ($response->successful()) {
                    $json = $response->json();
                    $reply = $json['candidates'][0]['content']['parts'][0]['text'] ?? 'Connection successful.';
                    return response()->json([
                        'success' => true,
                        'provider' => 'Google Gemini (gemini-1.5-flash)',
                        'latency_ms' => $latency,
                        'message' => trim($reply),
                    ]);
                } else {
                    $errorMsg = $response->json()['error']['message'] ?? $response->body();
                    return response()->json([
                        'success' => false,
                        'provider' => 'Google Gemini',
                        'latency_ms' => $latency,
                        'message' => "Google Gemini API Error (Status {$response->status()}): {$errorMsg}",
                    ], 400);
                }
            } else {
                // OpenAI Test
                $endpoint = 'https://api.openai.com/v1/chat/completions';
                $response = Http::timeout(10)->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])->post($endpoint, [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'user', 'content' => 'Respond with strictly: "Doc Helper AI: OpenAI connection verified successfully."']
                    ],
                    'max_tokens' => 40,
                    'temperature' => 0.1,
                ]);

                $latency = round((microtime(true) - $startTime) * 1000);

                if ($response->successful()) {
                    $json = $response->json();
                    $reply = $json['choices'][0]['message']['content'] ?? 'Connection successful.';
                    return response()->json([
                        'success' => true,
                        'provider' => 'OpenAI (gpt-4o-mini)',
                        'latency_ms' => $latency,
                        'message' => trim($reply),
                    ]);
                } else {
                    $errorMsg = $response->json()['error']['message'] ?? $response->body();
                    return response()->json([
                        'success' => false,
                        'provider' => 'OpenAI',
                        'latency_ms' => $latency,
                        'message' => "OpenAI API Error (Status {$response->status()}): {$errorMsg}",
                    ], 400);
                }
            }
        } catch (\Throwable $e) {
            $latency = round((microtime(true) - $startTime) * 1000);
            return response()->json([
                'success' => false,
                'provider' => ucfirst($provider),
                'latency_ms' => $latency,
                'message' => "Connection Exception: " . $e->getMessage(),
            ], 500);
        }
    }
}

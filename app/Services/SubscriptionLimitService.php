<?php

namespace App\Services;

use App\Models\Chamber;
use App\Models\User;
use Carbon\Carbon;

class SubscriptionLimitService
{
    /**
     * Check if user's subscription is currently active or on a valid trial.
     */
    public static function isSubscriptionActive(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Check if suspended
        if (!$user->is_active || $user->subscription_status === 'suspended') {
            return false;
        }

        // Lifetime plan is always active
        if ($user->subscription_plan === 'lifetime') {
            return true;
        }

        // Check active paid subscription
        if ($user->subscription_status === 'active') {
            if ($user->subscription_ends_at && Carbon::parse($user->subscription_ends_at)->isPast()) {
                return false;
            }
            return true;
        }

        // Check free trial
        if ($user->subscription_status === 'trialing' || $user->subscription_plan === 'free_trial') {
            if ($user->trial_ends_at && Carbon::parse($user->trial_ends_at)->isPast()) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Get the max allowed chambers for the doctor.
     * Solo: 1, Pro: Unlimited (-1), Enterprise/Lifetime/Trial: Unlimited (-1)
     */
    public static function getMaxChambers(User $user): int
    {
        if ($user->isSuperAdmin()) {
            return -1;
        }

        // During valid trial, allow unlimited
        if ($user->subscription_plan === 'free_trial' || $user->subscription_status === 'trialing') {
            return -1;
        }

        return match ($user->subscription_plan) {
            'solo' => 1,
            'pro', 'enterprise', 'lifetime' => -1,
            default => 1,
        };
    }

    /**
     * Check if doctor can create another chamber.
     */
    public static function canCreateChamber(User $user): array
    {
        if ($user->isSuperAdmin()) {
            return ['allowed' => true];
        }

        if (!self::isSubscriptionActive($user)) {
            return [
                'allowed' => false,
                'reason' => 'Your subscription or trial has expired. Please upgrade or renew your plan to create chambers.',
                'code' => 'SUBSCRIPTION_EXPIRED'
            ];
        }

        $max = self::getMaxChambers($user);
        if ($max === -1) {
            return ['allowed' => true];
        }

        $currentCount = Chamber::where('doctor_id', $user->id)->count();
        if ($currentCount >= $max) {
            return [
                'allowed' => false,
                'reason' => "Your Solo Physician plan allows a maximum of {$max} chamber. Please upgrade to Professional Practice or Enterprise for unlimited chambers.",
                'code' => 'CHAMBER_LIMIT_REACHED',
                'current' => $currentCount,
                'max' => $max,
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Get the max allowed staff accounts for the doctor.
     * Solo: 0, Pro: 3, Enterprise/Lifetime: Unlimited (-1), Trial: 3
     */
    public static function getMaxStaff(User $user): int
    {
        if ($user->isSuperAdmin()) {
            return -1;
        }

        if ($user->subscription_plan === 'free_trial' || $user->subscription_status === 'trialing') {
            return 3;
        }

        return match ($user->subscription_plan) {
            'solo' => 0,
            'pro' => 3,
            'enterprise', 'lifetime' => -1,
            default => 0,
        };
    }

    /**
     * Check if doctor can create another staff account.
     */
    public static function canCreateStaff(User $user): array
    {
        if ($user->isSuperAdmin()) {
            return ['allowed' => true];
        }

        if (!self::isSubscriptionActive($user)) {
            return [
                'allowed' => false,
                'reason' => 'Your subscription or trial has expired. Please upgrade to manage staff members.',
                'code' => 'SUBSCRIPTION_EXPIRED'
            ];
        }

        $max = self::getMaxStaff($user);

        if ($max === 0) {
            return [
                'allowed' => false,
                'reason' => 'Staff and receptionist accounts are not included in the Solo Physician plan. Please upgrade to Professional Practice (up to 3 staff) or Enterprise (unlimited staff).',
                'code' => 'STAFF_NOT_INCLUDED',
                'current' => 0,
                'max' => 0,
            ];
        }

        if ($max === -1) {
            return ['allowed' => true];
        }

        $currentCount = User::where('role', 'staff')
            ->whereHas('staffProfile', function ($q) use ($user) {
                $q->where('doctor_id', $user->id);
            })->count();

        if ($currentCount >= $max) {
            return [
                'allowed' => false,
                'reason' => "You have reached your limit of {$max} staff accounts for the Professional Practice plan. Upgrade to Enterprise for unlimited staff.",
                'code' => 'STAFF_LIMIT_REACHED',
                'current' => $currentCount,
                'max' => $max,
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Check if doctor can access AI Assistant.
     */
    public static function canAccessAi(User $user): array
    {
        if ($user->isSuperAdmin()) {
            return ['allowed' => true];
        }

        if (!self::isSubscriptionActive($user)) {
            return [
                'allowed' => false,
                'reason' => 'Your subscription or trial has expired. Please subscribe to use the AI Clinical Copilot.',
                'code' => 'SUBSCRIPTION_EXPIRED'
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Get complete subscription & usage summary for doctor.
     */
    public static function getSubscriptionSummary(User $user): array
    {
        $chambersCount = Chamber::where('doctor_id', $user->id)->count();
        $staffCount = User::where('role', 'staff')
            ->whereHas('staffProfile', function ($q) use ($user) {
                $q->where('doctor_id', $user->id);
            })->count();

        $maxChambers = self::getMaxChambers($user);
        $maxStaff = self::getMaxStaff($user);
        $isActive = self::isSubscriptionActive($user);

        $daysRemaining = null;
        if ($user->subscription_status === 'trialing' && $user->trial_ends_at) {
            $daysRemaining = max(0, (int) ceil(now()->diffInDays(Carbon::parse($user->trial_ends_at), false)));
        } elseif ($user->subscription_status === 'active' && $user->subscription_ends_at) {
            $daysRemaining = max(0, (int) ceil(now()->diffInDays(Carbon::parse($user->subscription_ends_at), false)));
        }

        return [
            'plan' => $user->subscription_plan ?? 'free_trial',
            'status' => $user->subscription_status ?? 'trialing',
            'is_active' => $isActive,
            'trial_ends_at' => $user->trial_ends_at ? $user->trial_ends_at->toISOString() : null,
            'subscription_ends_at' => $user->subscription_ends_at ? $user->subscription_ends_at->toISOString() : null,
            'days_remaining' => $daysRemaining,
            'usage' => [
                'chambers' => [
                    'current' => $chambersCount,
                    'max' => $maxChambers,
                    'is_unlimited' => $maxChambers === -1,
                    'can_create' => self::canCreateChamber($user)['allowed'],
                ],
                'staff' => [
                    'current' => $staffCount,
                    'max' => $maxStaff,
                    'is_unlimited' => $maxStaff === -1,
                    'can_create' => self::canCreateStaff($user)['allowed'],
                ],
            ]
        ];
    }
}
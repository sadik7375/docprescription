<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'avatar',
        'is_active',
        'subscription_plan',
        'subscription_status',
        'trial_ends_at',
        'subscription_ends_at',
        'admin_notes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
        ];
    }

    public function doctorProfile(): HasOne
    {
        return $this->hasOne(DoctorProfile::class);
    }

    public function patientProfile(): HasOne
    {
        return $this->hasOne(PatientProfile::class);
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function doctorStaff()
    {
        return $this->hasMany(StaffProfile::class, 'doctor_id');
    }

    public function chambers()
    {
        return $this->hasMany(Chamber::class, 'doctor_id');
    }

    public function defaultChamber()
    {
        return $this->hasOne(Chamber::class, 'doctor_id')->where('is_default', true);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'doctor_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isPatient(): bool
    {
        return $this->role === 'patient';
    }

    /**
     * Get the associated doctor user ID for scoping data queries.
     */
    public function getDoctorId(): int
    {
        if ($this->isStaff()) {
            return $this->staffProfile?->doctor_id ?? $this->id;
        }

        return $this->id;
    }

    /**
     * Check if user has specific feature permission.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin() || $this->isDoctor()) {
            return true;
        }

        if ($this->isStaff()) {
            return $this->staffProfile?->hasPermission($permission) ?? false;
        }

        return false;
    }

    /**
     * Get array of permissions for the active user.
     */
    public function getStaffPermissions(): array
    {
        if ($this->isSuperAdmin() || $this->isDoctor()) {
            return ['*'];
        }

        if ($this->isStaff()) {
            return $this->staffProfile?->permissions ?? [];
        }

        return [];
    }

    public function isTrialing(): bool
    {
        return $this->subscription_status === 'trialing' && 
               $this->trial_ends_at && 
               $this->trial_ends_at->isFuture();
    }

    public function isSubscriptionActive(): bool
    {
        return in_array($this->subscription_status, ['active', 'trialing']) && $this->is_active;
    }

    public function daysLeftInTrial(): int
    {
        if (!$this->trial_ends_at) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($this->trial_ends_at, false));
    }

    /**
     * Check if this user is allowed to use AI features based on subscription package & global settings.
     */
    public function canUseAi(): bool
    {
        $aiEnabled = (bool) \App\Models\SystemSetting::get('ai_enabled', true);
        if (!$aiEnabled) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        // If staff, inherit the permission from their associated doctor
        if ($this->isStaff()) {
            $doctorUser = $this->staffProfile?->doctor;
            return $doctorUser ? $doctorUser->canUseAi() : false;
        }

        // Lifetime plan doctors have full access
        if ($this->subscription_plan === 'lifetime') {
            return true;
        }

        // Active Free Trial doctors get AI access
        if ($this->isTrialing() || $this->subscription_plan === 'free_trial' || $this->subscription_status === 'trialing') {
            return true;
        }

        $rawAllowed = \App\Models\SystemSetting::get('ai_allowed_plans', ['pro', 'enterprise']);
        if (is_string($rawAllowed)) {
            $decoded = json_decode($rawAllowed, true);
            $rawAllowed = is_array($decoded) ? $decoded : [$rawAllowed];
        }
        if (!is_array($rawAllowed) || empty($rawAllowed)) {
            $rawAllowed = ['pro', 'enterprise'];
        }

        $validSlugs = \App\Models\PricingPlan::where('is_active', true)->pluck('slug')->toArray();
        $allowedPlans = !empty($validSlugs) ? array_values(array_intersect($rawAllowed, $validSlugs)) : $rawAllowed;

        if (empty($allowedPlans)) {
            $allowedPlans = ['pro', 'enterprise'];
        }

        $userPlan = $this->subscription_plan ?: 'solo';
        return in_array($userPlan, $allowedPlans, true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_code',
        'doctor_id',
        'user_id',
        'name',
        'phone',
        'email',
        'gender',
        'age',
        'dob',
        'blood_group',
        'marital_status',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'allergies',
        'medical_history',
        'note',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'dob' => 'date',
        'age' => 'integer',
    ];

    protected $appends = [
        'has_portal_account',
    ];

    /**
     * Boot function to generate automatic unique patient_code
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {
            if (empty($patient->patient_code)) {
                $yearMonth = date('ym');
                $lastPatient = static::where('patient_code', 'like', "PID-{$yearMonth}%")
                    ->orderByDesc('id')
                    ->first();

                if ($lastPatient && preg_match('/PID-' . $yearMonth . '(\d+)/', $lastPatient->patient_code, $matches)) {
                    $nextSeq = intval($matches[1]) + 1;
                } else {
                    $nextSeq = 1;
                }

                $patient->patient_code = sprintf('PID-%s%04d', $yearMonth, $nextSeq);
            }
        });
    }

    public function getHasPortalAccountAttribute(): bool
    {
        return !empty($this->user_id);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function chambers()
    {
        return $this->belongsToMany(Chamber::class, 'patient_chamber')
            ->withPivot('visit_count', 'last_visited_at')
            ->withTimestamps();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Chamber extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'name',
        'branch',
        'address',
        'phone',
        'consultation_fee',
        'followup_fee',
        'visiting_days',
        'visiting_time',
        'room_no',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'visiting_days' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'consultation_fee' => 'float',
        'followup_fee' => 'float',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class, 'patient_chamber')
            ->withPivot('visit_count', 'last_visited_at')
            ->withTimestamps();
    }
}

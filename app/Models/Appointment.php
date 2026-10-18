<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'chamber_id',
        'patient_id',
        'serial_no',
        'token_code',
        'appointment_date',
        'appointment_time',
        'type',
        'status',
        'fee_amount',
        'discount_type',
        'discount_value',
        'payable_amount',
        'payment_status',
        'vitals',
        'problem',
        'doctor_notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'vitals' => 'array',
        'fee_amount' => 'float',
        'discount_value' => 'float',
        'payable_amount' => 'float',
        'serial_no' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($appointment) {
            if (empty($appointment->serial_no)) {
                $maxSerial = static::where('chamber_id', $appointment->chamber_id)
                    ->whereDate('appointment_date', $appointment->appointment_date)
                    ->max('serial_no');

                $nextSerial = ($maxSerial ?? 0) + 1;
                $appointment->serial_no = $nextSerial;
            }

            if (empty($appointment->token_code)) {
                $appointment->token_code = sprintf('TK-%02d', $appointment->serial_no);
            }
        });
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function chamber(): BelongsTo
    {
        return $this->belongsTo(Chamber::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}

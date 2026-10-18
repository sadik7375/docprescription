<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'chamber_id',
        'patient_id',
        'appointment_id',
        'prescription_code',
        'prescription_date',
        'vitals',
        'chief_complaints',
        'on_examinations',
        'diagnoses',
        'investigations',
        'clinical_advices',
        'follow_up_date',
        'follow_up_notes',
        'doctor_notes',
        'status',
    ];

    protected $casts = [
        'prescription_date' => 'date',
        'vitals' => 'array',
        'chief_complaints' => 'array',
        'on_examinations' => 'array',
        'diagnoses' => 'array',
        'investigations' => 'array',
        'clinical_advices' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($prescription) {
            if (empty($prescription->prescription_code)) {
                $datePrefix = date('Ymd');
                $countToday = static::whereDate('created_at', today())->count() + 1;
                $prescription->prescription_code = sprintf('RX-%s-%04d', $datePrefix, $countToday);
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

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class)->orderBy('item_order');
    }
}


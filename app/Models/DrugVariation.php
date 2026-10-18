<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrugVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'drug_id',
        'type',
        'strength',
        'dose',
        'duration',
        'advice',
    ];

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }
}

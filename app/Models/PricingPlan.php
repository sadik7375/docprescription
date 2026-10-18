<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'tagline',
        'currency',
        'monthly_price',
        'annual_price',
        'annual_discount_text',
        'badge_text',
        'is_featured',
        'is_active',
        'button_text',
        'button_link',
        'features',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'monthly_price' => 'float',
        'annual_price' => 'float',
        'sort_order' => 'integer',
    ];

    /**
     * Scope active plans for public display.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'doctor_id',
        'chamber_id',
        'chamber_ids',
        'designation',
        'permissions',
        'is_active',
    ];

    protected $casts = [
        'chamber_ids' => 'array',
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function chamber(): BelongsTo
    {
        return $this->belongsTo(Chamber::class);
    }

    /**
     * Get array of assigned chamber IDs.
     * Empty array means assigned to all chambers.
     */
    public function assignedChamberIds(): array
    {
        if (!empty($this->chamber_ids) && is_array($this->chamber_ids)) {
            return array_values(array_filter(array_map('intval', $this->chamber_ids)));
        }

        if ($this->chamber_id) {
            return [(int) $this->chamber_id];
        }

        return [];
    }

    /**
     * Check if staff can access a specific chamber.
     */
    public function canAccessChamber(?int $chamberId): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $assigned = $this->assignedChamberIds();
        if (empty($assigned)) {
            return true;
        }

        return $chamberId ? in_array((int) $chamberId, $assigned, true) : true;
    }

    /**
     * Check if this staff profile has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $perms = $this->permissions ?? [];
        return in_array('*', $perms, true) || in_array($permission, $perms, true);
    }
}
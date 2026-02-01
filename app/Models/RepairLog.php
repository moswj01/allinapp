<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairLog extends Model
{
    protected $fillable = [
        'repair_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function repair(): BelongsTo
    {
        return $this->belongsTo(Repair::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Action constants
    public const ACTION_CREATED = 'created';
    public const ACTION_STATUS_CHANGED = 'status_changed';
    public const ACTION_ASSIGNED = 'assigned';
    public const ACTION_QUOTED = 'quoted';
    public const ACTION_PART_REQUESTED = 'part_requested';
    public const ACTION_PART_ISSUED = 'part_issued';
    public const ACTION_COMPLETED = 'completed';
    public const ACTION_DELIVERED = 'delivered';
    public const ACTION_PAYMENT = 'payment';
    public const ACTION_UPDATED = 'updated';
}

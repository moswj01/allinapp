<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'link',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    // Type constants
    public const TYPE_INFO = 'info';
    public const TYPE_SUCCESS = 'success';
    public const TYPE_WARNING = 'warning';
    public const TYPE_ERROR = 'error';

    // Notification types
    public const REPAIR_ASSIGNED = 'repair_assigned';
    public const REPAIR_STATUS_CHANGED = 'repair_status_changed';
    public const REPAIR_COMPLETED = 'repair_completed';
    public const LOW_STOCK_ALERT = 'low_stock_alert';
    public const REORDER_ALERT = 'reorder_alert';
    public const PAYMENT_RECEIVED = 'payment_received';
    public const AR_OVERDUE = 'ar_overdue';
    public const TRANSFER_RECEIVED = 'transfer_received';
    public const PO_APPROVED = 'po_approved';

    public static function send(int $userId, string $type, string $title, string $message, ?array $data = null, ?string $link = null): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'link' => $link,
        ]);
    }
}

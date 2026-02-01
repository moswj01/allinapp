<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairCommunication extends Model
{
    protected $fillable = [
        'repair_id',
        'user_id',
        'channel',
        'direction',
        'message',
        'response',
        'response_at',
        'status',
        'external_ref',
    ];

    protected $casts = [
        'response_at' => 'datetime',
    ];

    public function repair(): BelongsTo
    {
        return $this->belongsTo(Repair::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Channel constants
    public const CHANNEL_LINE = 'line';
    public const CHANNEL_FACEBOOK = 'facebook';
    public const CHANNEL_SMS = 'sms';
    public const CHANNEL_PHONE = 'phone';
    public const CHANNEL_EMAIL = 'email';

    // Direction constants
    public const DIRECTION_OUTGOING = 'outgoing';
    public const DIRECTION_INCOMING = 'incoming';
}

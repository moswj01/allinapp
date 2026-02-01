<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = [
        'branch_id',
        'group',
        'key',
        'value',
        'type',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getTypedValueAttribute()
    {
        return match ($this->type) {
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->value, true),
            'array' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    public static function get(string $key, $default = null, ?int $branchId = null)
    {
        $setting = self::where('key', $key)
            ->where('branch_id', $branchId)
            ->first();

        return $setting ? $setting->typed_value : $default;
    }

    public static function set(string $key, $value, ?string $type = null, ?string $group = null, ?int $branchId = null): self
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
            $type = $type ?? 'json';
        }

        return self::updateOrCreate(
            ['key' => $key, 'branch_id' => $branchId],
            [
                'value' => $value,
                'type' => $type ?? 'string',
                'group' => $group ?? 'general',
            ]
        );
    }

    public static function getByGroup(string $group, ?int $branchId = null): array
    {
        return self::where('group', $group)
            ->where('branch_id', $branchId)
            ->pluck('value', 'key')
            ->toArray();
    }

    // Common setting keys
    public const COMPANY_NAME = 'company_name';
    public const COMPANY_ADDRESS = 'company_address';
    public const COMPANY_PHONE = 'company_phone';
    public const COMPANY_EMAIL = 'company_email';
    public const COMPANY_TAX_ID = 'company_tax_id';
    public const COMPANY_LOGO = 'company_logo';

    public const DEFAULT_TAX_RATE = 'default_tax_rate';
    public const DEFAULT_WARRANTY_DAYS = 'default_warranty_days';
    public const LOW_STOCK_THRESHOLD = 'low_stock_threshold';

    public const RECEIPT_HEADER = 'receipt_header';
    public const RECEIPT_FOOTER = 'receipt_footer';
    public const QUOTATION_TERMS = 'quotation_terms';

    public const LINE_NOTIFY_TOKEN = 'line_notify_token';
    public const SMS_API_KEY = 'sms_api_key';
}

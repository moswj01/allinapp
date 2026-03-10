<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
    ];

    protected $casts = [
        'value' => 'string',
    ];

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

    /**
     * Get a system setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->typed_value : $default;
    }

    /**
     * Set a system setting value
     */
    public static function set(string $key, $value, ?string $type = null, ?string $group = null): self
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
            $type = $type ?? 'json';
        }

        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $value,
                'type' => $type ?? 'string',
                'group' => $group ?? 'general',
            ]
        );
    }

    /**
     * Get all settings in a group as key => value array
     */
    public static function getByGroup(string $group): array
    {
        return self::where('group', $group)
            ->get()
            ->mapWithKeys(fn($s) => [$s->key => $s->typed_value])
            ->toArray();
    }

    // Payment setting keys
    public const PAYMENT_BANK_NAME = 'payment_bank_name';
    public const PAYMENT_ACCOUNT_NUMBER = 'payment_account_number';
    public const PAYMENT_ACCOUNT_NAME = 'payment_account_name';
    public const PAYMENT_PROMPTPAY = 'payment_promptpay';
    public const PAYMENT_PROMPTPAY_NAME = 'payment_promptpay_name';
    public const PAYMENT_QRCODE_URL = 'payment_qrcode_url';
    public const PAYMENT_NOTE = 'payment_note';
    public const PAYMENT_LINE_ID = 'payment_line_id';
}

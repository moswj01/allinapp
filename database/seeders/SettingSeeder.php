<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Company Settings
            ['group' => 'company', 'key' => Setting::COMPANY_NAME, 'value' => 'All In Mobile', 'type' => 'string'],
            ['group' => 'company', 'key' => Setting::COMPANY_ADDRESS, 'value' => 'กรุงเทพมหานคร', 'type' => 'string'],
            ['group' => 'company', 'key' => Setting::COMPANY_PHONE, 'value' => '02-xxx-xxxx', 'type' => 'string'],
            ['group' => 'company', 'key' => Setting::COMPANY_EMAIL, 'value' => 'info@allinmobile.com', 'type' => 'string'],
            ['group' => 'company', 'key' => Setting::COMPANY_TAX_ID, 'value' => '', 'type' => 'string'],

            // Default Settings
            ['group' => 'defaults', 'key' => Setting::DEFAULT_TAX_RATE, 'value' => '7', 'type' => 'float'],
            ['group' => 'defaults', 'key' => Setting::DEFAULT_WARRANTY_DAYS, 'value' => '30', 'type' => 'integer'],
            ['group' => 'defaults', 'key' => Setting::LOW_STOCK_THRESHOLD, 'value' => '5', 'type' => 'integer'],

            // Receipt Settings
            ['group' => 'receipt', 'key' => Setting::RECEIPT_HEADER, 'value' => 'ขอบคุณที่ใช้บริการ', 'type' => 'string'],
            ['group' => 'receipt', 'key' => Setting::RECEIPT_FOOTER, 'value' => 'กรุณาเก็บใบเสร็จไว้เป็นหลักฐาน', 'type' => 'string'],
            ['group' => 'receipt', 'key' => Setting::QUOTATION_TERMS, 'value' => 'ใบเสนอราคานี้มีอายุ 7 วัน', 'type' => 'string'],

            // Integration Settings
            ['group' => 'integration', 'key' => Setting::LINE_NOTIFY_TOKEN, 'value' => '', 'type' => 'string'],
            ['group' => 'integration', 'key' => Setting::SMS_API_KEY, 'value' => '', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key'], 'branch_id' => null],
                $setting
            );
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $groups = [
            'company' => 'ข้อมูลบริษัท',
            'defaults' => 'ค่าเริ่มต้น',
            'receipt' => 'ใบเสร็จ / เอกสาร',
            'integrations' => 'การเชื่อมต่อ',
        ];

        $settings = Setting::whereNull('branch_id')->get()->groupBy('group');

        $settingLabels = [
            'company_name' => 'ชื่อบริษัท',
            'company_address' => 'ที่อยู่',
            'company_phone' => 'เบอร์โทร',
            'company_email' => 'อีเมล',
            'company_tax_id' => 'เลขผู้เสียภาษี',
            'company_logo' => 'โลโก้ (URL)',
            'default_tax_rate' => 'อัตราภาษีเริ่มต้น (%)',
            'default_warranty_days' => 'วันรับประกันเริ่มต้น (วัน)',
            'low_stock_threshold' => 'แจ้งเตือนสต๊อกต่ำ (ชิ้น)',
            'receipt_header' => 'หัวใบเสร็จ',
            'receipt_footer' => 'ท้ายใบเสร็จ',
            'quotation_terms' => 'เงื่อนไขใบเสนอราคา',
            'line_notify_token' => 'LINE Notify Token',
            'sms_api_key' => 'SMS API Key',
        ];

        return view('settings.index', compact('groups', 'settings', 'settingLabels'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->input('settings') as $key => $value) {
            $existing = Setting::where('key', $key)->whereNull('branch_id')->first();
            $type = $existing->type ?? 'string';
            $group = $existing->group ?? 'general';

            Setting::set($key, $value, $type, $group, null);
        }

        return redirect()->route('settings.index')
            ->with('success', 'บันทึกการตั้งค่าเรียบร้อย');
    }
}

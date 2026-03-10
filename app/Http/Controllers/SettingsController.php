<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * All setting definitions grouped
     */
    private function getSettingDefinitions(): array
    {
        return [
            'company' => [
                'company_name'    => ['label' => 'ชื่อบริษัท', 'type' => 'string'],
                'company_address' => ['label' => 'ที่อยู่', 'type' => 'string', 'textarea' => true],
                'company_phone'   => ['label' => 'เบอร์โทร', 'type' => 'string'],
                'company_email'   => ['label' => 'อีเมล', 'type' => 'string'],
                'company_tax_id'  => ['label' => 'เลขผู้เสียภาษี', 'type' => 'string'],
                'company_logo'    => ['label' => 'โลโก้ (URL)', 'type' => 'string'],
            ],
            'defaults' => [
                'default_tax_rate'       => ['label' => 'อัตราภาษีเริ่มต้น (%)', 'type' => 'float'],
                'default_warranty_days'  => ['label' => 'วันรับประกันเริ่มต้น (วัน)', 'type' => 'integer'],
                'low_stock_threshold'    => ['label' => 'แจ้งเตือนสต๊อกต่ำ (ชิ้น)', 'type' => 'integer'],
            ],
            'receipt' => [
                'receipt_header'   => ['label' => 'หัวใบเสร็จ', 'type' => 'string', 'textarea' => true],
                'receipt_footer'   => ['label' => 'ท้ายใบเสร็จ', 'type' => 'string', 'textarea' => true],
                'quotation_terms'  => ['label' => 'เงื่อนไขใบเสนอราคา', 'type' => 'string', 'textarea' => true],
            ],
            'integrations' => [
                'line_notify_token' => ['label' => 'LINE Notify Token', 'type' => 'string'],
                'sms_api_key'       => ['label' => 'SMS API Key', 'type' => 'string'],
            ],
        ];
    }

    public function index()
    {
        $groups = [
            'company' => 'ข้อมูลบริษัท',
            'defaults' => 'ค่าเริ่มต้น',
            'receipt' => 'ใบเสร็จ / เอกสาร',
            'integrations' => 'การเชื่อมต่อ',
        ];

        $definitions = $this->getSettingDefinitions();

        // Load existing values from DB as key => value (tenant-scoped via model)
        $existingSettings = Setting::whereNull('branch_id')
            ->get()
            ->pluck('value', 'key')
            ->toArray();

        return view('settings.index', compact('groups', 'definitions', 'existingSettings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        $definitions = $this->getSettingDefinitions();

        // Build flat key => definition map
        $allDefs = [];
        foreach ($definitions as $group => $fields) {
            foreach ($fields as $key => $def) {
                $allDefs[$key] = array_merge($def, ['group' => $group]);
            }
        }

        foreach ($request->input('settings') as $key => $value) {
            $def = $allDefs[$key] ?? null;
            $type = $def['type'] ?? 'string';
            $group = $def['group'] ?? 'general';

            Setting::set($key, $value ?? '', $type, $group, null);
        }

        return redirect()->route('settings.index')
            ->with('success', 'บันทึกการตั้งค่าเรียบร้อย');
    }
}

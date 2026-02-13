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

        return view('settings.index', compact('groups', 'settings'));
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

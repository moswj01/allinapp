<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index()
    {
        $payment = SystemSetting::getByGroup('payment');

        return view('superadmin.settings.index', compact('payment'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'payment_bank_name' => 'nullable|string|max:100',
            'payment_account_number' => 'nullable|string|max:50',
            'payment_account_name' => 'nullable|string|max:100',
            'payment_promptpay' => 'nullable|string|max:20',
            'payment_promptpay_name' => 'nullable|string|max:100',
            'payment_qrcode_url' => 'nullable|string|max:500',
            'payment_note' => 'nullable|string|max:1000',
            'payment_line_id' => 'nullable|string|max:100',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value ?? '', 'string', 'payment');
        }

        return back()->with('success', 'บันทึกการตั้งค่าการชำระเงินเรียบร้อย');
    }
}

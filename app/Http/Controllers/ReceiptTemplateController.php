<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class ReceiptTemplateController extends Controller
{
    /**
     * Default values for repair receipt template
     */
    public static function repairDefaults(): array
    {
        return [
            'repair_receipt_title'           => 'ใบรับเครื่องซ่อม',
            'repair_receipt_brand_color'      => '#111827',
            'repair_receipt_accent_color'     => '#0ea5e9',
            'repair_receipt_bg_color'         => '#f9fafb',
            'repair_receipt_shop_name'        => '',
            'repair_receipt_shop_info'        => '',
            'repair_receipt_logo_url'         => '',
            'repair_receipt_show_logo'        => '1',
            'repair_receipt_copies'           => '2',
            'repair_receipt_paper_size'       => 'A5',
            'repair_receipt_sign_left'        => 'ลูกค้าเซ็นรับทราบ',
            'repair_receipt_sign_right'       => 'All in Service Mac',
            'repair_receipt_show_qr'          => '1',
            'repair_receipt_show_accessories' => '1',
            'repair_receipt_show_barcode'     => '1',
            'repair_receipt_show_password'    => '1',
            'repair_receipt_terms'            => "1. รับคืนภายใน 7 วันหลังแจ้ง หรือภายใน 30 วันจากวันรับบริการ\n2. ต้องมีใบนี้เพื่อรับอุปกรณ์คืน\n3. รับประกันเริ่ม 1 วันหลังแจ้งรับ\n4. ไม่ครอบคลุมอุบัติเหตุ/การใช้งานผิดประเภท",
            'repair_receipt_terms_title'      => 'เงื่อนไขการรับบริการโดยสรุป',
        ];
    }

    /**
     * Default values for sales receipt template
     */
    public static function salesDefaults(): array
    {
        return [
            'sales_receipt_shop_name'      => '',
            'sales_receipt_shop_info'      => '',
            'sales_receipt_logo_url'       => '',
            'sales_receipt_show_logo'      => '1',
            'sales_receipt_paper_width'    => '80',
            'sales_receipt_font_size'      => '12',
            'sales_receipt_show_customer'  => '1',
            'sales_receipt_show_seller'    => '1',
            'sales_receipt_show_vat'       => '1',
            'sales_receipt_show_payment'   => '1',
            'sales_receipt_footer_thank'   => 'ขอบคุณที่ใช้บริการ',
            'sales_receipt_footer_text'    => "สินค้าที่ซื้อแล้วไม่สามารถเปลี่ยนคืนได้\nยกเว้นมีการรับประกันตามเงื่อนไข",
        ];
    }

    /**
     * Show receipt template editor
     */
    public function index()
    {
        $repairDefaults = self::repairDefaults();
        $salesDefaults  = self::salesDefaults();

        $repair = [];
        foreach ($repairDefaults as $key => $default) {
            $repair[$key] = Setting::get($key, $default);
        }

        $sales = [];
        foreach ($salesDefaults as $key => $default) {
            $sales[$key] = Setting::get($key, $default);
        }

        return view('settings.receipt-templates', compact('repair', 'sales'));
    }

    /**
     * Save receipt template settings
     */
    public function update(Request $request)
    {
        $type = $request->input('type', 'repair');

        if ($type === 'repair') {
            $defaults = self::repairDefaults();
        } else {
            $defaults = self::salesDefaults();
        }

        foreach ($defaults as $key => $default) {
            $value = $request->input($key, $default);
            $settingType = in_array($key, [
                'repair_receipt_show_logo',
                'repair_receipt_show_qr',
                'repair_receipt_show_accessories',
                'repair_receipt_show_barcode',
                'repair_receipt_show_password',
                'sales_receipt_show_logo',
                'sales_receipt_show_customer',
                'sales_receipt_show_seller',
                'sales_receipt_show_vat',
                'sales_receipt_show_payment',
            ]) ? 'boolean' : 'string';

            Setting::set($key, $value, $settingType, 'receipt_template', null);
        }

        return redirect()->route('receipt-templates.index')
            ->with('success', ($type === 'repair' ? 'ใบรับซ่อม' : 'ใบเสร็จ') . ' - บันทึกเทมเพลตเรียบร้อย');
    }

    /**
     * Preview repair receipt with current settings
     */
    public function previewRepair()
    {
        $defaults = self::repairDefaults();
        $template = [];
        foreach ($defaults as $key => $default) {
            $template[$key] = Setting::get($key, $default);
        }

        return view('settings.preview-repair', compact('template'));
    }

    /**
     * Preview sales receipt with current settings
     */
    public function previewSales()
    {
        $defaults = self::salesDefaults();
        $template = [];
        foreach ($defaults as $key => $default) {
            $template[$key] = Setting::get($key, $default);
        }

        return view('settings.preview-sales', compact('template'));
    }

    /**
     * Helper: get all repair template settings
     */
    public static function getRepairTemplate(): array
    {
        $defaults = self::repairDefaults();
        $result = [];
        foreach ($defaults as $key => $default) {
            $result[$key] = Setting::get($key, $default);
        }
        return $result;
    }

    /**
     * Helper: get all sales template settings
     */
    public static function getSalesTemplate(): array
    {
        $defaults = self::salesDefaults();
        $result = [];
        foreach ($defaults as $key => $default) {
            $result[$key] = Setting::get($key, $default);
        }
        return $result;
    }
}

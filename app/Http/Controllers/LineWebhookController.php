<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Product;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Repair;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineWebhookController extends Controller
{
    /**
     * Handle LINE webhook events
     */
    public function webhook(Request $request)
    {
        // Check if LINE OA is enabled
        if (!Setting::get('line_oa_enabled', false)) {
            return response('OK', 200);
        }

        $channelSecret = Setting::get('line_oa_channel_secret');
        $accessToken   = Setting::get('line_oa_access_token');

        if (!$channelSecret || !$accessToken) {
            Log::warning('LINE OA: Channel Secret or Access Token not configured.');
            return response('OK', 200);
        }

        // Verify signature
        $signature = $request->header('X-Line-Signature');
        $body = $request->getContent();
        $hash = base64_encode(hash_hmac('sha256', $body, $channelSecret, true));

        if ($signature !== $hash) {
            Log::warning('LINE OA: Invalid signature.');
            return response('Invalid signature', 403);
        }

        $events = $request->input('events', []);

        foreach ($events as $event) {
            if ($event['type'] === 'message' && $event['message']['type'] === 'text') {
                $this->handleTextMessage($event, $accessToken);
            } elseif ($event['type'] === 'follow') {
                $this->handleFollow($event, $accessToken);
            }
        }

        return response('OK', 200);
    }

    /**
     * Handle text messages
     */
    protected function handleTextMessage(array $event, string $accessToken): void
    {
        $text = trim($event['message']['text']);
        $replyToken = $event['replyToken'];

        // Command routing
        if ($this->startsWith($text, 'สต๊อก') || $this->startsWith($text, 'สต็อก') || $this->startsWith($text, 'stock') || $this->startsWith($text, 'เช็คสินค้า')) {
            $keyword = $this->extractKeyword($text);
            $reply = $this->checkStock($keyword);
        } elseif ($this->startsWith($text, 'ราคา') || $this->startsWith($text, 'price')) {
            $keyword = $this->extractKeyword($text);
            $reply = $this->checkPrice($keyword);
        } elseif ($this->startsWith($text, 'ติดตาม') || $this->startsWith($text, 'track') || $this->startsWith($text, 'เช็คงานซ่อม')) {
            $keyword = $this->extractKeyword($text);
            $reply = $this->trackRepair($keyword);
        } elseif ($text === 'เมนู' || $text === 'menu' || $text === 'help' || $text === 'คำสั่ง') {
            $reply = $this->getHelpMenu();
        } else {
            // Try auto-detect: search stock first
            $reply = $this->autoSearch($text);
        }

        $this->replyMessage($replyToken, $reply, $accessToken);
    }

    /**
     * Handle follow event (new friend)
     */
    protected function handleFollow(array $event, string $accessToken): void
    {
        $shopName = Setting::get('company_name', 'ร้านของเรา');
        $welcome = "สวัสดีค่ะ ยินดีต้อนรับสู่ {$shopName} 🎉\n\n";
        $welcome .= "พิมพ์คำสั่งเพื่อใช้งาน:\n";
        $welcome .= "📦 สต๊อก [ชื่อสินค้า] — เช็คสินค้าคงเหลือ\n";
        $welcome .= "💰 ราคา [ชื่อสินค้า] — เช็คราคา\n";
        $welcome .= "🔧 ติดตาม [เลขที่ซ่อม] — เช็คสถานะงานซ่อม\n";
        $welcome .= "📋 เมนู — แสดงคำสั่งทั้งหมด\n\n";
        $welcome .= "หรือพิมพ์ชื่อสินค้าได้เลยค่ะ!";

        $this->replyMessage($event['replyToken'], $welcome, $accessToken);
    }

    /**
     * Check stock by keyword
     */
    protected function checkStock(string $keyword): string
    {
        if (empty($keyword)) {
            return "กรุณาระบุชื่อสินค้า เช่น\n📦 สต๊อก iPhone 15\n📦 สต๊อก ฟิล์มกระจก";
        }

        $products = Product::where('is_active', true)
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('sku', 'LIKE', "%{$keyword}%")
                    ->orWhere('barcode', 'LIKE', "%{$keyword}%");
            })
            ->limit(10)
            ->get();

        if ($products->isEmpty()) {
            return "❌ ไม่พบสินค้า \"{$keyword}\"\n\nลองพิมพ์ชื่อสินค้าใหม่อีกครั้งค่ะ";
        }

        $lines = ["📦 ผลค้นหา \"{$keyword}\" ({$products->count()} รายการ)\n"];
        $lines[] = str_repeat('─', 20);

        foreach ($products as $product) {
            $qty = $product->quantity;
            $status = $qty > 0 ? "✅ มีสินค้า" : "❌ สินค้าหมด";
            $lines[] = "\n📌 {$product->name}";
            if ($product->sku) {
                $lines[] = "   SKU: {$product->sku}";
            }
            $lines[] = "   คงเหลือ: {$qty} {$product->unit}";
            $lines[] = "   สถานะ: {$status}";

            // Show per-branch stock
            $branchStocks = BranchStock::where('stockable_id', $product->id)
                ->where('stockable_type', 'App\\Models\\Product')
                ->where('quantity', '>', 0)
                ->with('branch')
                ->get();

            if ($branchStocks->isNotEmpty()) {
                foreach ($branchStocks as $bs) {
                    $branchName = $bs->branch->name ?? 'ไม่ระบุ';
                    $lines[] = "   📍 {$branchName}: {$bs->quantity} {$product->unit}";
                }
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Check price by keyword
     */
    protected function checkPrice(string $keyword): string
    {
        if (empty($keyword)) {
            return "กรุณาระบุชื่อสินค้า เช่น\n💰 ราคา iPhone 15\n💰 ราคา เคสซิลิโคน";
        }

        $products = Product::where('is_active', true)
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('sku', 'LIKE', "%{$keyword}%");
            })
            ->limit(10)
            ->get();

        if ($products->isEmpty()) {
            return "❌ ไม่พบสินค้า \"{$keyword}\"\n\nลองพิมพ์ชื่อสินค้าใหม่อีกครั้งค่ะ";
        }

        $lines = ["💰 ราคาสินค้า \"{$keyword}\"\n"];
        $lines[] = str_repeat('─', 20);

        foreach ($products as $product) {
            $lines[] = "\n📌 {$product->name}";
            $lines[] = "   ราคาปลีก: ฿" . number_format($product->retail_price, 0);
            if ($product->wholesale_price > 0 && $product->wholesale_price != $product->retail_price) {
                $lines[] = "   ราคาส่ง: ฿" . number_format($product->wholesale_price, 0);
            }
            $qty = $product->quantity;
            $lines[] = "   สถานะ: " . ($qty > 0 ? "✅ มีสินค้า ({$qty})" : "❌ สินค้าหมด");
        }

        return implode("\n", $lines);
    }

    /**
     * Track repair by number
     */
    protected function trackRepair(string $repairNumber): string
    {
        if (empty($repairNumber)) {
            return "กรุณาระบุเลขที่งานซ่อม เช่น\n🔧 ติดตาม RPR-2025-0001";
        }

        $repair = Repair::where('repair_number', 'LIKE', "%{$repairNumber}%")
            ->with('branch')
            ->first();

        if (!$repair) {
            return "❌ ไม่พบงานซ่อมเลขที่ \"{$repairNumber}\"\n\nกรุณาตรวจสอบเลขที่อีกครั้งค่ะ";
        }

        $statusMap = [
            'pending'       => '⏳ รอรับเครื่อง',
            'received'      => '📥 รับเครื่องแล้ว',
            'diagnosing'    => '🔍 กำลังตรวจสอบ',
            'waiting_parts' => '📦 รออะไหล่',
            'repairing'     => '🔧 กำลังซ่อม',
            'testing'       => '🧪 ทดสอบ',
            'completed'     => '✅ ซ่อมเสร็จ',
            'delivered'     => '🚀 ส่งมอบแล้ว',
            'cancelled'     => '❌ ยกเลิก',
        ];

        $status = $statusMap[$repair->status] ?? $repair->status;

        $lines = ["🔧 สถานะงานซ่อม\n"];
        $lines[] = str_repeat('─', 20);
        $lines[] = "\n📋 เลขที่: {$repair->repair_number}";
        $lines[] = "📱 อุปกรณ์: {$repair->device_brand} {$repair->device_model}";
        $lines[] = "🔧 อาการ: {$repair->problem_description}";
        $lines[] = "📊 สถานะ: {$status}";

        if ($repair->branch) {
            $lines[] = "📍 สาขา: {$repair->branch->name}";
        }

        if ($repair->estimated_cost) {
            $lines[] = "💰 ราคาประเมิน: ฿" . number_format($repair->estimated_cost, 0);
        }

        if ($repair->completed_at) {
            $lines[] = "✅ ซ่อมเสร็จ: " . $repair->completed_at->format('d/m/Y');
        }

        return implode("\n", $lines);
    }

    /**
     * Auto search — try to find products matching the text
     */
    protected function autoSearch(string $text): string
    {
        // Skip very short or irrelevant messages
        if (mb_strlen($text) < 2) {
            return $this->getHelpMenu();
        }

        $products = Product::where('is_active', true)
            ->where(function ($q) use ($text) {
                $q->where('name', 'LIKE', "%{$text}%")
                    ->orWhere('sku', 'LIKE', "%{$text}%")
                    ->orWhere('barcode', 'LIKE', "%{$text}%");
            })
            ->limit(5)
            ->get();

        if ($products->isNotEmpty()) {
            return $this->checkStock($text);
        }

        // Try track repair
        if (preg_match('/RPR-?\d/i', $text)) {
            return $this->trackRepair($text);
        }

        return "🤔 ไม่แน่ใจว่าต้องการอะไร\n\n" . $this->getHelpMenu();
    }

    /**
     * Help menu
     */
    protected function getHelpMenu(): string
    {
        $shopName = Setting::get('company_name', 'ร้านของเรา');

        $menu = "📋 เมนูคำสั่ง {$shopName}\n";
        $menu .= str_repeat('─', 20) . "\n\n";
        $menu .= "📦 สต๊อก [ชื่อสินค้า]\n   เช็คสินค้าคงเหลือ\n\n";
        $menu .= "💰 ราคา [ชื่อสินค้า]\n   เช็คราคาสินค้า\n\n";
        $menu .= "🔧 ติดตาม [เลขที่ซ่อม]\n   เช็คสถานะงานซ่อม\n\n";
        $menu .= "📋 เมนู\n   แสดงเมนูนี้\n\n";
        $menu .= str_repeat('─', 20) . "\n";
        $menu .= "💡 หรือพิมพ์ชื่อสินค้าได้เลย\nระบบจะค้นหาให้อัตโนมัติค่ะ";

        return $menu;
    }

    /**
     * Reply message via LINE API
     */
    protected function replyMessage(string $replyToken, string $text, string $accessToken): void
    {
        // LINE has a 5000 character limit per message
        if (mb_strlen($text) > 5000) {
            $text = mb_substr($text, 0, 4950) . "\n\n... (แสดงผลไม่ครบ)";
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post('https://api.line.me/v2/bot/message/reply', [
                'replyToken' => $replyToken,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $text,
                    ]
                ]
            ]);

            if (!$response->successful()) {
                Log::error('LINE OA Reply failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('LINE OA Reply exception: ' . $e->getMessage());
        }
    }

    /**
     * Helper: check if string starts with prefix
     */
    protected function startsWith(string $text, string $prefix): bool
    {
        return mb_stripos($text, $prefix) === 0;
    }

    /**
     * Helper: extract keyword after command prefix
     */
    protected function extractKeyword(string $text): string
    {
        // Remove the first word (command)
        $parts = preg_split('/\s+/', $text, 2);
        return isset($parts[1]) ? trim($parts[1]) : '';
    }

    // ==========================================
    // Settings Page Methods
    // ==========================================

    /**
     * Show LINE OA settings page
     */
    public function settings()
    {
        $settings = [
            'line_oa_enabled'        => Setting::get('line_oa_enabled', '0'),
            'line_oa_channel_id'     => Setting::get('line_oa_channel_id', ''),
            'line_oa_channel_secret' => Setting::get('line_oa_channel_secret', ''),
            'line_oa_access_token'   => Setting::get('line_oa_access_token', ''),
            'line_oa_welcome_msg'    => Setting::get('line_oa_welcome_msg', ''),
            'line_oa_show_price'     => Setting::get('line_oa_show_price', '1'),
            'line_oa_show_branch'    => Setting::get('line_oa_show_branch', '1'),
            'line_oa_auto_search'    => Setting::get('line_oa_auto_search', '1'),
        ];

        $webhookUrl = url('/api/line/webhook');

        return view('settings.line-oa', compact('settings', 'webhookUrl'));
    }

    /**
     * Save LINE OA settings
     */
    public function updateSettings(Request $request)
    {
        $keys = [
            'line_oa_enabled'        => 'boolean',
            'line_oa_channel_id'     => 'string',
            'line_oa_channel_secret' => 'string',
            'line_oa_access_token'   => 'string',
            'line_oa_welcome_msg'    => 'string',
            'line_oa_show_price'     => 'boolean',
            'line_oa_show_branch'    => 'boolean',
            'line_oa_auto_search'    => 'boolean',
        ];

        foreach ($keys as $key => $type) {
            $value = $request->input($key, $type === 'boolean' ? '0' : '');
            Setting::set($key, $value, $type, 'line_oa', null);
        }

        return redirect()->route('line-oa.index')
            ->with('success', 'บันทึกการตั้งค่า LINE OA เรียบร้อย');
    }

    /**
     * Test LINE connection
     */
    public function testConnection(Request $request)
    {
        $accessToken = $request->input('access_token') ?: Setting::get('line_oa_access_token');

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาระบุ Channel Access Token',
            ]);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get('https://api.line.me/v2/bot/info');

            if ($response->successful()) {
                $bot = $response->json();
                return response()->json([
                    'success' => true,
                    'message' => 'เชื่อมต่อสำเร็จ!',
                    'bot_name' => $bot['displayName'] ?? '',
                    'bot_id' => $bot['userId'] ?? '',
                    'picture' => $bot['pictureUrl'] ?? '',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'เชื่อมต่อไม่สำเร็จ: ' . ($response->json('message') ?? $response->status()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }
}

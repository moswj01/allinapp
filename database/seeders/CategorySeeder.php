<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Product Categories
        $productCategories = [
            ['name' => 'โทรศัพท์มือถือ', 'type' => 'product', 'description' => 'สมาร์ทโฟน, มือถือปุ่มกด'],
            ['name' => 'แท็บเล็ต', 'type' => 'product', 'description' => 'แท็บเล็ต iPad, Android Tablet'],
            ['name' => 'อุปกรณ์เสริม', 'type' => 'product', 'description' => 'เคส, ฟิล์ม, สายชาร์จ, หูฟัง'],
            ['name' => 'นาฬิกา Smart Watch', 'type' => 'product', 'description' => 'นาฬิกาอัจฉริยะ'],
            ['name' => 'หูฟัง', 'type' => 'product', 'description' => 'หูฟังบลูทูธ, หูฟังมีสาย'],
            ['name' => 'ลำโพง', 'type' => 'product', 'description' => 'ลำโพงบลูทูธ, ลำโพงพกพา'],
            ['name' => 'พาวเวอร์แบงค์', 'type' => 'product', 'description' => 'แบตสำรอง'],
        ];

        // Part Categories (now merged as product type)
        $partCategories = [
            ['name' => 'หน้าจอ LCD', 'type' => 'product', 'description' => 'หน้าจอ LCD สำหรับซ่อม'],
            ['name' => 'แบตเตอรี่', 'type' => 'product', 'description' => 'แบตเตอรี่มือถือ'],
            ['name' => 'ชุดชาร์จ', 'type' => 'product', 'description' => 'Charging Port, Flex'],
            ['name' => 'กล้อง', 'type' => 'product', 'description' => 'กล้องหน้า, กล้องหลัง'],
            ['name' => 'ลำโพง/ไมค์', 'type' => 'product', 'description' => 'ลำโพงสนทนา, ลำโพงกระดิ่ง, ไมโครโฟน'],
            ['name' => 'บอร์ด/IC', 'type' => 'product', 'description' => 'Main Board, IC ต่างๆ'],
            ['name' => 'ปุ่มกด/Flex', 'type' => 'product', 'description' => 'ปุ่ม Volume, Power, Flex Cable'],
            ['name' => 'เคสกลาง/ฝาหลัง', 'type' => 'product', 'description' => 'Housing, Back Cover'],
            ['name' => 'อื่นๆ', 'type' => 'product', 'description' => 'สินค้าอื่นๆ'],
        ];

        foreach (array_merge($productCategories, $partCategories) as $category) {
            Category::create(array_merge($category, ['is_active' => true]));
        }
    }
}

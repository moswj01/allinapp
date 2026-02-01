<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        Branch::create([
            'code' => 'HQ',
            'name' => 'สำนักงานใหญ่',
            'address' => 'กรุงเทพมหานคร',
            'phone' => '02-xxx-xxxx',
            'is_main' => true,
            'is_active' => true,
        ]);
    }
}

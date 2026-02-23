<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => Plan::FREE,
                'description' => 'เริ่มต้นใช้งานฟรี เหมาะสำหรับร้านเล็ก',
                'price' => 0,
                'yearly_price' => 0,
                'billing_cycle' => 'monthly',
                'max_users' => 2,
                'max_branches' => 1,
                'max_products' => 50,
                'max_repairs' => 30,
                'features' => [Plan::FEATURE_REPAIRS, Plan::FEATURE_STOCK],
                'is_active' => true,
                'trial_days' => 0,
                'sort_order' => 1,
            ],
            [
                'name' => 'Basic',
                'slug' => Plan::BASIC,
                'description' => 'สำหรับร้านขนาดเล็ก-กลาง',
                'price' => 499,
                'yearly_price' => 4990,
                'billing_cycle' => 'monthly',
                'max_users' => 5,
                'max_branches' => 1,
                'max_products' => 500,
                'max_repairs' => -1,
                'features' => [
                    Plan::FEATURE_REPAIRS,
                    Plan::FEATURE_POS,
                    Plan::FEATURE_STOCK,
                    Plan::FEATURE_QUOTATIONS,
                    Plan::FEATURE_REPORTS,
                ],
                'is_active' => true,
                'trial_days' => 14,
                'sort_order' => 2,
            ],
            [
                'name' => 'Pro',
                'slug' => Plan::PRO,
                'description' => 'สำหรับร้านที่ต้องการฟีเจอร์ครบ',
                'price' => 999,
                'yearly_price' => 9990,
                'billing_cycle' => 'monthly',
                'max_users' => 15,
                'max_branches' => 3,
                'max_products' => 5000,
                'max_repairs' => -1,
                'features' => [
                    Plan::FEATURE_REPAIRS,
                    Plan::FEATURE_POS,
                    Plan::FEATURE_STOCK,
                    Plan::FEATURE_PURCHASING,
                    Plan::FEATURE_FINANCE,
                    Plan::FEATURE_REPORTS,
                    Plan::FEATURE_QUOTATIONS,
                    Plan::FEATURE_LINE_OA,
                    Plan::FEATURE_MULTI_BRANCH,
                ],
                'is_active' => true,
                'trial_days' => 14,
                'sort_order' => 3,
            ],
            [
                'name' => 'Enterprise',
                'slug' => Plan::ENTERPRISE,
                'description' => 'สำหรับธุรกิจขนาดใหญ่ ครบทุกฟีเจอร์',
                'price' => 2499,
                'yearly_price' => 24990,
                'billing_cycle' => 'monthly',
                'max_users' => -1,
                'max_branches' => -1,
                'max_products' => -1,
                'max_repairs' => -1,
                'features' => [
                    Plan::FEATURE_REPAIRS,
                    Plan::FEATURE_POS,
                    Plan::FEATURE_STOCK,
                    Plan::FEATURE_PURCHASING,
                    Plan::FEATURE_FINANCE,
                    Plan::FEATURE_REPORTS,
                    Plan::FEATURE_QUOTATIONS,
                    Plan::FEATURE_LINE_OA,
                    Plan::FEATURE_API,
                    Plan::FEATURE_MULTI_BRANCH,
                ],
                'is_active' => true,
                'trial_days' => 14,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}

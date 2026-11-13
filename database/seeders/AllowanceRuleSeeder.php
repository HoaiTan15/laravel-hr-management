<?php

namespace Database\Seeders;

use App\Models\AllowanceRule;
use Illuminate\Database\Seeder;

class AllowanceRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['parking' => 'Phụ cấp gửi xe', 'meal' => 'Phụ cấp tiền ăn', 'responsibility' => 'Phụ cấp trách nhiệm'] as $type => $name) {
            AllowanceRule::firstOrCreate(['name' => $name], [
                'type' => $type, 'calculation_basis' => 'monthly', 'amount' => 200000, 'scope' => 'company',
                'description' => 'Cấu hình minh họa. Cần cập nhật theo chính sách công ty trước khi bật.',
                'is_active' => false,
            ]);
        }
    }
}

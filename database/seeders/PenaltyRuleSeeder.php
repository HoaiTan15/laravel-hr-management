<?php

namespace Database\Seeders;

use App\Models\PenaltyRule;
use Illuminate\Database\Seeder;

class PenaltyRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['late' => 'Đi muộn', 'early_departure' => 'Về sớm', 'work_rules' => 'Vi phạm nội quy công ty'] as $type => $name) {
            PenaltyRule::firstOrCreate(['name' => $name], [
                'type' => $type, 'amount' => 50000, 'scope' => 'company',
                'description' => 'Cấu hình minh họa. Cần cập nhật theo nội quy công ty trước khi bật.',
                'is_active' => false,
            ]);
        }
    }
}

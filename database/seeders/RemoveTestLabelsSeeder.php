<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RemoveTestLabelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fields = [
            'users' => ['name'],
            'employees' => ['full_name', 'address', 'termination_reason'],
            'departments' => ['name', 'description'],
            'positions' => ['name', 'description'],
            'attendances' => ['adjustment_reason'],
            'tasks' => ['title', 'description', 'notes'],
            'requests' => ['payload', 'processing_note'],
            'leave_requests' => ['reason', 'review_note'],
            'personnel_processes' => ['reason'],
            'allowance_rules' => ['name', 'description'],
            'penalty_rules' => ['name', 'description'],
            'monthly_salaries' => ['manual_penalties', 'adjustment_details'],
            'parking_permits' => ['vehicle_description', 'end_reason'],
            'parking_requests' => ['vehicle_description', 'reason', 'review_note'],
        ];
        $jsonFields = ['payload', 'manual_penalties', 'adjustment_details'];
        $changed = 0;
        DB::transaction(function () use ($fields, $jsonFields, &$changed): void {
            foreach ($fields as $table => $columns) {
                foreach (DB::table($table)->select(['id', ...$columns])->orderBy('id')->get() as $row) {
                    $updates = [];
                    foreach ($columns as $column) {
                        $value = $row->{$column};
                        if (! is_string($value) || ! preg_match('/\[test\]/iu', $value)) {
                            continue;
                        }
                        if (in_array($column, $jsonFields, true)) {
                            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                            $cleaned = json_encode($this->clean($decoded), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                        } else {
                            $cleaned = $this->clean($value);
                        }
                        if ($column === 'name' && in_array($table, ['departments', 'positions'], true)) {
                            $baseName = $cleaned;
                            $suffix = 2;
                            while (DB::table($table)->where('name', $cleaned)->where('id', '!=', $row->id)->exists()) {
                                $cleaned = $baseName.' ('.$suffix++.')';
                            }
                        }
                        $updates[$column] = $cleaned;
                    }
                    if ($updates !== []) {
                        DB::table($table)->where('id', $row->id)->update($updates);
                        $changed++;
                    }
                }
            }
            foreach ($fields as $table => $columns) {
                foreach ($columns as $column) {
                    foreach (DB::table($table)->pluck($column) as $value) {
                        if (is_string($value) && preg_match('/\[test\]/iu', $value)) {
                            throw new \RuntimeException('Vẫn còn nhãn trong '.$table.'.'.$column);
                        }
                    }
                }
            }
        });
        $this->command?->info('Đã bỏ nhãn khỏi '.$changed.' bản ghi. Giữ nguyên tài khoản, liên kết và số tiền.');
    }

    private function clean(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => $this->clean($item), $value);
        }

        return is_string($value) ? preg_replace('/\[test\]\h*/iu', '', $value) : $value;
    }
}

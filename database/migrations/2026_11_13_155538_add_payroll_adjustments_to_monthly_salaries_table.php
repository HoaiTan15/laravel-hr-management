<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('monthly_salaries', function (Blueprint $table) {
            $table->decimal('actual_worked_days', 5, 1)->default(0);
            $table->decimal('salary_before_adjustments', 15, 2)->default(0);
            $table->decimal('allowance_total', 15, 2)->default(0);
            $table->decimal('penalty_total', 15, 2)->default(0);
            $table->json('adjustment_details')->nullable();
            $table->json('manual_penalties')->nullable();
        });
        DB::table('monthly_salaries')->update(['salary_before_adjustments' => DB::raw('total_salary')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_salaries', function (Blueprint $table) {
            $table->dropColumn(['actual_worked_days', 'salary_before_adjustments', 'allowance_total', 'penalty_total', 'adjustment_details', 'manual_penalties']);
        });
    }
};

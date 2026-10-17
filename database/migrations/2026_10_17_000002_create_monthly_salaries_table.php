<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_salaries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('base_salary', 15, 2)->default(15000000);
            $table->unsignedTinyInteger('working_days')->default(0);
            $table->decimal('worked_days', 5, 1)->default(0);
            $table->decimal('approved_leave_days', 5, 1)->default(0);
            $table->decimal('paid_leave_days', 5, 1)->default(0);
            $table->decimal('unpaid_leave_days', 5, 1)->default(0);
            $table->decimal('daily_salary', 15, 2)->default(0);
            $table->decimal('total_salary', 15, 2)->default(0);
            $table->string('status', 20)->default('draft');
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'year', 'month']);
            $table->index(['year', 'month', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_salaries');
    }
};

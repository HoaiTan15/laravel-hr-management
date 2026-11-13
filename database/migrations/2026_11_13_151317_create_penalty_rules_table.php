<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('penalty_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('type', 30);
            $table->decimal('amount', 12, 0);
            $table->string('scope', 20);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['type', 'is_active']);
        });

        Schema::create('department_penalty_rule', function (Blueprint $table): void {
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->foreignId('penalty_rule_id')->constrained()->cascadeOnDelete();
            $table->primary(['department_id', 'penalty_rule_id']);
        });

        Schema::create('employee_penalty_rule', function (Blueprint $table): void {
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->foreignId('penalty_rule_id')->constrained()->cascadeOnDelete();
            $table->primary(['employee_id', 'penalty_rule_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_penalty_rule');
        Schema::dropIfExists('department_penalty_rule');
        Schema::dropIfExists('penalty_rules');
    }
};

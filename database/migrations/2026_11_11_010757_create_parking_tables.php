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
        Schema::create('parking_permits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->foreignId('active_employee_id')->nullable()->unique()->constrained('employees')->restrictOnDelete();
            $table->string('plate', 20);
            $table->string('active_plate', 20)->nullable()->unique();
            $table->string('vehicle_type', 20);
            $table->string('vehicle_description', 150);
            $table->decimal('monthly_cost', 12, 2);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->text('end_reason')->nullable();
            $table->timestamps();
        });
        Schema::create('parking_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->foreignId('pending_employee_id')->nullable()->unique()->constrained('employees')->restrictOnDelete();
            $table->foreignId('permit_id')->nullable()->constrained('parking_permits')->restrictOnDelete();
            $table->string('type', 20);
            $table->string('status', 20)->default('pending');
            $table->string('plate', 20)->nullable();
            $table->string('vehicle_type', 20)->nullable();
            $table->string('vehicle_description', 150)->nullable();
            $table->text('reason');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'status']);
        });
        Schema::create('parking_monthly_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->foreignId('permit_id')->constrained('parking_permits')->restrictOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->unique(['employee_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_monthly_costs');
        Schema::dropIfExists('parking_requests');
        Schema::dropIfExists('parking_permits');
    }
};

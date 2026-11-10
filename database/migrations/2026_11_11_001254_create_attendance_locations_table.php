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
        Schema::create('attendance_locations', function (Blueprint $table) {
            $table->id();
            $table->boolean('use_server_defaults')->default(true);
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->unsignedInteger('radius_meters')->nullable();
            $table->unsignedInteger('max_accuracy_meters')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_locations');
    }
};

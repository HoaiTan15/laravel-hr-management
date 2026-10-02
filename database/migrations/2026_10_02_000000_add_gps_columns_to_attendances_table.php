<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->decimal('check_in_latitude', 10, 7)->nullable()->after('check_in_at');
            $table->decimal('check_in_longitude', 10, 7)->nullable()->after('check_in_latitude');
            $table->decimal('check_in_accuracy', 8, 2)->nullable()->after('check_in_longitude');
            $table->decimal('check_out_latitude', 10, 7)->nullable()->after('check_out_at');
            $table->decimal('check_out_longitude', 10, 7)->nullable()->after('check_out_latitude');
            $table->decimal('check_out_accuracy', 8, 2)->nullable()->after('check_out_longitude');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropColumn([
                'check_in_latitude',
                'check_in_longitude',
                'check_in_accuracy',
                'check_out_latitude',
                'check_out_longitude',
                'check_out_accuracy',
            ]);
        });
    }
};

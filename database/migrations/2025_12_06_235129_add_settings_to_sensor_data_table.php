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
        Schema::table('sensor_data', function (Blueprint $table) {
            $table->float('temp1_threshold')->nullable()->after('timeDevice');
            $table->float('temp2_threshold')->nullable()->after('temp1_threshold');
            $table->float('hysteresis')->nullable()->after('temp2_threshold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensor_data', function (Blueprint $table) {
            $table->dropColumn(['temp1_threshold', 'temp2_threshold', 'hysteresis']);
        });
    }
};

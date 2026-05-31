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
        Schema::table('device_settings', function (Blueprint $table) {
            $table->string('charger_mode')->default('manual')->after('interval_record'); // manual or auto
            $table->float('charger_threshold_min')->default(11.0)->after('charger_mode');
            $table->float('charger_threshold_max')->default(13.5)->after('charger_threshold_min');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_settings', function (Blueprint $table) {
            $table->dropColumn(['charger_mode', 'charger_threshold_min', 'charger_threshold_max']);
        });
    }
};

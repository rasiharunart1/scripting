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
        Schema::create('sensor_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->float('battery_a')->nullable();
            $table->float('battery_b')->nullable();
            $table->float('battery_c')->nullable();
            $table->float('battery_d')->nullable();
            $table->float('temperature_1')->nullable();
            $table->float('temperature_2')->nullable();
            $table->float('pln_volt')->nullable();
            $table->float('pln_current')->nullable();
            $table->float('pln_power')->nullable();
            $table->integer('relay_1')->default(0);
            $table->integer('relay_2')->default(0);
            $table->string('timeDevice')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_data');
    }
};

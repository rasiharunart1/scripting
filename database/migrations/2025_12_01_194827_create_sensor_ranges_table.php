<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('sensor_key', 50);
            $table->decimal('min_value', 10, 2);
            $table->decimal('max_value', 10, 2);
            $table->timestamps();

            $table->unique(['user_id', 'sensor_key']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_ranges');
    }
};
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
        Schema::create('weather_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_id')->constrained()->restrictOnDelete();
            $table->date('date');
            $table->string('kind', 50);
            $table->decimal('temp_min', 5, 1)->nullable();
            $table->decimal('temp_max', 5, 1)->nullable();
            $table->decimal('precipitation', 5, 1)->nullable();
            $table->decimal('wind_speed', 5, 1)->nullable();
            $table->timestamp('fetched_at');
            $table->timestamps();
            $table->unique(['city_id', 'source_id', 'date', 'kind']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_records');
    }
};

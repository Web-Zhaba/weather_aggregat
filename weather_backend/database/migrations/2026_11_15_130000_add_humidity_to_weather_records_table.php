<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weather_records', function (Blueprint $table) {
            $table->decimal('humidity', 5, 1)->nullable()->after('wind_speed');
        });
    }

    public function down(): void
    {
        Schema::table('weather_records', function (Blueprint $table) {
            $table->dropColumn('humidity');
        });
    }
};

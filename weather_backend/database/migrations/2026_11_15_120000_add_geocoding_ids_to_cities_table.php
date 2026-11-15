<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Город определяется по geonames_id из геокодера, а не по названию:
     * одно и то же место может прийти как «Череповец» и как «Cherepovets».
     */
    public function up(): void
    {
        // Старые города без geonames_id не переносим: удаляем их вместе с кешем погоды,
        // при следующем запросе они будут заново найдены геокодером
        DB::table('weather_records')->delete();
        DB::table('cities')->delete();

        Schema::table('cities', function (Blueprint $table) {
            $table->unsignedBigInteger('geonames_id')->unique()->after('id');
            $table->char('country_code', 2)->nullable()->after('country');
            $table->string('timezone', 64)->nullable()->after('lon');
            $table->dropUnique(['name', 'country']);
            $table->index(['name', 'country']);
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropIndex(['name', 'country']);
            $table->unique(['name', 'country']);
            $table->dropUnique(['geonames_id']);
            $table->dropColumn(['geonames_id', 'country_code', 'timezone']);
        });
    }
};

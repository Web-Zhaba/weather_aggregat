<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Запомненные запросы пользователей: нормализованная строка (+ страна) -> город.
     * Повторный запрос с тем же написанием не ходит в геокодер.
     */
    public function up(): void
    {
        Schema::create('city_aliases', function (Blueprint $table) {
            $table->id();
            $table->string('query', 100);
            // '' означает «страна не указана» (NULL не участвует в unique)
            $table->string('country', 100)->default('');
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['query', 'country']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_aliases');
    }
};

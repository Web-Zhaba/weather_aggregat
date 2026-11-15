<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Source;

class SourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Source::updateOrCreate(
            ['slug' => 'open-meteo'],
            ['name' => 'Open-Meteo', 'is_active' => true],
        );
        Source::updateOrCreate(
            ['slug' => 'met-norway'],
            ['name' => 'MET Norway', 'is_active' => false]
        );
        Source::updateOrCreate(
            ['slug' => 'yandex'],
            ['name' => 'Yandex', 'is_active' => true]
        );
    }
}

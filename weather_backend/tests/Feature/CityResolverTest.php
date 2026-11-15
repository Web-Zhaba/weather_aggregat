<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\CityAlias;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CityResolverTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGeocoder(): void
    {
        Http::fake(['geocoding-api.open-meteo.com/*' => function ($request) {
            $ru = $request['language'] === 'ru';
            return Http::response(['results' => [[
                'id' => 569223,
                'name' => $ru ? 'Череповец' : 'Cherepovets',
                'country' => $ru ? 'Россия' : 'Russia',
                'country_code' => 'RU',
                'latitude' => 59.13333,
                'longitude' => 37.9,
                'timezone' => 'Europe/Moscow',
            ]]]);
        }]);
    }

    public function test_different_spellings_resolve_to_one_city(): void
    {
        $this->fakeGeocoder();

        $this->getJson('/api/v1/cities/resolve?city=Череповец')->assertCreated();
        $this->getJson('/api/v1/cities/resolve?city=Cherepovets&country=RU')->assertCreated();

        $this->assertSame(1, City::count());
        $this->assertSame(569223, (int) City::first()->geonames_id);
        $this->assertSame(2, CityAlias::count());
    }

    public function test_repeated_query_is_served_from_database(): void
    {
        $this->fakeGeocoder();

        $this->getJson('/api/v1/cities/resolve?city=Череповец')->assertCreated();
        $this->getJson('/api/v1/cities/resolve?' . http_build_query(['city' => '  ЧЕРЕПОВЕЦ ']))
            ->assertOk()
            ->assertJsonPath('source', 'database');

        Http::assertSentCount(1);
    }

    public function test_unknown_city_returns_404(): void
    {
        Http::fake(['geocoding-api.open-meteo.com/*' => Http::response(['results' => []])]);

        $this->getJson('/api/v1/cities/resolve?city=Nowhereville')
            ->assertNotFound()
            ->assertJsonPath('error', 'City not found.');
    }

    public function test_geocoder_failure_returns_502(): void
    {
        Http::fake(['geocoding-api.open-meteo.com/*' => Http::response(null, 500)]);

        $this->getJson('/api/v1/cities/resolve?city=Moscow')->assertStatus(502);
    }
}

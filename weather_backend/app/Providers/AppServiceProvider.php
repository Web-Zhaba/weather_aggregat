<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // В PHP под Windows/OSPanel часто не настроен curl.cainfo, и HTTPS-запросы падают
        // с cURL error 60. Путь к CA-бандлу можно задать в .env (HTTP_CA_BUNDLE).
        $options = [];

        $caBundle = env('HTTP_CA_BUNDLE');
        if ($caBundle && is_file($caBundle)) {
            $options['verify'] = $caBundle;
        }

        // Некоторые источники (api.open-meteo.com) недоступны напрямую из некоторых сетей.
        // PHP использует переменную HTTPS_PROXY, которой у сервера может не быть,
        // поэтому прокси для исходящих запросов можно задать явно в .env (HTTP_CLIENT_PROXY).
        $proxy = env('HTTP_CLIENT_PROXY');
        if ($proxy) {
            $options['proxy'] = $proxy;
        }

        if ($options) {
            Http::globalOptions($options);
        }
    }
}

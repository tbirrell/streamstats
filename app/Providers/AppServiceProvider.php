<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Http::macro('helix', function () {
            return Http::withHeaders([
                'Client-Id' => env('TWITCH_CLIENT_ID')
            ])->baseUrl('https://api.twitch.tv/helix');
        });
    }
}

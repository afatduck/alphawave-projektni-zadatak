<?php

namespace App\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Routing\UrlGenerator;
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
    public function boot(UrlGenerator $url): void
    {
        FilamentAsset::register([
            Js::make("linechart", asset('js/linechart.js')),
            Css::make("linechart", asset("css/linechart.css"))
        ]);

        if (env('APP_ENV') === 'production') {
            $url->forceScheme('https');
        }
    }
}

<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Filament;

use Birdcar\Markdown\Laravel\BfmServiceProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

final class BfmFilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'bfm-filament');

        FilamentAsset::register([
            Css::make('bfm-styles')
                ->html(BfmServiceProvider::renderStyles()),
        ], 'birdcar/markdown-filament');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/bfm-filament'),
            ], 'bfm-filament-views');
        }
    }
}

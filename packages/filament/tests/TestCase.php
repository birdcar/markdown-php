<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Filament\Tests;

use Birdcar\Markdown\Filament\BfmFilamentServiceProvider;
use Birdcar\Markdown\Laravel\BfmServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            SupportServiceProvider::class,
            SchemasServiceProvider::class,
            FormsServiceProvider::class,
            TablesServiceProvider::class,
            InfolistsServiceProvider::class,
            FilamentServiceProvider::class,
            BfmServiceProvider::class,
            BfmFilamentServiceProvider::class,
        ];
    }
}

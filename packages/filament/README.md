# birdcar/markdown-filament

[Filament v4](https://filamentphp.com/) integration for [Birdcar Flavored Markdown](../../README.md) (BFM).

Provides drop-in replacements for Filament's markdown components that render through the BFM pipeline instead of the default CommonMark config.

## Requirements

- PHP 8.2+
- Filament 4.x
- `birdcar/markdown-laravel` (pulled in automatically)

## Installation

```bash
composer require birdcar/markdown-filament
```

The service provider is auto-discovered. BFM styles are automatically registered as a Filament asset.

## Components

### `BfmEditor` (Form field)

Drop-in replacement for `MarkdownEditor` with a server-rendered BFM preview.

```php
use Birdcar\Markdown\Filament\Forms\Components\BfmEditor;

BfmEditor::make('body')
    ->previewDebounce(300) // optional, default is 300ms
    ->columnSpanFull();
```

The editor adds a Preview/Edit toggle button above the EasyMDE editor. Preview is rendered server-side through `Str::bfm()` so it matches your actual output exactly.

All standard `MarkdownEditor` methods work (file attachments, toolbar buttons, min/max height, etc.).

### `BfmTextColumn` (Table column)

Drop-in replacement for `TextColumn` that renders markdown content as BFM HTML.

```php
use Birdcar\Markdown\Filament\Tables\Columns\BfmTextColumn;

BfmTextColumn::make('body');
```

### `BfmTextEntry` (Infolist entry)

Drop-in replacement for `TextEntry` that renders markdown content as BFM HTML.

```php
use Birdcar\Markdown\Filament\Infolists\Components\BfmTextEntry;

BfmTextEntry::make('body');
```

## Publishable views

```bash
php artisan vendor:publish --tag=bfm-filament-views
```

Publishes the Blade views to `resources/views/vendor/bfm-filament/`. Only do this if you need to customize the editor markup.

## Gotchas

- **Filament v4 only.** This package uses `callSchemaComponentMethod` and `#[ExposedLivewireMethod]` which are v4 APIs. It will not work with Filament v3.
- **Preview is server-rendered.** Each preview toggle makes a Livewire round-trip. The `previewDebounce` option only applies if you wire up live preview (the default is toggle-based, not live).
- **BFM styles load automatically.** The service provider registers the CSS via `FilamentAsset::register()`. You don't need to add `@bfmStyles` in Filament panels — it's handled for you.
- **`BfmTextColumn` and `BfmTextEntry` call `->html()` internally.** Don't chain `->html()` again; it's already set up.
- **The Laravel package must be configured.** Render profile and resolvers are controlled by `config/bfm.php` from the Laravel package. The Filament package reads from the same converter singleton.

## Development

```bash
cd packages/filament
composer install
composer test      # Run tests
composer analyse   # Run PHPStan (level 8)
```

Tests use Orchestra Testbench with Filament's `SchemasServiceProvider`. The `composer.json` includes path repositories pointing to both `../../` (core) and `../laravel` (Laravel package) for monorepo resolution.

# birdcar/markdown-laravel

Laravel integration for [Birdcar Flavored Markdown](../../README.md) (BFM).

Provides a configured `MarkdownConverter` singleton, `Str` macros, a Blade directive for loading styles, and publishable config/assets.

## Requirements

- PHP 8.2+
- Laravel 10, 11, or 12
- `birdcar/markdown-php` (pulled in automatically)

## Installation

```bash
composer require birdcar/markdown-laravel
```

The service provider is auto-discovered.

## Usage

### String macros

```php
use Illuminate\Support\Str;

// Full BFM render (block-level HTML)
Str::bfm('# Hello **world**');

// Inline render (strips wrapping <p> tag)
Str::inlineBfm('Hello **world**');
```

### Blade directive

Include the BFM stylesheet in your layout:

```blade
<head>
    @bfmStyles
</head>
```

This outputs a `<link>` tag if you've published the CSS to `public/vendor/bfm/`, otherwise it inlines the stylesheet in a `<style>` tag.

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=bfm-config
```

This creates `config/bfm.php`:

```php
return [
    // Output format: 'html', 'email', or 'plain'
    'profile' => 'html',

    // Custom resolvers (resolved from the container)
    'resolvers' => [
        'mention' => null, // class implementing MentionResolverInterface
        'embed'   => null, // class implementing EmbedResolverInterface
    ],
];
```

### Resolvers

To handle `@mentions` or `!embed[...]` syntax, create classes implementing `MentionResolverInterface` or `EmbedResolverInterface` from the core package and reference them in config. They're resolved from the container, so you can inject dependencies.

## Publishing assets

```bash
# Publish the CSS to public/vendor/bfm/
php artisan vendor:publish --tag=bfm-assets
```

Publishing is optional. Without it, `@bfmStyles` inlines the CSS directly. Publishing is better for production since the browser can cache the external stylesheet.

## Gotchas

- **All BFM extensions are always enabled.** There are no config toggles for individual extensions (callouts, tasks, mentions, embeds).
- **`Str::bfm()` is a dynamic macro.** PHPStan won't recognize it statically. If you use PHPStan, add an ignore for calls to `Str::bfm()` and `Str::inlineBfm()`.
- **`@bfmStyles` is evaluated at runtime**, not at Blade compile time. No need to run `view:clear` after publishing assets.

## Development

```bash
cd packages/laravel
composer install
composer test      # Run tests
composer analyse   # Run PHPStan (level 8)
```

Tests use Orchestra Testbench. The `composer.json` includes a path repository pointing to `../../` so it can resolve the core `birdcar/markdown-php` package from the monorepo root.

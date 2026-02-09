# Implementation Spec: BFM Laravel Integration - Phase 1

**PRD**: ./prd-phase-1.md
**Estimated Effort**: M

## Technical Approach

This phase restructures the repository into a monorepo and adds a `birdcar/markdown-laravel` package under `packages/laravel/`. The core library stays at the repo root with its existing `birdcar/markdown-php` composer name.

The Laravel package follows standard Laravel package conventions: a service provider with auto-discovery, a publishable config file, Str macros, and a Blade directive. The service provider binds a singleton `MarkdownConverter` configured via `BfmEnvironmentFactory::create()`, pulling resolver classes and render profile from the config. Resolvers are resolved from the container, so users can bind their implementations however they prefer.

The monorepo uses Composer path repositories for local development. Each package has its own `composer.json`, test suite, and PHPStan config. The root `composer.json` gains a `repositories` key for path-based linking and scripts that run checks across all packages.

## File Changes

### New Files

| File Path | Purpose |
|-----------|---------|
| `packages/laravel/composer.json` | Package manifest for `birdcar/markdown-laravel` |
| `packages/laravel/src/BfmServiceProvider.php` | Laravel service provider: bindings, macros, config, Blade directives |
| `packages/laravel/src/Facades/Bfm.php` | Optional facade for `MarkdownConverter` |
| `packages/laravel/config/bfm.php` | Publishable config: profile, extensions, resolvers |
| `packages/laravel/resources/css/bfm.css` | Default stylesheet (placeholder, fully built in Phase 3) |
| `packages/laravel/tests/BfmServiceProviderTest.php` | Tests for provider registration, bindings, macros |
| `packages/laravel/tests/StrMacroTest.php` | Tests for `Str::bfm()` and `Str::inlineBfm()` |
| `packages/laravel/tests/TestCase.php` | Base test case using Orchestra Testbench |
| `packages/laravel/phpunit.xml` | PHPUnit config for Laravel package tests |
| `packages/laravel/phpstan.neon` | PHPStan config for Laravel package |

### Modified Files

| File Path | Changes |
|-----------|---------|
| `composer.json` (root) | Add `repositories` for path packages, add root-level scripts for cross-package testing |
| `phpstan.neon` (root, if exists) | May need updating to exclude `packages/` from root analysis |

### Deleted Files

None.

## Implementation Details

### 1. Monorepo Structure

**Overview**: Set up the directory structure and Composer path repositories so all packages resolve locally during development.

**Implementation steps**:
1. Create `packages/laravel/` directory
2. Add path repository to root `composer.json`:
   ```json
   {
     "repositories": [
       { "type": "path", "url": "packages/*" }
     ]
   }
   ```
3. Add root-level scripts:
   ```json
   {
     "scripts": {
       "test": "phpunit",
       "test:all": ["@test", "cd packages/laravel && composer test"],
       "analyse": "phpstan analyse -l 8 src/",
       "analyse:all": ["@analyse", "cd packages/laravel && composer analyse"]
     }
   }
   ```

### 2. Laravel Package composer.json

**Overview**: Package manifest declaring dependencies, autoload, and Laravel auto-discovery.

```json
{
  "name": "birdcar/markdown-laravel",
  "description": "Laravel integration for Birdcar Flavored Markdown",
  "type": "library",
  "license": "MIT",
  "require": {
    "php": "^8.2",
    "birdcar/markdown-php": "^0.1",
    "illuminate/support": "^10.0|^11.0|^12.0"
  },
  "require-dev": {
    "phpunit/phpunit": "^11.0",
    "phpstan/phpstan": "^2.0",
    "orchestra/testbench": "^8.0|^9.0|^10.0"
  },
  "autoload": {
    "psr-4": {
      "Birdcar\\Markdown\\Laravel\\": "src/"
    }
  },
  "autoload-dev": {
    "psr-4": {
      "Birdcar\\Markdown\\Laravel\\Tests\\": "tests/"
    }
  },
  "extra": {
    "laravel": {
      "providers": [
        "Birdcar\\Markdown\\Laravel\\BfmServiceProvider"
      ]
    }
  },
  "scripts": {
    "test": "phpunit",
    "analyse": "phpstan analyse -l 8 src/"
  }
}
```

**Key decisions**:
- Namespace is `Birdcar\Markdown\Laravel\` to avoid collision with the core `Birdcar\Markdown\` namespace
- Supports Laravel 10, 11, and 12 via `illuminate/support` constraint
- Orchestra Testbench versions align with Laravel versions

### 3. BfmServiceProvider

**Pattern to follow**: Standard Laravel package service provider conventions.

**Overview**: Registers the MarkdownConverter singleton, Str macros, Blade directives, and publishes config/assets.

```php
<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Laravel;

use Birdcar\Markdown\Contracts\EmbedResolverInterface;
use Birdcar\Markdown\Contracts\MentionResolverInterface;
use Birdcar\Markdown\Environment\BfmEnvironmentFactory;
use Birdcar\Markdown\Environment\RenderProfile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use League\CommonMark\MarkdownConverter;

final class BfmServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/bfm.php', 'bfm');

        $this->app->singleton(MarkdownConverter::class, function ($app) {
            $config = $app['config']['bfm'];

            $profile = RenderProfile::from($config['profile'] ?? 'Html');

            $mentionResolver = $config['resolvers']['mention']
                ? $app->make($config['resolvers']['mention'])
                : null;

            $embedResolver = $config['resolvers']['embed']
                ? $app->make($config['resolvers']['embed'])
                : null;

            $environment = BfmEnvironmentFactory::create(
                profile: $profile,
                embedResolver: $embedResolver,
                mentionResolver: $mentionResolver,
            );

            return new MarkdownConverter($environment);
        });
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerMacros();
        $this->registerBladeDirectives();
    }

    private function registerPublishing(): void
    {
        $this->publishes([
            __DIR__ . '/../config/bfm.php' => config_path('bfm.php'),
        ], 'bfm-config');

        $this->publishes([
            __DIR__ . '/../resources/css' => public_path('vendor/bfm'),
        ], 'bfm-assets');
    }

    private function registerMacros(): void
    {
        Str::macro('bfm', function (string $markdown): string {
            return (string) app(MarkdownConverter::class)->convert($markdown);
        });

        Str::macro('inlineBfm', function (string $markdown): string {
            // Inline rendering strips block-level wrappers
            $html = (string) app(MarkdownConverter::class)->convert($markdown);
            // Remove wrapping <p>...</p> for inline use
            return preg_replace('/^<p>(.*)<\/p>$/s', '$1', trim($html)) ?? $html;
        });
    }

    private function registerBladeDirectives(): void
    {
        Blade::directive('bfmStyles', function () {
            $path = public_path('vendor/bfm/bfm.css');
            if (file_exists($path)) {
                return '<link rel="stylesheet" href="<?php echo e(asset(\'vendor/bfm/bfm.css\')); ?>">';
            }
            // Fallback: inline styles from package
            $css = file_get_contents(__DIR__ . '/../resources/css/bfm.css');
            return '<style>' . $css . '</style>';
        });
    }
}
```

**Key decisions**:
- `MarkdownConverter::class` as the singleton binding key — matches the actual class, simple to resolve
- `RenderProfile` uses `from()` requiring the config to store the enum case name as a string (e.g., `'Html'`, `'Email'`)
- `inlineBfm` strips the wrapping `<p>` tag via regex rather than maintaining a separate inline-only environment. If this proves insufficient, we can add a proper `InlinesOnlyExtension`-based environment later.
- `@bfmStyles` checks for published assets first, falls back to inline. This is evaluated at Blade compilation time, so changes require `view:clear`.

**Implementation steps**:
1. Create `packages/laravel/src/BfmServiceProvider.php`
2. Implement `register()` with config merging and MarkdownConverter singleton
3. Implement `boot()` with publishing, macros, and Blade directives
4. Test auto-discovery in Testbench

### 4. Config File

**Overview**: Publishable config controlling BFM behavior.

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Render Profile
    |--------------------------------------------------------------------------
    |
    | Controls the output format. Options: 'Html', 'Email', 'Plain'
    |
    */
    'profile' => 'Html',

    /*
    |--------------------------------------------------------------------------
    | Resolvers
    |--------------------------------------------------------------------------
    |
    | Class names for mention and embed resolvers. Set to null to disable
    | resolution (mentions render as plain spans, embeds render as links).
    | Classes are resolved from the container.
    |
    */
    'resolvers' => [
        'mention' => null,
        'embed' => null,
    ],
];
```

**Key decisions**:
- No extension toggles in v1. All BFM extensions are always enabled. Toggling individual extensions adds complexity without clear user demand. Can add later if needed.
- Profile stored as string enum case name, not enum value — cleaner config files.

### 5. Test Suite (Orchestra Testbench)

**Overview**: Tests using Orchestra Testbench to simulate a Laravel application.

```php
<?php

namespace Birdcar\Markdown\Laravel\Tests;

use Birdcar\Markdown\Laravel\BfmServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [BfmServiceProvider::class];
    }
}
```

**Key test cases for BfmServiceProviderTest**:
- Provider registers `MarkdownConverter` as singleton
- Config is merged with defaults
- Published config path is correct
- `Str::bfm()` macro exists
- `Str::inlineBfm()` macro exists

**Key test cases for StrMacroTest**:
- `Str::bfm()` converts basic markdown to HTML
- `Str::bfm()` renders BFM task markers correctly
- `Str::bfm()` renders mentions correctly
- `Str::bfm()` renders callouts correctly
- `Str::inlineBfm()` returns HTML without `<p>` wrapper
- `Str::inlineBfm()` renders inline mentions
- Custom mention resolver from config is used
- Custom embed resolver from config is used

## Testing Requirements

### Unit Tests

| Test File | Coverage |
|-----------|----------|
| `packages/laravel/tests/BfmServiceProviderTest.php` | Service provider registration, config publishing, singleton binding |
| `packages/laravel/tests/StrMacroTest.php` | Str::bfm(), Str::inlineBfm() with all BFM syntax variants |

### Integration Tests

| Test File | Coverage |
|-----------|----------|
| `packages/laravel/tests/BfmServiceProviderTest.php` | Container resolution, config-driven resolver injection |

### Manual Testing

- [ ] `composer require birdcar/markdown-laravel` in a fresh Laravel 12 project
- [ ] `php artisan vendor:publish --tag=bfm-config` creates config
- [ ] `Str::bfm('- [>] Test')` in tinker returns expected HTML
- [ ] Binding a custom resolver class in config changes mention/embed output

## Error Handling

| Error Scenario | Handling Strategy |
|----------------|-------------------|
| Invalid resolver class in config | Container throws `BindingResolutionException` — let it propagate. Misconfiguration should fail fast. |
| Invalid render profile string | `RenderProfile::from()` throws `ValueError` — let it propagate with clear error. |
| Null/empty markdown input to macros | Return empty string. `MarkdownConverter::convert('')` handles this. |

## Validation Commands

```bash
# Core library tests (from root)
composer test

# Laravel package tests
cd packages/laravel && composer install && composer test

# Static analysis (core)
composer analyse

# Static analysis (Laravel package)
cd packages/laravel && composer analyse

# All tests
composer test:all
```

## Rollout Considerations

- **Package versioning**: `birdcar/markdown-laravel` starts at `0.1.0` matching the core library
- **Packagist**: Will need to register `birdcar/markdown-laravel` as a new package on Packagist (separate from `birdcar/markdown-php`)
- **Monorepo tooling**: Consider a tool like `symplify/monorepo-builder` for version sync if the package count grows

## Open Items

- [ ] Decide if `RenderProfile` should be backed by strings (`RenderProfile: string`) to make config cleaner, or keep the current unit enum and do string-to-enum mapping in the provider
- [ ] Consider whether `inlineBfm` regex approach is robust enough or if we need a proper `InlinesOnlyExtension` environment

---

*This spec is ready for implementation. Follow the patterns and validate at each step.*

# PRD: BFM Laravel Integration - Phase 1

**Contract**: ./contract.md
**Phase**: 1 of 3
**Focus**: Monorepo structure + `birdcar/markdown-laravel` core package

## Phase Overview

This phase establishes the monorepo architecture and builds the foundational Laravel integration package. Everything else depends on this: the Filament package requires the Laravel service provider, container bindings, and Str macros to function.

The monorepo structure uses Composer path repositories so all packages can be developed and tested together while remaining independently installable from Packagist. The core `birdcar/markdown-php` library stays at the repo root; integration packages go under `packages/`.

After this phase, Laravel developers can `composer require birdcar/markdown-laravel`, and immediately use `Str::bfm()`, `app(MarkdownConverter::class)`, and configure resolvers via a published config file. This alone covers the most common use case: rendering BFM content in Blade views.

## User Stories

1. As a Laravel developer, I want to install `birdcar/markdown-laravel` and have BFM rendering work immediately so that I don't need to manually configure `league/commonmark` extensions.
2. As a Laravel developer, I want `Str::bfm($markdown)` and `Str::inlineBfm($markdown)` macros so that I can render BFM content anywhere I'd normally use `Str::markdown()`.
3. As a Laravel developer, I want to bind my own `MentionResolverInterface` and `EmbedResolverInterface` implementations via config or container so that mentions and embeds resolve to my app's data.
4. As a Laravel developer, I want to publish and customize a config file so that I can toggle individual BFM extensions, set the render profile, and configure resolver classes.
5. As a Laravel developer, I want `@bfmStyles` in my Blade layout so that BFM output is styled correctly without manual CSS.

## Functional Requirements

### Monorepo Structure

- **FR-1.1**: Create `packages/laravel/` directory with its own `composer.json` declaring `birdcar/markdown-laravel` as the package name
- **FR-1.2**: Root `composer.json` must define path repositories pointing to `packages/*` for local development
- **FR-1.3**: Each package must have its own PHPUnit config and test suite that can run independently
- **FR-1.4**: Root-level `composer test` should run tests for all packages

### Service Provider

- **FR-1.5**: `BfmServiceProvider` must auto-discover via Composer's `extra.laravel.providers` configuration
- **FR-1.6**: Service provider registers a singleton `MarkdownConverter` using `BfmEnvironmentFactory::create()`, pulling render profile and resolver classes from config
- **FR-1.7**: Service provider registers `Str::bfm()` and `Str::inlineBfm()` macros
- **FR-1.8**: Service provider registers `@bfmStyles` Blade directive
- **FR-1.9**: Service provider publishes `config/bfm.php` via `vendor:publish --tag=bfm-config`

### Str Macros

- **FR-1.10**: `Str::bfm(string $markdown): string` converts BFM markdown to HTML using the container-bound converter
- **FR-1.11**: `Str::inlineBfm(string $markdown): string` converts inline BFM (no wrapping block elements) using an inline-only environment
- **FR-1.12**: Both macros must use the same resolver instances and render profile from config

### Config File

- **FR-1.13**: Config file at `config/bfm.php` with keys: `profile` (RenderProfile enum), `extensions` (array of extension class toggles), `resolvers.mention` (class string or null), `resolvers.embed` (class string or null)
- **FR-1.14**: Default config enables all extensions with `RenderProfile::Html` and null resolvers
- **FR-1.15**: Config values are resolved from the container where applicable (resolver classes instantiated via `app()->make()`)

### Blade Directive

- **FR-1.16**: `@bfmStyles` outputs a `<style>` tag with default CSS for BFM output elements OR a `<link>` to a published stylesheet
- **FR-1.17**: Styles must be publishable via `vendor:publish --tag=bfm-assets` for customization

## Non-Functional Requirements

- **NFR-1.1**: Package must support Laravel 10, 11, and 12
- **NFR-1.2**: PHP 8.2+ requirement (matching core library)
- **NFR-1.3**: PHPStan level 8 passes for all package code
- **NFR-1.4**: No runtime performance regression — `Str::bfm()` should add negligible overhead vs calling `BfmEnvironmentFactory::create()` directly (singleton converter)

## Dependencies

### Prerequisites

- Existing `birdcar/markdown-php` core library (complete)
- `league/commonmark` ^2.7 (already required)

### Outputs for Next Phase

- `BfmServiceProvider` with container bindings (Phase 2's Filament package depends on these)
- `Str::bfm()` macro for server-side rendering (Phase 2 uses this for editor preview)
- Published config structure (Phase 2's Filament config extends or references this)
- Monorepo structure with `packages/` pattern (Phase 2 adds `packages/filament/`)

## Acceptance Criteria

- [ ] `packages/laravel/composer.json` exists with correct name, dependencies, and autoload config
- [ ] Root `composer.json` defines path repository for `packages/laravel`
- [ ] `BfmServiceProvider` auto-discovers in a fresh Laravel app
- [ ] `Str::bfm('- [>] Task //due:tomorrow')` returns HTML with task marker, state, and modifier spans
- [ ] `Str::inlineBfm('@sarah hello')` returns mention HTML without `<p>` wrapper
- [ ] `app(MarkdownConverter::class)` resolves a working converter
- [ ] `php artisan vendor:publish --tag=bfm-config` creates `config/bfm.php`
- [ ] Config changes (e.g., disabling an extension) are reflected in converter output
- [ ] Custom resolver classes specified in config are instantiated and used
- [ ] `@bfmStyles` directive outputs valid CSS covering all BFM output element classes
- [ ] All unit tests pass (`composer test` from root and from `packages/laravel/`)
- [ ] PHPStan level 8 passes

## Open Questions

- Should the `@bfmStyles` directive inline the CSS or link to a published file? Inlining is simpler but can't be cached by browsers. Publishing requires `artisan vendor:publish`. Consider supporting both via config toggle.
- Should we provide a `bfm()` global helper function (like Laravel's `str()` helper) in addition to the Str macros?

---

*Review this PRD and provide feedback before spec generation.*

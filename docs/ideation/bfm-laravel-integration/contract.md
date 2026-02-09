# BFM Laravel Integration Contract

**Created**: 2026-02-09
**Confidence Score**: 95/100
**Status**: Draft

## Problem Statement

The `birdcar/markdown-php` library implements Birdcar Flavored Markdown (BFM) as a set of `league/commonmark` extensions. While it works with any PHP project, Laravel developers — especially those using Filament and Flux UI — face friction when integrating BFM into their applications. There's no service provider, no Blade helpers, no `Str` macro, and no editor integration. Users must manually wire up the `BfmEnvironmentFactory`, configure resolvers, and figure out how to render BFM content in their views and admin panels on their own.

Filament's `MarkdownEditor` uses `league/commonmark` under the hood (via Laravel's `Str::markdown()`), but its editor preview runs on client-side EasyMDE/marked.js which knows nothing about BFM syntax. Flux UI's editor is Tiptap-based and has no PHP markdown pipeline at all. Both require deliberate integration work that most Laravel developers shouldn't have to think about.

## Goals

1. **Zero-config Laravel setup**: `composer require birdcar/markdown-laravel` auto-discovers a service provider, registers `Str::bfm()` and `Str::inlineBfm()` macros, and binds a configured `MarkdownConverter` singleton — working out of the box.
2. **Filament form field with live preview**: Ship a `BfmEditor` Filament form field that wraps the standard markdown editor but provides server-side BFM preview rendering, so authors see their custom syntax rendered correctly.
3. **Monorepo package architecture**: All integration packages live in this repo under `packages/`, each with their own `composer.json`, independently installable via Composer.
4. **CSS/styling support**: Provide a `@bfmStyles` Blade directive (or publishable stylesheet) containing default styles for BFM output elements (task markers, callouts, mentions, embeds).
5. **Resolver integration**: Make it trivial to bind `MentionResolverInterface` and `EmbedResolverInterface` implementations via Laravel's container, with a config file for common settings.

## Success Criteria

- [ ] `composer require birdcar/markdown-laravel` installs and auto-discovers the service provider without any manual configuration
- [ ] `Str::bfm('- [>] Task //due:tomorrow')` returns correctly rendered HTML with BFM extensions active
- [ ] `Str::inlineBfm('@sarah mentioned you')` renders inline BFM (mentions, task modifiers) without wrapping `<p>` tags
- [ ] `app(MarkdownConverter::class)` resolves a fully-configured BFM converter from the container
- [ ] `php artisan vendor:publish --tag=bfm-config` publishes a config file for customizing render profile, resolver bindings, and extension toggles
- [ ] `@bfmStyles` Blade directive outputs a `<link>` or inline `<style>` with default BFM CSS
- [ ] `BfmEditor::make('content')` works as a drop-in Filament form field
- [ ] Filament `BfmEditor` preview panel renders BFM syntax correctly via server-side rendering (Livewire endpoint)
- [ ] Filament `BfmEditor` stores raw markdown (not HTML) in the database, consistent with Filament's standard `MarkdownEditor`
- [ ] All packages have PHPUnit tests passing
- [ ] PHPStan level 8 passes across all packages
- [ ] Packages can be installed independently (`birdcar/markdown-filament` requires `birdcar/markdown-laravel` which requires `birdcar/markdown-php`)

## Scope Boundaries

### In Scope

- **`birdcar/markdown-laravel`** package: Service provider, Str macros, Blade directives, config file, container bindings for resolvers and converter
- **`birdcar/markdown-filament`** package: Custom `BfmEditor` form field with server-side preview, `BfmTextColumn` and `BfmTextEntry` for tables/infolists, HTML sanitizer configuration for BFM output elements
- **Default CSS stylesheet** for BFM output elements, publishable via artisan
- **Config file** for toggling extensions, setting render profile, specifying resolver classes
- **Tests** for all packages (unit + integration)
- **Monorepo structure** with `packages/` directory and path-based Composer repositories for local development

### Out of Scope

- **Tiptap/JS editor extensions** — Deferred to a separate `@birdcar/markdown-tiptap` npm package. The editors in this project use standard text input (EasyMDE for Filament) with server-side preview.
- **Flux UI editor integration** — Flux's editor is Tiptap-based and requires JS extensions. Deferred until the Tiptap package exists. The Laravel package's `Str::bfm()` and Blade directives still work for rendering BFM in Flux-powered views.
- **Email rendering integration** — The core library already supports `RenderProfile::Email`. Laravel Mail integration is documented but not a dedicated package.
- **Markdown input/output mode for Flux** — Changing Flux editor's output from HTML to markdown requires Tiptap extensions (out of scope).

### Future Considerations

- `@birdcar/markdown-tiptap` npm package for Tiptap extensions (enables Flux editor + Filament Tiptap editor integration)
- `@bfmEditorScripts` Blade directive that loads Tiptap extensions from CDN or vendor-published assets
- Filament v4 editor extension support (when available)
- `birdcar/markdown-flux` package that registers Tiptap extensions with Flux's `flux:editor` event
- Inertia.js / Vue / React component wrappers for BFM rendering

---

*This contract was generated from brain dump input. Review and approve before proceeding to PRD generation.*

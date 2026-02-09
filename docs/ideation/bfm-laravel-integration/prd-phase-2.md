# PRD: BFM Laravel Integration - Phase 2

**Contract**: ./contract.md
**Phase**: 2 of 3
**Focus**: Filament integration package with server-side preview

## Phase Overview

This phase builds the `birdcar/markdown-filament` package — a Filament v3 integration that provides a `BfmEditor` form field, `BfmTextColumn` table column, and `BfmTextEntry` infolist entry. The centerpiece is the editor's server-side preview: while Filament's standard `MarkdownEditor` uses client-side marked.js (which doesn't know BFM syntax), our `BfmEditor` sends preview requests to a Livewire endpoint that renders through the PHP BFM pipeline.

This phase depends on Phase 1's service provider and container bindings. The Filament package requires `birdcar/markdown-laravel` and uses its `MarkdownConverter` singleton for all rendering.

After this phase, Filament users can swap `MarkdownEditor::make('content')` with `BfmEditor::make('content')` and get full BFM support with accurate preview rendering in the admin panel.

## User Stories

1. As a Filament admin panel developer, I want a `BfmEditor` form field that replaces `MarkdownEditor` so that I can author BFM content with accurate live preview.
2. As a Filament developer, I want `BfmTextColumn` and `BfmTextEntry` components so that stored BFM markdown renders correctly in tables and infolists without manual `formatStateUsing()`.
3. As a Filament developer, I want the `BfmEditor` to store raw markdown (not HTML) so that my data model stays consistent with Filament conventions.
4. As a Filament developer, I want the HTML sanitizer to allow BFM-specific elements so that rendered output isn't stripped of callout containers, embed figures, or task marker spans.

## Functional Requirements

### BfmEditor Form Field

- **FR-2.1**: `BfmEditor` extends or wraps Filament's `MarkdownEditor`, inheriting toolbar customization, file attachment support, and all standard configuration methods
- **FR-2.2**: Editor preview panel renders via a Livewire endpoint that calls `Str::bfm()` on the current editor content, returning rendered HTML
- **FR-2.3**: Preview updates are debounced (300ms default, configurable) to avoid excessive server calls during typing
- **FR-2.4**: Editor stores raw markdown as the field value (not HTML), identical to Filament's standard behavior
- **FR-2.5**: `BfmEditor::make('content')` is the only required call — all BFM configuration comes from the `bfm.php` config file registered by the Laravel package

### Display Components

- **FR-2.6**: `BfmTextColumn::make('content')` renders BFM markdown to HTML in Filament table columns, using the container-bound converter
- **FR-2.7**: `BfmTextEntry::make('content')` renders BFM markdown to HTML in Filament infolist entries
- **FR-2.8**: Both components apply HTML sanitization that preserves BFM-specific elements

### HTML Sanitizer Integration

- **FR-2.9**: Package registers a custom `HtmlSanitizer` configuration (or extends the existing one) that allows BFM output elements: `<aside>`, `<figure>`, `<figcaption>`, `<span>` with BFM data attributes, and any elements produced by embed resolvers
- **FR-2.10**: Sanitizer configuration is additive — it extends Filament's existing allowed elements, not replacing them

### Service Provider

- **FR-2.11**: `BfmFilamentServiceProvider` auto-discovers via Composer
- **FR-2.12**: Service provider registers the Livewire preview component/endpoint
- **FR-2.13**: Service provider configures HTML sanitizer allowlists for BFM elements

## Non-Functional Requirements

- **NFR-2.1**: Filament v3.x support (3.0+)
- **NFR-2.2**: Preview rendering latency under 100ms for typical markdown content (leverages singleton converter)
- **NFR-2.3**: No JavaScript bundle required — server-side preview uses Livewire's existing wire transport
- **NFR-2.4**: BfmEditor should not break Filament's standard form lifecycle (validation, saving, loading defaults)

## Dependencies

### Prerequisites

- Phase 1 complete: `birdcar/markdown-laravel` package with service provider, Str macros, container bindings
- Filament v3.x installed in target application
- `livewire/livewire` v3.x (comes with Filament)

### Outputs for Next Phase

- `BfmEditor`, `BfmTextColumn`, `BfmTextEntry` components (Phase 3 adds CSS that styles their output)
- Sanitizer configuration (Phase 3 ensures styles match allowed elements)

## Acceptance Criteria

- [ ] `packages/filament/composer.json` exists with `birdcar/markdown-filament` name, requiring `birdcar/markdown-laravel`
- [ ] `BfmEditor::make('content')` renders in a Filament form without errors
- [ ] Typing BFM syntax in the editor and clicking preview shows correctly rendered HTML (task markers, callouts, mentions, embeds)
- [ ] Preview updates are debounced and don't block the editor
- [ ] Editor stores raw markdown string when form is saved
- [ ] `BfmTextColumn::make('content')` renders BFM in a Filament table
- [ ] `BfmTextEntry::make('content')` renders BFM in a Filament infolist
- [ ] HTML sanitizer does not strip BFM-specific elements (callout `<aside>`, embed `<figure>`, task `<span>` with data attributes)
- [ ] All tests pass
- [ ] PHPStan level 8 passes

## Open Questions

- Should the Livewire preview endpoint be CSRF-protected and rate-limited, or trust Filament's existing middleware? Filament admin routes are already behind auth middleware.
- Should `BfmEditor` extend `MarkdownEditor` directly (tight coupling to Filament internals) or wrap it via composition? Composition is safer across Filament minor versions.
- Is there value in a `BfmRichEditor` that wraps Filament's `RichEditor` (Tiptap-based) for a future where we have Tiptap extensions? Or is that premature?

---

*Review this PRD and provide feedback before spec generation.*

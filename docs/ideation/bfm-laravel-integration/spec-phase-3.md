# Implementation Spec: BFM Laravel Integration - Phase 3

**PRD**: ./prd-phase-3.md
**Estimated Effort**: S

## Technical Approach

This phase creates the default CSS stylesheet for BFM output and wires it into both the Laravel package's `@bfmStyles` directive and the Filament components. The stylesheet uses CSS custom properties (variables) for theming, supports light/dark mode via `prefers-color-scheme`, and is scoped to BFM class names to prevent conflicts.

The CSS covers all HTML output classes produced by the core library's renderers. The design is functional and clean — not opinionated about fonts or layout, just the BFM-specific elements (task markers, callouts, mentions, embeds, task modifiers).

## File Changes

### New Files

| File Path | Purpose |
|-----------|---------|
| `packages/laravel/resources/css/bfm.css` | Main stylesheet (replaces Phase 1 placeholder) |
| `packages/laravel/resources/css/bfm.min.css` | Minified version for production |
| `spec/fixtures/kitchen-sink-styled.html` | Full HTML fixture for visual regression testing |

### Modified Files

| File Path | Changes |
|-----------|---------|
| `packages/laravel/src/BfmServiceProvider.php` | Update `@bfmStyles` directive to handle published vs inline correctly |
| `packages/filament/resources/views/forms/components/bfm-editor.blade.php` | Include BFM styles in preview panel |
| `packages/filament/src/BfmFilamentServiceProvider.php` | Register Filament-specific asset loading |

### Deleted Files

None.

## Implementation Details

### 1. CSS Custom Properties (Theming)

**Overview**: Define CSS custom properties at the root level for all BFM colors and spacing, with dark mode overrides.

```css
:root {
  /* Task marker colors */
  --bfm-task-open: #6b7280;
  --bfm-task-done: #16a34a;
  --bfm-task-scheduled: #2563eb;
  --bfm-task-migrated: #7c3aed;
  --bfm-task-irrelevant: #9ca3af;
  --bfm-task-event: #0891b2;
  --bfm-task-priority: #dc2626;

  /* Callout colors (border-left + background) */
  --bfm-callout-info-border: #2563eb;
  --bfm-callout-info-bg: #eff6ff;
  --bfm-callout-warning-border: #d97706;
  --bfm-callout-warning-bg: #fffbeb;
  --bfm-callout-error-border: #dc2626;
  --bfm-callout-error-bg: #fef2f2;
  --bfm-callout-tip-border: #16a34a;
  --bfm-callout-tip-bg: #f0fdf4;
  --bfm-callout-note-border: #6b7280;
  --bfm-callout-note-bg: #f9fafb;

  /* Mention */
  --bfm-mention-bg: #dbeafe;
  --bfm-mention-text: #1d4ed8;

  /* Embed */
  --bfm-embed-border: #e5e7eb;
  --bfm-embed-caption: #6b7280;

  /* Task modifier */
  --bfm-modifier-text: #6b7280;
  --bfm-modifier-bg: #f3f4f6;
}

@media (prefers-color-scheme: dark) {
  :root {
    --bfm-task-open: #9ca3af;
    --bfm-task-done: #22c55e;
    --bfm-task-scheduled: #60a5fa;
    --bfm-task-migrated: #a78bfa;
    --bfm-task-irrelevant: #6b7280;
    --bfm-task-event: #22d3ee;
    --bfm-task-priority: #ef4444;

    --bfm-callout-info-border: #3b82f6;
    --bfm-callout-info-bg: #1e293b;
    --bfm-callout-warning-border: #f59e0b;
    --bfm-callout-warning-bg: #1c1917;
    --bfm-callout-error-border: #ef4444;
    --bfm-callout-error-bg: #1c1917;
    --bfm-callout-tip-border: #22c55e;
    --bfm-callout-tip-bg: #0f1f17;
    --bfm-callout-note-border: #6b7280;
    --bfm-callout-note-bg: #1f2937;

    --bfm-mention-bg: #1e3a5f;
    --bfm-mention-text: #93c5fd;

    --bfm-embed-border: #374151;
    --bfm-embed-caption: #9ca3af;

    --bfm-modifier-text: #9ca3af;
    --bfm-modifier-bg: #1f2937;
  }
}
```

### 2. Component Styles

**Task markers and items**:
```css
.task-item {
  list-style: none;
}

.task-marker {
  font-weight: 600;
  margin-right: 0.25em;
}

.task-marker__icon {
  display: inline-block;
  width: 1.25em;
  text-align: center;
}

.task-marker--open { color: var(--bfm-task-open); }
.task-marker--done { color: var(--bfm-task-done); }
.task-marker--scheduled { color: var(--bfm-task-scheduled); }
.task-marker--migrated { color: var(--bfm-task-migrated); }
.task-marker--irrelevant { color: var(--bfm-task-irrelevant); }
.task-marker--event { color: var(--bfm-task-event); }
.task-marker--priority { color: var(--bfm-task-priority); }

.task-item--done {
  text-decoration: line-through;
  opacity: 0.7;
}

.task-item--irrelevant {
  text-decoration: line-through;
  opacity: 0.5;
}
```

**Task modifiers**:
```css
.task-mod {
  font-size: 0.8em;
  color: var(--bfm-modifier-text);
  background: var(--bfm-modifier-bg);
  padding: 0.1em 0.4em;
  border-radius: 0.25em;
  margin-left: 0.25em;
  font-family: monospace;
}
```

**Mentions**:
```css
.mention {
  color: var(--bfm-mention-text);
  background: var(--bfm-mention-bg);
  padding: 0.1em 0.4em;
  border-radius: 0.75em;
  font-weight: 500;
  text-decoration: none;
}

a.mention:hover {
  text-decoration: underline;
}
```

**Callouts**:
```css
.callout {
  border-left: 4px solid var(--bfm-callout-note-border);
  background: var(--bfm-callout-note-bg);
  padding: 0.75em 1em;
  margin: 1em 0;
  border-radius: 0 0.25em 0.25em 0;
}

.callout__header {
  font-weight: 600;
  margin-bottom: 0.5em;
}

.callout__body > :first-child { margin-top: 0; }
.callout__body > :last-child { margin-bottom: 0; }

.callout--info { border-left-color: var(--bfm-callout-info-border); background: var(--bfm-callout-info-bg); }
.callout--warning { border-left-color: var(--bfm-callout-warning-border); background: var(--bfm-callout-warning-bg); }
.callout--error { border-left-color: var(--bfm-callout-error-border); background: var(--bfm-callout-error-bg); }
.callout--tip { border-left-color: var(--bfm-callout-tip-border); background: var(--bfm-callout-tip-bg); }
.callout--note { border-left-color: var(--bfm-callout-note-border); background: var(--bfm-callout-note-bg); }
```

**Embeds**:
```css
.embed {
  border: 1px solid var(--bfm-embed-border);
  border-radius: 0.5em;
  overflow: hidden;
  margin: 1em 0;
}

.embed__link {
  display: block;
  padding: 1em;
  word-break: break-all;
}

.embed__caption {
  padding: 0.5em 1em;
  font-size: 0.875em;
  color: var(--bfm-embed-caption);
  border-top: 1px solid var(--bfm-embed-border);
}
```

**Key decisions**:
- All styles use BFM-specific class names (`task-marker`, `callout`, `mention`, `embed`, `task-mod`) — no collision risk with Tailwind or Bootstrap
- CSS custom properties enable theming by overriding variables without touching selectors
- Dark mode uses `prefers-color-scheme` media query by default. Users can override by redefining the variables under a `.dark` class selector if their app uses class-based dark mode.
- No `!important` declarations — styles are easy to override
- No font or layout opinions beyond BFM elements

### 3. @bfmStyles Directive Update

**Overview**: Update the Blade directive implementation to properly handle the asset loading.

```php
private function registerBladeDirectives(): void
{
    Blade::directive('bfmStyles', function () {
        return "<?php echo \\Birdcar\\Markdown\\Laravel\\BfmServiceProvider::renderStyles(); ?>";
    });
}

public static function renderStyles(): string
{
    $publishedPath = public_path('vendor/bfm/bfm.css');
    if (file_exists($publishedPath)) {
        $url = asset('vendor/bfm/bfm.css');
        return '<link rel="stylesheet" href="' . e($url) . '">';
    }

    // Fallback: inline styles
    $css = file_get_contents(__DIR__ . '/../resources/css/bfm.css');
    return '<style>' . $css . '</style>';
}
```

**Key change from Phase 1**: Moved to a static method call that executes at runtime (not Blade compile time). This correctly handles the published/inline fallback on every request, not just on view compilation.

### 4. Filament Style Integration

**Overview**: The Filament preview panel and display components need BFM styles applied. Two approaches:

1. **Preview panel**: Include inline styles in the preview container (scoped to the preview div)
2. **Admin panel**: Register a Filament asset that loads the BFM stylesheet

```php
// In BfmFilamentServiceProvider::boot()
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Css;

FilamentAsset::register([
    Css::make('bfm-styles', __DIR__ . '/../resources/css/bfm-filament.css')
        ->loadedOnRequest(),
], package: 'birdcar/markdown-filament');
```

The `bfm-filament.css` file imports or duplicates the base BFM styles with any Filament-specific adjustments (e.g., adapting to Filament's dark mode class `.fi-theme-dark` instead of `prefers-color-scheme`).

### 5. Kitchen-Sink Visual Fixture

**Overview**: A complete HTML file that renders all BFM elements with styles applied, usable for visual regression testing.

**Implementation steps**:
1. Take the existing `spec/fixtures/blocks/kitchen-sink.html` output
2. Wrap it in a full HTML document with the BFM stylesheet linked
3. Include both light and dark mode sections for comparison
4. Save as `spec/fixtures/kitchen-sink-styled.html`

## Testing Requirements

### Unit Tests

No new test files needed. Tests are primarily visual.

**Key validations**:
- CSS file exists and is non-empty
- `@bfmStyles` returns a `<link>` tag when assets are published
- `@bfmStyles` returns a `<style>` tag with CSS content when assets are not published
- CSS file is under 10KB minified

### Manual Testing

- [ ] Open kitchen-sink-styled.html in browser — verify all elements are styled
- [ ] Toggle system dark mode — verify colors adapt
- [ ] Include `@bfmStyles` in a Laravel Blade view — verify CSS loads
- [ ] Publish assets and verify `<link>` tag is used instead of inline
- [ ] Open Filament admin panel with BfmEditor — verify preview has styles
- [ ] Verify BfmTextColumn and BfmTextEntry render styled in tables/infolists
- [ ] Test alongside Tailwind CSS project — verify no class conflicts
- [ ] Test alongside Bootstrap project — verify no class conflicts
- [ ] Check color contrast ratios with browser dev tools (WCAG AA)

## Error Handling

| Error Scenario | Handling Strategy |
|----------------|-------------------|
| CSS file missing from package | `renderStyles()` returns empty string rather than erroring. Log a warning. |
| Filament asset registration fails | Graceful degradation — components still work, just unstyled |

## Validation Commands

```bash
# Verify CSS file size
wc -c packages/laravel/resources/css/bfm.css
wc -c packages/laravel/resources/css/bfm.min.css

# Run all tests (should still pass with style changes)
composer test:all

# Static analysis
composer analyse:all

# Visual check
open spec/fixtures/kitchen-sink-styled.html
```

## Rollout Considerations

- **Breaking change potential**: None. Styles are additive — existing BFM output that was unstyled will now have styling when `@bfmStyles` is used. No existing behavior changes.
- **CSS minification**: Consider using a build step (e.g., `lightningcss` or `csso`) or just ship both `.css` and `.min.css` manually for this small file.

## Open Items

- [ ] Decide on the exact set of callout types to style — currently spec has info, warning, error, tip, note. Are there others?
- [ ] Filament dark mode: determine if Filament uses `.dark`, `.fi-theme-dark`, or `prefers-color-scheme` — the CSS needs to support whichever mechanism Filament uses
- [ ] Consider if a Tailwind plugin is worth the effort for Phase 3 or if it should be deferred entirely

---

*This spec is ready for implementation. Follow the patterns and validate at each step.*

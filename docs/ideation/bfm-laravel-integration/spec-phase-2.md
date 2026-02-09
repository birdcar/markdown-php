# Implementation Spec: BFM Laravel Integration - Phase 2

**PRD**: ./prd-phase-2.md
**Estimated Effort**: L

## Technical Approach

This phase builds `birdcar/markdown-filament` under `packages/filament/`. The package provides three Filament components: `BfmEditor` (form field), `BfmTextColumn` (table column), and `BfmTextEntry` (infolist entry).

The core challenge is the editor preview. Filament's built-in `MarkdownEditor` uses EasyMDE with client-side marked.js for preview, which doesn't understand BFM. Our approach: **replace the preview mechanism with a Livewire-driven server-side render**. When the user toggles preview, the editor content is sent to a Livewire component method that calls `Str::bfm()` and returns rendered HTML. This keeps the raw EasyMDE editor for input (no Tiptap needed) while showing accurate BFM preview.

The implementation uses Filament's component architecture. `BfmEditor` extends `MarkdownEditor` and overrides the view to replace the preview panel with a Livewire-rendered section. The display components (`BfmTextColumn`, `BfmTextEntry`) are simpler — they format the stored markdown to HTML using the singleton converter and mark it as safe HTML.

## File Changes

### New Files

| File Path | Purpose |
|-----------|---------|
| `packages/filament/composer.json` | Package manifest for `birdcar/markdown-filament` |
| `packages/filament/src/BfmFilamentServiceProvider.php` | Filament service provider: component registration, sanitizer config |
| `packages/filament/src/Forms/Components/BfmEditor.php` | Custom form field extending MarkdownEditor with server-side preview |
| `packages/filament/src/Tables/Columns/BfmTextColumn.php` | Table column that renders BFM markdown |
| `packages/filament/src/Infolists/Components/BfmTextEntry.php` | Infolist entry that renders BFM markdown |
| `packages/filament/resources/views/forms/components/bfm-editor.blade.php` | Blade view for the editor with server-side preview panel |
| `packages/filament/resources/css/bfm-filament.css` | Filament-specific style overrides (admin panel context) |
| `packages/filament/tests/BfmEditorTest.php` | Tests for the editor component |
| `packages/filament/tests/BfmTextColumnTest.php` | Tests for the table column |
| `packages/filament/tests/BfmTextEntryTest.php` | Tests for the infolist entry |
| `packages/filament/tests/TestCase.php` | Base test case with Filament + Testbench setup |
| `packages/filament/phpunit.xml` | PHPUnit config |
| `packages/filament/phpstan.neon` | PHPStan config |

### Modified Files

| File Path | Changes |
|-----------|---------|
| `composer.json` (root) | Add `packages/filament` to path repositories |

### Deleted Files

None.

## Implementation Details

### 1. Filament Package composer.json

```json
{
  "name": "birdcar/markdown-filament",
  "description": "Filament integration for Birdcar Flavored Markdown",
  "type": "library",
  "license": "MIT",
  "require": {
    "php": "^8.2",
    "birdcar/markdown-laravel": "^0.1",
    "filament/filament": "^3.0"
  },
  "require-dev": {
    "phpunit/phpunit": "^11.0",
    "phpstan/phpstan": "^2.0",
    "orchestra/testbench": "^8.0|^9.0|^10.0"
  },
  "autoload": {
    "psr-4": {
      "Birdcar\\Markdown\\Filament\\": "src/"
    }
  },
  "autoload-dev": {
    "psr-4": {
      "Birdcar\\Markdown\\Filament\\Tests\\": "tests/"
    }
  },
  "extra": {
    "laravel": {
      "providers": [
        "Birdcar\\Markdown\\Filament\\BfmFilamentServiceProvider"
      ]
    }
  }
}
```

### 2. BfmEditor Form Field

**Pattern to follow**: Filament's `MarkdownEditor` component at `filament/forms/src/Components/MarkdownEditor.php`

**Overview**: Extends `MarkdownEditor` to override the preview behavior. The standard editor is kept for text input. Preview is handled by a Livewire action that renders BFM server-side.

```php
<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Filament\Forms\Components;

use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Support\Str;

class BfmEditor extends MarkdownEditor
{
    protected string $view = 'bfm-filament::forms.components.bfm-editor';

    protected int $previewDebounce = 300;

    public function previewDebounce(int $milliseconds): static
    {
        $this->previewDebounce = $milliseconds;
        return $this;
    }

    public function getPreviewDebounce(): int
    {
        return $this->previewDebounce;
    }

    public function getPreviewHtml(): string
    {
        $state = $this->getState();
        if (empty($state)) {
            return '';
        }

        return Str::bfm($state);
    }
}
```

**Key decisions**:
- **Extends MarkdownEditor** rather than wrapping via composition. This gives access to all standard MarkdownEditor methods (toolbar config, file attachments, etc.) without reimplementing. Risk: tight coupling to Filament internals. Acceptable for a v3-targeted package.
- **Custom Blade view** overrides only the preview panel. The editor input area uses the standard EasyMDE markup.
- **Server-side preview** via `$this->getPreviewHtml()` called from the Blade view. Filament's Livewire integration handles the reactivity.
- **Debounce** prevents excessive preview calls during typing.

**Implementation steps**:
1. Create the `BfmEditor` class extending `MarkdownEditor`
2. Create the Blade view that renders the editor with a preview panel
3. Wire up the preview to call `Str::bfm()` via Livewire action
4. Add debounce configuration
5. Test with various BFM syntax inputs

### 3. Blade View for BfmEditor

**Overview**: The custom Blade view replaces the client-side EasyMDE preview with a server-rendered HTML panel. The approach depends on how Filament's MarkdownEditor Blade view is structured — we may need to fully override it or partially inject via Alpine.js.

**Key approach**: Use a split-pane layout. Left side: EasyMDE text editor. Right side (or toggle): server-rendered BFM preview. The preview content is fetched via a Livewire call when the user clicks the preview button or after a debounce timeout.

```blade
{{-- Simplified concept --}}
<div
    x-data="{
        showPreview: false,
        previewHtml: '',
        debounceTimer: null,
    }"
    x-init="
        $watch('state', (value) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                if (showPreview) {
                    $wire.call('callSchemaComponentMethod', '{{ $getStatePath() }}', 'getPreviewHtml')
                        .then(html => previewHtml = html);
                }
            }, {{ $getPreviewDebounce() }});
        })
    "
>
    {{-- Standard EasyMDE editor --}}
    <div x-show="!showPreview">
        {{-- ... EasyMDE initialization from parent view ... --}}
    </div>

    {{-- Server-rendered preview --}}
    <div x-show="showPreview" class="bfm-preview prose">
        <div x-html="previewHtml"></div>
    </div>

    {{-- Toggle button --}}
    <button @click="showPreview = !showPreview; if (showPreview) { /* trigger preview fetch */ }">
        Preview
    </button>
</div>
```

**Key decisions**:
- Uses `$wire.call('callSchemaComponentMethod')` which is Filament's mechanism for calling methods on form components from the frontend
- Preview HTML is set via `x-html` Alpine directive — this means the HTML must be sanitized server-side
- The `showPreview` toggle replaces EasyMDE's built-in preview button behavior

**Note**: The exact Blade implementation will need to reference Filament's actual `markdown-editor.blade.php` view structure. The spec provides the architectural approach; the implementation will adapt to Filament's specific markup.

### 4. BfmTextColumn

**Overview**: Table column that renders stored markdown as BFM HTML.

```php
<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Filament\Tables\Columns;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;

class BfmTextColumn extends TextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->html();

        $this->formatStateUsing(function (?string $state): string {
            if ($state === null || $state === '') {
                return '';
            }
            return Str::bfm($state);
        });
    }
}
```

### 5. BfmTextEntry

**Overview**: Infolist entry that renders stored markdown as BFM HTML.

```php
<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Filament\Infolists\Components;

use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Str;

class BfmTextEntry extends TextEntry
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->html();

        $this->formatStateUsing(function (?string $state): string {
            if ($state === null || $state === '') {
                return '';
            }
            return Str::bfm($state);
        });
    }
}
```

### 6. BfmFilamentServiceProvider

**Overview**: Registers views, loads styles, and configures the HTML sanitizer.

```php
<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Filament;

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

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/bfm-filament'),
        ], 'bfm-filament-views');
    }
}
```

**Key decisions**:
- Views are namespaced under `bfm-filament::` to avoid conflicts
- HTML sanitizer configuration: Rather than replacing Filament's sanitizer globally, the `BfmTextColumn` and `BfmTextEntry` use `->html()` which trusts the HTML. Since the HTML is generated server-side by our own converter (not user input), this is safe. The editor preview also uses server-rendered HTML via `x-html`.
- No global sanitizer override needed — the BFM HTML is generated by trusted code, not passed through from user input.

## Testing Requirements

### Unit Tests

| Test File | Coverage |
|-----------|----------|
| `packages/filament/tests/BfmEditorTest.php` | Editor instantiation, preview rendering, debounce config, toolbar inheritance |
| `packages/filament/tests/BfmTextColumnTest.php` | Column renders BFM markdown, handles null/empty state |
| `packages/filament/tests/BfmTextEntryTest.php` | Entry renders BFM markdown, handles null/empty state |

**Key test cases**:
- `BfmEditor::make('content')` creates without error
- `BfmEditor` inherits all MarkdownEditor methods (toolbarButtons, fileAttachments)
- `BfmEditor->getPreviewHtml()` renders BFM syntax correctly (task markers, callouts, mentions)
- `BfmEditor->getPreviewHtml()` returns empty string for null/empty state
- `BfmTextColumn` renders markdown with task states to HTML
- `BfmTextColumn` handles null state gracefully
- `BfmTextEntry` renders callouts and mentions to HTML
- `BfmTextEntry` handles empty string gracefully
- Preview debounce is configurable

### Manual Testing

- [ ] Add `BfmEditor::make('content')` to a Filament resource form
- [ ] Type BFM syntax (tasks, callouts, mentions) in the editor
- [ ] Toggle preview and verify server-rendered HTML matches BFM spec
- [ ] Save form and verify raw markdown is stored (not HTML)
- [ ] Add `BfmTextColumn::make('content')` to a Filament table and verify rendering
- [ ] Add `BfmTextEntry::make('content')` to a Filament infolist and verify rendering
- [ ] Test with Filament dark mode enabled

## Error Handling

| Error Scenario | Handling Strategy |
|----------------|-------------------|
| Preview called with null state | Return empty string — no error |
| Malformed BFM in editor | `league/commonmark` is permissive — renders what it can, passes through what it can't. No special handling. |
| Livewire preview call fails (network) | Alpine catches the error; preview panel shows stale content or empty. No retry loop. |
| Very large markdown content in preview | Debounce prevents excessive calls. Content size is bounded by the database column. No explicit size limit. |

## Validation Commands

```bash
# Filament package tests
cd packages/filament && composer install && composer test

# Static analysis
cd packages/filament && composer analyse

# All packages
composer test:all
```

## Rollout Considerations

- **Filament version**: Targets v3.x only. v4 may change editor internals significantly.
- **Blade view override**: The custom Blade view is the most fragile part. If Filament updates their markdown editor view structure, ours may need updating. Publish the view from Filament as a reference during development.
- **Performance**: Preview rendering is server-side. For very large documents, this adds latency. The debounce mitigates this, but consider adding a loading indicator in the preview panel.

## Open Items

- [ ] Investigate Filament's `callSchemaComponentMethod` mechanism — confirm it's the correct way to call component methods from Alpine.js
- [ ] Determine if `BfmEditor` needs to explicitly disable EasyMDE's built-in preview button to avoid confusion with the server-side preview
- [ ] Research whether Filament's `MarkdownEditor` Blade view can be partially overridden or if a full copy is needed

---

*This spec is ready for implementation. Follow the patterns and validate at each step.*

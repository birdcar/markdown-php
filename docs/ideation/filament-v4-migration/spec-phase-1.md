# Implementation Spec: Filament v4 Migration - Phase 1

**PRD**: ./prd-phase-1.md
**Estimated Effort**: S

## Technical Approach

This is a targeted migration with well-defined API changes. The core strategy is:

1. Update Composer constraints to target Filament v4 / Laravel 11.28+
2. Move `renderBfmPreview()` from the `HasBfmEditor` trait to `BfmEditor` directly, using `#[ExposedLivewireMethod]` (new in v4)
3. Update the Blade view to match v4's patterns (`$getKey()`, `callSchemaComponentMethod`, v4 file upload, `hasFileAttachments()`)
4. Delete the now-unnecessary trait and its tests
5. Add tests for the preview method on `BfmEditor` directly

The key insight: Filament v4 introduced `callSchemaComponentMethod` which lets form components expose Livewire-callable methods. This means `BfmEditor` can own its preview rendering — users no longer need `HasBfmEditor` on their page classes.

## File Changes

### Modified Files

| File Path | Changes |
|-----------|---------|
| `packages/filament/composer.json` | `filament/filament: ^4.0`, `illuminate/support: ^11.28\|^12.0`, `orchestra/testbench: ^9.0\|^10.0` |
| `packages/filament/src/Forms/Components/BfmEditor.php` | Add `renderBfmPreview()` with `#[ExposedLivewireMethod]` + `#[Renderless]` |
| `packages/filament/resources/views/forms/components/bfm-editor.blade.php` | v4 Blade patterns: `$getKey()`, `callSchemaComponentMethod`, `hasFileAttachments()`, v4 file upload |
| `packages/filament/tests/BfmEditorTest.php` | Remove `hasToolbarButton` test, add preview method tests |
| `packages/filament/tests/TestCase.php` | Add `SchemasServiceProvider` (new in v4) |
| `packages/filament/phpstan.neon` | Remove unused trait ignore |

### Deleted Files

| File Path | Reason |
|-----------|--------|
| `packages/filament/src/Concerns/HasBfmEditor.php` | Replaced by `BfmEditor::renderBfmPreview()` via `callSchemaComponentMethod` |
| `packages/filament/tests/HasBfmEditorTest.php` | Tests for deleted trait |

## Implementation Details

### 1. Update composer.json

**Overview**: Bump version constraints to target Filament v4 and its minimum Laravel version.

```json
{
    "require": {
        "php": "^8.2",
        "birdcar/markdown-laravel": "@dev",
        "filament/filament": "^4.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^11.0",
        "phpstan/phpstan": "^2.0",
        "orchestra/testbench": "^9.0|^10.0"
    }
}
```

**Key decisions**:
- Drop `^3.0` for filament — clean break, no dual-version support
- Drop `^8.0` for orchestra/testbench — v8 is Laravel 10 which Filament v4 doesn't support
- `illuminate/support` constraint is implicit via `filament/filament` dependency, no need to specify separately

**Implementation steps**:
1. Edit `packages/filament/composer.json` — change `filament/filament` from `^3.0` to `^4.0`
2. Change `orchestra/testbench` from `^8.0|^9.0|^10.0` to `^9.0|^10.0`
3. Run `cd packages/filament && rm -rf vendor composer.lock && composer install`

### 2. Move Preview Method to BfmEditor

**Pattern to follow**: Filament v4's `HasFileAttachments` trait uses `#[ExposedLivewireMethod]` on `saveUploadedFileAttachmentAndGetUrl()`.

**Overview**: The `renderBfmPreview()` method moves from the user-facing `HasBfmEditor` trait to the `BfmEditor` component class itself.

```php
<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Filament\Forms\Components;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Support\Components\Attributes\ExposedLivewireMethod;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;

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

    #[ExposedLivewireMethod]
    #[Renderless]
    public function renderBfmPreview(): string
    {
        $state = $this->getState();

        if ($state === null || $state === '') {
            return '';
        }

        return Str::bfm($state);
    }
}
```

**Key decisions**:
- Method takes no arguments — it reads state via `$this->getState()` (available on all Field components)
- `#[ExposedLivewireMethod]` from `Filament\Support\Components\Attributes\ExposedLivewireMethod`
- `#[Renderless]` prevents unnecessary Livewire re-renders on preview calls

**Implementation steps**:
1. Add `use` imports for `ExposedLivewireMethod`, `Str`, `Renderless`
2. Add `renderBfmPreview()` method with both attributes
3. Method uses `$this->getState()` instead of `data_get()` with a state path

### 3. Update Blade View

**Pattern to follow**: Filament v4's `markdown-editor.blade.php` on the `4.x` branch.

**Overview**: Update the custom Blade view to use v4 patterns.

Changes from v3 → v4:
- Add `$key = $getKey();` to `@php` block
- Preview toggle: `$wire.renderBfmPreview('{{ $statePath }}')` → `$wire.callSchemaComponentMethod(@js($key), 'renderBfmPreview')`
- File upload: `$wire.getFormComponentFileAttachmentUrl('{{ $statePath }}')` → `$wire.callSchemaComponentMethod('{{ $key }}', 'saveUploadedFileAttachmentAndGetUrl')`
- Attach files check: `$hasToolbarButton('attachFiles')` → `$hasFileAttachments()`
- Field wrapper: add `label-tag` removal (v4 doesn't use it — uses default `<label>`)
- x-load: remove `visible || event (ax-modal-opened)` condition (v4 uses just `x-load`)
- Textarea: add `x-cloak` to `<textarea>` element
- Add `$extraAttributeBag = $getExtraAttributeBag()` and `$fieldWrapperView = $getFieldWrapperView()` to `@php` block for consistency with v4 patterns

The full updated Blade view:

```blade
@php
    use Filament\Support\Facades\FilamentAsset;

    $id = $getId();
    $fieldWrapperView = $getFieldWrapperView();
    $extraAttributeBag = $getExtraAttributeBag();
    $key = $getKey();
    $statePath = $getStatePath();
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    @if ($isDisabled())
        <div
            @class([
                'fi-fo-markdown-editor fi-disabled',
                'prose max-w-none dark:prose-invert',
                'min-h-[theme(spacing.48)]' => ! $isInline(),
            ])
        >
            {!! str($getState())->markdown()->sanitizeHtml() !!}
        </div>
    @else
        <div
            x-data="{
                showPreview: false,
                previewHtml: '',
                previewLoading: false,
            }"
            class="fi-fo-markdown-editor"
        >
            {{-- Preview toggle button --}}
            <div class="flex items-center justify-end border-b border-gray-200 px-2 py-1 dark:border-white/10">
                <button
                    type="button"
                    x-on:click="
                        showPreview = !showPreview;
                        if (showPreview) {
                            previewLoading = true;
                            $wire.callSchemaComponentMethod(@js($key), 'renderBfmPreview')
                                .then(html => {
                                    previewHtml = html;
                                    previewLoading = false;
                                })
                                .catch(() => {
                                    previewLoading = false;
                                });
                        }
                    "
                    class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-gray-600 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5"
                    :class="showPreview ? 'bg-gray-100 dark:bg-white/5' : ''"
                >
                    <x-filament::icon
                        icon="heroicon-m-eye"
                        x-show="!showPreview"
                        class="h-4 w-4"
                    />
                    <x-filament::icon
                        icon="heroicon-m-pencil-square"
                        x-show="showPreview"
                        class="h-4 w-4"
                    />
                    <span x-text="showPreview ? 'Edit' : 'Preview'"></span>
                </button>
            </div>

            {{-- EasyMDE editor (hidden when preview is active) --}}
            <div x-show="!showPreview">
                <x-filament::input.wrapper
                    :valid="! $errors->has($statePath)"
                    :attributes="
                        \Filament\Support\prepare_inherited_attributes($extraAttributeBag)
                            ->class(['overflow-hidden'])
                    "
                >
                    <div
                        x-load
                        x-load-src="{{ FilamentAsset::getAlpineComponentSrc('markdown-editor', 'filament/forms') }}"
                        x-data="markdownEditorFormComponent({
                            canAttachFiles: @js($hasFileAttachments()),
                            isLiveDebounced: @js($isLiveDebounced()),
                            isLiveOnBlur: @js($isLiveOnBlur()),
                            liveDebounce: @js($getNormalizedLiveDebounce()),
                            maxHeight: @js($getMaxHeight()),
                            minHeight: @js($getMinHeight()),
                            placeholder: @js($getPlaceholder()),
                            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')", isOptimisticallyLive: false) }},
                            toolbarButtons: @js($getToolbarButtons()),
                            translations: @js(__('filament-forms::components.markdown_editor')),
                            uploadFileAttachmentUsing: async (file, onSuccess, onError) => {
                                $wire.upload(`componentFileAttachments.{{ $statePath }}`, file, () => {
                                    $wire
                                        .callSchemaComponentMethod(
                                            '{{ $key }}',
                                            'saveUploadedFileAttachmentAndGetUrl',
                                        )
                                        .then((url) => {
                                            if (! url) {
                                                return onError()
                                            }

                                            onSuccess(url)
                                        })
                                })
                            },
                        })"
                        wire:ignore
                        {!! $isLiveOnBlur() ? 'x-on:blur="$wire.$refresh()"' : '' !!}
                        @class([
                            '[&_.CodeMirror]:min-h-[theme(spacing.48)]' => ! $isInline(),
                        ])
                    >
                        <textarea x-ref="editor" x-cloak></textarea>
                    </div>
                </x-filament::input.wrapper>
            </div>

            {{-- Server-rendered BFM preview --}}
            <div
                x-show="showPreview"
                x-cloak
                class="rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-white/5"
                style="min-height: {{ $getMinHeight() ?? '12rem' }}"
            >
                <template x-if="previewLoading">
                    <div class="flex items-center justify-center py-8 text-sm text-gray-400">
                        <x-filament::loading-indicator class="mr-2 h-4 w-4" />
                        Rendering preview...
                    </div>
                </template>

                <template x-if="!previewLoading && previewHtml === ''">
                    <div class="py-8 text-center text-sm text-gray-400">
                        Nothing to preview
                    </div>
                </template>

                <template x-if="!previewLoading && previewHtml !== ''">
                    <div class="prose max-w-none dark:prose-invert" x-html="previewHtml"></div>
                </template>
            </div>
        </div>
    @endif
</x-dynamic-component>
```

**Implementation steps**:
1. Replace the full Blade view content
2. Verify `@js($key)` vs `'{{ $key }}'` — use `@js($key)` in the preview toggle (consistent with Filament docs) and `'{{ $key }}'` in the file upload (consistent with Filament's own markdown-editor.blade.php)

### 4. Delete HasBfmEditor Trait

**Implementation steps**:
1. Delete `packages/filament/src/Concerns/HasBfmEditor.php`
2. Delete `packages/filament/tests/HasBfmEditorTest.php`

### 5. Update Tests

**Overview**: Remove trait tests, update editor tests, verify column/entry tests still pass.

`packages/filament/tests/BfmEditorTest.php` — updated:

```php
<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Filament\Tests;

use Birdcar\Markdown\Filament\Forms\Components\BfmEditor;

final class BfmEditorTest extends TestCase
{
    public function test_can_create_editor(): void
    {
        $editor = BfmEditor::make('content');
        $this->assertInstanceOf(BfmEditor::class, $editor);
    }

    public function test_default_preview_debounce(): void
    {
        $editor = BfmEditor::make('content');
        $this->assertSame(300, $editor->getPreviewDebounce());
    }

    public function test_custom_preview_debounce(): void
    {
        $editor = BfmEditor::make('content')->previewDebounce(500);
        $this->assertSame(500, $editor->getPreviewDebounce());
    }

    public function test_inherits_toolbar_buttons(): void
    {
        $editor = BfmEditor::make('content')
            ->toolbarButtons(['bold', 'italic']);
        $this->assertSame(['bold', 'italic'], $editor->getToolbarButtons());
    }

    public function test_uses_custom_view(): void
    {
        $editor = BfmEditor::make('content');
        $this->assertSame('bfm-filament::forms.components.bfm-editor', $editor->getView());
    }

    public function test_has_render_bfm_preview_method(): void
    {
        $editor = BfmEditor::make('content');
        $this->assertTrue(method_exists($editor, 'renderBfmPreview'));
    }

    public function test_render_bfm_preview_has_exposed_livewire_method_attribute(): void
    {
        $reflection = new \ReflectionMethod(BfmEditor::class, 'renderBfmPreview');
        $attributes = $reflection->getAttributes();
        $attributeNames = array_map(fn ($attr) => $attr->getName(), $attributes);

        $this->assertContains(
            'Filament\Support\Components\Attributes\ExposedLivewireMethod',
            $attributeNames,
        );
    }

    public function test_render_bfm_preview_has_renderless_attribute(): void
    {
        $reflection = new \ReflectionMethod(BfmEditor::class, 'renderBfmPreview');
        $attributes = $reflection->getAttributes();
        $attributeNames = array_map(fn ($attr) => $attr->getName(), $attributes);

        $this->assertContains(
            'Livewire\Attributes\Renderless',
            $attributeNames,
        );
    }
}
```

**Key decisions**:
- Removed `test_can_disable_toolbar_buttons` — `hasToolbarButton('attachFiles')` doesn't exist in v4; file attachments are controlled by `hasFileAttachments()`
- Added attribute reflection tests to verify `#[ExposedLivewireMethod]` and `#[Renderless]` are present
- Added `test_has_render_bfm_preview_method` to verify the method exists on the component
- Cannot easily test `renderBfmPreview()` output without a full Livewire lifecycle (it calls `$this->getState()` which requires component mounting)

`packages/filament/tests/TestCase.php` — add `SchemasServiceProvider`:

```php
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
```

### 6. Update PHPStan Config

Remove the unused trait ignore. Keep the `Str::bfm()` ignore.

```neon
parameters:
    level: 8
    paths:
        - src
    ignoreErrors:
        -
            identifier: staticMethod.notFound
            message: '#Call to an undefined static method Illuminate\\Support\\Str::bfm\(\)#'
```

## Testing Requirements

### Unit Tests

| Test File | Coverage |
|-----------|----------|
| `packages/filament/tests/BfmEditorTest.php` | Editor creation, debounce, toolbar, view, preview method existence, attributes |
| `packages/filament/tests/BfmTextColumnTest.php` | Column creation, HTML enabled (unchanged) |
| `packages/filament/tests/BfmTextEntryTest.php` | Entry creation, HTML enabled (unchanged) |

**Key test cases**:
- `BfmEditor::make()` creates instance
- Preview debounce defaults to 300ms, can be customized
- Toolbar buttons are inherited from parent
- Custom Blade view is set
- `renderBfmPreview()` method exists on BfmEditor
- `#[ExposedLivewireMethod]` attribute is present
- `#[Renderless]` attribute is present
- `BfmTextColumn` and `BfmTextEntry` still work

## Validation Commands

```bash
# Install dependencies with Filament v4
cd packages/filament && rm -rf vendor composer.lock && composer install

# Run tests
cd packages/filament && composer test

# Static analysis
cd packages/filament && composer analyse
```

## Open Items

- [ ] Verify `Filament\Schemas\SchemasServiceProvider` exists and is needed in test providers — it may be auto-discovered or pulled in by FormsServiceProvider

---

*This spec is ready for implementation. Follow the patterns and validate at each step.*

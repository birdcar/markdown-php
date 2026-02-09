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

        $this->assertSame([['bold', 'italic']], $editor->getToolbarButtons());
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

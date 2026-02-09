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
        /** @var string|null $state */
        $state = $this->getState();

        if ($state === null || $state === '') {
            return '';
        }

        return Str::bfm($state);
    }
}

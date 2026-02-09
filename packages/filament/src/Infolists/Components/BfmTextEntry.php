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

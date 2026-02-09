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

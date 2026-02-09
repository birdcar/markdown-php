<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Filament\Tests;

use Birdcar\Markdown\Filament\Tables\Columns\BfmTextColumn;

final class BfmTextColumnTest extends TestCase
{
    public function test_can_create_column(): void
    {
        $column = BfmTextColumn::make('content');

        $this->assertInstanceOf(BfmTextColumn::class, $column);
    }

    public function test_column_is_html_enabled(): void
    {
        $column = BfmTextColumn::make('content');

        $this->assertTrue($column->isHtml());
    }
}

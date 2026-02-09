<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Filament\Tests;

use Birdcar\Markdown\Filament\Infolists\Components\BfmTextEntry;

final class BfmTextEntryTest extends TestCase
{
    public function test_can_create_entry(): void
    {
        $entry = BfmTextEntry::make('content');

        $this->assertInstanceOf(BfmTextEntry::class, $entry);
    }

    public function test_entry_is_html_enabled(): void
    {
        $entry = BfmTextEntry::make('content');

        $this->assertTrue($entry->isHtml());
    }
}

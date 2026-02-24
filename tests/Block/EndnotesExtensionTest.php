<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests\Block;

use Birdcar\Markdown\Tests\FixtureTestCase;

class EndnotesExtensionTest extends FixtureTestCase
{
    public function testEndnotesWithFootnotes(): void
    {
        $md = "Some text with a footnote[^1].\n\n[^1]: This is the footnote content.\n\n@endnotes title=\"References\"\n@endendnotes";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('class="endnotes"', $html);
        $this->assertStringContainsString('role="doc-endnotes"', $html);
        $this->assertStringContainsString('References', $html);
        $this->assertStringContainsString('fn-1', $html);
    }

    public function testEndnotesAutoAppendedWhenNotPresent(): void
    {
        $md = "Text with footnote[^note].\n\n[^note]: A footnote definition.";
        $html = $this->convertToHtml($md);

        // Should auto-append endnotes section
        $this->assertStringContainsString('class="endnotes"', $html);
        $this->assertStringContainsString('fn-note', $html);
    }
}

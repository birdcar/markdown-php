<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests\Block;

use Birdcar\Markdown\Tests\FixtureTestCase;

class DetailsExtensionTest extends FixtureTestCase
{
    public function testDetailsWithSummary(): void
    {
        $md = "@details summary=\"Click to expand\"\nHidden content here.\n@enddetails";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('<details>', $html);
        $this->assertStringContainsString('<summary>Click to expand</summary>', $html);
        $this->assertStringContainsString('Hidden content here.', $html);
        $this->assertStringContainsString('</details>', $html);
    }

    public function testDetailsOpenFlag(): void
    {
        $md = "@details summary=\"Open by default\" open\nVisible content.\n@enddetails";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('<details open="open">', $html);
        $this->assertStringContainsString('<summary>Open by default</summary>', $html);
    }

    public function testDetailsWithoutSummary(): void
    {
        $md = "@details\nNo summary provided.\n@enddetails";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('<details>', $html);
        $this->assertStringNotContainsString('<summary>', $html);
        $this->assertStringContainsString('No summary provided.', $html);
    }

    public function testDetailsWithNestedMarkdown(): void
    {
        $md = "@details summary=\"Nested\"\nThis has **bold** and *italic*.\n@enddetails";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('<strong>bold</strong>', $html);
        $this->assertStringContainsString('<em>italic</em>', $html);
    }
}

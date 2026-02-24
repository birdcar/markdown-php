<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests\Block;

use Birdcar\Markdown\Tests\FixtureTestCase;

class TocExtensionTest extends FixtureTestCase
{
    public function testTocGeneratesFromHeadings(): void
    {
        $md = "@toc\n@endtoc\n\n# Introduction\n\n## Getting Started\n\n## Usage";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('<nav class="toc"', $html);
        $this->assertStringContainsString('aria-label="Table of contents"', $html);
        $this->assertStringContainsString('Introduction', $html);
        $this->assertStringContainsString('Getting Started', $html);
        $this->assertStringContainsString('Usage', $html);
    }

    public function testTocWithDepthParam(): void
    {
        $md = "@toc depth=1\n@endtoc\n\n# Top Level\n\n## Sub Level\n\n### Deep";
        $html = $this->convertToHtml($md);

        // Extract just the TOC nav section
        preg_match('/<nav class="toc".*?<\/nav>/s', $html, $tocMatch);
        $tocHtml = $tocMatch[0] ?? '';

        $this->assertStringContainsString('Top Level', $tocHtml);
        $this->assertStringNotContainsString('Sub Level', $tocHtml);
        $this->assertStringNotContainsString('Deep', $tocHtml);
    }

    public function testTocOrderedFlag(): void
    {
        $md = "@toc ordered\n@endtoc\n\n# First\n\n# Second";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('<ol>', $html);
    }

    public function testTocUnorderedByDefault(): void
    {
        $md = "@toc\n@endtoc\n\n# First\n\n# Second";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('<ul>', $html);
    }

    public function testTocEmptyWhenNoHeadings(): void
    {
        $md = "@toc\n@endtoc\n\nJust a paragraph.";
        $html = $this->convertToHtml($md);

        $this->assertStringNotContainsString('<nav', $html);
    }
}

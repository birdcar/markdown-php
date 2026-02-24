<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests\Inline;

use Birdcar\Markdown\Tests\FixtureTestCase;

class FootnoteExtensionTest extends FixtureTestCase
{
    public function testFootnoteRefRendersSupTag(): void
    {
        $md = "Some text[^1].\n\n[^1]: The footnote.";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('class="footnote-ref"', $html);
        $this->assertStringContainsString('id="fnref-1"', $html);
        $this->assertStringContainsString('href="#fn-1"', $html);
        $this->assertStringContainsString('role="doc-noteref"', $html);
    }

    public function testFootnoteRefAutoNumbering(): void
    {
        $md = "First[^a] and second[^b].\n\n[^a]: First note.\n[^b]: Second note.";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('[1]', $html);
        $this->assertStringContainsString('[2]', $html);
    }

    public function testFootnoteDefHiddenFromOutput(): void
    {
        $md = "Text[^x].\n\n[^x]: Definition here.";
        $html = $this->convertToHtml($md);

        // FootnoteDef should render as empty (content goes to endnotes)
        $this->assertStringContainsString('fn-x', $html);
        $this->assertStringContainsString('endnote-backref', $html);
    }

    public function testFootnoteBackref(): void
    {
        $md = "Content[^ref].\n\n[^ref]: A footnote.";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('href="#fnref-ref"', $html);
        $this->assertStringContainsString('class="endnote-backref"', $html);
        $this->assertStringContainsString('role="doc-backlink"', $html);
    }
}

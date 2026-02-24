<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests\Block;

use Birdcar\Markdown\Tests\FixtureTestCase;

class AsideExtensionTest extends FixtureTestCase
{
    public function testAsideWithTitle(): void
    {
        $md = "@aside title=\"Side Note\"\nSome aside content.\n@endaside";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('<aside class="aside">', $html);
        $this->assertStringContainsString('aside__title', $html);
        $this->assertStringContainsString('Side Note', $html);
        $this->assertStringContainsString('Some aside content.', $html);
    }

    public function testAsideWithoutTitle(): void
    {
        $md = "@aside\nJust content.\n@endaside";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('<aside class="aside">', $html);
        $this->assertStringNotContainsString('aside__title', $html);
        $this->assertStringContainsString('Just content.', $html);
    }

    public function testAsideWithNestedBfmContent(): void
    {
        $md = "@aside title=\"Note\"\nCheck out **bold** text and a [link](https://example.com).\n@endaside";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('<strong>bold</strong>', $html);
        $this->assertStringContainsString('href="https://example.com"', $html);
    }
}

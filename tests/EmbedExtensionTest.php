<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests;

class EmbedExtensionTest extends FixtureTestCase
{
    public function testEmbedWithUrl(): void
    {
        $md = "@embed https://example.com/video\n@endembed";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('https://example.com/video', $html);
        $this->assertStringContainsString('embed__link', $html);
    }

    public function testEmbedWithCaption(): void
    {
        $md = "@embed https://example.com/video\nA great video.\n@endembed";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('A great video.', $html);
        $this->assertStringContainsString('embed__caption', $html);
    }
}

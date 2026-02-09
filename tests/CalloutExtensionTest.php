<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests;

class CalloutExtensionTest extends FixtureTestCase
{
    public function testCalloutWithParams(): void
    {
        $md = "@callout type=warning title=\"Watch Out\"\nThis is a warning.\n@endcallout";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('callout--warning', $html);
        $this->assertStringContainsString('Watch Out', $html);
        $this->assertStringContainsString('This is a warning.', $html);
    }

    public function testCalloutWithNoParams(): void
    {
        $md = "@callout\nSome info.\n@endcallout";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('callout--info', $html);
    }

    public function testCalloutBodyParsesMarkdown(): void
    {
        $md = "@callout type=info\nThis has **bold** text.\n@endcallout";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('<strong>bold</strong>', $html);
    }
}

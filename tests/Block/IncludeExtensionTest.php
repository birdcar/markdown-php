<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests\Block;

use Birdcar\Markdown\Tests\FixtureTestCase;

class IncludeExtensionTest extends FixtureTestCase
{
    public function testIncludeRendersPlaceholder(): void
    {
        $md = "@include path=\"./other-doc.md\"\n@endinclude";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('class="include"', $html);
        $this->assertStringContainsString('data-path="./other-doc.md"', $html);
    }

    public function testIncludeWithHeadingParam(): void
    {
        $md = "@include path=\"./guide.md\" heading=\"Installation\"\n@endinclude";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('data-heading="Installation"', $html);
    }

    public function testIncludeRequiresPath(): void
    {
        $md = "@include heading=\"No path\"\n@endinclude";
        $html = $this->convertToHtml($md);

        $this->assertStringNotContainsString('class="include"', $html);
    }
}

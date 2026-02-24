<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests\Block;

use Birdcar\Markdown\Tests\FixtureTestCase;

class FigureExtensionTest extends FixtureTestCase
{
    public function testFigureWithSrcAndAlt(): void
    {
        $md = "@figure src=\"image.png\" alt=\"An image\"\n@endfigure";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('<figure>', $html);
        $this->assertStringContainsString('src="image.png"', $html);
        $this->assertStringContainsString('alt="An image"', $html);
    }

    public function testFigureWithId(): void
    {
        $md = "@figure src=\"arch.png\" alt=\"Architecture\" id=fig-arch\nThe architecture diagram.\n@endfigure";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('id="fig-arch"', $html);
        $this->assertStringContainsString('<figcaption>', $html);
        $this->assertStringContainsString('The architecture diagram.', $html);
    }

    public function testFigureWithoutCaption(): void
    {
        $md = "@figure src=\"photo.jpg\"\n@endfigure";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('<figure>', $html);
        $this->assertStringContainsString('src="photo.jpg"', $html);
        $this->assertStringNotContainsString('<figcaption>', $html);
    }

    public function testFigureRequiresSrc(): void
    {
        $md = "@figure alt=\"No src\"\nSome text\n@endfigure";
        $html = $this->convertToHtml($md);

        // Without src, it should not be parsed as a figure
        $this->assertStringNotContainsString('<figure>', $html);
    }
}

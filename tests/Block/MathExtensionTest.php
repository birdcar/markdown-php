<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests\Block;

use Birdcar\Markdown\Tests\FixtureTestCase;

class MathExtensionTest extends FixtureTestCase
{
    public function testMathWithLatex(): void
    {
        $md = "@math\nE = mc^2\n@endmath";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('class="math"', $html);
        $this->assertStringContainsString('role="math"', $html);
        $this->assertStringContainsString('E = mc^2', $html);
    }

    public function testMathWithLabel(): void
    {
        $md = "@math label=eq-euler\ne^{i\\pi} + 1 = 0\n@endmath";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('id="eq-euler"', $html);
    }

    public function testMathPreservesRawContent(): void
    {
        $md = "@math\n\\frac{a}{b} = \\sqrt{c}\n@endmath";
        $html = $this->convertToHtml($md);

        // Raw LaTeX should be preserved, not parsed as markdown
        $this->assertStringContainsString('\\frac{a}{b}', $html);
        $this->assertStringNotContainsString('<em>', $html);
    }
}

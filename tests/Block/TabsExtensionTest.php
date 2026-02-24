<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests\Block;

use Birdcar\Markdown\Tests\FixtureTestCase;

class TabsExtensionTest extends FixtureTestCase
{
    public function testTabsWithMultipleTabs(): void
    {
        $md = "@tabs\n@tab label=\"JavaScript\"\nJS content.\n@endtab\n@tab label=\"Python\"\nPython content.\n@endtab\n@endtabs";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('class="tabs"', $html);
        $this->assertStringContainsString('tabs__nav', $html);
        $this->assertStringContainsString('role="tablist"', $html);
        $this->assertStringContainsString('JavaScript', $html);
        $this->assertStringContainsString('Python', $html);
        $this->assertStringContainsString('tabs__panel', $html);
    }

    public function testTabsWithActiveFlag(): void
    {
        $md = "@tabs\n@tab label=\"First\"\nFirst tab.\n@endtab\n@tab label=\"Second\" active\nSecond tab.\n@endtab\n@endtabs";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('tabs__tab--active', $html);
        $this->assertStringContainsString('tabs__panel--active', $html);
        $this->assertStringContainsString('aria-selected="true"', $html);
    }

    public function testTabsWithSyncId(): void
    {
        $md = "@tabs id=lang-picker\n@tab label=\"JS\"\nJS.\n@endtab\n@endtabs";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('data-sync-id="lang-picker"', $html);
    }

    public function testFirstTabActiveByDefault(): void
    {
        $md = "@tabs\n@tab label=\"Alpha\"\nAlpha content.\n@endtab\n@tab label=\"Beta\"\nBeta content.\n@endtab\n@endtabs";
        $html = $this->convertToHtml($md);

        // First tab button should have active class
        $this->assertMatchesRegularExpression('/tabs__tab tabs__tab--active.*Alpha/s', $html);
    }
}

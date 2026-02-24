<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests\Block;

use Birdcar\Markdown\Tests\FixtureTestCase;

class QueryExtensionTest extends FixtureTestCase
{
    public function testQueryRendersPlaceholder(): void
    {
        $md = "@query type=tasks\n@endquery";
        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('class="query"', $html);
        $this->assertStringContainsString('data-query-type="tasks"', $html);
    }

    public function testQueryRequiresType(): void
    {
        $md = "@query status=open\n@endquery";
        $html = $this->convertToHtml($md);

        $this->assertStringNotContainsString('class="query"', $html);
    }
}

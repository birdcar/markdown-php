<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests\Inline\Hashtag;

use Birdcar\Markdown\Tests\FixtureTestCase;

class HashtagExtensionTest extends FixtureTestCase
{
    public function testBasicHashtag(): void
    {
        $html = $this->convertToHtml('This is #typescript and #react.');

        $this->assertStringContainsString('class="hashtag"', $html);
        $this->assertStringContainsString('#typescript', $html);
        $this->assertStringContainsString('#react', $html);
    }

    public function testHashtagWithHyphensAndUnderscores(): void
    {
        $html = $this->convertToHtml('Tags: #multi-word and #with_underscores here.');

        $this->assertStringContainsString('#multi-word', $html);
        $this->assertStringContainsString('#with_underscores', $html);
    }

    public function testHashtagNotMidWord(): void
    {
        $html = $this->convertToHtml('Not a tag: foo#bar.');

        $this->assertStringNotContainsString('class="hashtag"', $html);
    }

    public function testHashtagAfterPunctuation(): void
    {
        $html = $this->convertToHtml('In parens (#tag) works.');

        $this->assertStringContainsString('class="hashtag"', $html);
        $this->assertStringContainsString('#tag', $html);
    }

    public function testHashtagNotInCodeSpan(): void
    {
        $html = $this->convertToHtml('Code: `#not-a-tag` should not parse.');

        // The #not-a-tag should be in a <code> element, not a .hashtag span
        $this->assertStringNotContainsString('class="hashtag"', $html);
    }

    public function testHashtagAlongsideMention(): void
    {
        $html = $this->convertToHtml('Mention @sarah and tag #project together.');

        $this->assertStringContainsString('class="hashtag"', $html);
        $this->assertStringContainsString('class="mention"', $html);
        $this->assertStringContainsString('#project', $html);
        $this->assertStringContainsString('@sarah', $html);
    }
}

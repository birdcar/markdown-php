<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests;

class MentionExtensionTest extends FixtureTestCase
{
    public function testBasicMention(): void
    {
        $html = $this->convertToHtml('Hey @sarah, check this');

        $this->assertStringContainsString('class="mention"', $html);
        $this->assertStringContainsString('@sarah', $html);
    }

    public function testMentionWithDots(): void
    {
        $html = $this->convertToHtml('cc @john.doe please');

        $this->assertStringContainsString('@john.doe', $html);
    }

    public function testMentionWithHyphen(): void
    {
        $html = $this->convertToHtml('ping @dev-team');

        $this->assertStringContainsString('@dev-team', $html);
    }

    public function testMentionNotMidWord(): void
    {
        $html = $this->convertToHtml('email@example.com');

        $this->assertStringNotContainsString('class="mention"', $html);
    }

    public function testMentionTrailingPunctuation(): void
    {
        $html = $this->convertToHtml('Hey @sarah.');

        $this->assertStringContainsString('@sarah</span>', $html);
        // The period should NOT be part of the mention
        $this->assertStringNotContainsString('@sarah.', $html);
    }
}

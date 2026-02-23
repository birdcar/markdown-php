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

    public function testPlatformMentionGithub(): void
    {
        $html = $this->convertToHtml('Follow @github:birdcar');

        $this->assertStringContainsString('href="https://github.com/birdcar"', $html);
        $this->assertStringContainsString('class="mention mention--github"', $html);
        $this->assertStringContainsString('title="GitHub: birdcar"', $html);
        $this->assertStringContainsString('>@github:birdcar</a>', $html);
    }

    public function testPlatformMentionTwitter(): void
    {
        $html = $this->convertToHtml('Follow @twitter:birdcar');

        $this->assertStringContainsString('href="https://twitter.com/birdcar"', $html);
        $this->assertStringContainsString('class="mention mention--twitter"', $html);
    }

    public function testPlatformMentionBluesky(): void
    {
        $html = $this->convertToHtml('@bluesky:birdcar.bsky.social');

        $this->assertStringContainsString('href="https://bsky.app/profile/birdcar.bsky.social"', $html);
        $this->assertStringContainsString('title="Bluesky: birdcar.bsky.social"', $html);
    }

    public function testPlatformMentionMastodon(): void
    {
        $html = $this->convertToHtml('@mastodon:user@mastodon.social');

        $this->assertStringContainsString('href="https://mastodon.social/@user"', $html);
        $this->assertStringContainsString('title="Mastodon: user@mastodon.social"', $html);
    }

    public function testPlatformMentionNpm(): void
    {
        $html = $this->convertToHtml('@npm:express');

        $this->assertStringContainsString('href="https://www.npmjs.com/package/express"', $html);
        $this->assertStringContainsString('class="mention mention--npm"', $html);
    }

    public function testPlatformMentionLinkedin(): void
    {
        $html = $this->convertToHtml('@linkedin:birdcar');

        $this->assertStringContainsString('href="https://www.linkedin.com/in/birdcar"', $html);
        $this->assertStringContainsString('class="mention mention--linkedin"', $html);
    }

    public function testPlatformMentionUnknown(): void
    {
        $html = $this->convertToHtml('@unknown:foo');

        $this->assertStringContainsString('<span class="mention">@unknown:foo</span>', $html);
        $this->assertStringNotContainsString('<a ', $html);
    }

    public function testPlainMentionStillWorks(): void
    {
        $html = $this->convertToHtml('Plain @birdcar still works');

        $this->assertStringContainsString('<span class="mention">@birdcar</span>', $html);
    }

    public function testPlatformMentionFixture(): void
    {
        $this->assertFixtureHtml('inlines/mentions-platform');
    }
}

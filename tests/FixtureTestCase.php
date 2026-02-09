<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests;

use Birdcar\Markdown\Environment\BfmEnvironmentFactory;
use Birdcar\Markdown\Environment\RenderProfile;
use League\CommonMark\MarkdownConverter;
use PHPUnit\Framework\TestCase;

abstract class FixtureTestCase extends TestCase
{
    protected function assertFixtureHtml(string $fixturePath): void
    {
        $base = __DIR__ . '/../spec/fixtures/' . $fixturePath;
        $md = file_get_contents($base . '.md');
        $expectedHtml = trim(file_get_contents($base . '.html'));

        $env = BfmEnvironmentFactory::create(RenderProfile::Html);
        $converter = new MarkdownConverter($env);
        $actualHtml = trim((string) $converter->convert($md));

        $this->assertSame(
            $this->normalizeHtml($expectedHtml),
            $this->normalizeHtml($actualHtml),
        );
    }

    protected function convertToHtml(string $markdown): string
    {
        $env = BfmEnvironmentFactory::create(RenderProfile::Html);
        $converter = new MarkdownConverter($env);

        return trim((string) $converter->convert($markdown));
    }

    protected function normalizeHtml(string $html): string
    {
        // Collapse whitespace
        $html = preg_replace('/\s+/', ' ', trim($html)) ?? $html;
        // Normalize self-closing tags
        $html = preg_replace('/\s*\/?>/', '>', $html) ?? $html;

        return $html;
    }
}

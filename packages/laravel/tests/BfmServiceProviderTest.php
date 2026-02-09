<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Laravel\Tests;

use Illuminate\Support\Str;
use League\CommonMark\MarkdownConverter;

final class BfmServiceProviderTest extends TestCase
{
    public function test_registers_markdown_converter_singleton(): void
    {
        $converter = $this->app->make(MarkdownConverter::class);

        $this->assertInstanceOf(MarkdownConverter::class, $converter);
        $this->assertSame($converter, $this->app->make(MarkdownConverter::class));
    }

    public function test_config_is_merged_with_defaults(): void
    {
        $config = $this->app['config']['bfm'];

        $this->assertSame('html', $config['profile']);
        $this->assertNull($config['resolvers']['mention']);
        $this->assertNull($config['resolvers']['embed']);
    }

    public function test_str_bfm_macro_is_registered(): void
    {
        $this->assertTrue(Str::hasMacro('bfm'));
    }

    public function test_str_inline_bfm_macro_is_registered(): void
    {
        $this->assertTrue(Str::hasMacro('inlineBfm'));
    }

    public function test_bfm_styles_renders_inline_fallback(): void
    {
        $styles = \Birdcar\Markdown\Laravel\BfmServiceProvider::renderStyles();

        $this->assertStringStartsWith('<style>', $styles);
        $this->assertStringContainsString('.task-marker', $styles);
        $this->assertStringContainsString('.callout', $styles);
    }

    public function test_css_file_contains_all_bfm_selectors(): void
    {
        $css = file_get_contents(__DIR__ . '/../resources/css/bfm.css');

        $this->assertNotEmpty($css);

        // Task markers
        $this->assertStringContainsString('.task-item', $css);
        $this->assertStringContainsString('.task-marker', $css);
        $this->assertStringContainsString('.task-marker--open', $css);
        $this->assertStringContainsString('.task-marker--done', $css);
        $this->assertStringContainsString('.task-marker--scheduled', $css);
        $this->assertStringContainsString('.task-marker--migrated', $css);
        $this->assertStringContainsString('.task-marker--irrelevant', $css);
        $this->assertStringContainsString('.task-marker--event', $css);
        $this->assertStringContainsString('.task-marker--priority', $css);
        $this->assertStringContainsString('.task-marker__icon', $css);

        // Task modifiers
        $this->assertStringContainsString('.task-mod', $css);

        // Mentions
        $this->assertStringContainsString('.mention', $css);

        // Callouts
        $this->assertStringContainsString('.callout', $css);
        $this->assertStringContainsString('.callout__header', $css);
        $this->assertStringContainsString('.callout__body', $css);
        $this->assertStringContainsString('.callout--info', $css);
        $this->assertStringContainsString('.callout--warning', $css);
        $this->assertStringContainsString('.callout--error', $css);
        $this->assertStringContainsString('.callout--tip', $css);
        $this->assertStringContainsString('.callout--note', $css);

        // Embeds
        $this->assertStringContainsString('.embed', $css);
        $this->assertStringContainsString('.embed__link', $css);
        $this->assertStringContainsString('.embed__caption', $css);
    }

    public function test_css_uses_custom_properties(): void
    {
        $css = file_get_contents(__DIR__ . '/../resources/css/bfm.css');

        $this->assertStringContainsString('--bfm-task-', $css);
        $this->assertStringContainsString('--bfm-callout-', $css);
        $this->assertStringContainsString('--bfm-mention-', $css);
        $this->assertStringContainsString('--bfm-embed-', $css);
        $this->assertStringContainsString('--bfm-modifier-', $css);
    }

    public function test_css_supports_dark_mode(): void
    {
        $css = file_get_contents(__DIR__ . '/../resources/css/bfm.css');

        $this->assertStringContainsString('prefers-color-scheme: dark', $css);
        $this->assertStringContainsString('.dark', $css);
    }

    public function test_minified_css_exists_and_is_smaller(): void
    {
        $cssPath = __DIR__ . '/../resources/css/bfm.css';
        $minPath = __DIR__ . '/../resources/css/bfm.min.css';

        $this->assertFileExists($minPath);
        $this->assertLessThan(filesize($cssPath), filesize($minPath));
        $this->assertLessThan(10240, filesize($minPath), 'Minified CSS should be under 10KB');
    }

    public function test_bfm_styles_contains_custom_properties(): void
    {
        $styles = \Birdcar\Markdown\Laravel\BfmServiceProvider::renderStyles();

        $this->assertStringContainsString('--bfm-', $styles);
    }
}

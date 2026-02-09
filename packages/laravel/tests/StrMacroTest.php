<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Laravel\Tests;

use Birdcar\Markdown\Contracts\MentionResolverInterface;
use Illuminate\Support\Str;

final class StrMacroTest extends TestCase
{
    public function test_bfm_converts_basic_markdown(): void
    {
        $html = Str::bfm('**bold** text');

        $this->assertStringContainsString('<strong>bold</strong>', $html);
    }

    public function test_bfm_renders_task_markers(): void
    {
        $html = Str::bfm("- [>] Scheduled task\n");

        $this->assertStringContainsString('task-marker--scheduled', $html);
        $this->assertStringContainsString('data-state="scheduled"', $html);
    }

    public function test_bfm_renders_task_modifiers(): void
    {
        $html = Str::bfm("- [ ] Task //due:2025-03-01\n");

        $this->assertStringContainsString('task-mod--due', $html);
        $this->assertStringContainsString('data-key="due"', $html);
        $this->assertStringContainsString('data-value="2025-03-01"', $html);
    }

    public function test_bfm_renders_mentions(): void
    {
        $html = Str::bfm('Hello @sarah');

        $this->assertStringContainsString('class="mention"', $html);
        $this->assertStringContainsString('@sarah', $html);
    }

    public function test_bfm_renders_callouts(): void
    {
        $html = Str::bfm("@callout type=warning title=\"Watch Out\"\nContent here.\n@endcallout");

        $this->assertStringContainsString('callout--warning', $html);
        $this->assertStringContainsString('Watch Out', $html);
        $this->assertStringContainsString('Content here.', $html);
    }

    public function test_inline_bfm_strips_paragraph_wrapper(): void
    {
        $html = Str::inlineBfm('Hello @sarah');

        $this->assertStringNotContainsString('<p>', $html);
        $this->assertStringContainsString('class="mention"', $html);
    }

    public function test_inline_bfm_renders_mentions(): void
    {
        $html = Str::inlineBfm('@john.doe check this');

        $this->assertStringContainsString('class="mention"', $html);
        $this->assertStringContainsString('@john.doe', $html);
    }

    public function test_bfm_with_custom_mention_resolver(): void
    {
        $this->app['config']->set('bfm.resolvers.mention', TestMentionResolver::class);

        // Clear the singleton so it's rebuilt with new config
        $this->app->forgetInstance(\League\CommonMark\MarkdownConverter::class);

        $html = Str::bfm('Hello @testuser');

        $this->assertStringContainsString('href="/users/testuser"', $html);
        $this->assertStringContainsString('Test User', $html);
    }

    public function test_bfm_renders_all_task_states(): void
    {
        $states = [
            '[ ]' => 'open',
            '[x]' => 'done',
            '[>]' => 'scheduled',
            '[<]' => 'migrated',
            '[-]' => 'irrelevant',
            '[o]' => 'event',
            '[!]' => 'priority',
        ];

        foreach ($states as $marker => $expectedClass) {
            $html = Str::bfm("- {$marker} Task\n");
            $this->assertStringContainsString(
                "task-marker--{$expectedClass}",
                $html,
                "Failed to render task state: {$expectedClass}"
            );
        }
    }
}

final class TestMentionResolver implements MentionResolverInterface
{
    public function resolve(string $identifier): ?array
    {
        return [
            'label' => 'Test User',
            'url' => '/users/' . $identifier,
        ];
    }
}

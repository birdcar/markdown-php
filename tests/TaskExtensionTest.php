<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests;

class TaskExtensionTest extends FixtureTestCase
{
    public function testAllSevenStatesRender(): void
    {
        $md = <<<'MD'
        - [ ] Open task
        - [x] Completed task
        - [>] Scheduled task
        - [<] Migrated task
        - [-] Irrelevant task
        - [o] Event task
        - [!] Priority task
        MD;

        $html = $this->convertToHtml($md);

        $this->assertStringContainsString('task-marker--open', $html);
        $this->assertStringContainsString('task-marker--done', $html);
        $this->assertStringContainsString('task-marker--scheduled', $html);
        $this->assertStringContainsString('task-marker--migrated', $html);
        $this->assertStringContainsString('task-marker--irrelevant', $html);
        $this->assertStringContainsString('task-marker--event', $html);
        $this->assertStringContainsString('task-marker--priority', $html);
    }

    public function testTaskMarkerSetsListItemAttribute(): void
    {
        $html = $this->convertToHtml('- [x] Done task');

        $this->assertStringContainsString('data-task="done"', $html);
        $this->assertStringContainsString('task-item--done', $html);
    }

    public function testTaskMarkerOutsideListIsLiteral(): void
    {
        $html = $this->convertToHtml('[x] This is not a task');

        $this->assertStringNotContainsString('task-marker', $html);
        $this->assertStringContainsString('[x]', $html);
    }

    public function testInvalidStateCharIsLiteral(): void
    {
        $html = $this->convertToHtml('- [z] Not a valid state');

        $this->assertStringNotContainsString('task-marker', $html);
    }

    public function testOrderedListWithTasks(): void
    {
        $html = $this->convertToHtml("1. [x] Done\n2. [ ] Open");

        $this->assertStringContainsString('task-marker--done', $html);
        $this->assertStringContainsString('task-marker--open', $html);
    }
}

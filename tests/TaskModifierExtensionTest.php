<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests;

class TaskModifierExtensionTest extends FixtureTestCase
{
    public function testKeyValueModifier(): void
    {
        $html = $this->convertToHtml('- [>] Call dentist //due:2025-03-01');

        $this->assertStringContainsString('task-mod--due', $html);
        $this->assertStringContainsString('data-value="2025-03-01"', $html);
    }

    public function testBooleanFlagModifier(): void
    {
        $html = $this->convertToHtml('- [!] Urgent //hard');

        $this->assertStringContainsString('task-mod--hard', $html);
    }

    public function testMultipleModifiers(): void
    {
        $html = $this->convertToHtml('- [>] Follow up //around:2025-03 //wait');

        $this->assertStringContainsString('task-mod--around', $html);
        $this->assertStringContainsString('task-mod--wait', $html);
    }

    public function testCronValueWithSpaces(): void
    {
        $html = $this->convertToHtml('- [ ] Backups //cron:0 9 * * 1');

        $this->assertStringContainsString('data-value="0 9 * * 1"', $html);
    }

    public function testUrlsNotParsedAsModifiers(): void
    {
        $html = $this->convertToHtml('Visit https://example.com for info');

        $this->assertStringNotContainsString('task-mod', $html);
    }
}

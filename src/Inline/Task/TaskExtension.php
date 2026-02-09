<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\Task;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;

/**
 * Registers the bullet-journal task marker parser, renderer, and list item processor.
 *
 * Usage:
 * ```php
 * $environment->addExtension(new TaskExtension());
 * ```
 */
final class TaskExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addInlineParser(new TaskMarkerParser(), 100);
        $environment->addRenderer(TaskMarker::class, new TaskMarkerRenderer());
        $environment->addEventListener(DocumentParsedEvent::class, new TaskListItemProcessor());
    }
}

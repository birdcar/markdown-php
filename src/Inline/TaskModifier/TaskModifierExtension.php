<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\TaskModifier;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

/**
 * Registers the task modifier inline parser and renderer.
 *
 * Usage:
 * ```php
 * $environment->addExtension(new TaskModifierExtension());
 * ```
 */
final class TaskModifierExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addInlineParser(new TaskModifierParser());
        $environment->addRenderer(TaskModifier::class, new TaskModifierRenderer());
    }
}

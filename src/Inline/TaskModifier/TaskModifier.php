<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\TaskModifier;

use League\CommonMark\Node\Inline\AbstractInline;

/**
 * AST node representing a task modifier (e.g. `//due:2024-01-15` or `//important`).
 *
 * Modifiers are key-value annotations embedded inline within task text.
 * A modifier with a null value acts as a boolean flag (e.g. `//pinned`).
 */
class TaskModifier extends AbstractInline
{
    public function __construct(
        public readonly string $key,
        public readonly ?string $value = null,
    ) {
        parent::__construct();
    }
}

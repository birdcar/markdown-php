<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\Task;

use League\CommonMark\Node\Inline\AbstractInline;

/**
 * AST node representing a bullet-journal-style task marker (e.g. [x], [>], [ ]).
 *
 * The marker appears at the very start of a list item's first paragraph
 * and carries the semantic state of the task (open, done, scheduled, etc.).
 */
class TaskMarker extends AbstractInline
{
    public function __construct(
        public readonly TaskState $state,
    ) {
        parent::__construct();
    }
}

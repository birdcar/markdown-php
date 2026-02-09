<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\Mention;

use League\CommonMark\Node\Inline\AbstractInline;

/**
 * AST node representing an @-mention (e.g. `@username`, `@org.team`).
 *
 * The identifier is stored without the leading `@` symbol.
 */
class Mention extends AbstractInline
{
    public function __construct(
        public readonly string $identifier,
    ) {
        parent::__construct();
    }
}

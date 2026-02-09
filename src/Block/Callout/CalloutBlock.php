<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Callout;

use League\CommonMark\Node\Block\AbstractBlock;

/**
 * AST node representing a @callout directive block.
 *
 * Callouts are container blocks that wrap arbitrary markdown content
 * with a semantic type (info, warning, danger, etc.) and an optional title.
 */
final class CalloutBlock extends AbstractBlock
{
    /**
     * @param string $type  The semantic callout type (info, warning, danger, success, note).
     * @param string $title An optional human-readable title rendered in the header.
     */
    public function __construct(
        public readonly string $type = 'info',
        public readonly string $title = '',
    ) {
        parent::__construct();
    }
}

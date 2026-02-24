<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\Footnote;

use League\CommonMark\Node\Inline\AbstractInline;

final class FootnoteRef extends AbstractInline
{
    public function __construct(
        public readonly string $label,
    ) {
        parent::__construct();
    }
}

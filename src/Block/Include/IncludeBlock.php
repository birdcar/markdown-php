<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Include;

use League\CommonMark\Node\Block\AbstractBlock;

final class IncludeBlock extends AbstractBlock
{
    public function __construct(
        public readonly string $path,
        public readonly string $heading = '',
    ) {
        parent::__construct();
    }
}

<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Figure;

use League\CommonMark\Node\Block\AbstractBlock;

final class FigureBlock extends AbstractBlock
{
    public function __construct(
        public readonly string $src,
        public readonly string $alt = '',
        public readonly string $id = '',
    ) {
        parent::__construct();
    }
}

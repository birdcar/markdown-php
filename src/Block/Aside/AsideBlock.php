<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Aside;

use League\CommonMark\Node\Block\AbstractBlock;

final class AsideBlock extends AbstractBlock
{
    public function __construct(
        public readonly string $title = '',
    ) {
        parent::__construct();
    }
}

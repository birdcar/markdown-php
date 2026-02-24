<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Details;

use League\CommonMark\Node\Block\AbstractBlock;

final class DetailsBlock extends AbstractBlock
{
    public function __construct(
        public readonly string $summary = '',
        public readonly bool $open = false,
    ) {
        parent::__construct();
    }
}

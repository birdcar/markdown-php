<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Tabs;

use League\CommonMark\Node\Block\AbstractBlock;

final class TabBlock extends AbstractBlock
{
    public function __construct(
        public readonly string $label,
        public readonly bool $active = false,
    ) {
        parent::__construct();
    }
}

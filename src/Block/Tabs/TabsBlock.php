<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Tabs;

use League\CommonMark\Node\Block\AbstractBlock;

final class TabsBlock extends AbstractBlock
{
    public function __construct(
        public readonly string $syncId = '',
    ) {
        parent::__construct();
    }
}

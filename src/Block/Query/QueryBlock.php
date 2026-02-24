<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Query;

use League\CommonMark\Node\Block\AbstractBlock;

final class QueryBlock extends AbstractBlock
{
    /** @param array<string, string|bool> $params */
    public function __construct(
        public readonly string $queryType,
        public readonly array $params = [],
    ) {
        parent::__construct();
    }
}

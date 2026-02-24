<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Toc;

use League\CommonMark\Node\Block\AbstractBlock;

final class TocBlock extends AbstractBlock
{
    private string $generatedHtml = '';

    public function __construct(
        public readonly int $maxDepth = 3,
        public readonly bool $ordered = false,
    ) {
        parent::__construct();
    }

    public function getGeneratedHtml(): string
    {
        return $this->generatedHtml;
    }

    public function setGeneratedHtml(string $html): void
    {
        $this->generatedHtml = $html;
    }
}

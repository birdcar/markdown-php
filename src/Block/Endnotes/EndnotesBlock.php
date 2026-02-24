<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Endnotes;

use League\CommonMark\Node\Block\AbstractBlock;

final class EndnotesBlock extends AbstractBlock
{
    private string $generatedHtml = '';

    public function __construct(
        public readonly string $title = '',
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

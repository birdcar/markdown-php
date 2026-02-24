<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\FootnoteDef;

use League\CommonMark\Node\Block\AbstractBlock;

final class FootnoteDefBlock extends AbstractBlock
{
    private string $content = '';

    public function __construct(
        public readonly string $label,
    ) {
        parent::__construct();
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}

<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\FootnoteDef;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;

final class FootnoteDefBlockContinueParser extends AbstractBlockContinueParser
{
    /** @var string[] */
    private array $lines = [];

    public function __construct(
        private readonly FootnoteDefBlock $block,
        string $firstLine,
    ) {
        if ($firstLine !== '') {
            $this->lines[] = $firstLine;
        }
    }

    public function getBlock(): FootnoteDefBlock
    {
        return $this->block;
    }

    public function isContainer(): bool
    {
        return false;
    }

    public function canContain(AbstractBlock $childBlock): bool
    {
        return false;
    }

    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
    {
        // Continuation lines must be indented by at least 4 spaces
        if ($cursor->getIndent() >= 4) {
            $cursor->advanceBy(4, true);
            return BlockContinue::at($cursor);
        }

        return BlockContinue::finished();
    }

    public function addLine(string $line): void
    {
        $this->lines[] = $line;
    }

    public function closeBlock(): void
    {
        $this->block->setContent(trim(implode("\n", $this->lines)));
    }
}

<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Toc;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;

final class TocBlockContinueParser extends AbstractBlockContinueParser
{
    public function __construct(
        private readonly TocBlock $block,
    ) {
    }

    public function getBlock(): TocBlock
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
        $cursor->advanceToNextNonSpaceOrTab();

        if (preg_match('/^@endtoc\s*$/', $cursor->getRemainder()) === 1) {
            return BlockContinue::finished();
        }

        // Consume and ignore body lines
        return BlockContinue::at($cursor);
    }

    public function addLine(string $line): void
    {
        // Ignore body lines
    }
}

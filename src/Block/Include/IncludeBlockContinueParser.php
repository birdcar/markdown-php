<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Include;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;

final class IncludeBlockContinueParser extends AbstractBlockContinueParser
{
    public function __construct(
        private readonly IncludeBlock $block,
    ) {
    }

    public function getBlock(): IncludeBlock
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

        if (preg_match('/^@endinclude\s*$/', $cursor->getRemainder()) === 1) {
            return BlockContinue::finished();
        }

        return BlockContinue::at($cursor);
    }

    public function addLine(string $line): void
    {
        // No body expected
    }
}

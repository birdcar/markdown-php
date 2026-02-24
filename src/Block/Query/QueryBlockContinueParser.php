<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Query;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;

final class QueryBlockContinueParser extends AbstractBlockContinueParser
{
    public function __construct(
        private readonly QueryBlock $block,
    ) {
    }

    public function getBlock(): QueryBlock
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

        if (preg_match('/^@endquery\s*$/', $cursor->getRemainder()) === 1) {
            return BlockContinue::finished();
        }

        return BlockContinue::at($cursor);
    }

    public function addLine(string $line): void
    {
        // No body expected
    }
}

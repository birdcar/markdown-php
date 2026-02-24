<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Aside;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Block\BlockContinueParserWithInlinesInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\InlineParserEngineInterface;

final class AsideBlockContinueParser extends AbstractBlockContinueParser implements BlockContinueParserWithInlinesInterface
{
    public function __construct(
        private readonly AsideBlock $block,
    ) {
    }

    public function getBlock(): AsideBlock
    {
        return $this->block;
    }

    public function isContainer(): bool
    {
        return true;
    }

    public function canContain(AbstractBlock $childBlock): bool
    {
        return true;
    }

    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
    {
        $cursor->advanceToNextNonSpaceOrTab();

        if (preg_match('/^@endaside\s*$/', $cursor->getRemainder()) === 1) {
            return BlockContinue::finished();
        }

        return BlockContinue::at($cursor);
    }

    public function parseInlines(InlineParserEngineInterface $inlineParser): void
    {
    }
}

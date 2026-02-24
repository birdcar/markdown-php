<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Math;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;

final class MathBlockContinueParser extends AbstractBlockContinueParser
{
    /** @var string[] */
    private array $lines = [];

    public function __construct(
        private readonly MathBlock $block,
    ) {
    }

    public function getBlock(): MathBlock
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

        if (preg_match('/^@endmath\s*$/', $cursor->getRemainder()) === 1) {
            return BlockContinue::finished();
        }

        return BlockContinue::at($cursor);
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

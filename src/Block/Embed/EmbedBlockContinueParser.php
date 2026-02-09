<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Embed;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;

/**
 * Continues parsing an @embed directive block until @endembed is found.
 *
 * Embed is a leaf block -- its body is accumulated as literal caption text,
 * not parsed as markdown. Lines between @embed and @endembed are joined
 * with newlines and trimmed to form the caption.
 */
final class EmbedBlockContinueParser extends AbstractBlockContinueParser
{
    private EmbedBlock $block;

    /** @var string[] Accumulated body lines used as caption text. */
    private array $captionLines = [];

    public function __construct(
        private readonly string $url,
    ) {
        $this->block = new EmbedBlock($this->url);
    }

    public function getBlock(): EmbedBlock
    {
        return $this->block;
    }

    /**
     * Embed is a leaf block -- its body is not parsed as markdown.
     */
    public function isContainer(): bool
    {
        return false;
    }

    /**
     * Leaf blocks cannot contain other blocks.
     */
    public function canContain(AbstractBlock $childBlock): bool
    {
        return false;
    }

    /**
     * Check whether the current line closes the embed via @endembed.
     * Non-closing lines are accumulated as caption text.
     */
    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
    {
        $cursor->advanceToNextNonSpaceOrTab();

        if (preg_match('/^@endembed\s*$/', $cursor->getRemainder()) === 1) {
            return BlockContinue::finished();
        }

        return BlockContinue::at($cursor);
    }

    /**
     * Accumulate body lines as caption text.
     */
    public function addLine(string $line): void
    {
        $this->captionLines[] = $line;
    }

    /**
     * Finalize the block, setting the caption from accumulated body lines.
     */
    public function closeBlock(): void
    {
        $caption = trim(implode("\n", $this->captionLines));
        $this->block->setCaption($caption);
    }
}

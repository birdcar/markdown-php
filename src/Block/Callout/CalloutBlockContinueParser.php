<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Callout;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Block\BlockContinueParserWithInlinesInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\InlineParserEngineInterface;

/**
 * Continues parsing a @callout directive block until @endcallout is found.
 *
 * As a container block, the body between @callout and @endcallout is parsed
 * as full BFM markdown, supporting nested blocks, inlines, and other directives.
 */
final class CalloutBlockContinueParser extends AbstractBlockContinueParser implements BlockContinueParserWithInlinesInterface
{
    public function __construct(
        private readonly CalloutBlock $block,
    ) {
    }

    public function getBlock(): CalloutBlock
    {
        return $this->block;
    }

    /**
     * Callout is a container block -- its body is parsed as markdown.
     */
    public function isContainer(): bool
    {
        return true;
    }

    /**
     * A callout container can hold any block-level child.
     */
    public function canContain(AbstractBlock $childBlock): bool
    {
        return true;
    }

    /**
     * Check whether the current line closes the callout via @endcallout.
     */
    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
    {
        $cursor->advanceToNextNonSpaceOrTab();

        if (preg_match('/^@endcallout\s*$/', $cursor->getRemainder()) === 1) {
            return BlockContinue::finished();
        }

        return BlockContinue::at($cursor);
    }

    /**
     * Delegate inline parsing to the engine for all child paragraph nodes.
     */
    public function parseInlines(InlineParserEngineInterface $inlineParser): void
    {
        // Container blocks have their children parsed by the main parser loop.
        // The inline parser engine is invoked on paragraph children automatically
        // by the framework, so no manual delegation is needed here.
    }
}

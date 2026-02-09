<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Embed;

use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;

/**
 * Identifies the start of an @embed directive block.
 *
 * Matches lines of the form:
 *   @embed https://example.com/video
 *
 * The URL is a required positional parameter and must be an http(s) URL.
 */
final class EmbedBlockStartParser implements BlockStartParserInterface
{
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->isIndented()) {
            return BlockStart::none();
        }

        $cursor->advanceToNextNonSpaceOrTab();
        $remainder = $cursor->getRemainder();

        if (preg_match('/^@embed\s+(https?:\/\/\S+)/', $remainder, $matches) !== 1) {
            return BlockStart::none();
        }

        $url = $matches[1];

        $cursor->advanceToEnd();

        return BlockStart::of(new EmbedBlockContinueParser($url))->at($cursor);
    }
}

<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\FootnoteDef;

use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;

final class FootnoteDefBlockStartParser implements BlockStartParserInterface
{
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->isIndented()) {
            return BlockStart::none();
        }

        $cursor->advanceToNextNonSpaceOrTab();
        $remainder = $cursor->getRemainder();

        if (preg_match('/^\[\^([a-zA-Z0-9_-]+)\]:\s*(.*)/', $remainder, $matches) !== 1) {
            return BlockStart::none();
        }

        $label = $matches[1];
        $firstLine = $matches[2];

        $cursor->advanceToEnd();

        return BlockStart::of(new FootnoteDefBlockContinueParser(
            new FootnoteDefBlock($label),
            $firstLine,
        ))->at($cursor);
    }
}

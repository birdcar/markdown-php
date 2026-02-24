<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Details;

use Birdcar\Markdown\Support\DirectiveParamParser;
use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;

final class DetailsBlockStartParser implements BlockStartParserInterface
{
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->isIndented()) {
            return BlockStart::none();
        }

        $cursor->advanceToNextNonSpaceOrTab();
        $remainder = $cursor->getRemainder();

        if (preg_match('/^@details\b(.*)$/', $remainder, $matches) !== 1) {
            return BlockStart::none();
        }

        $params = DirectiveParamParser::parse(trim($matches[1]));
        $summary = (string) ($params['summary'] ?? '');
        $open = ($params['open'] ?? false) === true;

        $cursor->advanceToEnd();

        return BlockStart::of(new DetailsBlockContinueParser(
            new DetailsBlock($summary, $open),
        ))->at($cursor);
    }
}

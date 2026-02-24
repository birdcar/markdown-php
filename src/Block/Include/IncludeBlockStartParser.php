<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Include;

use Birdcar\Markdown\Support\DirectiveParamParser;
use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;

final class IncludeBlockStartParser implements BlockStartParserInterface
{
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->isIndented()) {
            return BlockStart::none();
        }

        $cursor->advanceToNextNonSpaceOrTab();
        $remainder = $cursor->getRemainder();

        if (preg_match('/^@include\b(.*)$/', $remainder, $matches) !== 1) {
            return BlockStart::none();
        }

        $params = DirectiveParamParser::parse(trim($matches[1]));
        $path = (string) ($params['path'] ?? '');

        if ($path === '') {
            return BlockStart::none();
        }

        $heading = (string) ($params['heading'] ?? '');

        $cursor->advanceToEnd();

        return BlockStart::of(new IncludeBlockContinueParser(
            new IncludeBlock($path, $heading),
        ))->at($cursor);
    }
}

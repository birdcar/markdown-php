<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Query;

use Birdcar\Markdown\Support\DirectiveParamParser;
use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;

final class QueryBlockStartParser implements BlockStartParserInterface
{
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->isIndented()) {
            return BlockStart::none();
        }

        $cursor->advanceToNextNonSpaceOrTab();
        $remainder = $cursor->getRemainder();

        if (preg_match('/^@query\b(.*)$/', $remainder, $matches) !== 1) {
            return BlockStart::none();
        }

        $params = DirectiveParamParser::parse(trim($matches[1]));
        $queryType = (string) ($params['type'] ?? '');

        if ($queryType === '') {
            return BlockStart::none();
        }

        // Remove 'type' from params, rest are query params
        unset($params['type']);

        $cursor->advanceToEnd();

        return BlockStart::of(new QueryBlockContinueParser(
            new QueryBlock($queryType, $params),
        ))->at($cursor);
    }
}

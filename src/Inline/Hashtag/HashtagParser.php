<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\Hashtag;

use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

final class HashtagParser implements InlineParserInterface
{
    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::string('#');
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();
        $position = $cursor->getPosition();

        // Must not be preceded by alphanumeric
        if ($position > 0) {
            $prevChar = $cursor->peek(-1);
            if ($prevChar !== null && preg_match('/[a-zA-Z0-9]/', $prevChar) === 1) {
                return false;
            }
        }

        $remainder = $cursor->getRemainder();

        // Match # followed by identifier: [a-zA-Z][a-zA-Z0-9_-]*
        if (preg_match('/^#([a-zA-Z][a-zA-Z0-9_-]*)/', $remainder, $matches) !== 1) {
            return false;
        }

        $identifier = $matches[1];

        // Strip trailing _ or - characters
        $identifier = rtrim($identifier, '_-');

        if ($identifier === '') {
            return false;
        }

        $cursor->advanceBy(1 + mb_strlen($identifier, 'UTF-8'));

        $inlineContext->getContainer()->appendChild(new Hashtag($identifier));

        return true;
    }
}

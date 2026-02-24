<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\Footnote;

use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

final class FootnoteRefParser implements InlineParserInterface
{
    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::string('[^');
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();
        $remainder = $cursor->getRemainder();

        if (preg_match('/^\[\^([a-zA-Z0-9_-]+)\]/', $remainder, $matches) !== 1) {
            return false;
        }

        $label = $matches[1];
        $cursor->advanceBy(strlen($matches[0]));

        $inlineContext->getContainer()->appendChild(new FootnoteRef($label));

        return true;
    }
}

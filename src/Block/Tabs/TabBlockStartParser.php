<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Tabs;

use Birdcar\Markdown\Support\DirectiveParamParser;
use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;

final class TabBlockStartParser implements BlockStartParserInterface
{
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->isIndented()) {
            return BlockStart::none();
        }

        // @tab must be inside a @tabs container
        if (! ($parserState->getActiveBlockParser()->getBlock() instanceof TabsBlock)) {
            return BlockStart::none();
        }

        $cursor->advanceToNextNonSpaceOrTab();
        $remainder = $cursor->getRemainder();

        if (preg_match('/^@tab\b(.*)$/', $remainder, $matches) !== 1) {
            return BlockStart::none();
        }

        $params = DirectiveParamParser::parse(trim($matches[1]));
        $label = (string) ($params['label'] ?? '');

        if ($label === '') {
            return BlockStart::none();
        }

        $active = ($params['active'] ?? false) === true;

        $cursor->advanceToEnd();

        return BlockStart::of(new TabBlockContinueParser(
            new TabBlock($label, $active),
        ))->at($cursor);
    }
}

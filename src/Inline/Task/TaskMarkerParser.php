<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\Task;

use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

/**
 * Parses bullet-journal task markers at the start of list items.
 *
 * Matches patterns like `[x] `, `[>] `, `[ ] `, etc. where the character
 * inside the brackets maps to a {@see TaskState} case. The marker must
 * appear at position 0 of the inline content and the container must be a
 * Paragraph whose parent is a ListItem.
 */
final class TaskMarkerParser implements InlineParserInterface
{
    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::string('[');
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $container = $inlineContext->getContainer();
        $cursor = $inlineContext->getCursor();

        // The marker must be at the very beginning of the paragraph
        if ($cursor->getPosition() !== 0) {
            return false;
        }

        // Must be inside a Paragraph that is a direct child of a ListItem
        if (! $container instanceof Paragraph || ! $container->parent() instanceof ListItem) {
            return false;
        }

        // Must not already have children (i.e., this is the first inline parsed)
        if ($container->hasChildren()) {
            return false;
        }

        $remainder = $cursor->getRemainder();

        // Match `[<marker>] ` where <marker> is one of: space, x, X, >, <, !, o, -
        if (preg_match('/^\[([xX ><!o\-])\] /', $remainder, $matches) !== 1) {
            return false;
        }

        $marker = strtolower($matches[1]);
        $state = TaskState::fromMarker($marker);

        if ($state === null) {
            return false;
        }

        // Advance the cursor past the full match (e.g. "[x] " = 4 chars)
        $cursor->advanceBy(mb_strlen($matches[0], 'UTF-8'));

        $container->appendChild(new TaskMarker($state));

        return true;
    }
}

<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\TaskModifier;

use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

/**
 * Parses task modifiers that begin with `//` followed by a key and optional `:value`.
 *
 * Syntax:
 *   - `//key` -- boolean flag (value is null)
 *   - `//key:value` -- key-value pair; value extends until the next ` //` that
 *     is followed by a valid key, or until end of remaining text
 *
 * The `//` prefix must be preceded by whitespace or appear at the start of the
 * inline content. Keys must match `[a-z][a-z0-9]*`.
 */
final class TaskModifierParser implements InlineParserInterface
{
    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::string('/');
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();
        $position = $cursor->getPosition();

        // Must be preceded by whitespace or be at the start
        if ($position > 0) {
            $prevChar = $cursor->getCharacter($position - 1);
            if ($prevChar !== ' ' && $prevChar !== "\t") {
                return false;
            }
        }

        // Must have a second `/` to form the `//` prefix
        if ($cursor->peek(1) !== '/') {
            return false;
        }

        $remainder = $cursor->getRemainder();

        // Match `//key` optionally followed by `:value`
        if (preg_match('/^\/\/([a-z][a-z0-9]*)/', $remainder, $keyMatch) !== 1) {
            return false;
        }

        $key = $keyMatch[1];
        $consumed = $keyMatch[0]; // e.g. "//due"
        $value = null;

        // Check for `:value` portion
        $afterKey = substr($remainder, strlen($consumed));
        if (str_starts_with($afterKey, ':')) {
            $valueText = substr($afterKey, 1); // everything after the colon

            // Value extends until the next ` //` followed by a valid key, or end of text.
            // Use a lookahead to find the boundary.
            if (preg_match('/^(.*?)(?= \/\/[a-z][a-z0-9]*(?:[:\s]|$))|^(.+)$/s', $valueText, $valueMatch) === 1) {
                $rawValue = $valueMatch[1] !== '' ? $valueMatch[1] : ($valueMatch[2] ?? '');
                $value = rtrim($rawValue);
                $consumed .= ':' . $rawValue;
            }
        }

        // Advance cursor past everything we consumed
        $cursor->advanceBy(mb_strlen($consumed, 'UTF-8'));

        $inlineContext->getContainer()->appendChild(new TaskModifier($key, $value));

        return true;
    }
}

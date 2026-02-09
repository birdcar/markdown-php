<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\Mention;

use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

/**
 * Parses @-mentions into {@see Mention} AST nodes.
 *
 * The `@` must be preceded by whitespace, punctuation, or appear at the start
 * of the inline content (i.e., not preceded by an alphanumeric character).
 * The identifier pattern is `[a-zA-Z][a-zA-Z0-9._-]*` with trailing `.`, `_`,
 * or `-` characters stripped so they are treated as punctuation rather than
 * part of the identifier.
 */
final class MentionParser implements InlineParserInterface
{
    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::string('@');
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();
        $position = $cursor->getPosition();

        // The @ must not be preceded by an alphanumeric character
        if ($position > 0) {
            $prevChar = $cursor->getCharacter($position - 1);
            if ($prevChar !== null && preg_match('/[a-zA-Z0-9]/', $prevChar) === 1) {
                return false;
            }
        }

        $remainder = $cursor->getRemainder();

        // Match @ followed by an identifier: starts with a letter, then letters/digits/._-
        if (preg_match('/^@([a-zA-Z][a-zA-Z0-9._-]*)/', $remainder, $matches) !== 1) {
            return false;
        }

        $identifier = $matches[1];

        // Strip trailing `.`, `_`, or `-` characters
        $identifier = rtrim($identifier, '._-');

        // After stripping, the identifier must still be non-empty
        if ($identifier === '') {
            return false;
        }

        // Advance cursor past `@` + the (possibly trimmed) identifier
        $cursor->advanceBy(1 + mb_strlen($identifier, 'UTF-8'));

        $inlineContext->getContainer()->appendChild(new Mention($identifier));

        return true;
    }
}

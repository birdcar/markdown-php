<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Callout;

use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;

/**
 * Identifies the start of a @callout directive block.
 *
 * Matches lines of the form:
 *   @callout [type=value] [title="quoted value"]
 *
 * Parameters are parsed as key=value pairs where values may be
 * unquoted (terminated at whitespace) or double-quoted (may contain spaces,
 * with \" for literal quote escaping).
 */
final class CalloutBlockStartParser implements BlockStartParserInterface
{
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        if ($cursor->isIndented()) {
            return BlockStart::none();
        }

        $cursor->advanceToNextNonSpaceOrTab();
        $remainder = $cursor->getRemainder();

        if (preg_match('/^@callout\b(.*)$/', $remainder, $matches) !== 1) {
            return BlockStart::none();
        }

        $params = self::parseParams(trim($matches[1]));
        $type = $params['type'] ?? 'info';
        $title = $params['title'] ?? '';

        $cursor->advanceToEnd();

        return BlockStart::of(new CalloutBlockContinueParser(
            new CalloutBlock($type, $title),
        ))->at($cursor);
    }

    /**
     * Parse a parameter string into an associative array.
     *
     * Supports two value formats:
     *   - Unquoted: key=value (value terminates at whitespace)
     *   - Quoted:   key="value with spaces" (\" escapes a literal quote)
     *
     * @return array<string, string>
     */
    private static function parseParams(string $input): array
    {
        $params = [];
        $offset = 0;
        $length = strlen($input);

        while ($offset < $length) {
            // Skip whitespace
            while ($offset < $length && ctype_space($input[$offset])) {
                $offset++;
            }

            if ($offset >= $length) {
                break;
            }

            // Match key
            if (preg_match('/\G([a-z][a-z0-9_]*)=/', $input, $keyMatch, 0, $offset) !== 1) {
                // Not a key=value pair; skip this token
                while ($offset < $length && !ctype_space($input[$offset])) {
                    $offset++;
                }
                continue;
            }

            $key = $keyMatch[1];
            $offset += strlen($keyMatch[0]);

            if ($offset < $length && $input[$offset] === '"') {
                // Quoted value
                $offset++; // skip opening quote
                $value = '';
                while ($offset < $length) {
                    if ($input[$offset] === '\\' && $offset + 1 < $length && $input[$offset + 1] === '"') {
                        $value .= '"';
                        $offset += 2;
                    } elseif ($input[$offset] === '"') {
                        $offset++; // skip closing quote
                        break;
                    } else {
                        $value .= $input[$offset];
                        $offset++;
                    }
                }
                $params[$key] = $value;
            } else {
                // Unquoted value: runs until whitespace or end
                $start = $offset;
                while ($offset < $length && !ctype_space($input[$offset])) {
                    $offset++;
                }
                $params[$key] = substr($input, $start, $offset - $start);
            }
        }

        return $params;
    }
}

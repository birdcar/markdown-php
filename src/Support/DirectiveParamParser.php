<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Support;

final class DirectiveParamParser
{
    /** @return array<string, string|bool> */
    public static function parse(string $input): array
    {
        $params = [];
        $offset = 0;
        $length = strlen($input);

        while ($offset < $length) {
            while ($offset < $length && ctype_space($input[$offset])) {
                $offset++;
            }

            if ($offset >= $length) {
                break;
            }

            // Match key
            if (preg_match('/\G([a-z][a-z0-9_]*)/', $input, $keyMatch, 0, $offset) !== 1) {
                while ($offset < $length && !ctype_space($input[$offset])) {
                    $offset++;
                }
                continue;
            }

            $key = $keyMatch[1];
            $offset += strlen($keyMatch[0]);

            // Check for = sign
            if ($offset < $length && $input[$offset] === '=') {
                $offset++;

                if ($offset < $length && $input[$offset] === '"') {
                    // Quoted value
                    $offset++;
                    $value = '';
                    while ($offset < $length) {
                        if ($input[$offset] === '\\' && $offset + 1 < $length && $input[$offset + 1] === '"') {
                            $value .= '"';
                            $offset += 2;
                        } elseif ($input[$offset] === '"') {
                            $offset++;
                            break;
                        } else {
                            $value .= $input[$offset];
                            $offset++;
                        }
                    }
                    $params[$key] = $value;
                } else {
                    // Unquoted value
                    $start = $offset;
                    while ($offset < $length && !ctype_space($input[$offset])) {
                        $offset++;
                    }
                    $params[$key] = substr($input, $start, $offset - $start);
                }
            } else {
                // Boolean flag (no =)
                $params[$key] = true;
            }
        }

        return $params;
    }
}

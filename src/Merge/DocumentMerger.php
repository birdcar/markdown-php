<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Merge;

final class DocumentMerger
{
    /**
     * @param BfmDocument[] $documents
     */
    public function merge(array $documents, ?MergeOptions $options = null): BfmDocument
    {
        $options ??= new MergeOptions();

        if ($documents === []) {
            return new BfmDocument(frontmatter: [], body: '');
        }

        $result = array_shift($documents);

        foreach ($documents as $doc) {
            $body = $result->body !== '' && $doc->body !== ''
                ? $result->body . $options->separator . $doc->body
                : $result->body . $doc->body;

            $result = new BfmDocument(
                frontmatter: $this->deepMerge($result->frontmatter, $doc->frontmatter, $options),
                body: $body,
            );
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $target
     * @param array<string, mixed> $source
     * @return array<string, mixed>
     */
    private function deepMerge(array $target, array $source, MergeOptions $options): array
    {
        $result = $target;

        foreach ($source as $key => $value) {
            if (! array_key_exists($key, $result)) {
                $result[$key] = $value;

                continue;
            }

            $existing = $result[$key];

            // Both are lists — concatenate
            if (is_array($existing) && is_array($value) && array_is_list($existing) && array_is_list($value)) {
                $result[$key] = array_merge($existing, $value);
            }
            // Both are associative arrays — recurse
            elseif (is_array($existing) && is_array($value) && ! array_is_list($existing) && ! array_is_list($value)) {
                $result[$key] = $this->deepMerge($existing, $value, $options);
            }
            // Scalar conflict — apply strategy
            else {
                $result[$key] = $options->resolveConflict($key, $existing, $value);
            }
        }

        return $result;
    }
}

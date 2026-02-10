<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Merge;

final readonly class BfmDocument
{
    /**
     * @param array<string, mixed> $frontmatter
     */
    public function __construct(
        public array $frontmatter,
        public string $body,
    ) {
    }
}

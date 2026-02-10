<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Metadata;

final readonly class DocumentMetadata
{
    /**
     * @param array<string, mixed> $frontmatter
     * @param array<string, mixed> $computed
     * @param array<string, mixed> $custom
     */
    public function __construct(
        public array $frontmatter,
        public array $computed,
        public array $custom = [],
    ) {
    }
}

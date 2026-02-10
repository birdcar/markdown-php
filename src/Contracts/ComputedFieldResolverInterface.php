<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Contracts;

use League\CommonMark\Node\Block\Document;

interface ComputedFieldResolverInterface
{
    /**
     * @param array<string, mixed> $frontmatter
     * @param array<string, mixed> $builtins
     *
     * @return array<string, mixed>
     */
    public function resolve(Document $document, array $frontmatter, array $builtins): array;
}

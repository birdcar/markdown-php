<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Contracts;

use League\CommonMark\Node\Block\Document;

interface QueryResolverInterface
{
    /** @return array<int, array<string, mixed>> */
    public function resolve(string $type, array $params, Document $document): array;
}

<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Contracts;

interface MentionResolverInterface
{
    /**
     * @return array{label: string, url: string|null}|null
     */
    public function resolve(string $identifier): ?array;
}

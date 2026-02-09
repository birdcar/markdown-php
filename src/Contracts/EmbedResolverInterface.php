<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Contracts;

interface EmbedResolverInterface
{
    /**
     * @return array{type: string, html?: string, title?: string, description?: string, thumbnailUrl?: string, providerName?: string, url?: string}|null
     */
    public function resolve(string $url): ?array;
}

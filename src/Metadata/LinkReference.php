<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Metadata;

final readonly class LinkReference
{
    public function __construct(
        public string $url,
        public ?string $title = null,
        public ?int $line = null,
    ) {
    }
}

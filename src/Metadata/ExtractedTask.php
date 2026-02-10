<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Metadata;

final readonly class ExtractedTask
{
    /**
     * @param array<array{key: string, value: string|null}> $modifiers
     */
    public function __construct(
        public string $text,
        public string $state,
        public array $modifiers = [],
        public ?int $line = null,
    ) {
    }
}

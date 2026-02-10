<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Metadata;

final readonly class TaskCollection
{
    /**
     * @param ExtractedTask[] $all
     * @param ExtractedTask[] $open
     * @param ExtractedTask[] $done
     * @param ExtractedTask[] $scheduled
     * @param ExtractedTask[] $migrated
     * @param ExtractedTask[] $irrelevant
     * @param ExtractedTask[] $event
     * @param ExtractedTask[] $priority
     */
    public function __construct(
        public array $all = [],
        public array $open = [],
        public array $done = [],
        public array $scheduled = [],
        public array $migrated = [],
        public array $irrelevant = [],
        public array $event = [],
        public array $priority = [],
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Merge;

final class MergeConflictException extends \RuntimeException
{
    public function __construct(string $key, mixed $existing, mixed $incoming)
    {
        parent::__construct(sprintf(
            'Merge conflict at key "%s": cannot merge %s with %s',
            $key,
            get_debug_type($existing),
            get_debug_type($incoming),
        ));
    }
}

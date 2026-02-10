<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Merge;

final class MergeOptions
{
    public readonly MergeStrategy|null $strategyEnum;
    public readonly ?\Closure $strategyFn;
    public readonly string $separator;

    public function __construct(
        MergeStrategy|\Closure $strategy = MergeStrategy::LastWins,
        string $separator = "\n\n",
    ) {
        if ($strategy instanceof \Closure) {
            $this->strategyEnum = null;
            $this->strategyFn = $strategy;
        } else {
            $this->strategyEnum = $strategy;
            $this->strategyFn = null;
        }
        $this->separator = $separator;
    }

    public function resolveConflict(string $key, mixed $existing, mixed $incoming): mixed
    {
        if ($this->strategyFn !== null) {
            return ($this->strategyFn)($key, $existing, $incoming);
        }

        return match ($this->strategyEnum) {
            MergeStrategy::LastWins => $incoming,
            MergeStrategy::FirstWins => $existing,
            MergeStrategy::Error => throw new MergeConflictException($key, $existing, $incoming),
            default => $incoming,
        };
    }
}

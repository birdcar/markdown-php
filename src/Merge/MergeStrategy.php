<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Merge;

enum MergeStrategy: string
{
    case LastWins = 'last-wins';
    case FirstWins = 'first-wins';
    case Error = 'error';
}

<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Contracts;

interface IncludeResolverInterface
{
    public function resolve(string $path, ?string $heading = null): ?string;
}

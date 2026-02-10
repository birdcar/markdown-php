<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\Hashtag;

use League\CommonMark\Node\Inline\AbstractInline;

final class Hashtag extends AbstractInline
{
    public function __construct(
        private readonly string $identifier,
    ) {
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}

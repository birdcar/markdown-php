<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\Hashtag;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

final class HashtagExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addInlineParser(new HashtagParser());
        $environment->addRenderer(Hashtag::class, new HashtagRenderer());
    }
}

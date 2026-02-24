<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Details;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

final class DetailsExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addBlockStartParser(new DetailsBlockStartParser(), 80);
        $environment->addRenderer(DetailsBlock::class, new DetailsBlockRenderer());
    }
}

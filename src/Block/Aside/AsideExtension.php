<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Aside;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

final class AsideExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addBlockStartParser(new AsideBlockStartParser(), 80);
        $environment->addRenderer(AsideBlock::class, new AsideBlockRenderer());
    }
}

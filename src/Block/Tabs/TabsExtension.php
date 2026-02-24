<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Tabs;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

final class TabsExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addBlockStartParser(new TabsBlockStartParser(), 80);
        $environment->addBlockStartParser(new TabBlockStartParser(), 80);
        $environment->addRenderer(TabsBlock::class, new TabsBlockRenderer());
        $environment->addRenderer(TabBlock::class, new TabBlockRenderer());
    }
}

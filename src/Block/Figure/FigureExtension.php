<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Figure;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

final class FigureExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addBlockStartParser(new FigureBlockStartParser(), 80);
        $environment->addRenderer(FigureBlock::class, new FigureBlockRenderer());
    }
}

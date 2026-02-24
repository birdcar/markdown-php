<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Math;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

final class MathExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addBlockStartParser(new MathBlockStartParser(), 80);
        $environment->addRenderer(MathBlock::class, new MathBlockRenderer());
    }
}

<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Endnotes;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

final class EndnotesExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addBlockStartParser(new EndnotesBlockStartParser(), 80);
        $environment->addRenderer(EndnotesBlock::class, new EndnotesBlockRenderer());
    }
}

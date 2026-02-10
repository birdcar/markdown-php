<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Frontmatter;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

final class FrontmatterExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addBlockStartParser(new FrontmatterBlockStartParser(), 200);
        $environment->addRenderer(FrontmatterBlock::class, new FrontmatterRenderer());
    }
}

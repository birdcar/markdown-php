<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Include;

use Birdcar\Markdown\Contracts\IncludeResolverInterface;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

final class IncludeExtension implements ExtensionInterface
{
    public function __construct(
        private readonly ?IncludeResolverInterface $resolver = null,
    ) {
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addBlockStartParser(new IncludeBlockStartParser(), 80);
        $environment->addRenderer(IncludeBlock::class, new IncludeBlockRenderer($this->resolver));
    }
}

<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Embed;

use Birdcar\Markdown\Contracts\EmbedResolverInterface;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

/**
 * Registers the @embed directive block parser and renderer.
 *
 * Usage:
 *   $environment->addExtension(new EmbedExtension());
 *   $environment->addExtension(new EmbedExtension($myOembedResolver));
 */
final class EmbedExtension implements ExtensionInterface
{
    public function __construct(
        private readonly ?EmbedResolverInterface $resolver = null,
    ) {
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addBlockStartParser(new EmbedBlockStartParser(), 80);
        $environment->addRenderer(EmbedBlock::class, new EmbedBlockRenderer($this->resolver));
    }
}

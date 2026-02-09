<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Callout;

use Birdcar\Markdown\Environment\RenderProfile;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

/**
 * Registers the @callout directive block parser and renderer.
 *
 * Usage:
 *   $environment->addExtension(new CalloutExtension());
 *   $environment->addExtension(new CalloutExtension(RenderProfile::Email));
 */
final class CalloutExtension implements ExtensionInterface
{
    public function __construct(
        private readonly RenderProfile $profile = RenderProfile::Html,
    ) {
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addBlockStartParser(new CalloutBlockStartParser(), 80);
        $environment->addRenderer(CalloutBlock::class, new CalloutBlockRenderer());
    }
}

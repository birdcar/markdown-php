<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\Mention;

use Birdcar\Markdown\Contracts\MentionResolverInterface;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

/**
 * Registers the @-mention inline parser and renderer.
 *
 * Optionally accepts a {@see MentionResolverInterface} to resolve mention
 * identifiers into URLs and display labels.
 *
 * Usage:
 * ```php
 * $environment->addExtension(new MentionExtension($resolver));
 * ```
 */
final class MentionExtension implements ExtensionInterface
{
    public function __construct(
        private readonly ?MentionResolverInterface $resolver = null,
    ) {
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addInlineParser(new MentionParser());
        $environment->addRenderer(Mention::class, new MentionRenderer($this->resolver));
    }
}

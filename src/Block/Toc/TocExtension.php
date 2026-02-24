<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Toc;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;

final class TocExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addBlockStartParser(new TocBlockStartParser(), 80);
        $environment->addRenderer(TocBlock::class, new TocBlockRenderer());
        $environment->addEventListener(DocumentParsedEvent::class, new TocHeadingCollector());
    }
}

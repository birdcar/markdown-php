<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Footnote;

use Birdcar\Markdown\Block\FootnoteDef\FootnoteDefBlock;
use Birdcar\Markdown\Block\FootnoteDef\FootnoteDefBlockStartParser;
use Birdcar\Markdown\Block\FootnoteDef\FootnoteDefRenderer;
use Birdcar\Markdown\Inline\Footnote\FootnoteRef;
use Birdcar\Markdown\Inline\Footnote\FootnoteRefParser;
use Birdcar\Markdown\Inline\Footnote\FootnoteRefRenderer;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;

final class FootnoteExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addBlockStartParser(new FootnoteDefBlockStartParser(), 80);
        $environment->addInlineParser(new FootnoteRefParser(), 100);
        $environment->addRenderer(FootnoteDefBlock::class, new FootnoteDefRenderer());
        $environment->addRenderer(FootnoteRef::class, new FootnoteRefRenderer());
        $environment->addEventListener(DocumentParsedEvent::class, new FootnoteCollectorProcessor());
    }
}

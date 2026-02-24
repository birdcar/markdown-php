<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Footnote;

use Birdcar\Markdown\Block\Endnotes\EndnotesBlock;
use Birdcar\Markdown\Block\FootnoteDef\FootnoteDefBlock;
use Birdcar\Markdown\Inline\Footnote\FootnoteRef;
use League\CommonMark\Event\DocumentParsedEvent;

final class FootnoteCollectorProcessor
{
    public function __invoke(DocumentParsedEvent $event): void
    {
        $document = $event->getDocument();
        $collection = new FootnoteCollection();

        // Pass 1: Collect all refs in document order (assigns indexes)
        $walker = $document->walker();
        while ($e = $walker->next()) {
            if ($e->isEntering() && $e->getNode() instanceof FootnoteRef) {
                $ref = $e->getNode();
                assert($ref instanceof FootnoteRef);
                $index = $collection->addRef($ref->label);
                $ref->data->set('footnoteIndex', $index);
            }
        }

        // Pass 2: Collect all definitions
        $walker = $document->walker();
        while ($e = $walker->next()) {
            if ($e->isEntering() && $e->getNode() instanceof FootnoteDefBlock) {
                $def = $e->getNode();
                assert($def instanceof FootnoteDefBlock);
                $collection->addDefinition($def->label, $def->getContent());
            }
        }

        if (!$collection->hasFootnotes()) {
            return;
        }

        // Build endnotes HTML
        $html = $this->buildEndnotesHtml($collection);

        // Find EndnotesBlock and set its generated HTML
        $endnotesBlock = null;
        $walker = $document->walker();
        while ($e = $walker->next()) {
            if ($e->isEntering() && $e->getNode() instanceof EndnotesBlock) {
                $endnotesBlock = $e->getNode();
                assert($endnotesBlock instanceof EndnotesBlock);
                break;
            }
        }

        if ($endnotesBlock !== null) {
            $endnotesBlock->setGeneratedHtml($html);
        } else {
            // Auto-append endnotes at document end
            $autoBlock = new EndnotesBlock();
            $autoBlock->setGeneratedHtml($html);
            $document->appendChild($autoBlock);
        }
    }

    private function buildEndnotesHtml(FootnoteCollection $collection): string
    {
        $items = [];
        foreach ($collection->getRefIndex() as $label => $index) {
            $label = (string) $label;
            $content = $collection->getContent($label) ?? '';
            $escapedContent = htmlspecialchars($content, ENT_QUOTES | ENT_HTML5);
            $backref = '<a href="#fnref-' . $label . '" class="endnote-backref" role="doc-backlink">&#8617;</a>';
            $items[] = '<li id="fn-' . $label . '"><p>' . $escapedContent . ' ' . $backref . '</p></li>';
        }

        return "<ol>\n" . implode("\n", $items) . "\n</ol>";
    }
}

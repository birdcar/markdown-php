<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Toc;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\Inline\Text;

final class TocHeadingCollector
{
    public function __invoke(DocumentParsedEvent $event): void
    {
        $document = $event->getDocument();
        $walker = $document->walker();

        // Find all TocBlock nodes and all Heading nodes
        $tocBlocks = [];
        $headings = [];

        while ($e = $walker->next()) {
            $node = $e->getNode();
            if (!$e->isEntering()) {
                continue;
            }

            if ($node instanceof TocBlock) {
                $tocBlocks[] = $node;
            } elseif ($node instanceof Heading) {
                $text = $this->extractText($node);
                $slug = $this->slugify($text);
                $headings[] = [
                    'level' => $node->getLevel(),
                    'text' => $text,
                    'slug' => $slug,
                ];
            }
        }

        foreach ($tocBlocks as $toc) {
            $filtered = array_filter(
                $headings,
                fn (array $h) => $h['level'] <= $toc->maxDepth,
            );
            $html = $this->buildList(array_values($filtered), $toc->ordered);
            $toc->setGeneratedHtml($html);
        }
    }

    private function extractText(\League\CommonMark\Node\Node $node): string
    {
        $text = '';
        $walker = $node->walker();
        while ($e = $walker->next()) {
            if ($e->isEntering() && $e->getNode() instanceof Text) {
                $text .= $e->getNode()->getLiteral();
            }
        }

        return $text;
    }

    private function slugify(string $text): string
    {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug) ?? $slug;
        $slug = preg_replace('/[\s-]+/', '-', $slug) ?? $slug;

        return trim($slug, '-');
    }

    /** @param array<int, array{level: int, text: string, slug: string}> $headings */
    private function buildList(array $headings, bool $ordered): string
    {
        if (count($headings) === 0) {
            return '';
        }

        $tag = $ordered ? 'ol' : 'ul';
        $html = "<{$tag}>\n";

        foreach ($headings as $heading) {
            $html .= '<li><a href="#' . htmlspecialchars($heading['slug']) . '">'
                . htmlspecialchars($heading['text']) . "</a></li>\n";
        }

        $html .= "</{$tag}>";

        return $html;
    }
}

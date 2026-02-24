<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\Footnote;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class FootnoteRefRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string
    {
        FootnoteRef::assertInstanceOf($node);
        assert($node instanceof FootnoteRef);

        $label = $node->label;

        // The index is stored on the node by the FootnoteCollectorProcessor
        $index = $node->data->get('footnoteIndex', $label);

        $link = new HtmlElement('a', [
            'href' => '#fn-' . $label,
            'role' => 'doc-noteref',
        ], '[' . $index . ']');

        return new HtmlElement('sup', [
            'class' => 'footnote-ref',
            'id' => 'fnref-' . $label,
        ], (string) $link);
    }
}

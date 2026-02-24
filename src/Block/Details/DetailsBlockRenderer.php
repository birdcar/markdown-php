<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Details;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class DetailsBlockRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string
    {
        DetailsBlock::assertInstanceOf($node);
        assert($node instanceof DetailsBlock);

        $attrs = [];
        if ($node->open) {
            $attrs['open'] = 'open';
        }

        $inner = '';

        if ($node->summary !== '') {
            $inner .= (string) new HtmlElement('summary', [], $node->summary);
        }

        $inner .= $childRenderer->renderNodes($node->children());

        return new HtmlElement('details', $attrs, "\n" . $inner . "\n");
    }
}

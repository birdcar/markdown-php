<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Figure;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class FigureBlockRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string
    {
        FigureBlock::assertInstanceOf($node);
        assert($node instanceof FigureBlock);

        $figureAttrs = [];
        if ($node->id !== '') {
            $figureAttrs['id'] = $node->id;
        }

        $imgAttrs = ['src' => $node->src];
        if ($node->alt !== '') {
            $imgAttrs['alt'] = $node->alt;
        }

        $inner = (string) new HtmlElement('img', $imgAttrs, '', true);

        $children = $node->children();
        if (count($children) > 0) {
            $captionContent = $childRenderer->renderNodes($children);
            $inner .= "\n" . (string) new HtmlElement('figcaption', [], $captionContent);
        }

        return new HtmlElement('figure', $figureAttrs, "\n" . $inner . "\n");
    }
}

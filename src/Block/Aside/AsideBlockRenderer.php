<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Aside;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class AsideBlockRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string
    {
        AsideBlock::assertInstanceOf($node);
        assert($node instanceof AsideBlock);

        $inner = '';

        if ($node->title !== '') {
            $inner .= (string) new HtmlElement('p', ['class' => 'aside__title'], $node->title);
        }

        $inner .= $childRenderer->renderNodes($node->children());

        return new HtmlElement('aside', ['class' => 'aside'], "\n" . $inner . "\n");
    }
}

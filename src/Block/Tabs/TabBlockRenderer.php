<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Tabs;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class TabBlockRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string
    {
        TabBlock::assertInstanceOf($node);
        assert($node instanceof TabBlock);

        $attrs = [
            'class' => 'tabs__panel' . ($node->active ? ' tabs__panel--active' : ''),
            'role' => 'tabpanel',
        ];

        return new HtmlElement('div', $attrs, $childRenderer->renderNodes($node->children()));
    }
}

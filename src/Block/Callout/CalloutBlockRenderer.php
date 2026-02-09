<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Callout;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * Renders a CalloutBlock AST node to HTML.
 *
 * Output structure:
 *   <aside class="callout callout--{type}">
 *     <div class="callout__header">{title}</div>          (only if title is non-empty)
 *     <div class="callout__body">{children}</div>
 *   </aside>
 *
 * The header div is omitted entirely when the callout has no title.
 */
final class CalloutBlockRenderer implements NodeRendererInterface
{
    /**
     * @param Node                       $node          The CalloutBlock node to render.
     * @param ChildNodeRendererInterface $childRenderer Renderer for child nodes.
     *
     * @return \Stringable|string
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string
    {
        CalloutBlock::assertInstanceOf($node);
        assert($node instanceof CalloutBlock);

        $innerElements = [];

        if ($node->title !== '') {
            $innerElements[] = new HtmlElement(
                'div',
                ['class' => 'callout__header'],
                $node->title,
            );
        }

        $innerElements[] = new HtmlElement(
            'div',
            ['class' => 'callout__body'],
            $childRenderer->getInnerSeparator() . $childRenderer->renderNodes($node->children()) . $childRenderer->getInnerSeparator(),
        );

        return new HtmlElement(
            'aside',
            ['class' => 'callout callout--' . $node->type],
            $childRenderer->getInnerSeparator() . implode($childRenderer->getInnerSeparator(), $innerElements) . $childRenderer->getInnerSeparator(),
        );
    }
}

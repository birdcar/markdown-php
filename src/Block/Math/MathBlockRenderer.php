<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Math;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class MathBlockRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string
    {
        MathBlock::assertInstanceOf($node);
        assert($node instanceof MathBlock);

        $attrs = ['class' => 'math', 'role' => 'math'];

        if ($node->label !== '') {
            $attrs['id'] = $node->label;
        }

        return new HtmlElement('div', $attrs, "\n" . $node->getContent() . "\n");
    }
}

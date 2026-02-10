<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\Hashtag;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class HashtagRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string|null
    {
        Hashtag::assertInstanceOf($node);
        \assert($node instanceof Hashtag);

        return new HtmlElement(
            'span',
            ['class' => 'hashtag'],
            '#' . $node->getIdentifier(),
        );
    }
}

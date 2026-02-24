<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Query;

use Birdcar\Markdown\Contracts\QueryResolverInterface;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class QueryBlockRenderer implements NodeRendererInterface
{
    public function __construct(
        private readonly ?QueryResolverInterface $resolver = null,
    ) {
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string
    {
        QueryBlock::assertInstanceOf($node);
        assert($node instanceof QueryBlock);

        $attrs = [
            'class' => 'query',
            'data-query-type' => $node->queryType,
        ];

        return new HtmlElement('div', $attrs, '');
    }
}

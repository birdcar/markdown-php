<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Include;

use Birdcar\Markdown\Contracts\IncludeResolverInterface;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class IncludeBlockRenderer implements NodeRendererInterface
{
    public function __construct(
        private readonly ?IncludeResolverInterface $resolver = null,
    ) {
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string
    {
        IncludeBlock::assertInstanceOf($node);
        assert($node instanceof IncludeBlock);

        if ($this->resolver !== null) {
            $heading = $node->heading !== '' ? $node->heading : null;
            $resolved = $this->resolver->resolve($node->path, $heading);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        $attrs = [
            'class' => 'include',
            'data-path' => $node->path,
        ];

        if ($node->heading !== '') {
            $attrs['data-heading'] = $node->heading;
        }

        return new HtmlElement('div', $attrs, '');
    }
}

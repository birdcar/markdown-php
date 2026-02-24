<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Toc;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class TocBlockRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string
    {
        TocBlock::assertInstanceOf($node);
        assert($node instanceof TocBlock);

        $generatedHtml = $node->getGeneratedHtml();

        if ($generatedHtml === '') {
            return '';
        }

        return new HtmlElement(
            'nav',
            ['class' => 'toc', 'aria-label' => 'Table of contents'],
            "\n" . $generatedHtml . "\n",
        );
    }
}

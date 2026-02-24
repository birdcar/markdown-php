<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Endnotes;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class EndnotesBlockRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string
    {
        EndnotesBlock::assertInstanceOf($node);
        assert($node instanceof EndnotesBlock);

        $generatedHtml = $node->getGeneratedHtml();

        if ($generatedHtml === '') {
            return '';
        }

        $inner = '';

        if ($node->title !== '') {
            $inner .= (string) new HtmlElement('h2', [], $node->title);
        }

        $inner .= $generatedHtml;

        return new HtmlElement(
            'section',
            ['class' => 'endnotes', 'role' => 'doc-endnotes'],
            "\n" . $inner . "\n",
        );
    }
}

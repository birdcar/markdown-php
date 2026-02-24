<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\FootnoteDef;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

final class FootnoteDefRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string
    {
        // Rendered by EndnotesBlockRenderer / FootnoteCollectorProcessor
        return '';
    }
}

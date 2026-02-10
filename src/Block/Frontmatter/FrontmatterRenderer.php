<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Frontmatter;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

final class FrontmatterRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string|null
    {
        FrontmatterBlock::assertInstanceOf($node);

        // Front-matter is metadata, not visible content
        return '';
    }
}

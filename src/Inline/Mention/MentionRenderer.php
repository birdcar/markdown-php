<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\Mention;

use Birdcar\Markdown\Contracts\MentionResolverInterface;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * Renders a {@see Mention} node as either a link or a plain span.
 *
 * When a {@see MentionResolverInterface} is provided and resolves the identifier
 * to a URL, the mention is rendered as a clickable link:
 * ```html
 * <a href="https://example.com/@user" class="mention">@user</a>
 * ```
 *
 * Otherwise it falls back to a non-interactive span:
 * ```html
 * <span class="mention">@identifier</span>
 * ```
 */
final class MentionRenderer implements NodeRendererInterface
{
    public function __construct(
        private readonly ?MentionResolverInterface $resolver = null,
    ) {
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string|null
    {
        Mention::assertInstanceOf($node);
        \assert($node instanceof Mention);

        $identifier = $node->identifier;

        if ($this->resolver !== null) {
            $resolved = $this->resolver->resolve($identifier);

            if ($resolved !== null && $resolved['url'] !== null) {
                return new HtmlElement('a', [
                    'href' => $resolved['url'],
                    'class' => 'mention',
                ], "@{$resolved['label']}");
            }
        }

        return new HtmlElement('span', [
            'class' => 'mention',
        ], "@{$identifier}");
    }
}

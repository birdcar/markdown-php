<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Embed;

use Birdcar\Markdown\Contracts\EmbedResolverInterface;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * Renders an EmbedBlock AST node to HTML.
 *
 * When a resolver is provided and successfully resolves the URL, the response
 * determines the rendered output (e.g. inline HTML from an oEmbed provider).
 *
 * When no resolver is provided (or the resolver returns null), the fallback
 * rendering is used:
 *   <figure class="embed">
 *     <a href="{url}" class="embed__link">{url}</a>
 *     <figcaption class="embed__caption">{caption}</figcaption>  (only if caption is non-empty)
 *   </figure>
 */
final class EmbedBlockRenderer implements NodeRendererInterface
{
    public function __construct(
        private readonly ?EmbedResolverInterface $resolver = null,
    ) {
    }

    /**
     * @param Node                       $node          The EmbedBlock node to render.
     * @param ChildNodeRendererInterface $childRenderer Renderer for child nodes (unused for leaf blocks).
     *
     * @return \Stringable|string
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string
    {
        EmbedBlock::assertInstanceOf($node);
        assert($node instanceof EmbedBlock);

        // Attempt resolution via the injected resolver
        if ($this->resolver !== null) {
            $resolved = $this->resolver->resolve($node->url);
            if ($resolved !== null) {
                return $this->renderResolved($resolved, $node);
            }
        }

        return $this->renderFallback($node);
    }

    /**
     * Render using a resolved embed response.
     *
     * If the response contains pre-built HTML, render it directly. Otherwise
     * fall back to the default link rendering.
     *
     * @param array{type: string, html?: string, title?: string, description?: string, thumbnailUrl?: string, providerName?: string, url?: string} $resolved
     */
    private function renderResolved(array $resolved, EmbedBlock $node): \Stringable|string
    {
        if (isset($resolved['html']) && $resolved['html'] !== '') {
            $elements = [$resolved['html']];

            if ($node->getCaption() !== '') {
                $elements[] = (string) new HtmlElement(
                    'figcaption',
                    ['class' => 'embed__caption'],
                    $node->getCaption(),
                );
            }

            return new HtmlElement(
                'figure',
                ['class' => 'embed'],
                "\n" . implode("\n", $elements) . "\n",
            );
        }

        return $this->renderFallback($node);
    }

    /**
     * Render the default fallback: a linked URL with optional caption.
     */
    private function renderFallback(EmbedBlock $node): HtmlElement
    {
        $link = new HtmlElement(
            'a',
            ['href' => $node->url, 'class' => 'embed__link'],
            $node->url,
        );

        $elements = [(string) $link];

        if ($node->getCaption() !== '') {
            $elements[] = (string) new HtmlElement(
                'figcaption',
                ['class' => 'embed__caption'],
                $node->getCaption(),
            );
        }

        return new HtmlElement(
            'figure',
            ['class' => 'embed'],
            "\n" . implode("\n", $elements) . "\n",
        );
    }
}

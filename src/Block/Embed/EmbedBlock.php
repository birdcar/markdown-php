<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Embed;

use League\CommonMark\Node\Block\AbstractBlock;

/**
 * AST node representing an @embed directive block.
 *
 * Embeds are leaf blocks that reference an external URL with an optional
 * caption collected from the body text between @embed and @endembed.
 *
 * The URL is immutable once set at construction. The caption is populated
 * by the continue parser as body lines are accumulated, then finalized
 * when the block is closed.
 */
final class EmbedBlock extends AbstractBlock
{
    private string $caption;

    /**
     * @param string $url     The URL to embed (must be an http or https URL).
     * @param string $caption Optional caption text collected from the directive body.
     */
    public function __construct(
        public readonly string $url,
        string $caption = '',
    ) {
        parent::__construct();
        $this->caption = $caption;
    }

    public function getCaption(): string
    {
        return $this->caption;
    }

    /**
     * Set the caption text (called by the continue parser during closeBlock).
     */
    public function setCaption(string $caption): void
    {
        $this->caption = $caption;
    }
}

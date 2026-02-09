<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\TaskModifier;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * Renders a {@see TaskModifier} as an inline span with data attributes.
 *
 * Key-value output:
 * ```html
 * <span class="task-mod task-mod--due" data-key="due" data-value="2024-01-15">//due:2024-01-15</span>
 * ```
 *
 * Boolean flag output:
 * ```html
 * <span class="task-mod task-mod--pinned" data-key="pinned">//pinned</span>
 * ```
 */
final class TaskModifierRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string|null
    {
        TaskModifier::assertInstanceOf($node);
        \assert($node instanceof TaskModifier);

        $key = $node->key;
        $value = $node->value;

        $attributes = [
            'class' => "task-mod task-mod--{$key}",
            'data-key' => $key,
        ];

        if ($value !== null) {
            $attributes['data-value'] = $value;
            $content = "//{$key}:{$value}";
        } else {
            $content = "//{$key}";
        }

        return new HtmlElement('span', $attributes, $content);
    }
}

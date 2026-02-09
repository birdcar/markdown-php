<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\Task;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * Renders a {@see TaskMarker} node as an HTML span element with an icon.
 *
 * Output example:
 * ```html
 * <span class="task-marker task-marker--done" title="Done" data-state="done">
 *   <span class="task-marker__icon">&#x2713;</span>
 * </span>
 * ```
 */
final class TaskMarkerRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string|null
    {
        TaskMarker::assertInstanceOf($node);
        \assert($node instanceof TaskMarker);

        $state = $node->state;
        $cssClass = $state->cssClass();

        $icon = new HtmlElement('span', ['class' => 'task-marker__icon'], $state->icon());

        return new HtmlElement('span', [
            'class' => "task-marker task-marker--{$cssClass}",
            'title' => $state->label(),
            'data-state' => $cssClass,
        ], (string) $icon);
    }
}

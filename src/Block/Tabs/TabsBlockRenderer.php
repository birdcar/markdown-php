<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Tabs;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class TabsBlockRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): \Stringable|string
    {
        TabsBlock::assertInstanceOf($node);
        assert($node instanceof TabsBlock);

        $attrs = ['class' => 'tabs'];
        if ($node->syncId !== '') {
            $attrs['data-sync-id'] = $node->syncId;
        }

        // Collect tab metadata for nav buttons
        $tabs = [];
        $hasActive = false;
        foreach ($node->children() as $child) {
            if ($child instanceof TabBlock) {
                $tabs[] = $child;
                if ($child->active) {
                    $hasActive = true;
                }
            }
        }

        // Build nav buttons
        $buttons = [];
        foreach ($tabs as $i => $tab) {
            $isActive = $tab->active || (!$hasActive && $i === 0);
            $btnAttrs = [
                'class' => 'tabs__tab' . ($isActive ? ' tabs__tab--active' : ''),
                'role' => 'tab',
                'aria-selected' => $isActive ? 'true' : 'false',
            ];
            $buttons[] = (string) new HtmlElement('button', $btnAttrs, $tab->label);
        }

        $nav = new HtmlElement(
            'div',
            ['class' => 'tabs__nav', 'role' => 'tablist'],
            "\n" . implode("\n", $buttons) . "\n",
        );

        $panels = $childRenderer->renderNodes($node->children());

        return new HtmlElement('div', $attrs, "\n" . $nav . "\n" . $panels . "\n");
    }
}

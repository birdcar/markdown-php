<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\Task;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Node\Block\Paragraph;

/**
 * Post-parse processor that decorates ListItem nodes containing a TaskMarker.
 *
 * After the document is fully parsed, this listener walks the AST and finds
 * every ListItem whose first Paragraph starts with a TaskMarker. It then
 * sets `data-task` and `class` attributes on the ListItem so downstream
 * renderers (or the Attributes extension) can style task items.
 */
final class TaskListItemProcessor
{
    public function __invoke(DocumentParsedEvent $event): void
    {
        $document = $event->getDocument();
        $walker = $document->walker();

        while ($walkerEvent = $walker->next()) {
            $node = $walkerEvent->getNode();

            if (! $walkerEvent->isEntering() || ! $node instanceof ListItem) {
                continue;
            }

            $firstChild = $node->firstChild();
            if (! $firstChild instanceof Paragraph) {
                continue;
            }

            $firstInline = $firstChild->firstChild();
            if (! $firstInline instanceof TaskMarker) {
                continue;
            }

            $cssClass = $firstInline->state->cssClass();

            $node->data->set('attributes.data-task', $cssClass);
            $node->data->set('attributes.class', "task-item task-item--{$cssClass}");
        }
    }
}

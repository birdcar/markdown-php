<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Metadata;

use Birdcar\Markdown\Block\Frontmatter\FrontmatterBlock;
use Birdcar\Markdown\Contracts\ComputedFieldResolverInterface;
use Birdcar\Markdown\Inline\Hashtag\Hashtag;
use Birdcar\Markdown\Inline\Task\TaskMarker;
use Birdcar\Markdown\Inline\TaskModifier\TaskModifier;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;

final class MetadataExtractor
{
    /** @param ComputedFieldResolverInterface[] $resolvers */
    public function __construct(
        private readonly int $wordsPerMinute = 200,
        private readonly array $resolvers = [],
    ) {
    }

    public function extract(Document $document): DocumentMetadata
    {
        $frontmatter = $this->extractFrontmatter($document);
        $wordCount = $this->computeWordCount($document);
        $readingTime = $wordCount > 0 ? (int) ceil($wordCount / $this->wordsPerMinute) : 0;
        $tasks = $this->extractTasks($document);
        $tags = $this->extractTags($document, $frontmatter);
        $links = $this->extractLinks($document);

        $builtins = [
            'wordCount' => $wordCount,
            'readingTime' => $readingTime,
            'tasks' => $tasks,
            'tags' => $tags,
            'links' => $links,
        ];

        $custom = [];
        foreach ($this->resolvers as $resolver) {
            $custom = array_merge($custom, $resolver->resolve($document, $frontmatter, $builtins));
        }

        return new DocumentMetadata(
            frontmatter: $frontmatter,
            computed: $builtins,
            custom: $custom,
        );
    }

    /** @return array<string, mixed> */
    private function extractFrontmatter(Document $document): array
    {
        $walker = $document->walker();

        while ($event = $walker->next()) {
            $node = $event->getNode();
            if ($event->isEntering() && $node instanceof FrontmatterBlock) {
                return $node->getParsedData();
            }
        }

        return [];
    }

    private function computeWordCount(Document $document): int
    {
        $count = 0;
        $walker = $document->walker();

        while ($event = $walker->next()) {
            $node = $event->getNode();

            // Skip front-matter subtree
            if ($node instanceof FrontmatterBlock) {
                continue;
            }

            if (! $event->isEntering()) {
                continue;
            }

            if ($node instanceof Text) {
                $count += str_word_count($node->getLiteral());
            } elseif ($node instanceof Code) {
                $count += str_word_count($node->getLiteral());
            } elseif ($node instanceof FencedCode || $node instanceof IndentedCode) {
                $count += str_word_count($node->getLiteral());
            }
        }

        return $count;
    }

    private function extractTasks(Document $document): TaskCollection
    {
        $all = [];
        $grouped = [
            'open' => [],
            'done' => [],
            'scheduled' => [],
            'migrated' => [],
            'irrelevant' => [],
            'event' => [],
            'priority' => [],
        ];

        $walker = $document->walker();

        while ($event = $walker->next()) {
            $node = $event->getNode();

            if (! $event->isEntering() || ! ($node instanceof ListItem)) {
                continue;
            }

            // Walk children of this list item looking for TaskMarker
            $taskMarker = null;
            $modifiers = [];
            $textParts = [];
            $itemWalker = $node->walker();

            while ($itemEvent = $itemWalker->next()) {
                $child = $itemEvent->getNode();

                if (! $itemEvent->isEntering()) {
                    continue;
                }

                if ($child instanceof TaskMarker) {
                    $taskMarker = $child;
                } elseif ($child instanceof TaskModifier) {
                    $modifiers[] = [
                        'key' => $child->key,
                        'value' => $child->value,
                    ];
                } elseif ($child instanceof Text) {
                    $textParts[] = $child->getLiteral();
                }
            }

            if ($taskMarker === null) {
                continue;
            }

            $state = $taskMarker->state->cssClass();
            $text = trim(implode('', $textParts));
            $line = $node->getStartLine();

            $task = new ExtractedTask(
                text: $text,
                state: $state,
                modifiers: $modifiers,
                line: $line,
            );

            $all[] = $task;
            if (isset($grouped[$state])) {
                $grouped[$state][] = $task;
            }
        }

        return new TaskCollection(
            all: $all,
            open: $grouped['open'],
            done: $grouped['done'],
            scheduled: $grouped['scheduled'],
            migrated: $grouped['migrated'],
            irrelevant: $grouped['irrelevant'],
            event: $grouped['event'],
            priority: $grouped['priority'],
        );
    }

    /**
     * @param array<string, mixed> $frontmatter
     * @return string[]
     */
    private function extractTags(Document $document, array $frontmatter): array
    {
        $tags = [];

        // Tags from front-matter
        if (isset($frontmatter['tags']) && is_array($frontmatter['tags'])) {
            foreach ($frontmatter['tags'] as $tag) {
                if (is_string($tag)) {
                    $tags[] = strtolower($tag);
                }
            }
        }

        // Tags from inline hashtags
        $walker = $document->walker();
        while ($event = $walker->next()) {
            $node = $event->getNode();
            if ($event->isEntering() && $node instanceof Hashtag) {
                $tags[] = strtolower($node->getIdentifier());
            }
        }

        return array_values(array_unique($tags));
    }

    /** @return LinkReference[] */
    private function extractLinks(Document $document): array
    {
        $links = [];
        $walker = $document->walker();

        while ($event = $walker->next()) {
            $node = $event->getNode();

            if (! $event->isEntering()) {
                continue;
            }

            if ($node instanceof Link) {
                $links[] = new LinkReference(
                    url: $node->getUrl(),
                    title: $node->getTitle() !== '' ? $node->getTitle() : null,
                    line: $this->getNodeLine($node),
                );
            } elseif ($node instanceof Image) {
                $links[] = new LinkReference(
                    url: $node->getUrl(),
                    title: $node->getTitle() !== '' ? $node->getTitle() : null,
                    line: $this->getNodeLine($node),
                );
            }
        }

        return $links;
    }

    private function getNodeLine(Node $node): ?int
    {
        // Walk up to find the nearest block parent with line info
        $current = $node;
        while ($current !== null) {
            if ($current instanceof AbstractBlock) {
                return $current->getStartLine();
            }
            $current = $current->parent();
        }

        return null;
    }
}

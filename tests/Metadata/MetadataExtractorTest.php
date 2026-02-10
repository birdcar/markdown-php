<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests\Metadata;

use Birdcar\Markdown\Contracts\ComputedFieldResolverInterface;
use Birdcar\Markdown\Environment\BfmEnvironmentFactory;
use Birdcar\Markdown\Environment\RenderProfile;
use Birdcar\Markdown\Metadata\MetadataExtractor;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Parser\MarkdownParser;
use PHPUnit\Framework\TestCase;

class MetadataExtractorTest extends TestCase
{
    private function parse(string $markdown): Document
    {
        $env = BfmEnvironmentFactory::create(RenderProfile::Html);
        $parser = new MarkdownParser($env);

        return $parser->parse($markdown);
    }

    // --- Word count ---

    public function testWordCount(): void
    {
        $doc = $this->parse("Hello world, this is four words more.\n");
        $meta = (new MetadataExtractor())->extract($doc);

        $this->assertSame(7, $meta->computed['wordCount']);
    }

    public function testWordCountExcludesFrontmatter(): void
    {
        $doc = $this->parse("---\ntitle: Test\n---\n\nTwo words.\n");
        $meta = (new MetadataExtractor())->extract($doc);

        $this->assertSame(2, $meta->computed['wordCount']);
    }

    public function testWordCountEmptyDocument(): void
    {
        $doc = $this->parse('');
        $meta = (new MetadataExtractor())->extract($doc);

        $this->assertSame(0, $meta->computed['wordCount']);
    }

    // --- Reading time ---

    public function testReadingTimeDefault(): void
    {
        $words = implode(' ', array_fill(0, 200, 'word'));
        $doc = $this->parse($words . "\n");
        $meta = (new MetadataExtractor())->extract($doc);

        $this->assertSame(1, $meta->computed['readingTime']);
    }

    public function testReadingTimeCustomWpm(): void
    {
        $words = implode(' ', array_fill(0, 100, 'word'));
        $doc = $this->parse($words . "\n");
        $meta = (new MetadataExtractor(wordsPerMinute: 100))->extract($doc);

        $this->assertSame(1, $meta->computed['readingTime']);
    }

    // --- Tasks ---

    public function testExtractsTasksByState(): void
    {
        $md = "- [ ] Open task\n- [x] Done task\n- [>] Scheduled task\n- [!] Priority task\n";
        $doc = $this->parse($md);
        $meta = (new MetadataExtractor())->extract($doc);

        /** @var \Birdcar\Markdown\Metadata\TaskCollection $tasks */
        $tasks = $meta->computed['tasks'];

        $this->assertCount(4, $tasks->all);
        $this->assertCount(1, $tasks->open);
        $this->assertCount(1, $tasks->done);
        $this->assertCount(1, $tasks->scheduled);
        $this->assertCount(1, $tasks->priority);
    }

    public function testTaskModifiers(): void
    {
        $doc = $this->parse("- [>] Meeting //due:2025-03-01 //hard\n");
        $meta = (new MetadataExtractor())->extract($doc);

        /** @var \Birdcar\Markdown\Metadata\TaskCollection $tasks */
        $tasks = $meta->computed['tasks'];

        $this->assertCount(1, $tasks->scheduled);
        $task = $tasks->scheduled[0];
        $this->assertCount(2, $task->modifiers);
        $this->assertSame('due', $task->modifiers[0]['key']);
        $this->assertSame('2025-03-01', $task->modifiers[0]['value']);
        $this->assertSame('hard', $task->modifiers[1]['key']);
        $this->assertNull($task->modifiers[1]['value']);
    }

    public function testNoTasksReturnsEmptyCollection(): void
    {
        $doc = $this->parse("Just a paragraph.\n");
        $meta = (new MetadataExtractor())->extract($doc);

        /** @var \Birdcar\Markdown\Metadata\TaskCollection $tasks */
        $tasks = $meta->computed['tasks'];

        $this->assertCount(0, $tasks->all);
    }

    // --- Tags ---

    public function testTagsFromFrontmatter(): void
    {
        $doc = $this->parse("---\ntags:\n  - bfm\n  - markdown\n---\n\nContent.\n");
        $meta = (new MetadataExtractor())->extract($doc);

        $this->assertSame(['bfm', 'markdown'], $meta->computed['tags']);
    }

    public function testTagsFromInlineHashtags(): void
    {
        $doc = $this->parse("Discussion about #typescript and #react.\n");
        $meta = (new MetadataExtractor())->extract($doc);

        $this->assertSame(['typescript', 'react'], $meta->computed['tags']);
    }

    public function testTagsDeduplication(): void
    {
        $doc = $this->parse("---\ntags:\n  - bfm\n  - markdown\n---\n\nAbout #bfm and #typescript.\n");
        $meta = (new MetadataExtractor())->extract($doc);

        $this->assertSame(['bfm', 'markdown', 'typescript'], $meta->computed['tags']);
    }

    public function testTagsNormalization(): void
    {
        $doc = $this->parse("---\ntags:\n  - BFM\n---\n\nAbout #TypeScript.\n");
        $meta = (new MetadataExtractor())->extract($doc);

        $this->assertSame(['bfm', 'typescript'], $meta->computed['tags']);
    }

    // --- Links ---

    public function testExtractsLinks(): void
    {
        $doc = $this->parse("See [example](https://example.com \"Title\") for more.\n");
        $meta = (new MetadataExtractor())->extract($doc);

        /** @var \Birdcar\Markdown\Metadata\LinkReference[] $links */
        $links = $meta->computed['links'];

        $this->assertCount(1, $links);
        $this->assertSame('https://example.com', $links[0]->url);
        $this->assertSame('Title', $links[0]->title);
    }

    public function testExtractsImageLinks(): void
    {
        $doc = $this->parse("![alt](https://img.example.com/photo.jpg)\n");
        $meta = (new MetadataExtractor())->extract($doc);

        /** @var \Birdcar\Markdown\Metadata\LinkReference[] $links */
        $links = $meta->computed['links'];

        $this->assertCount(1, $links);
        $this->assertSame('https://img.example.com/photo.jpg', $links[0]->url);
    }

    // --- Frontmatter ---

    public function testFrontmatterExtraction(): void
    {
        $doc = $this->parse("---\ntitle: Test\ncount: 42\n---\n\nContent.\n");
        $meta = (new MetadataExtractor())->extract($doc);

        $this->assertSame('Test', $meta->frontmatter['title']);
        $this->assertSame(42, $meta->frontmatter['count']);
    }

    public function testNoFrontmatterReturnsEmpty(): void
    {
        $doc = $this->parse("Just content.\n");
        $meta = (new MetadataExtractor())->extract($doc);

        $this->assertSame([], $meta->frontmatter);
    }

    // --- Custom resolvers ---

    public function testCustomResolver(): void
    {
        $resolver = new class implements ComputedFieldResolverInterface {
            public function resolve(Document $document, array $frontmatter, array $builtins): array
            {
                return ['doubleWordCount' => $builtins['wordCount'] * 2];
            }
        };

        $doc = $this->parse("Hello world.\n");
        $meta = (new MetadataExtractor(resolvers: [$resolver]))->extract($doc);

        $this->assertSame(4, $meta->custom['doubleWordCount']);
    }
}

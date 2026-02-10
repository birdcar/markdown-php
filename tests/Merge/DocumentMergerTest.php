<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests\Merge;

use Birdcar\Markdown\Merge\BfmDocument;
use Birdcar\Markdown\Merge\DocumentMerger;
use Birdcar\Markdown\Merge\MergeConflictException;
use Birdcar\Markdown\Merge\MergeOptions;
use Birdcar\Markdown\Merge\MergeStrategy;
use PHPUnit\Framework\TestCase;

class DocumentMergerTest extends TestCase
{
    private DocumentMerger $merger;

    protected function setUp(): void
    {
        $this->merger = new DocumentMerger();
    }

    public function testMergesNonOverlappingKeys(): void
    {
        $a = new BfmDocument(frontmatter: ['key1' => 'value1'], body: 'Body A');
        $b = new BfmDocument(frontmatter: ['keyA' => 'valueB'], body: 'Body B');

        $result = $this->merger->merge([$a, $b]);

        $this->assertSame(['key1' => 'value1', 'keyA' => 'valueB'], $result->frontmatter);
        $this->assertSame("Body A\n\nBody B", $result->body);
    }

    public function testConcatenatesArrays(): void
    {
        $a = new BfmDocument(frontmatter: ['tags' => ['a1', 'a2']], body: '');
        $b = new BfmDocument(frontmatter: ['tags' => ['b1', 'b2']], body: '');

        $result = $this->merger->merge([$a, $b]);

        $this->assertSame(['a1', 'a2', 'b1', 'b2'], $result->frontmatter['tags']);
    }

    public function testDeepMergesNestedObjects(): void
    {
        $a = new BfmDocument(frontmatter: ['author' => ['name' => 'Nick', 'role' => 'dev']], body: '');
        $b = new BfmDocument(frontmatter: ['author' => ['email' => 'nick@birdcar.dev']], body: '');

        $result = $this->merger->merge([$a, $b]);

        $this->assertSame([
            'name' => 'Nick',
            'role' => 'dev',
            'email' => 'nick@birdcar.dev',
        ], $result->frontmatter['author']);
    }

    public function testLastWinsForScalarConflicts(): void
    {
        $a = new BfmDocument(frontmatter: ['title' => 'A'], body: '');
        $b = new BfmDocument(frontmatter: ['title' => 'B'], body: '');

        $result = $this->merger->merge([$a, $b]);

        $this->assertSame('B', $result->frontmatter['title']);
    }

    public function testFirstWinsStrategy(): void
    {
        $a = new BfmDocument(frontmatter: ['title' => 'A'], body: '');
        $b = new BfmDocument(frontmatter: ['title' => 'B'], body: '');

        $result = $this->merger->merge([$a, $b], new MergeOptions(strategy: MergeStrategy::FirstWins));

        $this->assertSame('A', $result->frontmatter['title']);
    }

    public function testErrorStrategyThrows(): void
    {
        $a = new BfmDocument(frontmatter: ['title' => 'A'], body: '');
        $b = new BfmDocument(frontmatter: ['title' => 'B'], body: '');

        $this->expectException(MergeConflictException::class);
        $this->expectExceptionMessage('Merge conflict');

        $this->merger->merge([$a, $b], new MergeOptions(strategy: MergeStrategy::Error));
    }

    public function testCustomResolver(): void
    {
        $a = new BfmDocument(frontmatter: ['count' => 1], body: '');
        $b = new BfmDocument(frontmatter: ['count' => 2], body: '');

        $options = new MergeOptions(
            strategy: fn (string $key, mixed $existing, mixed $incoming): mixed => $existing + $incoming,
        );

        $result = $this->merger->merge([$a, $b], $options);

        $this->assertSame(3, $result->frontmatter['count']);
    }

    public function testMergesThreeDocuments(): void
    {
        $docs = [
            new BfmDocument(frontmatter: ['tags' => ['a']], body: 'A'),
            new BfmDocument(frontmatter: ['tags' => ['b']], body: 'B'),
            new BfmDocument(frontmatter: ['tags' => ['c']], body: 'C'),
        ];

        $result = $this->merger->merge($docs);

        $this->assertSame(['a', 'b', 'c'], $result->frontmatter['tags']);
        $this->assertSame("A\n\nB\n\nC", $result->body);
    }

    public function testEmptyDocumentsArray(): void
    {
        $result = $this->merger->merge([]);

        $this->assertSame([], $result->frontmatter);
        $this->assertSame('', $result->body);
    }

    public function testCustomSeparator(): void
    {
        $a = new BfmDocument(frontmatter: [], body: 'A');
        $b = new BfmDocument(frontmatter: [], body: 'B');

        $result = $this->merger->merge([$a, $b], new MergeOptions(separator: "\n---\n"));

        $this->assertSame("A\n---\nB", $result->body);
    }
}

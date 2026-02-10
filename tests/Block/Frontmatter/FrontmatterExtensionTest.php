<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests\Block\Frontmatter;

use Birdcar\Markdown\Block\Frontmatter\FrontmatterBlock;
use Birdcar\Markdown\Environment\BfmEnvironmentFactory;
use Birdcar\Markdown\Environment\RenderProfile;
use Birdcar\Markdown\Tests\FixtureTestCase;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Parser\MarkdownParser;

class FrontmatterExtensionTest extends FixtureTestCase
{
    private function parseDocument(string $markdown): \League\CommonMark\Node\Block\Document
    {
        $env = BfmEnvironmentFactory::create(RenderProfile::Html);
        $parser = new MarkdownParser($env);

        return $parser->parse($markdown);
    }

    private function findFrontmatter(\League\CommonMark\Node\Block\Document $document): ?FrontmatterBlock
    {
        $walker = $document->walker();
        while ($event = $walker->next()) {
            $node = $event->getNode();
            if ($event->isEntering() && $node instanceof FrontmatterBlock) {
                return $node;
            }
        }

        return null;
    }

    public function testBasicFrontmatter(): void
    {
        $doc = $this->parseDocument("---\ntitle: Hello World\nauthor: nick\ntags:\n  - bfm\n  - markdown\n---\n\nBody content.\n");
        $fm = $this->findFrontmatter($doc);

        $this->assertNotNull($fm);
        $this->assertSame('Hello World', $fm->getParsedData()['title']);
        $this->assertSame('nick', $fm->getParsedData()['author']);
        $this->assertSame(['bfm', 'markdown'], $fm->getParsedData()['tags']);
    }

    public function testEmptyFrontmatter(): void
    {
        $doc = $this->parseDocument("---\n---\n\nBody after empty front-matter.\n");
        $fm = $this->findFrontmatter($doc);

        $this->assertNotNull($fm);
        $this->assertSame([], $fm->getParsedData());
    }

    public function testComplexFrontmatter(): void
    {
        $md = "---\ntitle: Complex\ncount: 42\ndraft: true\nauthor:\n  name: Nick\n  email: nick@birdcar.dev\n---\n\nContent.\n";
        $doc = $this->parseDocument($md);
        $fm = $this->findFrontmatter($doc);

        $this->assertNotNull($fm);
        $this->assertSame(42, $fm->getParsedData()['count']);
        $this->assertTrue($fm->getParsedData()['draft']);
        $this->assertSame(['name' => 'Nick', 'email' => 'nick@birdcar.dev'], $fm->getParsedData()['author']);
    }

    public function testThematicBreakNotFrontmatter(): void
    {
        $doc = $this->parseDocument("Some text\n\n---\n");
        $fm = $this->findFrontmatter($doc);

        $this->assertNull($fm);
    }

    public function testNoFrontmatter(): void
    {
        $doc = $this->parseDocument("Just a paragraph.\n");
        $fm = $this->findFrontmatter($doc);

        $this->assertNull($fm);
    }

    public function testFrontmatterRendersNothing(): void
    {
        $html = $this->convertToHtml("---\ntitle: Test\n---\n\nVisible content.\n");

        $this->assertStringNotContainsString('title', $html);
        $this->assertStringContainsString('Visible content', $html);
    }
}

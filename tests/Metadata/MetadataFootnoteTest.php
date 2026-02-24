<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Tests\Metadata;

use Birdcar\Markdown\Environment\BfmEnvironmentFactory;
use Birdcar\Markdown\Environment\RenderProfile;
use Birdcar\Markdown\Metadata\MetadataExtractor;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Parser\MarkdownParser;
use PHPUnit\Framework\TestCase;

class MetadataFootnoteTest extends TestCase
{
    private function parse(string $markdown): Document
    {
        $env = BfmEnvironmentFactory::create(RenderProfile::Html);
        $parser = new MarkdownParser($env);

        return $parser->parse($markdown);
    }

    public function testExtractsFootnoteLabels(): void
    {
        $doc = $this->parse("Text[^first] and more[^second].\n\n[^first]: First note.\n[^second]: Second note.");
        $meta = (new MetadataExtractor())->extract($doc);

        $this->assertSame(['first', 'second'], $meta->computed['footnotes']);
    }

    public function testNoFootnotesReturnsEmpty(): void
    {
        $doc = $this->parse("Just plain text.\n");
        $meta = (new MetadataExtractor())->extract($doc);

        $this->assertSame([], $meta->computed['footnotes']);
    }

    public function testDeduplicatesFootnoteLabels(): void
    {
        $doc = $this->parse("First[^a] and second[^a] use same label.\n\n[^a]: Shared note.");
        $meta = (new MetadataExtractor())->extract($doc);

        $this->assertSame(['a'], $meta->computed['footnotes']);
    }
}

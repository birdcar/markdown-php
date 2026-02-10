<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Frontmatter;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;
use Symfony\Component\Yaml\Yaml;

final class FrontmatterBlockContinueParser extends AbstractBlockContinueParser
{
    private FrontmatterBlock $block;

    /** @var string[] */
    private array $lines = [];

    public function __construct()
    {
        $this->block = new FrontmatterBlock();
    }

    public function getBlock(): AbstractBlock
    {
        return $this->block;
    }

    public function isContainer(): bool
    {
        return false;
    }

    public function canContain(AbstractBlock $childBlock): bool
    {
        return false;
    }

    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
    {
        $line = $cursor->getRemainder();

        if (trim($line) === '---') {
            $cursor->advanceToEnd();

            return BlockContinue::finished();
        }

        $this->lines[] = $line;
        $cursor->advanceToEnd();

        return BlockContinue::at($cursor);
    }

    public function closeBlock(): void
    {
        $raw = implode("\n", $this->lines);
        $this->block->setRawYaml($raw);

        $parsed = Yaml::parse($raw);
        $this->block->setParsedData(is_array($parsed) ? $parsed : []);
    }
}

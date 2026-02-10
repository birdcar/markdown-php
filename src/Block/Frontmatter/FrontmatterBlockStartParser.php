<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Frontmatter;

use League\CommonMark\Parser\Block\BlockStart;
use League\CommonMark\Parser\Block\BlockStartParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;

final class FrontmatterBlockStartParser implements BlockStartParserInterface
{
    public function tryStart(Cursor $cursor, MarkdownParserStateInterface $parserState): ?BlockStart
    {
        // Front-matter can only appear as the very first block in the document
        $container = $parserState->getActiveBlockParser()->getBlock();
        if ($container->hasChildren()) {
            return BlockStart::none();
        }

        if ($cursor->isIndented()) {
            return BlockStart::none();
        }

        $line = $cursor->getRemainder();
        if (trim($line) !== '---') {
            return BlockStart::none();
        }

        $cursor->advanceToEnd();

        return BlockStart::of(new FrontmatterBlockContinueParser())
            ->at($cursor);
    }
}

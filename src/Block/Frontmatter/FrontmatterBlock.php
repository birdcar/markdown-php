<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Frontmatter;

use League\CommonMark\Node\Block\AbstractBlock;

final class FrontmatterBlock extends AbstractBlock
{
    private string $rawYaml = '';

    /** @var array<string, mixed> */
    private array $parsedData = [];

    public function getRawYaml(): string
    {
        return $this->rawYaml;
    }

    public function setRawYaml(string $yaml): void
    {
        $this->rawYaml = $yaml;
    }

    /** @return array<string, mixed> */
    public function getParsedData(): array
    {
        return $this->parsedData;
    }

    /** @param array<string, mixed> $parsedData */
    public function setParsedData(array $parsedData): void
    {
        $this->parsedData = $parsedData;
    }
}

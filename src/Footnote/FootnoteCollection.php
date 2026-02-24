<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Footnote;

final class FootnoteCollection
{
    /** @var array<string, int> label → 1-based index */
    private array $refIndex = [];

    /** @var array<string, string> label → content */
    private array $definitions = [];

    private int $nextIndex = 1;

    public function addRef(string $label): int
    {
        if (!isset($this->refIndex[$label])) {
            $this->refIndex[$label] = $this->nextIndex++;
        }

        return $this->refIndex[$label];
    }

    public function addDefinition(string $label, string $content): void
    {
        $this->definitions[$label] = $content;
    }

    public function getIndex(string $label): ?int
    {
        return $this->refIndex[$label] ?? null;
    }

    public function getContent(string $label): ?string
    {
        return $this->definitions[$label] ?? null;
    }

    /** @return array<string, int> */
    public function getRefIndex(): array
    {
        return $this->refIndex;
    }

    public function hasFootnotes(): bool
    {
        return count($this->refIndex) > 0;
    }
}

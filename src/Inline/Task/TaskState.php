<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\Task;

enum TaskState: string
{
    case Open = ' ';
    case Done = 'x';
    case Scheduled = '>';
    case Migrated = '<';
    case Irrelevant = '-';
    case Event = 'o';
    case Priority = '!';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Done => 'Done',
            self::Scheduled => 'Scheduled',
            self::Migrated => 'Migrated',
            self::Irrelevant => 'Irrelevant',
            self::Event => 'Event',
            self::Priority => 'Priority',
        };
    }

    public function cssClass(): string
    {
        return strtolower($this->label());
    }

    public function icon(): string
    {
        return match ($this) {
            self::Open => "\u{25CB}",
            self::Done => "\u{2713}",
            self::Scheduled => "\u{25B7}",
            self::Migrated => "\u{25C1}",
            self::Irrelevant => "\u{2014}",
            self::Event => "\u{25C6}",
            self::Priority => "\u{203C}",
        };
    }

    public static function fromMarker(string $char): ?self
    {
        return self::tryFrom($char);
    }
}

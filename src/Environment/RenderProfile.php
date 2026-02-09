<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Environment;

enum RenderProfile: string
{
    case Html = 'html';
    case Email = 'email';
    case Plain = 'plain';
}

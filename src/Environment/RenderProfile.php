<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Environment;

enum RenderProfile
{
    case Html;
    case Email;
    case Plain;
}

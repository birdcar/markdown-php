<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Laravel\Tests;

use Birdcar\Markdown\Laravel\BfmServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [BfmServiceProvider::class];
    }
}

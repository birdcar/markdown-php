<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Block\Query;

use Birdcar\Markdown\Contracts\QueryResolverInterface;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

final class QueryExtension implements ExtensionInterface
{
    public function __construct(
        private readonly ?QueryResolverInterface $resolver = null,
    ) {
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addBlockStartParser(new QueryBlockStartParser(), 80);
        $environment->addRenderer(QueryBlock::class, new QueryBlockRenderer($this->resolver));
    }
}

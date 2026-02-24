<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Environment;

use Birdcar\Markdown\Block\Aside\AsideExtension;
use Birdcar\Markdown\Block\Callout\CalloutExtension;
use Birdcar\Markdown\Block\Details\DetailsExtension;
use Birdcar\Markdown\Block\Embed\EmbedExtension;
use Birdcar\Markdown\Block\Endnotes\EndnotesExtension;
use Birdcar\Markdown\Block\Figure\FigureExtension;
use Birdcar\Markdown\Block\Frontmatter\FrontmatterExtension;
use Birdcar\Markdown\Block\Include\IncludeExtension;
use Birdcar\Markdown\Block\Math\MathExtension;
use Birdcar\Markdown\Block\Query\QueryExtension;
use Birdcar\Markdown\Block\Tabs\TabsExtension;
use Birdcar\Markdown\Block\Toc\TocExtension;
use Birdcar\Markdown\Contracts\EmbedResolverInterface;
use Birdcar\Markdown\Contracts\IncludeResolverInterface;
use Birdcar\Markdown\Contracts\MentionResolverInterface;
use Birdcar\Markdown\Contracts\QueryResolverInterface;
use Birdcar\Markdown\Footnote\FootnoteExtension;
use Birdcar\Markdown\Inline\Hashtag\HashtagExtension;
use Birdcar\Markdown\Inline\Mention\MentionExtension;
use Birdcar\Markdown\Inline\Task\TaskExtension;
use Birdcar\Markdown\Inline\TaskModifier\TaskModifierExtension;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;

final class BfmEnvironmentFactory
{
    /** @param array<string, mixed> $config */
    public static function create(
        RenderProfile $profile = RenderProfile::Html,
        ?EmbedResolverInterface $embedResolver = null,
        ?MentionResolverInterface $mentionResolver = null,
        ?IncludeResolverInterface $includeResolver = null,
        ?QueryResolverInterface $queryResolver = null,
        array $config = [],
    ): Environment {
        $environment = new Environment($config);

        $environment->addExtension(new CommonMarkCoreExtension());

        // GFM extensions minus TaskList (BFM replaces it)
        $environment->addExtension(new AutolinkExtension());
        $environment->addExtension(new DisallowedRawHtmlExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new TableExtension());

        // BFM extensions — front-matter first to capture --- before thematic break
        $environment->addExtension(new FrontmatterExtension());
        $environment->addExtension(new TaskExtension());
        $environment->addExtension(new TaskModifierExtension());
        $environment->addExtension(new MentionExtension($mentionResolver));
        $environment->addExtension(new HashtagExtension());
        $environment->addExtension(new CalloutExtension($profile));
        $environment->addExtension(new EmbedExtension($embedResolver));

        // New directives
        $environment->addExtension(new DetailsExtension());
        $environment->addExtension(new AsideExtension());
        $environment->addExtension(new FigureExtension());
        $environment->addExtension(new TabsExtension());
        $environment->addExtension(new MathExtension());
        $environment->addExtension(new TocExtension());
        $environment->addExtension(new IncludeExtension($includeResolver));
        $environment->addExtension(new QueryExtension($queryResolver));
        $environment->addExtension(new EndnotesExtension());
        $environment->addExtension(new FootnoteExtension());

        return $environment;
    }
}

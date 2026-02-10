<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Environment;

use Birdcar\Markdown\Block\Callout\CalloutExtension;
use Birdcar\Markdown\Block\Embed\EmbedExtension;
use Birdcar\Markdown\Block\Frontmatter\FrontmatterExtension;
use Birdcar\Markdown\Contracts\EmbedResolverInterface;
use Birdcar\Markdown\Contracts\MentionResolverInterface;
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
    /**
     * @param array<string, mixed> $config
     */
    public static function create(
        RenderProfile $profile = RenderProfile::Html,
        ?EmbedResolverInterface $embedResolver = null,
        ?MentionResolverInterface $mentionResolver = null,
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

        return $environment;
    }
}

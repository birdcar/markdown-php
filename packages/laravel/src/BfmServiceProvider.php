<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Laravel;

use Birdcar\Markdown\Environment\BfmEnvironmentFactory;
use Birdcar\Markdown\Environment\RenderProfile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use League\CommonMark\MarkdownConverter;

final class BfmServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/bfm.php', 'bfm');

        $this->app->singleton(MarkdownConverter::class, function ($app) {
            /** @var array{profile: string, resolvers: array{mention: class-string|null, embed: class-string|null}} $config */
            $config = $app['config']['bfm'];

            $profile = RenderProfile::from($config['profile']);

            $mentionResolver = $config['resolvers']['mention']
                ? $app->make($config['resolvers']['mention'])
                : null;

            $embedResolver = $config['resolvers']['embed']
                ? $app->make($config['resolvers']['embed'])
                : null;

            return new MarkdownConverter(
                BfmEnvironmentFactory::create(
                    profile: $profile,
                    embedResolver: $embedResolver,
                    mentionResolver: $mentionResolver,
                )
            );
        });
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerMacros();
        $this->registerBladeDirectives();
    }

    private function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/bfm.php' => config_path('bfm.php'),
            ], 'bfm-config');

            $this->publishes([
                __DIR__ . '/../resources/css' => public_path('vendor/bfm'),
            ], 'bfm-assets');
        }
    }

    private function registerMacros(): void
    {
        Str::macro('bfm', function (string $markdown): string {
            /** @var MarkdownConverter $converter */
            $converter = app(MarkdownConverter::class);

            return (string) $converter->convert($markdown);
        });

        Str::macro('inlineBfm', function (string $markdown): string {
            /** @var MarkdownConverter $converter */
            $converter = app(MarkdownConverter::class);
            $html = (string) $converter->convert($markdown);

            return preg_replace('/^<p>(.*)<\/p>$/s', '$1', trim($html)) ?? $html;
        });
    }

    private function registerBladeDirectives(): void
    {
        Blade::directive('bfmStyles', function () {
            return "<?php echo \\Birdcar\\Markdown\\Laravel\\BfmServiceProvider::renderStyles(); ?>";
        });
    }

    public static function renderStyles(): string
    {
        $publishedPath = public_path('vendor/bfm/bfm.css');

        if (file_exists($publishedPath)) {
            return '<link rel="stylesheet" href="' . e(asset('vendor/bfm/bfm.css')) . '">';
        }

        $css = file_get_contents(__DIR__ . '/../resources/css/bfm.css');

        return '<style>' . $css . '</style>';
    }
}

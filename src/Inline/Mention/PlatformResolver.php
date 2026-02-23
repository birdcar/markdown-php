<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Inline\Mention;

final class PlatformResolver
{
    /** @var array<string, string> */
    private const PLATFORM_LABELS = [
        'github' => 'GitHub',
        'twitter' => 'Twitter',
        'bluesky' => 'Bluesky',
        'mastodon' => 'Mastodon',
        'npm' => 'npm',
        'linkedin' => 'LinkedIn',
    ];

    public static function resolve(string $platform, string $identifier): ?string
    {
        return match ($platform) {
            'github' => "https://github.com/{$identifier}",
            'twitter' => "https://twitter.com/{$identifier}",
            'bluesky' => "https://bsky.app/profile/{$identifier}",
            'mastodon' => self::resolveMastodon($identifier),
            'npm' => "https://www.npmjs.com/package/{$identifier}",
            'linkedin' => "https://www.linkedin.com/in/{$identifier}",
            default => null,
        };
    }

    public static function label(string $platform): ?string
    {
        return self::PLATFORM_LABELS[$platform] ?? null;
    }

    private static function resolveMastodon(string $identifier): string
    {
        // identifier is user@instance
        $atPos = strpos($identifier, '@');
        if ($atPos !== false) {
            $user = substr($identifier, 0, $atPos);
            $instance = substr($identifier, $atPos + 1);

            return "https://{$instance}/@{$user}";
        }

        // Fallback if no @ found
        return "https://mastodon.social/@{$identifier}";
    }
}

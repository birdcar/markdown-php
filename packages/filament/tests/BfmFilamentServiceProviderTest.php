<?php

declare(strict_types=1);

namespace Birdcar\Markdown\Filament\Tests;

use Filament\Support\Facades\FilamentAsset;

final class BfmFilamentServiceProviderTest extends TestCase
{
    public function test_views_are_loaded(): void
    {
        $this->assertTrue(
            view()->exists('bfm-filament::forms.components.bfm-editor'),
        );
    }

    public function test_bfm_styles_asset_is_registered(): void
    {
        $css = FilamentAsset::getStyleHref('bfm-styles', 'birdcar/markdown-filament');

        $this->assertNotNull($css);
    }
}

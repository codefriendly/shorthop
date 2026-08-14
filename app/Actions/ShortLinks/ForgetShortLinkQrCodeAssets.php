<?php

namespace App\Actions\ShortLinks;

use AshAllenDesign\ShortURL\Models\ShortURL;
use Illuminate\Support\Facades\Cache;

class ForgetShortLinkQrCodeAssets
{
    public function handle(ShortURL $shortURL): void
    {
        foreach (GenerateShortLinkQrCodeAsset::formats() as $format) {
            foreach (GenerateShortLinkQrCodeAsset::backgrounds() as $background) {
                Cache::forget(GenerateShortLinkQrCodeAsset::cacheKey($shortURL, $format, $background));
            }
        }
    }
}

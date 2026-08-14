<?php

namespace App\Http\Controllers;

use App\Actions\ShortLinks\GenerateShortLinkQrCodeAsset;
use AshAllenDesign\ShortURL\Models\ShortURL;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ShortLinkQrCodeController extends Controller
{
    public function show(ShortURL $shortURL, string $format, Request $request, GenerateShortLinkQrCodeAsset $generateQrCodeAsset): Response
    {
        return $this->response($shortURL, $format, $request, $generateQrCodeAsset);
    }

    public function download(ShortURL $shortURL, string $format, Request $request, GenerateShortLinkQrCodeAsset $generateQrCodeAsset): Response
    {
        return $this->response($shortURL, $format, $request, $generateQrCodeAsset, download: true);
    }

    private function response(ShortURL $shortURL, string $format, Request $request, GenerateShortLinkQrCodeAsset $generateQrCodeAsset, bool $download = false): Response
    {
        abort_unless(in_array($format, GenerateShortLinkQrCodeAsset::formats(), strict: true), 404);

        $background = $this->background($request);

        $headers = [
            'Content-Type' => $generateQrCodeAsset->mimeType($format),
            'Cache-Control' => 'private, no-cache',
        ];

        if ($download) {
            $headers['Content-Disposition'] = 'attachment; filename="'.GenerateShortLinkQrCodeAsset::filename($shortURL, $format, $background).'"';
        }

        return response($generateQrCodeAsset->handle($shortURL, $format, $background), headers: $headers);
    }

    private function background(Request $request): string
    {
        $background = $request->query('background', GenerateShortLinkQrCodeAsset::BACKGROUND_WHITE);

        return in_array($background, GenerateShortLinkQrCodeAsset::backgrounds(), strict: true)
            ? $background
            : GenerateShortLinkQrCodeAsset::BACKGROUND_WHITE;
    }
}

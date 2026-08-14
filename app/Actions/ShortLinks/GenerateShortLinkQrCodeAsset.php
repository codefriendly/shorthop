<?php

namespace App\Actions\ShortLinks;

use AshAllenDesign\ShortURL\Models\ShortURL;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WriterInterface;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class GenerateShortLinkQrCodeAsset
{
    public const FORMAT_PNG = 'png';

    public const FORMAT_SVG = 'svg';

    public const BACKGROUND_WHITE = 'white';

    public const BACKGROUND_TRANSPARENT = 'transparent';

    /**
     * @return list<string>
     */
    public static function formats(): array
    {
        return [self::FORMAT_PNG, self::FORMAT_SVG];
    }

    /**
     * @return list<string>
     */
    public static function backgrounds(): array
    {
        return [self::BACKGROUND_WHITE, self::BACKGROUND_TRANSPARENT];
    }

    public static function cacheKey(ShortURL $shortURL, string $format, string $background = self::BACKGROUND_WHITE): string
    {
        self::guardFormat($format);
        self::guardBackground($background);

        return "short-link-qr:{$shortURL->getKey()}:{$format}:{$background}";
    }

    public function handle(ShortURL $shortURL, string $format, string $background = self::BACKGROUND_WHITE): string
    {
        self::guardFormat($format);
        self::guardBackground($background);

        return base64_decode(
            Cache::rememberForever(
                self::cacheKey($shortURL, $format, $background),
                fn (): string => base64_encode($this->generate($shortURL, $format, $background)),
            ),
            strict: true,
        ) ?: '';
    }

    public function mimeType(string $format): string
    {
        return match ($format) {
            self::FORMAT_PNG => 'image/png',
            self::FORMAT_SVG => 'image/svg+xml',
            default => throw new InvalidArgumentException("Unsupported QR format [{$format}]."),
        };
    }

    public static function filename(ShortURL $shortURL, string $format, string $background = self::BACKGROUND_WHITE): string
    {
        self::guardFormat($format);
        self::guardBackground($background);

        return $shortURL->url_key.'.'.$format;
    }

    private function generate(ShortURL $shortURL, string $format, string $background): string
    {
        $writer = $this->writer($format);

        return (new Builder(
            writer: $writer,
            validateResult: false,
            data: url($shortURL->url_key),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 1024,
            margin: 48,
            backgroundColor: $this->backgroundColor($background),
            roundBlockSizeMode: $format === self::FORMAT_SVG
                ? RoundBlockSizeMode::None
                : RoundBlockSizeMode::Margin,
        ))->build()->getString();
    }

    private function backgroundColor(string $background): Color
    {
        return match ($background) {
            self::BACKGROUND_WHITE => new Color(255, 255, 255),
            self::BACKGROUND_TRANSPARENT => new Color(255, 255, 255, 127),
            default => throw new InvalidArgumentException("Unsupported QR background [{$background}]."),
        };
    }

    private function writer(string $format): WriterInterface
    {
        return match ($format) {
            self::FORMAT_PNG => new PngWriter,
            self::FORMAT_SVG => new SvgWriter,
            default => throw new InvalidArgumentException("Unsupported QR format [{$format}]."),
        };
    }

    private static function guardFormat(string $format): void
    {
        if (! in_array($format, self::formats(), strict: true)) {
            throw new InvalidArgumentException("Unsupported QR format [{$format}].");
        }
    }

    private static function guardBackground(string $background): void
    {
        if (! in_array($background, self::backgrounds(), strict: true)) {
            throw new InvalidArgumentException("Unsupported QR background [{$background}].");
        }
    }
}

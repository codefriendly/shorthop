<?php

use App\Actions\ShortLinks\GenerateShortLinkQrCodeAsset;
use App\Models\User;
use AshAllenDesign\ShortURL\Models\ShortURL;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('access app', 'web');

    Role::findOrCreate('admin', 'web')
        ->syncPermissions(['access app']);
});

it('serves cached SVG QR codes for app users', function () {
    $link = ShortURL::factory()->create([
        'url_key' => 'svg-code',
        'default_short_url' => url('svg-code'),
    ]);

    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get(route('short-links.qr.show', [$link, 'svg']))
        ->assertOk()
        ->assertHeader('content-type', 'image/svg+xml')
        ->assertSee('<svg', false);
});

it('serves transparent SVG QR codes when requested', function () {
    $link = ShortURL::factory()->create([
        'url_key' => 'transparent-svg-code',
        'default_short_url' => url('transparent-svg-code'),
    ]);

    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get(route('short-links.qr.show', [
            'shortURL' => $link,
            'format' => GenerateShortLinkQrCodeAsset::FORMAT_SVG,
            'background' => GenerateShortLinkQrCodeAsset::BACKGROUND_TRANSPARENT,
        ]))
        ->assertOk()
        ->assertHeader('content-type', 'image/svg+xml')
        ->assertSee('fill-opacity="0"', false);
});

it('serves cached PNG QR codes for app users', function () {
    $link = ShortURL::factory()->create([
        'url_key' => 'png-code',
        'default_short_url' => url('png-code'),
    ]);

    $user = User::factory()->create();
    $user->assignRole('admin');

    $response = $this->actingAs($user)
        ->get(route('short-links.qr.show', [$link, 'png']))
        ->assertOk()
        ->assertHeader('content-type', 'image/png')
        ->baseResponse;

    expect($response->getContent())->toStartWith("\x89PNG");
});

it('serves transparent PNG QR codes when requested', function () {
    $link = ShortURL::factory()->create([
        'url_key' => 'transparent-png-code',
        'default_short_url' => url('transparent-png-code'),
    ]);

    $user = User::factory()->create();
    $user->assignRole('admin');

    $response = $this->actingAs($user)
        ->get(route('short-links.qr.show', [
            'shortURL' => $link,
            'format' => GenerateShortLinkQrCodeAsset::FORMAT_PNG,
            'background' => GenerateShortLinkQrCodeAsset::BACKGROUND_TRANSPARENT,
        ]))
        ->assertOk()
        ->assertHeader('content-type', 'image/png')
        ->baseResponse;

    expect($response->getContent())->toStartWith("\x89PNG");
});

it('downloads QR assets using the short key as the filename', function (string $format, string $contentType) {
    $link = ShortURL::factory()->create([
        'url_key' => 'download-name',
        'default_short_url' => url('download-name'),
    ]);

    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get(route('short-links.qr.download', [$link, $format]))
        ->assertOk()
        ->assertHeader('content-type', $contentType)
        ->assertHeader('content-disposition', 'attachment; filename="download-name.'.$format.'"');
})->with([
    'png' => ['png', 'image/png'],
    'svg' => ['svg', 'image/svg+xml'],
]);

it('downloads transparent QR assets using the short key as the filename', function (string $format, string $contentType) {
    $link = ShortURL::factory()->create([
        'url_key' => 'transparent-download-name',
        'default_short_url' => url('transparent-download-name'),
    ]);

    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get(route('short-links.qr.download', [
            'shortURL' => $link,
            'format' => $format,
            'background' => GenerateShortLinkQrCodeAsset::BACKGROUND_TRANSPARENT,
        ]))
        ->assertOk()
        ->assertHeader('content-type', $contentType)
        ->assertHeader('content-disposition', 'attachment; filename="transparent-download-name.'.$format.'"');
})->with([
    'png' => ['png', 'image/png'],
    'svg' => ['svg', 'image/svg+xml'],
]);

it('downloads transparent PNG QR assets with transparent pixels', function () {
    $link = ShortURL::factory()->create([
        'url_key' => 'transparent-png-download',
        'default_short_url' => url('transparent-png-download'),
    ]);

    $user = User::factory()->create();
    $user->assignRole('admin');

    $content = $this->actingAs($user)
        ->get(route('short-links.qr.download', [
            'shortURL' => $link,
            'format' => GenerateShortLinkQrCodeAsset::FORMAT_PNG,
            'background' => GenerateShortLinkQrCodeAsset::BACKGROUND_TRANSPARENT,
        ]))
        ->assertOk()
        ->assertHeader('content-type', 'image/png')
        ->baseResponse
        ->getContent();

    $image = imagecreatefromstring($content);

    expect($image)->not->toBeFalse();

    $cornerPixel = imagecolorat($image, 0, 0);
    $cornerColor = imagecolorsforindex($image, $cornerPixel);

    imagedestroy($image);

    expect($cornerColor['alpha'])->toBe(127);
});

it('requires app access to view QR assets', function () {
    $link = ShortURL::factory()->create([
        'url_key' => 'private-qr',
        'default_short_url' => url('private-qr'),
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('short-links.qr.show', [$link, 'svg']))
        ->assertForbidden();
});

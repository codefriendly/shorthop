<?php

use AshAllenDesign\ShortURL\Models\ShortURL;
use Database\Seeders\ShortUrlSeeder;

it('seeds neutral example short links', function () {
    $this->seed(ShortUrlSeeder::class);

    expect(ShortURL::query()->count())->toBe(6);

    expect(ShortURL::query()->where('url_key', 'docs')->first())
        ->title->toBe('Documentation')
        ->destination_url->toBe('https://example.org')
        ->default_short_url->toBe(url('docs'))
        ->track_visits->toBeTrue()
        ->track_ip_address->toBeFalse()
        ->track_operating_system->toBeTrue()
        ->track_operating_system_version->toBeTrue()
        ->track_browser->toBeTrue()
        ->track_browser_version->toBeTrue()
        ->track_referer_url->toBeTrue()
        ->track_device_type->toBeTrue();
});

it('can run more than once without duplicating links', function () {
    $this->seed(ShortUrlSeeder::class);
    $this->seed(ShortUrlSeeder::class);

    expect(ShortURL::query()->count())->toBe(6);
});

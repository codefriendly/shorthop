<?php

use App\Models\User;
use AshAllenDesign\ShortURL\Models\ShortURL;
use AshAllenDesign\ShortURL\Models\ShortURLVisit;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('redirects root short links to their destination', function () {
    ShortURL::factory()->create([
        'url_key' => 'laravel',
        'destination_url' => 'https://laravel.com',
        'default_short_url' => url('laravel'),
        'single_use' => false,
        'track_visits' => false,
    ]);

    $this->get('/laravel')
        ->assertRedirect('https://laravel.com');
});

it('stores only the referrer origin and omits the visitor ip address', function () {
    $shortUrl = ShortURL::factory()->create([
        'url_key' => 'privacy',
        'destination_url' => 'https://example.com',
        'default_short_url' => url('privacy'),
        'single_use' => false,
        'track_visits' => true,
        'track_ip_address' => false,
        'track_operating_system' => false,
        'track_operating_system_version' => false,
        'track_browser' => false,
        'track_browser_version' => false,
        'track_referer_url' => true,
        'track_device_type' => false,
    ]);

    $this->withHeader('Referer', 'https://user:secret@source.example:8443/private/path?token=sensitive#fragment')
        ->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
        ->get('/privacy')
        ->assertRedirect('https://example.com');

    $visit = ShortURLVisit::query()->whereBelongsTo($shortUrl, 'shortURL')->sole();

    expect($visit)
        ->ip_address->toBeNull()
        ->referer_url->toBe('https://source.example:8443/')
        ->operating_system->toBeNull()
        ->operating_system_version->toBeNull()
        ->browser->toBeNull()
        ->browser_version->toBeNull()
        ->device_type->toBeNull();
});

it('discards referrers that do not have a valid http origin', function (string $referrer) {
    $shortUrl = ShortURL::factory()->create([
        'url_key' => 'privacy',
        'destination_url' => 'https://example.com',
        'default_short_url' => url('privacy'),
        'single_use' => false,
        'track_visits' => true,
        'track_referer_url' => true,
    ]);

    $this->withHeader('Referer', $referrer)
        ->get('/privacy')
        ->assertRedirect('https://example.com');

    expect(ShortURLVisit::query()->whereBelongsTo($shortUrl, 'shortURL')->sole())
        ->referer_url->toBeNull();
})->with([
    'unsupported scheme' => 'javascript:alert(1)',
    'missing scheme' => '//source.example/private',
    'malformed URL' => 'https://',
]);

it('does not register the package default short route', function () {
    ShortURL::factory()->create([
        'url_key' => 'laravel',
        'destination_url' => 'https://laravel.com',
        'default_short_url' => url('laravel'),
        'single_use' => false,
        'track_visits' => false,
    ]);

    $this->get('/short/laravel')
        ->assertNotFound();
});

it('does not clobber existing app routes', function () {
    Permission::findOrCreate('access app', 'web');

    Role::findOrCreate('admin', 'web')
        ->syncPermissions(['access app']);

    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user);

    $this->get('/app')
        ->assertOk()
        ->assertSee('Dashboard');
});

it('does not match multi-segment paths', function () {
    $this->get('/settings/profile')
        ->assertRedirect('/login');
});

it('rate limits sustained short link redirects by ip address', function () {
    ShortURL::factory()->create([
        'url_key' => 'laravel',
        'destination_url' => 'https://laravel.com',
        'default_short_url' => url('laravel'),
        'single_use' => false,
        'track_visits' => false,
    ]);

    for ($request = 1; $request <= 120; $request++) {
        $this->get('/laravel')
            ->assertRedirect('https://laravel.com');
    }

    $this->get('/laravel')
        ->assertTooManyRequests();

    $this->get(route('home'))
        ->assertOk();
});

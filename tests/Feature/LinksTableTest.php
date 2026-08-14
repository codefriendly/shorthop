<?php

use App\Actions\ShortLinks\ForgetShortLinkQrCodeAssets;
use App\Filament\Widgets\LinksTable;
use App\Models\User;
use AshAllenDesign\ShortURL\Models\ShortURL;
use AshAllenDesign\ShortURL\Models\ShortURLVisit;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ViewColumn;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('shows visit counts with a seven day sparkline', function () {
    $link = ShortURL::factory()->create([
        'url_key' => 'spark',
        'default_short_url' => url('spark'),
    ]);

    ShortURLVisit::factory()->count(2)->create([
        'short_url_id' => $link->id,
        'visited_at' => now()->subDays(2),
    ]);

    ShortURLVisit::factory()->create([
        'short_url_id' => $link->id,
        'visited_at' => now(),
    ]);

    Livewire::test(LinksTable::class)
        ->assertSee('3')
        ->assertSee('Visits over last 7 days', false);
});

it('shows a copy affordance next to each short link', function () {
    ShortURL::factory()->create([
        'url_key' => 'copy-me',
        'default_short_url' => url('copy-me'),
    ]);

    $columns = Livewire::test(LinksTable::class)
        ->instance()
        ->getTable()
        ->getColumns();

    $linkColumn = $columns['url_key'];

    expect($linkColumn)->toBeInstanceOf(ViewColumn::class)
        ->and($linkColumn->getView())->toBe('filament.tables.columns.short-link');

    Livewire::test(LinksTable::class)
        ->assertSee('/copy-me')
        ->assertSee('Copy link', false)
        ->assertSee(url('copy-me'), false);
});

it('can create a short link', function () {
    Livewire::test(LinksTable::class)
        ->callAction(TestAction::make(CreateAction::class)->table(), data: [
            'title' => 'Docs',
            'destination_url' => 'https://example.com/docs',
            'url_key' => 'docs',
            'redirect_status_code' => 302,
            'track_visits' => true,
            'forward_query_params' => false,
        ])
        ->assertHasNoFormErrors();

    assertDatabaseHas(ShortURL::class, [
        'title' => 'Docs',
        'destination_url' => 'https://example.com/docs',
        'url_key' => 'docs',
        'default_short_url' => url('docs'),
        'redirect_status_code' => 302,
        'track_visits' => true,
        'track_ip_address' => false,
        'track_operating_system' => true,
        'track_operating_system_version' => true,
        'track_browser' => true,
        'track_browser_version' => true,
        'track_referer_url' => true,
        'track_device_type' => true,
    ]);
});

it('can choose visitor details for a short link', function () {
    Livewire::test(LinksTable::class)
        ->callAction(TestAction::make(CreateAction::class)->table(), data: [
            'title' => 'Private',
            'destination_url' => 'https://example.com/private',
            'url_key' => 'private',
            'redirect_status_code' => 302,
            'track_visits' => true,
            'track_ip_address' => true,
            'track_operating_system' => false,
            'track_operating_system_version' => false,
            'track_browser' => false,
            'track_browser_version' => false,
            'track_referer_url' => false,
            'track_device_type' => false,
            'forward_query_params' => false,
        ])
        ->assertHasNoFormErrors();

    assertDatabaseHas(ShortURL::class, [
        'url_key' => 'private',
        'track_visits' => true,
        'track_ip_address' => true,
        'track_operating_system' => false,
        'track_operating_system_version' => false,
        'track_browser' => false,
        'track_browser_version' => false,
        'track_referer_url' => false,
        'track_device_type' => false,
    ]);
});

it('rejects reserved short keys', function () {
    Livewire::test(LinksTable::class)
        ->callAction(TestAction::make(CreateAction::class)->table(), data: [
            'title' => 'Reserved',
            'destination_url' => 'https://example.com/app',
            'url_key' => 'app',
            'redirect_status_code' => 302,
            'track_visits' => true,
            'forward_query_params' => false,
        ])
        ->assertHasFormErrors(['url_key']);
});

it('validates destination url when the create action field is updated', function () {
    Livewire::test(LinksTable::class)
        ->mountAction(TestAction::make(CreateAction::class)->table())
        ->set('mountedActions.0.data.destination_url', 'not-a-url')
        ->assertHasErrors(['mountedActions.0.data.destination_url' => 'url']);
});

it('validates required destination urls when the create action field is updated', function () {
    Livewire::test(LinksTable::class)
        ->mountAction(TestAction::make(CreateAction::class)->table())
        ->set('mountedActions.0.data.destination_url', '')
        ->assertHasErrors(['mountedActions.0.data.destination_url' => 'required']);
});

it('validates reserved short keys when the create action field is updated', function () {
    Livewire::test(LinksTable::class)
        ->mountAction(TestAction::make(CreateAction::class)->table())
        ->set('mountedActions.0.data.url_key', 'app')
        ->assertHasErrors(['mountedActions.0.data.url_key']);
});

it('can edit a short link', function () {
    $link = ShortURL::factory()->create([
        'destination_url' => 'https://example.com/old',
        'url_key' => 'old',
        'default_short_url' => url('old'),
    ]);

    Livewire::test(LinksTable::class)
        ->callAction(TestAction::make(EditAction::class)->table($link), data: [
            'title' => 'New title',
            'destination_url' => 'https://example.com/new',
            'url_key' => 'new',
            'redirect_status_code' => 301,
            'track_visits' => false,
            'forward_query_params' => true,
        ])
        ->assertHasNoFormErrors();

    expect($link->refresh())
        ->title->toBe('New title')
        ->destination_url->toBe('https://example.com/new')
        ->url_key->toBe('new')
        ->default_short_url->toBe(url('new'))
        ->redirect_status_code->toBe(301)
        ->track_visits->toBeFalse()
        ->forward_query_params->toBeTrue();
});

it('shows a friendly edit modal heading', function () {
    $link = ShortURL::factory()->create([
        'destination_url' => 'https://example.com/edit-heading',
        'url_key' => 'edit-heading',
        'default_short_url' => url('edit-heading'),
    ]);

    Livewire::test(LinksTable::class)
        ->mountAction(TestAction::make(EditAction::class)->table($link))
        ->assertMountedActionModalSee('Edit link')
        ->assertMountedActionModalDontSee('Short u r l');
});

it('normalizes edited destination urls to https when configured', function () {
    config(['short-url.enforce_https' => true]);

    $link = ShortURL::factory()->create([
        'destination_url' => 'https://example.com/old',
        'url_key' => 'secure-edit',
        'default_short_url' => url('secure-edit'),
    ]);

    Livewire::test(LinksTable::class)
        ->callAction(TestAction::make(EditAction::class)->table($link), data: [
            'title' => 'Secure edit',
            'destination_url' => 'http://example.com/new',
            'url_key' => 'secure-edit',
            'redirect_status_code' => 302,
            'track_visits' => true,
            'forward_query_params' => false,
        ])
        ->assertHasNoFormErrors();

    expect($link->refresh()->destination_url)->toBe('https://example.com/new');
});

it('busts QR code cache when a short link is edited', function () {
    $link = ShortURL::factory()->create([
        'destination_url' => 'https://example.com/old',
        'url_key' => 'qr-cache',
        'default_short_url' => url('qr-cache'),
    ]);

    $forgetQrCodeAssets = Mockery::mock(ForgetShortLinkQrCodeAssets::class);
    $forgetQrCodeAssets->shouldReceive('handle')
        ->once()
        ->with(Mockery::on(fn (ShortURL $record): bool => $record->is($link)));

    app()->instance(ForgetShortLinkQrCodeAssets::class, $forgetQrCodeAssets);

    Livewire::test(LinksTable::class)
        ->callAction(TestAction::make(EditAction::class)->table($link), data: [
            'title' => 'QR cache',
            'destination_url' => 'https://example.com/new',
            'url_key' => 'qr-cache',
            'redirect_status_code' => 302,
            'track_visits' => true,
            'forward_query_params' => false,
        ])
        ->assertHasNoFormErrors();
});

it('validates required short keys when the edit action field is updated', function () {
    $link = ShortURL::factory()->create([
        'destination_url' => 'https://example.com/edit',
        'url_key' => 'edit',
        'default_short_url' => url('edit'),
    ]);

    Livewire::test(LinksTable::class)
        ->mountAction(TestAction::make(EditAction::class)->table($link))
        ->set('mountedActions.0.data.url_key', '')
        ->assertHasErrors(['mountedActions.0.data.url_key' => 'required']);
});

it('can view a short link', function () {
    $link = ShortURL::factory()->create([
        'title' => 'View title',
        'destination_url' => 'https://example.com/view',
        'url_key' => 'view',
        'default_short_url' => url('view'),
        'redirect_status_code' => 302,
        'track_visits' => true,
        'forward_query_params' => false,
    ]);

    Livewire::test(LinksTable::class)
        ->mountAction(TestAction::make(ViewAction::class)->table($link))
        ->assertMountedActionModalDontSee('Link details')
        ->assertMountedActionModalDontSee('Title')
        ->assertMountedActionModalDontSee('Record')
        ->assertMountedActionModalDontSee('Short u r l')
        ->assertMountedActionModalDontSee('Save')
        ->assertMountedActionModalSee('View title')
        ->assertMountedActionModalSee(url('view'))
        ->assertMountedActionModalSee('https://example.com/view')
        ->assertMountedActionModalSee('aria-label="Copy short URL"', false)
        ->assertMountedActionModalSee('aria-label="Open destination URL"', false)
        ->assertMountedActionModalSee('href="https://example.com/view"', false)
        ->assertMountedActionModalSee('target="_blank"', false)
        ->assertMountedActionModalSee(route('short-links.qr.show', [$link, 'svg']), false)
        ->assertMountedActionModalSee('Transparent background')
        ->assertMountedActionModalSee('background=transparent', false)
        ->assertMountedActionModalSee(route('short-links.qr.download', [$link, 'png']), false)
        ->assertMountedActionModalSee(route('short-links.qr.download', [$link, 'svg']), false)
        ->assertMountedActionModalSee('download="view.png"', false)
        ->assertMountedActionModalSee('download="view.svg"', false)
        ->assertMountedActionModalSee('302 Temporary')
        ->assertMountedActionModalSee('Created')
        ->assertMountedActionModalSee('Updated')
        ->assertMountedActionModalSee('Track visits')
        ->assertMountedActionModalSee('Forward query parameters');
});

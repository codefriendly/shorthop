<?php

use App\Models\User;
use Database\Seeders\ShortUrlSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('access app', 'web');

    Role::findOrCreate('admin', 'web')
        ->syncPermissions(['access app']);

    Role::findOrCreate('operator', 'web')
        ->syncPermissions(['access app']);
});

test('the app dashboard uses the configured app name', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/app')
        ->assertOk()
        ->assertSee(config('app.name'));
});

test('the app dashboard lists short links', function () {
    $this->seed(ShortUrlSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user);

    $this->get('/app')
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Links')
        ->assertSee('Documentation')
        ->assertSee('/docs')
        ->assertSee('https://example.org')
        ->assertSee('Visits');
});

test('operators can access the app dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('operator');

    $this->actingAs($user)
        ->get('/app')
        ->assertOk();
});

test('the app dashboard links to self management pages from the user menu', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/app')
        ->assertOk()
        ->assertSee(route('profile.edit'), false)
        ->assertSee(route('security.edit'), false)
        ->assertSee(route('appearance.edit'), false);
});

test('users without app access permission cannot access the app dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get('/app')
        ->assertForbidden();
});

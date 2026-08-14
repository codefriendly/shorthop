<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('access app', 'web');

    Role::findOrCreate('admin', 'web')
        ->syncPermissions(['access app']);
});

it('shows the ui lab navigation item in local environments', function () {
    config(['app.env' => 'local']);

    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user);

    $this->get('/app')
        ->assertOk()
        ->assertSee('UI Lab');

    $this->get('/app/ui-lab')->assertOk();
});

it('does not expose the ui lab outside local environments', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user);

    $this->get('/app')
        ->assertOk()
        ->assertDontSee('UI Lab');

    $this->get('/app/ui-lab')->assertForbidden();
});

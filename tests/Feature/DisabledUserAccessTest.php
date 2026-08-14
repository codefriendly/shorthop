<?php

use App\Actions\Users\DisableUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Passkeys\Passkey;
use Laravel\Passkeys\Passkeys;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('access app', 'web');

    Role::findOrCreate('admin', 'web')
        ->syncPermissions(['access app']);
});

test('disabled users cannot authenticate', function () {
    $user = User::factory()->create([
        'disabled_at' => now(),
    ]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});

test('disabled users cannot access the app panel', function () {
    $user = User::factory()->create([
        'disabled_at' => now(),
    ]);
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/app')
        ->assertForbidden();
});

test('disabled users cannot authenticate with passkeys', function () {
    $user = User::factory()->create([
        'disabled_at' => now(),
    ]);

    /** @var Passkey $passkey */
    $passkey = $user->passkeys()->create([
        'name' => 'Security key',
        'credential_id' => 'disabled-user-credential',
        'credential' => [],
    ]);

    expect(Passkeys::allowsLogin(request(), $passkey))->toBeFalse();
});

test('enabled users can authenticate with passkeys', function () {
    $user = User::factory()->create();

    /** @var Passkey $passkey */
    $passkey = $user->passkeys()->create([
        'name' => 'Security key',
        'credential_id' => 'enabled-user-credential',
        'credential' => [],
    ]);

    expect(Passkeys::allowsLogin(request(), $passkey))->toBeTrue();
});

test('disabling a user invalidates existing remember me cookies', function () {
    $user = User::factory()->create([
        'remember_token' => 'known-remember-token',
    ]);
    $user->assignRole('admin');

    $guard = Auth::guard('web');
    $recaller = $user->getAuthIdentifier().'|'.$user->getRememberToken().'|'.$guard->hashPasswordForCookie($user->getAuthPassword());

    app(DisableUser::class)->handle($user, User::factory()->create());

    $this->withCookie($guard->getRecallerName(), $recaller)
        ->get('/app')
        ->assertRedirect(route('filament.app.auth.login'));

    expect($user->fresh()->remember_token)->toBeNull();

    $this->assertGuest();
});

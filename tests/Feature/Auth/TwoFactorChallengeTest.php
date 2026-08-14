<?php

use App\Actions\Users\DisableUser;
use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());
});

test('two factor challenge redirects to login when not authenticated', function () {
    $response = $this->get(route('two-factor.login'));

    $response->assertRedirect(route('login'));
});

test('two factor challenge can be rendered', function () {
    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('two-factor.login'));
});

test('disabled users cannot complete a pending two factor challenge', function () {
    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('two-factor.login'));

    $this->assertGuest();
    expect(session('login.id'))->toBe($user->getKey());

    app(DisableUser::class)->handle($user, User::factory()->create());

    $this->post(route('two-factor.login.store'), [
        'recovery_code' => 'recovery-code-1',
    ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email')
        ->assertSessionMissing('login.id')
        ->assertSessionMissing('login.remember');

    $this->assertGuest();
});

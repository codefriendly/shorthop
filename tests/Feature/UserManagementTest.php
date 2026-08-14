<?php

use App\Actions\Users\DisableUser;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    foreach (['access app', 'manage users', 'manage roles'] as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    Role::findOrCreate('admin', 'web')
        ->syncPermissions(['access app', 'manage users', 'manage roles']);

    Role::findOrCreate('operator', 'web')
        ->syncPermissions(['access app']);
});

test('admins can access user management', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(UserResource::getUrl())
        ->assertOk();
});

test('operators cannot access user management', function () {
    $operator = User::factory()->create();
    $operator->assignRole('operator');

    $this->actingAs($operator)
        ->get(UserResource::getUrl())
        ->assertForbidden();
});

test('admins can create users and assign roles', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $operatorRole = Role::findByName('operator', 'web');

    $this->actingAs($admin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Client Operator',
            'email' => 'operator@example.com',
            'password' => 'long-enough-password',
            'password_confirmation' => 'long-enough-password',
            'roles' => [$operatorRole->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect(UserResource::getUrl());

    $user = User::where('email', 'operator@example.com')->firstOrFail();

    expect($user->hasRole('operator'))->toBeTrue();

    assertDatabaseHas(User::class, [
        'name' => 'Client Operator',
        'email' => 'operator@example.com',
    ]);
});

test('user creation requires password confirmation', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Missing Confirmation',
            'email' => 'missing-confirmation@example.com',
            'password' => 'long-enough-password',
            'password_confirmation' => 'different-password',
        ])
        ->call('create')
        ->assertHasFormErrors(['password' => 'same'])
        ->assertNoRedirect();
});

test('admins can edit users and return to the user list', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();
    $oldPassword = $user->password;

    $this->actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm([
            'name' => 'Updated User',
            'email' => $user->email,
            'password' => 'new-long-enough-password',
            'password_confirmation' => 'new-long-enough-password',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertRedirect(UserResource::getUrl());

    expect($user->refresh())
        ->name->toBe('Updated User')
        ->password->not->toBe($oldPassword);

    expect(Hash::check('new-long-enough-password', $user->password))->toBeTrue();
});

test('admins can disable and enable another user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('operator');

    $this->actingAs($admin);

    Livewire::test(ListUsers::class)
        ->callTableAction('disable', $user);

    expect($user->refresh()->isDisabled())->toBeTrue();

    Livewire::test(ListUsers::class)
        ->callTableAction('enable', $user);

    expect($user->refresh()->isDisabled())->toBeFalse();
});

test('admins cannot change their own roles from user management', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $operatorRole = Role::findByName('operator', 'web');

    $this->actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $admin->id])
        ->fillForm([
            'roles' => [$operatorRole->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($admin->refresh())
        ->hasRole('admin')->toBeTrue()
        ->hasRole('operator')->toBeFalse();
});

test('users cannot disable themselves', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect(fn () => app(DisableUser::class)->handle($admin, $admin))
        ->toThrow(ValidationException::class);

    expect($admin->refresh()->isDisabled())->toBeFalse();
});

test('disabling a user invalidates database sessions', function () {
    config(['session.driver' => 'database']);

    $admin = User::factory()->create();
    $user = User::factory()->create();

    DB::table('sessions')->insert([
        'id' => 'session-to-delete',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => 'payload',
        'last_activity' => now()->timestamp,
    ]);

    app(DisableUser::class)->handle($user, $admin);

    expect(DB::table('sessions')->where('id', 'session-to-delete')->exists())->toBeFalse();
});

<?php

use App\Models\User;
use AshAllenDesign\ShortURL\Models\ShortURL;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DevelopmentSeeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    app()->detectEnvironment(fn (): string => 'testing');
});

test('the database seeder creates no development records locally', function () {
    app()->detectEnvironment(fn (): string => 'local');

    $this->seed(DatabaseSeeder::class);

    expect(User::where('email', 'admin@example.com')->exists())->toBeFalse();
    expect(ShortURL::query()->doesntExist())->toBeTrue();
    expect(Role::where('name', 'admin')->exists())->toBeTrue();
});

test('the development seeder creates a predictable administrator and sample links locally', function () {
    app()->detectEnvironment(fn (): string => 'local');

    $this->seed(DevelopmentSeeder::class);

    $user = User::where('email', 'admin@example.com')->firstOrFail();

    expect($user)
        ->name->toBe('Administrator')
        ->email_verified_at->not->toBeNull()
        ->hasRole('admin')->toBeTrue();

    expect(Hash::check('password', $user->password))->toBeTrue();
    expect(ShortURL::query()->count())->toBe(6);
});

test('the database seeder does not create an administrator outside local environments', function () {
    app()->detectEnvironment(fn (): string => 'production');

    $this->artisan('db:seed', [
        '--class' => DatabaseSeeder::class,
        '--force' => true,
    ])->assertSuccessful();

    expect(User::where('email', 'admin@example.com')->exists())->toBeFalse();
    expect(ShortURL::query()->doesntExist())->toBeTrue();

    $adminRole = Role::query()
        ->where('name', 'admin')
        ->where('guard_name', 'web')
        ->firstOrFail();

    expect($adminRole->permissions)
        ->toHaveCount(5)
        ->pluck('name')->toContain('manage users');
});

test('the development seeder refuses to run outside local environments', function () {
    app()->detectEnvironment(fn (): string => 'production');

    expect(fn () => app(DevelopmentSeeder::class)->run())
        ->toThrow(RuntimeException::class, 'The development seeder may only run in the local environment.');

    expect(Permission::query()->doesntExist())->toBeTrue();
    expect(User::query()->doesntExist())->toBeTrue();
    expect(ShortURL::query()->doesntExist())->toBeTrue();
});

test('the provision admin command creates a verified administrator without a default password', function () {
    $this->seed(DatabaseSeeder::class);

    $this->artisan('app:provision-admin', [
        '--name' => 'Cloud Owner',
        '--email' => 'OWNER@EXAMPLE.COM',
    ])
        ->expectsOutput('Administrator created.')
        ->expectsOutput('Use the Forgot password flow to choose a password before signing in.')
        ->assertSuccessful();

    $user = User::where('email', 'owner@example.com')->firstOrFail();

    expect($user)
        ->name->toBe('Cloud Owner')
        ->email_verified_at->not->toBeNull()
        ->hasRole('admin')->toBeTrue();

    expect(Hash::check('password', $user->password))->toBeFalse();
});

test('the provision admin command preserves an existing users identity and password', function () {
    $this->seed(DatabaseSeeder::class);

    $user = User::factory()->unverified()->create([
        'name' => 'Existing Owner',
        'email' => 'owner@example.com',
        'password' => 'existing-secure-password',
    ]);
    $password = $user->password;

    $this->artisan('app:provision-admin', [
        '--name' => 'Replacement Name',
        '--email' => 'owner@example.com',
    ])
        ->expectsOutput('Administrator access confirmed for the existing user.')
        ->assertSuccessful();

    expect($user->refresh())
        ->name->toBe('Existing Owner')
        ->password->toBe($password)
        ->email_verified_at->not->toBeNull()
        ->hasRole('admin')->toBeTrue();
});

test('the provision admin command requires a valid email', function () {
    $this->seed(DatabaseSeeder::class);

    $this->artisan('app:provision-admin', ['--email' => 'not-an-email'])
        ->assertFailed();

    expect(User::count())->toBe(0);
});

test('the provision admin command requires seeded roles', function () {
    expect(Permission::count())->toBe(0);

    $this->artisan('app:provision-admin', ['--email' => 'owner@example.com'])
        ->expectsOutput('The admin role does not exist. Run [php artisan db:seed --force] first.')
        ->assertFailed();

    expect(User::count())->toBe(0);
});

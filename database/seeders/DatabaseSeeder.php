<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /** @var list<string> */
    private const PERMISSIONS = [
        'access app',
        'manage links',
        'view analytics',
        'manage users',
        'manage roles',
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findOrCreate('admin', 'web')
            ->syncPermissions(self::PERMISSIONS);

        Role::findOrCreate('operator', 'web')
            ->syncPermissions([
                'access app',
                'manage links',
                'view analytics',
            ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

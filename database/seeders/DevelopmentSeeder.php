<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DevelopmentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's local development data.
     */
    public function run(): void
    {
        if (! app()->isLocal()) {
            throw new RuntimeException('The development seeder may only run in the local environment.');
        }

        $this->call(DatabaseSeeder::class);

        $user = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $user->assignRole('admin');

        $this->call(ShortUrlSeeder::class);
    }
}

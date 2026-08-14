<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ProvisionAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:provision-admin
                            {--name=Administrator : The administrator name}
                            {--email= : The administrator email address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provision an administrator without exposing a default password';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $attributes = [
            'name' => trim((string) $this->option('name')),
            'email' => Str::lower(trim((string) $this->option('email'))),
        ];

        $validator = Validator::make($attributes, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $adminRole = Role::query()
            ->where('name', 'admin')
            ->where('guard_name', 'web')
            ->first();

        if ($adminRole === null) {
            $this->error('The admin role does not exist. Run [php artisan db:seed --force] first.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $attributes['email'])->first();
        $created = $user === null;

        if ($created) {
            $user = new User([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'password' => Str::password(64),
            ]);
        }

        if ($user->email_verified_at === null) {
            $user->email_verified_at = Carbon::now();
        }

        $user->save();
        $user->assignRole($adminRole);

        $this->info($created ? 'Administrator created.' : 'Administrator access confirmed for the existing user.');
        $this->line('Use the Forgot password flow to choose a password before signing in.');

        if ($user->isDisabled()) {
            $this->warn('This user is disabled and must be enabled before signing in.');
        }

        return self::SUCCESS;
    }
}

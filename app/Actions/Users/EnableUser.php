<?php

namespace App\Actions\Users;

use App\Models\User;

class EnableUser
{
    public function handle(User $user): void
    {
        $user->forceFill([
            'disabled_at' => null,
        ])->save();
    }
}

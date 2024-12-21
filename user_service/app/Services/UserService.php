<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function store(array $data): User
    {
        if (isset($data['image']) && $data['image']) {
            $data['image'] = Storage::put('/images/users/', $data['image']);
        }

        return User::query()
            ->create($data);
    }
}

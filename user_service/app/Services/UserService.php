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

    public function destroy(User $user): ?bool
    {
        if ($user->image) {
            Storage::delete($user->image);
        }

        return $user->delete();
    }

    public function update(array $data, User $user): User
    {
        if (isset($data['image']) && $data['image'] && $user->image) {
            Storage::delete($user->image);
        }

        if (isset($data['image']) && $data['image']) {
            $data['image'] = Storage::put('/images/users/', $data['image']);
        }

        $user->update($data);

        return $user->refresh();
    }
}

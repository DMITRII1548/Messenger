<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {
    }

    public function index()
    {
        //
    }

    public function store(StoreRequest $request): UserResource
    {
        $data = $request->validated();

        $user = $this->userService->store($data);

        return UserResource::make($user);
    }

    public function show(User $user): array
    {
        return UserResource::make($user)->resolve();
    }

    public function update(UpdateRequest $request, User $user): array
    {
        $data = $request->validated();

        $user = $this->userService->update($data, $user);

        return UserResource::make($user)->resolve();
    }

    public function destroy(User $user): Response
    {
        $this->userService->destroy($user);

        return response([
            'destroyed' => true,
        ], 200);
    }
}

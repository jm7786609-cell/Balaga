<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserCollection;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService, Request $request)
    {
        parent::__construct($request);
        $this->userService = $userService;

        // Optional: Apply middleware only to specific methods
        // $this->middleware('role:admin')->except(['profile']);
    }

    public function index(Request $request): UserCollection
    {
        $validated = $request->validate([
            'role' => 'nullable|in:admin,cashier,pharmacist,supplier',
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|string|in:id,first_name,last_name,email,role,created_at',
            'order' => 'nullable|in:asc,desc',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $query = $this->userService->getAllUsers($validated);
        $users = $query->paginate($this->limit ?? 15);

        return new UserCollection($users);
    }

    public function show(User $user): JsonResponse
    {
        return $this->success(new UserResource($user));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());
        return $this->success(new UserResource($user), 'User created successfully', 201);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user = $this->userService->update($user, $request->validated());
        return $this->success(new UserResource($user), 'User updated successfully');
    }

    public function destroy(User $user): JsonResponse
    {
        $this->userService->delete($user);
        return $this->success(null, 'User deleted successfully');
    }

    // Optional: View current logged in user
    public function profile(): JsonResponse
    {
        return $this->success(new UserResource(auth()->user()));
    }
}

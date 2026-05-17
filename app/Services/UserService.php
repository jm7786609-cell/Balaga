<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAllUsers(array $filters): Builder
    {
        return User::query()
            ->where('role', '!=', 'Supplier')
            ->when($filters['role'] ?? null, fn($q, $role) => $q->where('role', $role))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', COALESCE(middle_name,''), ' ', last_name) LIKE ?", ["%{$search}%"]);
                });
            })
            ->when($filters['sort'] ?? null, function ($q, $sort) use ($filters) {
                $order = $filters['order'] ?? 'asc';
                if ($sort === 'full_name') {
                    $q->orderByRaw("CONCAT(first_name, ' ', COALESCE(middle_name,''), ' ', last_name) {$order}");
                } else {
                    $q->orderBy($sort, $order);
                }
            });
    }

    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); // Don't update password if not provided
        }

        $user->update($data);
        return $user->refresh();
    }

    public function delete(User $user): bool
    {
        // Prevent deleting the last admin
        if ($user->role === 'admin') {
            $adminCount = User::admins()->count();
            if ($adminCount <= 1) {
                throw new \Exception('Cannot delete the last admin user.');
            }
        }

        return $user->delete();
    }

    public function toggleStatus(User $user): User
    {
        // Optional: add soft deletes or active flag later
        // For now, just return user (or implement login lock if needed)
        return $user;
    }
}

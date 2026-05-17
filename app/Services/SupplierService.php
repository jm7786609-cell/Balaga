<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SupplierService
{

    public function __construct(
        protected UserService $userService
    ) {}

    public function getAllSuppliers(array $filters): Builder
    {
        return Supplier::query()
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('supplier_name', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->when($filters['sort'] ?? null, function ($q, $sort) use ($filters) {
                $order = $filters['order'] ?? 'asc';
                $q->orderBy($sort, $order);
            });
    }

    public function create(array $data): Supplier
    {
        return DB::transaction(function () use ($data) {

            $user = $this->userService->create([
                'first_name'  => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name'   => $data['last_name'],
                'email'       => $data['email'],
                'password'    => $data['password'],
                'role'        => 'supplier',
            ]);

            return Supplier::create([
                'supplier_name' => $data['supplier_name'],
                'user_id'       => $user->id,
                'email'         => $data['email'],
                'phone_number'  => $data['phone_number'],
                'address'       => $data['address'],
                'website'       => $data['website'] ?? null,
                'notes'         => $data['notes'] ?? null,
            ]);
        });
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        return DB::transaction(function () use ($supplier, $data) {

            if ($supplier->user) {
                $this->userService->update($supplier->user, [
                    'first_name'  => $data['first_name'],
                    'middle_name' => $data['middle_name'],
                    'last_name'   => $data['last_name'],
                    'email'       => $data['email'],
                ]);
            }

            $supplier->update(collect($data)->only([
                'supplier_name',
                'phone_number',
                'address',
                'website',
                'notes',
            ])->toArray());

            return $supplier->refresh();
        });
    }

    public function delete(Supplier $supplier): bool
    {
        if ($supplier->products()->exists()) {
            throw new \Exception('Cannot delete supplier with existing products.');
        }

        return $supplier->delete();
    }
}

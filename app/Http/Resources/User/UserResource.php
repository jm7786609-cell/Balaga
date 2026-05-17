<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Resource;

class UserResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'fullname' => $this->full_name,
            'email' => $this->email,
            'role' => $this->role,
            'role_label' => ucfirst($this->role), // e.g., "Admin", "Cashier"
            'email_verified_at' => $this->email_verified_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}

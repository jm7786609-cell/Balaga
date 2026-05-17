<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\Resource;

class LoginResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'fullname'   => $this->full_name,
            'email'      => $this->email,
            'role'       => $this->role,
            'role_label' => ucfirst($this->role),
            'token'      => $this->token,
        ];
    }
}

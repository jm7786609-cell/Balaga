<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'supplier_name',
        'contact_person',
        'phone_number',
        'email',
        'address',
        'website',
        'notes',
        'user_id',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function returns()
    {
        return $this->hasMany(ProductReturn::class);
    }

    public function getContactPersonAttribute(): string
    {
        return trim(
            "{$this->user->full_name}"
        );
    }
}

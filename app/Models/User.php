<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasFactory, HasApiTokens;

    protected $fillable = ['first_name', 'middle_name', 'last_name', 'email', 'password', 'role'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['email_verified_at' => 'datetime', 'role' => 'string'];

    // === SCOPES ===
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopePharmacists($query)
    {
        return $query->where('role', 'pharmacist');
    }

    public function scopeCashiers($query)
    {
        return $query->where('role', 'cashier');
    }

    public function scopeActive($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    // === RELATIONSHIPS ===
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'cashier_id');
    }
    public function stockIns()
    {
        return $this->hasMany(StockIn::class);
    }

    // === ATTRIBUTES ===
    public function getFullNameAttribute(): string
    {
        $name = trim("{$this->first_name} {$this->last_name}");
        if ($this->middle_name) {
            $name = trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
        }
        return $name;
    }
}

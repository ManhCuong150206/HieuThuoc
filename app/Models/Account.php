<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Account extends Authenticatable
{
    public $timestamps = false;

    protected $hidden = ['password'];

    public function profile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class, 'account_id');
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function isAdmin(): bool
    {
        return $this->is_active && strtolower($this->role) === 'admin';
    }
}

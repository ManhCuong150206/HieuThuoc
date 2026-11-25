<?php

namespace App\Models;

use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class Account extends Authenticatable
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $dateFormat = 'Y-m-d';

    protected $fillable = ['username', 'password'];

    protected $hidden = ['password'];

    protected $rememberTokenName = null;

    public function profile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class, 'account_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_account_id');
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'created_at' => 'date'];
    }

    public function matchesPassword(string $password): bool
    {
        // Legacy records may store the password as plain text.
        return password_get_info($this->password)['algoName'] !== 'unknown'
            ? password_verify($password, $this->password)
            : hash_equals($this->password, $password);
    }

    public function changePassword(string $password): void
    {
        $this->password = Hash::make($password);
        $this->save();
    }

    public function isAdmin(): bool
    {
        return $this->is_active && strtolower($this->role) === 'admin';
    }
}

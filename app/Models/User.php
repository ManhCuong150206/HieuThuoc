<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// The users table stores customer profiles. Authentication uses Account.
class User extends CustomerProfile
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
}

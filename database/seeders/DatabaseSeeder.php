<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // No automatic demo accounts or imported customer data.
        // Use Account::factory() and User::factory() explicitly for development.
    }
}

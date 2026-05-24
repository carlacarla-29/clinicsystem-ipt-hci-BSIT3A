<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Creates the default clinic admin account.
     * Login with: admin@clinic.com / password
     */
    public function run(): void
    {
        // FIX: Original seeder had no password — you couldn't log in.
        // Now creates a proper admin account with a hashed password.
        User::firstOrCreate(
            ['email' => 'admin@clinic.com'],
            [
                'name'     => 'Clinic Admin',
                'email'    => 'admin@clinic.com',
                'password' => Hash::make('password'),
            ]
        );
    }
}

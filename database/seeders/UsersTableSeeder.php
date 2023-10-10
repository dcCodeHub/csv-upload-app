<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Import the User model

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // Create a test user
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'), // Hashed password
        ]);

        // You can add more users as needed
    }
}

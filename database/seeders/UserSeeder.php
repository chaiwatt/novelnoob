<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // User 1 - Admin
        User::create([
            'name' => 'User1',
            'email' => 'user1@example.com',
            'password' => Hash::make('11111111'),
            'type' => 'admin',
        ]);

        // User 2 - Writer
        User::create([
            'name' => 'User2',
            'email' => 'user2@example.com',
            'password' => Hash::make('11111111'),
            'type' => 'writer',
        ]);

        // User 3 - Writer
        User::create([
            'name' => 'User3',
            'email' => 'user3@example.com',
            'password' => Hash::make('11111111'),
            'type' => 'writer',
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@spacex-dashboard.com'],
            [
                'name' => 'SpaceX Admin',
                'email' => 'admin@spacex-dashboard.com',
                'password' => Hash::make('admin123'),
                'role' => 'ADMIN',
            ]
        );

        // Create regular user
        User::updateOrCreate(
            ['email' => 'user@spacex-dashboard.com'],
            [
                'name' => 'SpaceX User',
                'email' => 'user@spacex-dashboard.com',
                'password' => Hash::make('user123'),
                'role' => 'USER',
            ]
        );
    }
}

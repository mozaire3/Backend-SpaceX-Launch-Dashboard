<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un utilisateur admin pour les tests
        User::firstOrCreate(
            ['email' => 'admin@spacex.test'],
            [
                'name' => 'Admin SpaceX',
                'password' => bcrypt('password123'),
                'role' => 'ADMIN',
            ]
        );

        // Créer un utilisateur normal pour les tests
        User::firstOrCreate(
            ['email' => 'user@spacex.test'],
            [
                'name' => 'User SpaceX',
                'password' => bcrypt('password123'),
                'role' => 'USER',
            ]
        );

        $this->command->info('Utilisateurs de test créés avec succès !');
        $this->command->info('Admin: admin@spacex.test / password123');
        $this->command->info('User: user@spacex.test / password123');
    }
}

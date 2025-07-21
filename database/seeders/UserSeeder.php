<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin ou utilisateur test principal
        User::create([
            'name' => 'Admin',
            'email' => 'admin@ofo.com',
            'email_verified_at' => now(),
            'password' => Hash::make('Admin'), // 🔐 Mot de passe simple pour test
        ]);

        // Utilisateurs générés aléatoirement
        User::factory()->count(5)->create();
    }
}

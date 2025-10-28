<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Si aucun admin n'existe déjà
        if (!User::where('role', 'admin')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@myroadbook.be',
                'password' => Hash::make('39AG?xgM9y76FXfH'), // ⚠️ à changer avant prod
                'role' => 'admin',
            ]);

            $this->command->info('✅ Utilisateur admin créé : admin@myroadbook.be / password');
        } else {
            $this->command->info('ℹ️ Un admin existe déjà, aucun nouveau créé.');
        }
    }
}

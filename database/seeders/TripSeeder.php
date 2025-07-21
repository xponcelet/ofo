<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Trip;
use App\Models\User;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::all()->each(function ($user) {
            // Chaque user reçoit entre 2 et 5 trips
            Trip::factory()->count(rand(2, 5))->create([
                'user_id' => $user->id,
            ]);
        });
    }
}

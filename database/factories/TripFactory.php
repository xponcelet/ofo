<?php

namespace Database\Factories;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TripFactory extends Factory
{
    protected $model = Trip::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'destination' => $this->faker->city(),
            'image' => $this->faker->imageUrl(640, 480, 'travel', true),
            'start_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'end_date' => $this->faker->dateTimeBetween('+1 month', '+2 months'),
            'budget' => $this->faker->randomFloat(2, 100, 5000),
            'currency' => 'EUR',
            'average_rating' => $this->faker->randomFloat(2, 3, 5),
            'is_public' => $this->faker->boolean(20),
        ];
    }
}

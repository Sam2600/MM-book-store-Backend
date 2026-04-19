<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class NovelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'translator_id' => User::inRandomOrder()->first()?->id ?? 1,
            'original_author_name' => $this->faker->name,
            'original_book_name' => $this->faker->sentence(3),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'cover_image' => null,
            'view_count' => $this->faker->numberBetween(0, 10000),
            'status' => $this->faker->randomElement(['ongoing', 'completed']),
        ];
    }
}

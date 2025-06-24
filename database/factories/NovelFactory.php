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
            'translator_id' => User::inRandomOrder()->first()->id,
            'original_author_name' => $this->faker->name,
            'original_book_name' => $this->faker->jobTitle(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'view_count' => $this->faker->numberBetween(1000, 9000),
            'cover_image' => $this->faker->imageUrl,
        ];
    }
}

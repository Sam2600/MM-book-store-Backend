<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Volume;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ChapterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'volume_id' => Volume::inRandomOrder()->first()?->id ?? 1,
            'chapter_number' => $this->faker->numberBetween(1, 50),
            'title' => $this->faker->sentence(4),
            'file_path' => $this->faker->word . '.txt',
            'coin_cost' => $this->faker->numberBetween(0, 10),
            'status' => $this->faker->randomElement(['pending', 'processed', 'approved']),
        ];
    }
}

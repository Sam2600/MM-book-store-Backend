<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class VolumeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'volume_number' => $this->faker->unique()->numberBetween(1, 100),
            'novel_id' => $this->faker->numberBetween(1, 10),
            'volume_title' => $this->faker->sentence,
            'order' => $this->faker->numberBetween(1, 10),
        ];
    }
}

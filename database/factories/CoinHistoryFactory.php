<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Novel;
use App\Models\Volume;
use App\Models\Chapter;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CoinHistory>
 */
class CoinHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $novel = Novel::inRandomOrder()->first();
        $volume = $novel ? $novel->volumes()->inRandomOrder()->first() : null;
        $chapter = $volume ? $volume->chapters()->inRandomOrder()->first() : null;

        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? 1,
            'novel_id' => $novel?->id ?? 1,
            'volume_id' => $volume?->id ?? 1,
            'chapter_id' => $chapter?->id ?? 1,
            'status' => $this->faker->randomElement(['spent', 'earned']),
            'coin_amount' => $this->faker->numberBetween(1, 100),
            'description' => $this->faker->sentence,
        ];
    }
}

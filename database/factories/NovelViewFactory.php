<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Volume;
use App\Models\Novel;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class NovelViewFactory extends Factory
{
   /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
   public function definition(): array
   {
      return [
            'user_id' => User::inRandomOrder()->first()?->id ?? 1,
            'novel_id' => Novel::inRandomOrder()->first()?->id ?? 1,
            'ip_address' => $this->faker->ipv4,
      ];
   }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Novel;
use App\Models\CoinHistory;

class CoinHistorySeeder extends Seeder
{
    public function run(): void
    {
        $users   = User::pluck('id');
        $novels  = Novel::with('volumes.chapters')->get();

        if ($novels->isEmpty() || $users->isEmpty()) return;

        for ($i = 0; $i < 100; $i++) {
            $novel   = $novels->random();
            $volume  = $novel->volumes->random();
            $chapter = $volume->chapters->random();

            CoinHistory::create([
                'user_id'     => $users->random(),
                'novel_id'    => $novel->id,
                'volume_id'   => $volume->id,
                'chapter_id'  => $chapter->id,
                'status'      => fake()->randomElement(['spent', 'earned']),
                'coin_amount' => fake()->numberBetween(1, 50),
                'description' => fake()->sentence(),
            ]);
        }
    }
}

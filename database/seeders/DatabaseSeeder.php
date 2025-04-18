<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Chapter;
use App\Models\Novel;
use App\Models\Volume;
use App\Models\Category;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        User::factory(10)->create(); // authors

        Category::factory()->count(5)->create();

        Novel::factory(10)->create()->each(function ($novel) {
            
            $novel->categories()->sync(
                Category::inRandomOrder()->take(2)->pluck('id')->toArray()
            );

            Volume::factory(3)->create(['novel_id' => $novel->id])->each(function ($volume) use ($novel) {
                Chapter::factory(5)->create([
                    'volume_id' => $volume->id,
                    'novel_id' => $novel->id,
                ]);
            });
        });
    }
}

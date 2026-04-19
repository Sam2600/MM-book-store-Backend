<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Novel;
use App\Models\Volume;
use App\Models\Chapter;
use App\Models\Category;
use App\Models\NovelView;
use Faker\Factory as Faker;

class NovelSeeder extends Seeder
{
    public function run(): void
    {
        $faker      = Faker::create();
        $authorIds  = User::whereHas('role', fn($q) => $q->where('name', 'author'))->pluck('id');
        $userIds    = User::pluck('id');
        $categoryIds = Category::pluck('id');

        Novel::factory(25)->make()->each(function (Novel $novel) use ($faker, $authorIds, $userIds, $categoryIds) {

            $novel->translator_id = $authorIds->random();
            $novel->save();

            // Attach 1–3 random categories
            $novel->categories()->attach($categoryIds->random(rand(1, 3))->all());

            // Seed views spread across last 60 days
            $viewCount = rand(5, 25);
            for ($i = 0; $i < $viewCount; $i++) {
                NovelView::create([
                    'novel_id'   => $novel->id,
                    'user_id'    => $userIds->random(),
                    'ip_address' => $faker->ipv4,
                    'created_at' => $faker->dateTimeBetween('-60 days', 'now'),
                    'updated_at' => now(),
                ]);
            }

            // Create volumes and chapters
            for ($v = 1; $v <= 3; $v++) {
                $volume = Volume::create([
                    'novel_id'     => $novel->id,
                    'volume_number' => $v,
                    'volume_title'  => $faker->sentence(3),
                ]);

                for ($c = 1; $c <= 8; $c++) {
                    Chapter::create([
                        'volume_id'      => $volume->id,
                        'chapter_number' => $c,
                        'title'          => $faker->sentence(4),
                        'content'        => $faker->paragraphs(3, true),
                        'coin_cost'      => $faker->numberBetween(0, 10),
                        'status'         => $faker->randomElement(['pending', 'processed', 'approved']),
                    ]);
                }
            }
        });
    }
}

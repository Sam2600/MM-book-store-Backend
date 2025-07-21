<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Chapter;
use App\Models\Novel;
use App\Models\Volume;
use App\Models\Category;
use App\Models\CoinHistory;
use App\Models\Role;
use App\Models\NovelView;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Initialize Faker
        $faker = Faker::create();

        // Seed roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $translatorRole = Role::firstOrCreate(['name' => 'translator']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Seed admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role_id' => $adminRole->id,
        ]);

        // Seed translators
        User::factory(20)->create(['role_id' => rand(2,3)]);

        // Seed categories
        $categories = array ("Action", "Adventure", "Comedy", "Drama", "Fantasy", "Horror", "Mystery", "Romance", "Sci-Fi", "Thriller");
        Category::factory()->createMany(array_map(fn($name) => ['name' => $name], $categories));

        // Seed novels, volumes, chapters, and attach categories
        Novel::factory(100)->create()->each(function ($novel) use ($faker) {
            
            // Attach 2 random categories
            $novel->categories()->sync(
                Category::inRandomOrder()->take(2)->pluck('id')->toArray()
            );

            NovelView::factory()->create([
                'user_id' => User::inRandomOrder()->first()->id, // Random user
                'novel_id' => $novel->id,
                'ip_address' => $faker->ipv4,
                'created_at' => $faker->dateTimeBetween('-60 days', 'now'), // Random date within the last week
            ]);

            // Create volumes for each novel
            for ($v = 1; $v <= 15; $v++) {

                $volume = Volume::factory()->create([
                    'novel_id' => $novel->id,
                    'volume_number' => $v,
                    'volume_title' => $faker->sentence(3),
                ]);

                // Create 5 chapters with unique chapter_number per volume
                for ($c = 1; $c <= 15; $c++) {
                    Chapter::factory()->create([
                        'volume_id' => $volume->id,
                        'chapter_number' => $c,
                        'title' => $faker->sentence(4),
                        'content' => $faker->paragraphs(3, true),
                        'coin_cost' => $faker->numberBetween(0, 10),
                        'status' => $faker->randomElement(['pending', 'processed', 'approved']),
                    ]);
                }
            }
        });

        // Seed coin histories
        CoinHistory::factory(200)->create();
    }   
}

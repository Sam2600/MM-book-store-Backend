<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Core genres
            'Action',
            'Adventure',
            'Comedy',
            'Drama',
            'Fantasy',
            'Horror',
            'Mystery',
            'Romance',
            'Sci-Fi',
            'Thriller',
            'Tragedy',
            'Psychological',

            // Asian web novel staples
            'Martial Arts',
            'Cultivation',
            'Wuxia',
            'Xianxia',
            'Xuanhuan',
            'Historical',
            'Urban / Modern',
            'Reincarnation',
            'Transmigration',
            'System / Game Elements',

            // Subgenres & themes
            'Slice of Life',
            'School Life',
            'Supernatural',
            'Harem',
            'Reverse Harem',
            'Boys Love (BL)',
            'Girls Love (GL)',
            'Gender Bender',
            'Time Travel',
            'Virtual Reality',
            'Post-Apocalyptic',
            'Military',
            'Sports',
            'Cooking / Gourmet',
            'Music',
            'Mecha',
        ];

        foreach ($categories as $name) {
            Category::updateOrCreate(['name' => $name]);
        }
    }
}

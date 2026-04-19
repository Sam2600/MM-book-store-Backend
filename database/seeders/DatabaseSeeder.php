<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Always seeded in every environment
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            PaymentMethodSeeder::class,
            RateSeeder::class,
            AdminUserSeeder::class,
        ]);

        // Development-only data
        if (!app()->isProduction()) {
            $this->call([
                UserSeeder::class,
                NovelSeeder::class,
                CoinHistorySeeder::class,
            ]);
        }
    }
}

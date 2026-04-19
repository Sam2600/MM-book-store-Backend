<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rate;
use App\Constants\Rate\RateConstant;

class RateSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            ['type' => RateConstant::CHAPTER_READ, 'rate' => RateConstant::CHAPTER_READ_RATE],
            ['type' => RateConstant::DEFAULT, 'rate' => RateConstant::DEFAULT_RATE],
            ['type' => RateConstant::SPECIAL, 'rate' => RateConstant::SPECIAL_RATE],
        ];

        foreach ($rates as $rate) {
            Rate::updateOrCreate(['type' => $rate['type']], $rate);
        }
    }
}

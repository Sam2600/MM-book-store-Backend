<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            ['code' => 'kbzpay',        'label' => 'KBZ Pay'],
            ['code' => 'wave',          'label' => 'Wave Pay'],
            ['code' => 'aya_pay',       'label' => 'AYA Pay'],
            ['code' => 'bank_transfer', 'label' => 'Bank Transfer'],
        ];

        foreach ($methods as $method) {
            \App\Models\PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                ['label' => $method['label'], 'is_active' => true]
            );
        }
    }
}

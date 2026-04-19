<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;
use App\Constants\PaymentMethod\PaymentMethodConstant;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['code' => PaymentMethodConstant::KBZPAY,        'label' => 'KBZ Pay'],
            ['code' => PaymentMethodConstant::WAVE,          'label' => 'Wave Pay'],
            ['code' => PaymentMethodConstant::AYA_PAY,       'label' => 'AYA Pay'],
            ['code' => PaymentMethodConstant::BANK_TRANSFER, 'label' => 'Bank Transfer'],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                ['label' => $method['label'], 'is_active' => true]
            );
        }
    }
}

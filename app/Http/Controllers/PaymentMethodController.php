<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $methods = PaymentMethod::where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'code', 'label']);

        return $this->success('OK', $methods);
    }
}

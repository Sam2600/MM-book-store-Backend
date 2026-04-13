<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * @property string $name
 * @property string $email
 * @property int|null $payment_method_id
 * @property string|null $payment_account
 */
class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name'            => ['required', 'string', 'max:255', Rule::unique('users', 'name')->ignore($userId)],
            'email'           => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'payment_method_id' => ['required', Rule::exists('payment_methods', 'id')],
            'payment_account'   => ['required', 'string', 'max:100'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json(['status' => 'NG', 'message' => $validator->errors()], 422)
        );
    }
}

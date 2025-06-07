<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $title
 * @property string $original_author_name
 * @property string $original_book_name
 * @property string $description
 * @property string|mixed $cover_image
 * @property array $categories
 * @property string|int $categories
 */
class NovelRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'original_author_name' => 'required|string',
            'original_book_name' => 'required|string',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|file|image|max:2048',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
        ];
    }
}

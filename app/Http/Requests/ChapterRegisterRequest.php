<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string|int $novel_id
 * @property string|int $volume_id
 * @property string|int $chapter_id
 * @property string $title
 * @property string $volume_title
 * @property string $content
 */
class ChapterRegisterRequest extends FormRequest
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
            'novel_id'       => 'required|exists:novels,id',
            'novel_name'     => 'required|string|max:255',
            'volume_number'  => 'required|integer|min:1',
            'volume_title'   => 'nullable|string|max:255',
            'chapter_number' => 'required|integer|min:1',
            'title'          => 'required|string|max:255',
            'content'        => 'required|string',
            'coin_cost'      => 'nullable|integer|min:0|max:9999',
        ];
    }
}

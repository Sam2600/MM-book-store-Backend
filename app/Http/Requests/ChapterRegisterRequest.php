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
            'novel_id' => 'required|exists:novels,id',
            'volume_id' => 'required|integer',
            'chapter_id' => 'required|integer',
            'title' => 'required|string',
            'volume_title' => 'required|string',
            'content' => 'required|string',
        ];
    }
}

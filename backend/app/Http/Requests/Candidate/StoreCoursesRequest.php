<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCoursesRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'candidate_id' => ['nullable', 'integer', 'exists:candidate_profiles,id'],
            'name'         => ['required', 'string', 'max:255'],
            'link'         => ['nullable', 'url', 'max:2048'],
            'image_path'   => ['nullable', 'string', 'max:500'],
            'UID'          => ['nullable', 'integer', 'exists:users,id'],
            'LUID'         => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}

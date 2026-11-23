<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class StoreSkillsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic if needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Require either skill_id or skill_name (so candidates can pick an existing one or type a new one)
            'skill_id'   => 'nullable|integer|exists:skills,id',
            'skill_name' => 'required_without:skill_id|nullable|string|max:255',
            'level'      => 'nullable|string|max:50',
            'type'       => 'nullable|string|max:50', // Optional category/type if typing a new skill name
        ];
    }

    /**
     * Custom error messages for validation failures.
     */
    public function messages(): array
    {
        return [
            'skill_name.required_without' => 'Please provide either an existing skill selection.',
        ];
    }
}
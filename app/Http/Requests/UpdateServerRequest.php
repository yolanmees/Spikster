<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServerRequest extends FormRequest
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
            'name' => 'sometimes|string|min:3|max:255',
            'provider' => 'sometimes|string|max:255',
            'location' => 'sometimes|string|max:255',
            'php' => 'sometimes|string|in:7.4,8.0,8.1,8.2,8.3',
            'github_key' => 'sometimes|string',
            'cron' => 'sometimes|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.min' => 'Server name must be at least 3 characters.',
            'php.in' => 'Invalid PHP version. Allowed versions: 7.4, 8.0, 8.1, 8.2, 8.3',
        ];
    }
}

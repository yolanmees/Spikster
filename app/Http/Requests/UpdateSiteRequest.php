<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteRequest extends FormRequest
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
            'domain' => 'sometimes|string|regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i',
            'php' => 'sometimes|string|in:7.4,8.0,8.1,8.2,8.3',
            'basepath' => 'sometimes|string|max:255',
            'repository' => 'sometimes|nullable|string|url',
            'branch' => 'sometimes|nullable|string|max:100',
            'supervisor' => 'sometimes|nullable|string',
            'nginx' => 'sometimes|nullable|string',
            'deploy' => 'sometimes|nullable|string',
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
            'domain.regex' => 'Invalid domain format. Use valid domain like example.com',
            'php.in' => 'Invalid PHP version. Allowed versions: 7.4, 8.0, 8.1, 8.2, 8.3',
            'repository.url' => 'Repository must be a valid URL.',
        ];
    }
}

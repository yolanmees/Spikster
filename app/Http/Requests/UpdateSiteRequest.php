<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'domain' => 'sometimes|string|regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i',
            'php' => 'sometimes|string|in:7.4,8.0,8.1,8.2,8.3',
            'php_memory_limit' => 'sometimes|nullable|string|regex:/^\d+[KMG]?$/i',
            'php_upload_max_filesize' => 'sometimes|nullable|string|regex:/^\d+[KMG]?$/i',
            'php_max_execution_time' => 'sometimes|nullable|integer|min:10|max:86400',
            'php_max_input_vars' => 'sometimes|nullable|integer|min:100|max:100000',
            'php_post_max_size' => 'sometimes|nullable|string|regex:/^\d+[KMG]?$/i',
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

<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSiteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'server_id' => ['required', 'uuid', 'exists:servers,server_id'],
            'domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,}$/',
                'unique:sites,domain',
            ],
            'username' => ['required', 'string', 'max:100', 'alpha_dash'],
            'password' => ['required', 'string', 'min:8'],
            'database' => ['required', 'string', 'min:8'],
            'php' => ['required', 'string', Rule::in(['8.0', '8.1', '8.2', '8.3'])],
            'basepath' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:500', 'url'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'server_id.required' => 'Server selection is required.',
            'server_id.exists' => 'Selected server does not exist.',
            'domain.required' => 'Domain name is required.',
            'domain.regex' => 'Please provide a valid domain name.',
            'domain.unique' => 'This domain is already in use.',
            'username.required' => 'Username is required.',
            'username.alpha_dash' => 'Username may only contain letters, numbers, dashes and underscores.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'database.required' => 'Database password is required.',
            'database.min' => 'Database password must be at least 8 characters.',
            'php.required' => 'PHP version is required.',
            'php.in' => 'Invalid PHP version selected.',
            'source.url' => 'Git source must be a valid URL.',
        ];
    }
}

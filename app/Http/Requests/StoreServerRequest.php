<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServerRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'ip' => ['required', 'ip', 'unique:servers,ip'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['required', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:8'],
            'ssh_key' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Server name is required.',
            'ip.required' => 'Server IP address is required.',
            'ip.ip' => 'Please provide a valid IP address.',
            'ip.unique' => 'This IP address is already registered.',
            'port.required' => 'SSH port is required.',
            'port.integer' => 'Port must be a number.',
            'port.min' => 'Port must be at least 1.',
            'port.max' => 'Port must not exceed 65535.',
            'username.required' => 'SSH username is required.',
            'password.min' => 'Password must be at least 8 characters.',
        ];
    }
}

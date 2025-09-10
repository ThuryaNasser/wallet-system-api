<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Handles validation for user registration with wallet functionality
 */
class CreateUserAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // API is open for demo purposes
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
        ];
    }

    /**
     * Get custom error messages for validation rules
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'name.max' => 'Name cannot exceed 255 characters',
            'email.required' => 'Email address is required',
            'email.email' => 'Email address must be valid',
            'email.unique' => 'Email address is already registered',
        ];
    }
}

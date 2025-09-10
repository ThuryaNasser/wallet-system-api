<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Handles validation for deducting balance from user accounts
 */
class ChargeRequest extends FormRequest
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
            'user_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'reference' => 'required|string|unique:transactions,reference|max:255',
            'description' => 'nullable|string|max:255',
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
            'user_id.required' => 'User ID is required',
            'user_id.exists' => 'User does not exist',
            'amount.required' => 'Amount is required',
            'amount.min' => 'Amount must be at least 0.01',
            'amount.max' => 'Amount cannot exceed 999,999.99',
            'reference.required' => 'Reference is required',
            'reference.unique' => 'Reference has already been used',
        ];
    }
}

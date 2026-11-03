<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // La ruta API exige auth:sanctum; este Request no añade restricciones por rol.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // sometimes permite actualizaciones parciales; ignore usa el proveedor del binding.
        return [
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'document_number' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('suppliers', 'document_number')->ignore($this->supplier),
            ],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'nullable', 'boolean'],
        ];
    }
}

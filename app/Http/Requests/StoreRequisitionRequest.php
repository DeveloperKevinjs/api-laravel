<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequisitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // La ruta exige sesión autenticada mediante Sanctum, sin permisos por rol adicionales.
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
            'notes' => ['nullable', 'string'],

            'requisitionLines' => ['required', 'array', 'min:1'],

            // Cada línea solo admite los datos que utiliza createMany().
            'requisitionLines.*' => ['array:product_id,quantity'],

            'requisitionLines.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],

            // Ejercicio completado: decimal(12, 3) admite nueve dígitos enteros y tres decimales.
            'requisitionLines.*.quantity' => ['required', 'numeric', 'gt:0', 'decimal:0,3', 'max:999999999.999'],
        ];
    }
}

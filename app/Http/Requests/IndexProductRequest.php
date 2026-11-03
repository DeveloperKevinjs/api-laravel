<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexProductRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:100'],
            // El filtro solo admite una categoría existente; el controlador aplica el scope.
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'status' => ['nullable', 'boolean'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],

            // Lista cerrada de columnas y direcciones que puede usar orderBy().
            'sort' => [
                'nullable',
                'in:code,-code,name,-name,price,-price,created_at,-created_at',
            ],
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // Mantiene un booleano en el JSON aunque la base de datos devuelva 0 o 1.
            'status' => $request->routeIs('categories.show') ? true : (bool) $this->status,
            'products_count' => $this->whenCounted('products'),
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'status',
    ];

    // products.category_id conecta los productos y restringe borrar categorías utilizadas.
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}

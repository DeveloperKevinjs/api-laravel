<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    protected $fillable = [
        'name',
        'status',
    ];

    public function scopeStatus(Builder $query, bool $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeSearch(Builder $query, bool $search): Builder
    {
        return $query->where('search', 'ILIKE', '%' . $search . '%');
    }

    // products.category_id conecta los productos y restringe borrar categorías utilizadas.
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}

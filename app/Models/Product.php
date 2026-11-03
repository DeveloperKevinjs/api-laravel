<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'code',
        'name',
        'description',
        'price',
        'status',
    ];

    // ProductController combina estos scopes con los filtros del IndexProductRequest.
    #[Scope]
    protected function status(Builder $query, bool $status): void
    {
        $query->where('status', $status);
    }

    #[Scope]
    protected function category_id(Builder $query, int $categoryId): void
    {
        $query->where('category_id', $categoryId);
    }

    #[Scope]
    protected function search(Builder $query, string $search): void
    {
        $query->where(
            function (Builder $query) use ($search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }
        );
    }

    #[Scope]
    protected function min_price(Builder $query, float $min_price): void
    {
        $query->where('price', '>=', $min_price);
    }

    #[Scope]
    protected function max_price(Builder $query, float $max_price): void
    {
        $query->where('price', '<=', $max_price);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // La FK de estas líneas restringe la eliminación de productos referenciados.
    public function requisitionLines(): HasMany
    {
        return $this->hasMany(RequisitionLine::class);
    }
}

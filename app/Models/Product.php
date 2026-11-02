<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

// use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{

    // use SoftDeletes;

    protected $fillable = [
        'category_id',
        'code',
        'name',
        'description',
        'price',
        'status',
    ];

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
        return  $this->belongsTo(Category::class);
    }

    public function requisitionLines(): HasMany
    {
        return $this->hasMany(RequisitionLine::class);
    }
}

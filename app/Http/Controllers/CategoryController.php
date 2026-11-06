<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Requests\IndexCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function index(IndexCategoryRequest $request)
    {
        Gate::authorize('viewAny', Category::class);

        $filters = $request->validated();
        $query = Category::query();

        if (array_key_exists('status', $filters)) {
            $query->status($filters['status']);
        }

        if (array_key_exists('search', $filters)) {
            $query->search($filters['search']);
        }

        // Un orden estable evita cambios de posición entre páginas.
        $categories = $query->orderBy('name')->orderBy('id')->paginate(15)->withQueryString();

        return CategoryResource::collection($categories);
    }

    public function show(Category $category)
    {
        Gate::authorize('view', $category);

        $category->loadCount('products');

        return new CategoryResource($category);
    }

    public function store(CategoryRequest $request)
    {
        // El Request autoriza mediante CategoryPolicy y limita los datos que se guardan.
        $category = Category::create($request->validated());

        // Recupera valores predeterminados de la base de datos, como status si se omitió.
        $category->refresh();

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }
}

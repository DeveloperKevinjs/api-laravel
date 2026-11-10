<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Requests\IndexCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

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

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $attributes = $request->validated();

        // if ($request->isMethod('PUT') && ! array_key_exists('name', $attributes)) {
        //     throw ValidationException::withMessages(['name' => 'El nombre es obligatorio para actualizar.']);
        // }

        if (array_key_exists('name', $attributes) && array_key_exists('status', $attributes) && $category->products()->exists()) {
            return response()->json(['message' => 'La categoría no se puede actualizar en este estado.'], 409);
        }

        $category->update($attributes);

        return new CategoryResource($category);
    }

    public function destroy(Category $category)
    {
        Gate::authorize('delete', $category);

        if ($category->products()->exists()) {
            return response()->json(['message' => 'No se puede eliminar la categoría porque tiene productos asociados.'], 409);
        }

        $category->delete();

        return response()->noContent();
    }
}

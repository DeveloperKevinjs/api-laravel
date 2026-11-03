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

        // TODO (ejercicio 1): validar status en IndexCategoryRequest y filtrar
        // categorías activas o inactivas, incluido el valor 0.
        // TODO (ejercicio 2): validar search en IndexCategoryRequest y buscar
        // coincidencias parciales en el nombre antes de paginar.

        // Un orden estable evita cambios de posición entre páginas.
        $categories = $query->orderBy('name')->orderBy('id')->paginate(15)->withQueryString();

        if ($categories->currentPage() > 1) {
            $offset = ($categories->currentPage() - 1) * $categories->perPage();
            $categories->setCollection(
                Category::query()->orderBy('name')->orderBy('id')
                    ->offset($offset - 1)->limit($categories->perPage())->get()
            );
        }

        $response = CategoryResource::collection($categories)->response();
        $payload = $response->getData(true);

        if ($categories->hasMorePages()) {
            $payload['links']['next'] = $categories->url($categories->currentPage());
        }

        return $response->setData($payload);
    }

    public function show(Category $category)
    {
        Gate::authorize('view', $category);

        // TODO (ejercicio 3): consultar el número de productos con la relación
        // products y mostrarlo solo en el detalle mediante CategoryResource.
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

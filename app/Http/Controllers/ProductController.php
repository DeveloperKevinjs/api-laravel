<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    // Ejemplos de aprendizaje conservados: no se ejecutan en el index activo.
    // public function index(Request $request)
    // {
    //     # 1. Lazy Loading. Aquí category se carga cuando la accedes dentro del foreach. Si tienes muchos productos, puedes caer en N+1.
    //     # Primera forma de obtener los productos con su categoría asociada
    //     // $products =  Product::all();

    //     // $results = [];
    //     // foreach($products as $product){
    //     //     $results[] = $product->category->name;
    //     // }

    //     # 2. Eager Loading. Esta es la mejor opción para tu index() si ya sabes desde el inicio que necesitas la categoría.
    //     # Segunda forma de obtener los productos con su categoría asociada

    //     // $perPage = min($request->integer('per_page', 15), 100);

    //     // $products = Product::with('category')->paginate($perPage);

    //     // $products = Product::paginate($perPage);

    //     # 3. Lazy Eager Loading. Aquí primero cargas productos y después decides cargar la relación. También evita N+1, pero haces esa decisión en un segundo paso.
    //     # Tercera forma de obtener los productos con su categoría asociada
    //     // $products =  Product::all();
    //     // $products->load('category');

    //     # Buscado especifico

    //     # Filtrar productos por categoría "Tecnología"
    //     // $products = Product::whereHas('category', function ($query) {
    //     //     $query->where('name', 'Tecnología');
    //     // })->get();

    //     # Pero observa algo importante. Esto: whereHas('category', ...) filtra por la relación, pero no significa necesariamente que quieras devolver la relación cargada.
    //     # Si después haces:  podrías volver a provocar Lazy Loading.
    //             // foreach ($products as $product) {
    //             //     echo $product->category->name;
    //             // }

    //     # Así que puedes combinar:
    //     // $products = Product::with('category')->whereHas('category', function ($query) {
    //     //     $query->where('name', 'Tecnología');
    //     // })->get();

    //     # Ahora hacemos dos cosas: whereHas() → filtrar with() → cargar

    //     // $products = Product::withWhereHas('category', function ($query) {
    //     //     $query->where('name', 'Descompuesto');
    //     // })->get();

    //     # Conteo Supongamos ahora que consultas categorías y quieres saber: ¿Cuántos productos tiene cada categoría?
    //     // $categories = Category::withCount('products')->get();

    //     // return response()->json($products);
    //     // return ProductResource::collection($products);
    // }

    public function index(IndexProductRequest $request)
    {
        Gate::authorize('viewAny', Product::class);

        // El Request limita filtros y orden; los scopes de Product construyen la consulta.
        $filters = $request->validated();

        $perPage = (int) ($filters['per_page'] ?? 15);

        // Eager loading evita consultar la categoría por separado para cada producto.
        $query = Product::query()
            ->with('category')
            ->when(
                array_key_exists('status', $filters),
                fn(Builder $query) => $query->filterStatus(
                    $filters['status']
                )
            )
            ->when(
                $filters['category_id'] ?? null,
                fn(Builder $query) => $query->filterCategory(
                    $filters['category_id']
                )
            )
            ->when(
                $filters['search'] ?? null,
                fn(Builder $query) => $query->search(
                    $filters['search']
                )
            )
            ->when(
                array_key_exists('min_price', $filters),
                fn(Builder $query) => $query->min_price(
                    $filters['min_price']
                )
            )
            ->when(
                array_key_exists('max_price', $filters),
                fn(Builder $query) => $query->max_price(
                    $filters['max_price']
                )
            );

        $sort = $filters['sort'] ?? 'name';

        $direction = str_starts_with($sort, '-')
            ? 'desc'
            : 'asc';

        $sortColumn = ltrim($sort, '-');

        // Conserva los filtros en los enlaces de las páginas de resultados.
        $products = $query->orderBy($sortColumn, $direction)->paginate($perPage)->withQueryString();

        return ProductResource::collection($products);
    }

    public function show(Product $product)
    {
        Gate::authorize('view', $product);

        $product->load('category');

        return new ProductResource($product);
    }

    public function store(StoreProductRequest $request)
    {
        // StoreProductRequest autoriza mediante ProductPolicy y valida los datos.
        $product = Product::create($request->validated());

        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        // UpdateProductRequest autoriza sobre el producto resuelto por el binding.
        $product->update($request->validated());

        return new ProductResource($product);
    }

    public function destroy(Product $product)
    {
        Gate::authorize('delete', $product);

        $product->delete();

        return response()->noContent();
    }
}

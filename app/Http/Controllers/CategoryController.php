<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function store(CategoryRequest $request)
    {
        // El Request autoriza mediante CategoryPolicy y limita los datos que se guardan.
        $category = Category::create($request->validated());

        // Recupera valores predeterminados de la base de datos, como status si se omitió.
        $category->refresh();

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }
}

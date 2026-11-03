<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::all();

        return response()->json($suppliers);
    }

    // El binding resuelve por id en la API y por documento en la ruta web pública.
    public function show(Supplier $supplier)
    {
        return response()->json($supplier);
    }

    public function store(StoreSupplierRequest $request)
    {
        $supplier = Supplier::create($request->validated());

        return response()->json($supplier, 201);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());

        return response()->json($supplier);
    }

    public function destroy(Supplier $supplier)
    {
        // SoftDeletes marca deleted_at y conserva el registro en la base de datos.
        $supplier->delete();

        return response()->noContent();
    }
}

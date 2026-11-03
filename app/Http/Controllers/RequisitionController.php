<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequisitionRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RequisitionController extends Controller
{
    public function store(StoreRequisitionRequest $request)
    {
        $validated = $request->validated();

        // Cabecera y líneas se guardan juntas; una excepción revierte todo el bloque.
        $requisition = DB::transaction(function () use ($request, $validated) {
            // La relación fija user_id; el número se genera en el servidor.
            $newRequisition = $request->user()
                ->requisitions()
                ->create([
                    'number' => 'REQ-'.Str::ulid(),
                    'notes' => $validated['notes'] ?? null,
                ]);

            // La relación asigna requisition_id a cada línea validada por el Request.
            $newRequisition->requisitionLines()->createMany(
                $validated['requisitionLines']
            );

            return $newRequisition;
        });

        // Carga las relaciones que se incluirán en la respuesta JSON.
        $requisition->load([
            'user',
            'requisitionLines.product',
        ]);

        return response()->json($requisition, 201);
    }
}

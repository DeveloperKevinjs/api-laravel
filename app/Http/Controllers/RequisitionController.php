<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\StoreRequisitionRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RequisitionController extends Controller
{
    public function store(StoreRequisitionRequest $request)
    {
        $validated = $request->validated();

        $requisition = DB::transaction(function () use ($request, $validated) {

            $newRequisition = $request->user()
                ->requisitions()
                ->create([
                    'number' => 'REQ-' . Str::ulid(),
                    'notes' => $validated['notes'] ?? null,
                ]);

            $newRequisition->requisitionLines()->createMany(
                $validated['requisitionLines']
            );

            return $newRequisition;
        });

        $requisition->load([
            'user',
            'requisitionLines.product',
        ]);

        return response()->json($requisition, 201);
    }
}

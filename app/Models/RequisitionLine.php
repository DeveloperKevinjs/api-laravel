<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// TODO (aprendizaje): Configurar los atributos asignables de las líneas siguiendo
// el ejemplo de Requisition, sin incluir la clave foránea que asigna la relación.
#[Fillable(['quantity', 'product_id'])]
class RequisitionLine extends Model
{
    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// user_id lo asigna la relación del usuario, no el formulario.
#[Fillable(['number', 'notes'])]
class Requisition extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // createMany() utiliza esta relación para asignar requisition_id a las líneas.
    public function requisitionLines(): HasMany
    {
        return $this->hasMany(RequisitionLine::class);
    }
}

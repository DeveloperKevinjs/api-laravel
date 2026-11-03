<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    // Las consultas normales excluyen los proveedores marcados con deleted_at.
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'document_number',
        'email',
        'phone',
        'status',
    ];
}

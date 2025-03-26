<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_producto',
        'modelo_producto',
        'codigo_proveedor',
        'marca_comercial',
        'codigos_articulos',
        'variantes',
        'ai_texts_id',
    ];

    public function casts()
    {
        return [
            'variantes' => 'array',
            'codigos_articulos' => 'array',
        ];
    }
}

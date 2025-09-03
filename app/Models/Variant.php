<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function products()
    {
        return Product::find($this->codigos_articulos);
    }

    public function aiTexts(): BelongsTo
    {
        return $this->belongsTo(AiTexts::class);
    }
}

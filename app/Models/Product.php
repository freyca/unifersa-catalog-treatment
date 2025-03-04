<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'codigo_articulo',
        'descripcion',
        'codigo_proveedor',
        'marca_comercial',
        'referencia_proveedor',
        'ean13',
        'familia',
        'precio_venta',
        'stock',
        'imagen',
        'caracteristicas',
        'family_id',
        'ai_texts_id',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function aiTexts(): BelongsTo
    {
        return $this->belongsTo(AiTexts::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'id',
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
        'nombre_producto',
        'modelo_producto',
        'nombre_variante',
        'descatalogado',
        'family_id',
        'unidad_facturacion',
        'descripcion_formato_venta',
        'unidades_formato_venta',
        'unidad_minima_venta',
        'ancho_especial',
        'agreement_dangerous_road_especial',
        'logistica_especial',
        'peso_unidad_minima_venta',
        'tipo_iva',
    ];

    public function casts()
    {
        return [
            'descatalogado' => 'boolean',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }
}

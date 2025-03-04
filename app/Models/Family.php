<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Family extends Model
{
    protected $fillable = [
        'codigo_proveedor',
        'marca_comercial',
        'caracteristicas',
        'nombre_familia',
        'procesado_con_ia',
        'descripcion_corta',
        'descripcion_larga',
        'meta_titulo',
        'meta_descripcion',
        'necesita_revision_manual',
    ];

    protected function casts(): array
    {
        return [
            'procesado_con_ia' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}

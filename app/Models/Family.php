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
        'processed_with_ai',
        'descripcion_corta',
        'descripcion_larga',
        'meta_titulo',
        'meta_descripcion',
    ];

    protected function casts(): array
    {
        return [
            'processed_with_ai' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}

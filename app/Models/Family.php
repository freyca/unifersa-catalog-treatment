<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Family extends Model
{
    protected $fillable = [
        'codigo_familia',
        'descripcion',
        'codigo_padre',
        'nombre_variantes',
    ];

    protected function casts(): array
    {
        return [
            'procesado_con_ia' => 'boolean',
            'necesita_revision_manual' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}

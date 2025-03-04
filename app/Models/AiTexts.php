<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AiTexts extends Model
{
    protected $fillable = [
        'meta_titulo',
        'meta_descripcion',
        'descripcion_corta',
        'descripcion_larga',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AiTexts extends Model
{
    protected $fillable = [
        'meta_titulo',
        'meta_descripcion',
        'descripcion_corta',
        'descripcion_larga',
    ];

    public function variant(): HasOne
    {
        return $this->hasOne(Variant::class);
    }
}

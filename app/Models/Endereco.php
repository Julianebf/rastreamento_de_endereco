<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Endereco extends Model
{
    protected $fillable = [
        'cep_id',
        'numero',
        'ponto_referencia'
    ];

    public function cep(): BelongsTo
    {
        return $this->belongsTo(Cep::class);
    }
}
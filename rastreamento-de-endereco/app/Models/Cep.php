<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cep extends Model
{
    protected $fillable = [
        'cep',
        'logradouro',
        'bairro',
        'cidade',
        'uf'
    ];

    // Um CEP pode ter vários números/endereços cadastrados
    public function enderecos(): HasMany
    {
        return $this->hasMany(Endereco::class);
    }
}
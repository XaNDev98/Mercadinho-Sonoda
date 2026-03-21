<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Categoria;

class Produto extends Model
{
    protected $table = 'produtos';

    protected $fillable = [
        'id',
        'nome',
        'preco',
        'estoque',
        'categoria_id',
        'imagem',
        'ativo',
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'estoque' => 'integer',
        'ativo' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
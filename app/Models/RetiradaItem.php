<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetiradaItem extends Model
{
    protected $table = 'retirada_itens';

    protected $fillable = [
        'retirada_id',
        'produto_id',
        'quantidade',
        'valor_unitario',
        'subtotal'
    ];

    public function retirada()
    {
        return $this->belongsTo(Retirada::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
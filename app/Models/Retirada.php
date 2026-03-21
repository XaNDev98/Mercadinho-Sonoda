<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Funcionario;
use App\Models\RetiradaItem;

class Retirada extends Model
{
    protected $table = 'retiradas';

    protected $fillable = [
        'funcionario_id',
        'numero_folha',
        'valor_total',
        'data_hora'
    ];

    protected $casts = [
        'data_hora' => 'datetime',
        'valor_total' => 'decimal:2'
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function itens()
    {
        return $this->hasMany(RetiradaItem::class);
    }

    // 🔥 SOMA TOTAL AUTOMÁTICA (opcional top)
    public function calcularTotal()
    {
        return $this->itens->sum('subtotal');
    }
}
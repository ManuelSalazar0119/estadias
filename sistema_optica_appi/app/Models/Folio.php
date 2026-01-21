<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folio extends Model
{
    protected $table = 'folios';
    protected $primaryKey = 'id_folio';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'folios',
        'total',
        'saldo_nuevo',
        // otros campos si existen
    ];

    public function clienteContrato()
    {
        return $this->belongsTo(ClienteContrato::class, 'id_cliente', 'id_cliente');
    }
}

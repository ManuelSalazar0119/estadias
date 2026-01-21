<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteContrato extends Model
{
    use HasFactory;

    protected $table = 'clientecontrato'; // Nombre de tu tabla

    protected $primaryKey = 'id_cliente'; // Llave primaria

    public $timestamps = false; // Si no usas created_at y updated_at

    // Los campos que puedes llenar con create() o fill()
    protected $fillable = [
        'id_usuario',
        'id_cobrador',
        'id_folio',
        'nombre_cliente',
        'alias_cliente',
        'telefono_cliente',
        'estado_cliente',
        // agrega más si los necesitas
    ];



    public function folio()
    {
        return $this->hasOne(Folio::class, 'id_cliente', 'id_cliente');
    }
}

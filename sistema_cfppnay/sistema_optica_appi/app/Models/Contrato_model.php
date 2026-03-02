<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contrato {
  final int idContrato;
  final String folio;
  final String nombreCliente;
  final String aliasCliente;
  final String estadoCobranza;
  final String? fechaCreacion;
  final double saldoTotal;

  Contrato({
    required this.idContrato,
    required this.folio,
    required this.nombreCliente,
    required this.aliasCliente,
    required this.estadoCobranza,
    this.fechaCreacion,
    required this.saldoTotal,
  });

  factory Contrato.fromJson(Map<String, dynamic> json) {
    return Contrato(
      idContrato: json['id_cliente'],
      folio: json['folio'] ?? 'Sin folio',
      nombreCliente: json['nombre_cliente'] ?? '',
      aliasCliente: json['alias_cliente'] ?? '',
      estadoCobranza: json['estado_cobranza'] ?? '',
      fechaCreacion: json['fecha_creacion'],
      saldoTotal: double.tryParse(json['saldo_total'].toString()) ?? 0.0,
    );
  }
}

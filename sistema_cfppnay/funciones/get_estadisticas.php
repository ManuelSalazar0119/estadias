<?php
include_once("conexion.php");
session_start();

// Verifica login
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

// Recibe parámetros
$id_actividad = isset($_GET['id_actividad']) ? intval($_GET['id_actividad']) : 0;
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : 0; // 0 = todos los meses

// Inicializa arrays
$labels = [];
$valores = [];

if ($mes > 0) {
    // Estadísticas por mes específico: mostrar días del mes
    $numDias = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
    for ($d = 1; $d <= $numDias; $d++) {
        $labels[] = $d;
        $valores[] = 0;
    }

    $sql = "
        SELECT DAY(fecha_registro) as dia, COUNT(*) as total
        FROM registros_actividad
        WHERE id_actividad = $id_actividad
          AND YEAR(fecha_registro) = $anio
          AND MONTH(fecha_registro) = $mes
        GROUP BY dia
        ORDER BY dia
    ";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $valores[$row['dia'] - 1] = intval($row['total']);
    }

} else {
    // Estadísticas por todo el año: mostrar meses
    $meses = [
        1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
        5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
        9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'
    ];

    $labels = array_values($meses);
    $valores = array_fill(0, 12, 0);

    $sql = "
        SELECT MONTH(fecha_registro) as mes, COUNT(*) as total
        FROM registros_actividad
        WHERE id_actividad = $id_actividad
          AND YEAR(fecha_registro) = $anio
        GROUP BY mes
        ORDER BY mes
    ";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $valores[$row['mes'] - 1] = intval($row['total']);
    }
}

// Devuelve JSON
header('Content-Type: application/json');
echo json_encode([
    "labels" => $labels,
    "valores" => $valores
]);

<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
require_once("../funciones/conexion.php");
require_once("../funciones/funciones_reportes.php");
ini_set('display_errors', 1);

$id_area = intval($_GET['id_area'] ?? 0);
$anio = intval($_GET['anio'] ?? date('Y'));
$mes  = intval($_GET['mes'] ?? date('m'));

if (!$id_area) {
    echo json_encode([]);
    exit;
}

// Obtener programaciones mensuales con su grupo
$sql = "SELECT pm.id_actividad, gad.id_grupo, pm.unidad, pm.programado, a.nombre_actividad
        FROM programacion_mensual pm
        INNER JOIN actividades a ON pm.id_actividad = a.id_actividad
        INNER JOIN grupo_actividad_detalle gad ON gad.id_actividad = pm.id_actividad
        WHERE a.id_area = ?
          AND pm.anio = ?
          AND pm.mes = ?
        ORDER BY a.nombre_actividad, pm.unidad";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $id_area, $anio, $mes);
$stmt->execute();
$res = $stmt->get_result();
$actividades = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$resultado = [];

foreach ($actividades as $act) {
    $idActividad = intval($act['id_actividad']);
    $idGrupo = intval($act['id_grupo']);
    $unidad = $act['unidad'];

    // Obtener lo realizado según unidad
    if (strtolower($unidad) === 'cabezas') {
        $realizado = obtenerCabezas($conn, $idActividad, $anio, $mes);
    } else {
        $realizado = obtenerUPPs($conn, $idActividad, $anio, $mes);
    }

    $programado = intval($act['programado']);

    // 🚩 FILTRAR: solo si programado ≠ realizado
    if ($programado !== $realizado) {
        // Buscar justificación
        $stmtJ = $conn->prepare("SELECT justificacion 
                                 FROM justificaciones_mensuales 
                                 WHERE id_grupo = ? AND anio = ? AND mes = ? 
                                 LIMIT 1");
        $stmtJ->bind_param("iii", $idGrupo, $anio, $mes);
        $stmtJ->execute();
        $resJ = $stmtJ->get_result();
        $justificacion = $resJ->num_rows > 0 ? ($resJ->fetch_assoc()['justificacion'] ?? '') : '';
        $stmtJ->close();

        $resultado[] = [
            'id_actividad'     => $idActividad,
            'id_grupo'         => $idGrupo,
            'nombre_actividad' => $act['nombre_actividad'],
            'unidad'           => $unidad,
            'programado'       => $programado,
            'realizado'        => $realizado,
            'justificacion'    => $justificacion
        ];
    }
}

echo json_encode($resultado);
exit;

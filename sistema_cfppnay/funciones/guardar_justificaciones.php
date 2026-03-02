<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
require_once("../funciones/conexion.php");
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/guardar_justificaciones_debug.log');

// Leer datos JSON
$data = json_decode(file_get_contents("php://input"), true);
$anio = intval($data['anio'] ?? 0);
$mes  = intval($data['mes'] ?? 0);
$justificaciones = $data['justificaciones'] ?? [];

$resultados = [];

if ($anio && $mes && !empty($justificaciones)) {

    foreach ($justificaciones as $item) {
        $id_grupo = intval($item['id_grupo'] ?? 0);
        $unidad = trim($item['unidad'] ?? '');
        $justificacion = trim($item['justificacion'] ?? '');

        if ($id_grupo && $unidad !== '') {
            // Normalizar unidad para evitar problemas de mayúsculas/minúsculas
            $unidad_norm = mb_strtolower($unidad, 'UTF-8');

            error_log("Insertando: id_grupo=$id_grupo, anio=$anio, mes=$mes, unidad=$unidad_norm, justificacion=$justificacion");

            $stmt = $conn->prepare("
                INSERT INTO justificaciones_mensuales (id_grupo, anio, mes, unidad, justificacion)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    justificacion = VALUES(justificacion),
                    actualizado_en = CURRENT_TIMESTAMP
            ");
            $stmt->bind_param("iiiss", $id_grupo, $anio, $mes, $unidad_norm, $justificacion);
            $stmt->execute();
            $stmt->close();

            $resultados[] = [
                'id_grupo' => $id_grupo,
                'unidad' => $unidad,
                'justificacion' => $justificacion
            ];
        }
    }

    // Verificar lo que quedó guardado
    $sql = "SELECT id_grupo, unidad, justificacion, creado_en, actualizado_en
            FROM justificaciones_mensuales
            WHERE anio = ? AND mes = ?";
    $stmtSel = $conn->prepare($sql);
    $stmtSel->bind_param("ii", $anio, $mes);
    $stmtSel->execute();
    $resSel = $stmtSel->get_result();
    $guardados = $resSel->fetch_all(MYSQLI_ASSOC);
    $stmtSel->close();

    echo json_encode([
        'success' => true,
        'mensaje' => 'Se guardaron ' . count($resultados) . ' justificaciones (incluyendo vacías).',
        'enviados' => $resultados,
        'guardados' => $guardados
    ]);
    exit;
}

echo json_encode([
    'success' => false,
    'mensaje' => 'No se recibieron datos válidos.',
    'enviados' => $justificaciones
]);
exit;

<?php
include_once("../funciones/conexion.php");

$tipo = $_GET['tipo'] ?? 'anual';
$anio = (int)($_GET['anio'] ?? date("Y"));
$mes  = isset($_GET['mes']) ? (int)$_GET['mes'] : null;

// 1️⃣ Actividades ya registradas
$programadas = [];
if ($tipo === 'anual') {
    $sql = "SELECT pa.id_actividad, pa.unidad, pa.programado, a.nombre_actividad
            FROM programacion_anual pa
            INNER JOIN actividades a ON pa.id_actividad = a.id_actividad
            WHERE pa.anio = ?
            ORDER BY a.nombre_actividad";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $anio);
    $stmt->execute();
    $res = $stmt->get_result();
    $programadas = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} elseif ($tipo === 'mensual' && $mes) {
    $sql = "SELECT pm.id_actividad, pm.unidad, pm.programado, a.nombre_actividad
            FROM programacion_mensual pm
            INNER JOIN actividades a ON pm.id_actividad = a.id_actividad
            WHERE pm.anio = ? AND pm.mes = ?
            ORDER BY a.nombre_actividad";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $anio, $mes);
    $stmt->execute();
    $res = $stmt->get_result();
    $programadas = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// 2️⃣ Actividades faltantes
$idsRegistradas = array_column($programadas, 'id_actividad');
$idsRegistradasStr = implode(",", $idsRegistradas ?: [0]);

$sql = "SELECT id_actividad, nombre_actividad 
        FROM actividades 
        WHERE id_actividad NOT IN ($idsRegistradasStr)
        ORDER BY nombre_actividad";
$result = $conn->query($sql);
$faltantes = $result->fetch_all(MYSQLI_ASSOC);

// 3️⃣ Generar tbody
foreach ($programadas as $r) {
    echo '<tr class="programada">';
    echo '<td>' . htmlspecialchars($r['nombre_actividad']) . '</td>';
    echo '<td><input type="text" name="unidad['.$r['id_actividad'].'][]" value="'.htmlspecialchars($r['unidad']).'"></td>';
    echo '<td><input type="number" name="programado['.$r['id_actividad'].'][]" value="'.$r['programado'].'" min="0"></td>';
    echo '</tr>';
}

foreach ($faltantes as $f) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($f['nombre_actividad']) . '</td>';
    echo '<td><input type="text" name="unidad['.$f['id_actividad'].'][]" value="" placeholder="Ej: UPP"></td>';
    echo '<td><input type="number" name="programado['.$f['id_actividad'].'][]" value="" min="0"></td>';
    echo '</tr>';
}

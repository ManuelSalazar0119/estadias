<?php
// funciones/exportar_excel.php - VERSIÓN COMPLETA Y CORREGIDA
include_once("conexion.php");
header('Content-Type: text/html; charset=utf-8');
session_start();

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    die("Acceso no autorizado");
}

// Obtener parámetros de filtro - ESENCIALES
$id_actividad = isset($_GET['id_actividad']) ? intval($_GET['id_actividad']) : 0;
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : 0;

// Validar que haya una actividad seleccionada
if ($id_actividad == 0) {
    die("Error: No se ha seleccionado una actividad para exportar.");
}

// Obtener nombre de la actividad
$nombre_actividad = "Actividad";
$res_nombre = $conn->query("SELECT nombre_actividad FROM actividades WHERE id_actividad = $id_actividad");
if ($row_nombre = $res_nombre->fetch_assoc()) {
    $nombre_actividad = $row_nombre['nombre_actividad'];
}

// Obtener la MISMA consulta que en panel_control.php - CON LOS MISMOS FILTROS
$sql = "
SELECT 
    r.id_registro,
    DATE_FORMAT(r.fecha_registro, '%Y-%m-%d') as fecha_formateada,
    a.nombre_actividad,
    u.nombre_usuario,
    c.nombre_campo_actividad,
    v.valor
FROM registros_actividad r
JOIN actividades a ON r.id_actividad = a.id_actividad
JOIN usuarios u ON r.id_usuario = u.id_usuario
JOIN valores_actividad v ON r.id_registro = v.id_registro
JOIN campos_actividad c ON v.id_camposA = c.id_camposA
WHERE r.id_actividad = $id_actividad
AND YEAR(r.fecha_registro) = $anio
";

if ($mes > 0) {
    $sql .= " AND MONTH(r.fecha_registro) = $mes";
}

$sql .= "
ORDER BY r.fecha_registro DESC, r.id_registro, c.id_camposA
";

// DEPURACIÓN: Verificar la consulta
error_log("EXPORT EXCEL - SQL: " . $sql);
error_log("EXPORT EXCEL - Filtros: actividad=$id_actividad, año=$anio, mes=$mes");

$res = $conn->query($sql);

if (!$res) {
    die("Error en la consulta: " . $conn->error);
}

// Armar el arreglo de registros EXACTAMENTE como en panel_control.php
$registros = [];
$campos_unicos = [];

while ($row = $res->fetch_assoc()) {
    $id = $row['id_registro'];
    if (!isset($registros[$id])) {
        $registros[$id] = [
            'fecha' => $row['fecha_formateada'],
            'actividad' => $row['nombre_actividad'],
            'usuario' => $row['nombre_usuario'],
            'campos' => []
        ];
    }
    $registros[$id]['campos'][$row['nombre_campo_actividad']] = $row['valor'];
    
    // Recopilar campos únicos
    if (!in_array($row['nombre_campo_actividad'], $campos_unicos)) {
        $campos_unicos[] = $row['nombre_campo_actividad'];
    }
}

// DEPURACIÓN: Verificar cuántos registros se obtuvieron
error_log("EXPORT EXCEL - Registros obtenidos: " . count($registros));

// Si no hay datos
if (empty($registros)) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<h2>No hay datos para exportar con los filtros seleccionados</h2>";
    echo "<p><strong>Filtros aplicados:</strong></p>";
    echo "<ul>";
    echo "<li>Actividad: " . htmlspecialchars($nombre_actividad) . " (ID: $id_actividad)</li>";
    echo "<li>Año: $anio</li>";
    echo "<li>Mes: " . ($mes > 0 ? $mes : 'Todos') . "</li>";
    echo "</ul>";
    echo "<p><a href='javascript:history.back()'>← Volver al panel</a></p>";
    exit;
}

// Configurar nombre del archivo
$nombre_safe = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $nombre_actividad);
$nombre_archivo = "export_" . $nombre_safe . "_" . 
                 ($mes > 0 ? sprintf("%02d", $mes) . "_" : "") . 
                 $anio . "_" . date('Y-m-d') . ".xls";

// Configurar headers para Excel
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Generar Excel
echo "<html>";
echo "<head>";
echo "<meta charset=\"UTF-8\">";
echo "<style>
    table {
        border-collapse: collapse;
        width: 100%;
        font-family: Arial, sans-serif;
        font-size: 12px;
    }
    th {
        background-color: #2c3e50;
        color: white;
        font-weight: bold;
        padding: 8px;
        text-align: center;
        border: 1px solid #ddd;
        white-space: nowrap;
    }
    td {
        padding: 6px;
        border: 1px solid #ddd;
        text-align: left;
        vertical-align: top;
    }
    tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    .header-info {
        background-color: #f1f8e9;
        padding: 10px;
        margin-bottom: 15px;
        border-left: 4px solid #4CAF50;
        font-size: 11px;
    }
</style>";
echo "</head>";
echo "<body>";

// Información del encabezado
echo "<div class='header-info'>";
echo "<strong>INFORMACIÓN DE EXPORTACIÓN</strong><br>";
echo "Actividad: " . htmlspecialchars($nombre_actividad) . "<br>";
echo "Año: " . $anio . "<br>";
echo "Mes: " . ($mes > 0 ? date('F', mktime(0, 0, 0, $mes, 1)) : 'Todos') . "<br>";
echo "Total registros exportados: " . count($registros) . "<br>";
echo "Fecha de exportación: " . date('Y-m-d H:i:s');
echo "</div>";

echo "<table>";
echo "<tr>";

// Encabezados fijos
echo "<th>FECHA</th>";
echo "<th>ACTIVIDAD</th>";
echo "<th>USUARIO</th>";

// Encabezados dinámicos (campos específicos de la actividad)
foreach ($campos_unicos as $campo) {
    echo "<th>" . strtoupper(htmlspecialchars($campo)) . "</th>";
}
echo "</tr>";

// Datos de las filas
foreach ($registros as $id => $registro) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($registro['fecha']) . "</td>";
    echo "<td>" . htmlspecialchars($registro['actividad']) . "</td>";
    echo "<td>" . htmlspecialchars($registro['usuario']) . "</td>";
    
    // Campos dinámicos
    foreach ($campos_unicos as $campo) {
        $valor = $registro['campos'][$campo] ?? '-';
        // Formatear números grandes (como UPP) para que Excel los muestre correctamente
        if (is_numeric($valor) && strlen($valor) > 10) {
            echo "<td style=\"mso-number-format:'\\@';\">" . htmlspecialchars($valor) . "</td>";
        } else {
            echo "<td>" . htmlspecialchars($valor) . "</td>";
        }
    }
    echo "</tr>";
}

echo "</table>";

// Pie de página
echo "<div style='margin-top: 20px; padding: 10px; border-top: 1px solid #ddd; font-size: 10px; color: #666;'>";
echo "Sistema de Control - Exportado por: " . ($_SESSION['nombre_usuario'] ?? 'Usuario') . " - " . date('Y-m-d H:i:s');
echo "</div>";

echo "</body>";
echo "</html>";

exit();
?>
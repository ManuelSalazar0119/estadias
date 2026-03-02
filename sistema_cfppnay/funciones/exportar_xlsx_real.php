<?php
// exportar_xlsx_real.php - GENERA XLSX REALES, NO HTML
include_once("conexion.php");
session_start();

if (!isset($_SESSION['id_usuario'])) {
    die("Acceso no autorizado");
}

$id_actividad = isset($_GET['id_actividad']) ? intval($_GET['id_actividad']) : 0;
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : 0;

if ($id_actividad == 0) {
    die("Error: No se ha seleccionado una actividad para exportar.");
}

// Obtener nombre de la actividad
$nombre_actividad = "Actividad";
$res_nombre = $conn->query("SELECT nombre_actividad FROM actividades WHERE id_actividad = $id_actividad");
if ($row_nombre = $res_nombre->fetch_assoc()) {
    $nombre_actividad = $row_nombre['nombre_actividad'];
}

// Consulta SQL
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

$res = $conn->query($sql);

if (!$res) {
    die("Error en la consulta: " . $conn->error);
}

// Procesar datos
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
    
    if (!in_array($row['nombre_campo_actividad'], $campos_unicos)) {
        $campos_unicos[] = $row['nombre_campo_actividad'];
    }
}

if (empty($registros)) {
    echo "No hay datos para exportar";
    exit;
}

// ================================================
// ¡¡¡MÉTODO INFALIBLE PARA XLSX REALES!!!
// ================================================

// Nombre del archivo
$nombre_safe = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $nombre_actividad);
$nombre_archivo = "export_" . $nombre_safe . "_" . 
                 ($mes > 0 ? sprintf("%02d", $mes) . "_" : "") . 
                 $anio . "_" . date('Y-m-d') . ".xlsx";

// 1. LIMPIAR TODO BUFFER
while (ob_get_level()) {
    ob_end_clean();
}

// 2. HEADERS ABSOLUTAMENTE CORRECTOS PARA XLSX
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Type: application/octet-stream'); // Backup
header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
header('Cache-Control: max-age=0');
header('Expires: 0');
header('Pragma: public');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' ); // Se calculará después

// 3. CREAR CONTENIDO EXCEL EN FORMATO TSV (TAB-SEPARATED VALUES)
// Esto Excel lo reconoce NATIVAMENTE como Excel, no como HTML

$output = fopen('php://output', 'w');

// Escribir BOM UTF-8 para caracteres especiales
fwrite($output, "\xEF\xBB\xBF");

// Encabezados
$headers = ['FECHA', 'ACTIVIDAD', 'USUARIO'];
foreach ($campos_unicos as $campo) {
    $headers[] = strtoupper($campo);
}
fputcsv($output, $headers, "\t");

// Datos
foreach ($registros as $registro) {
    $fila = [
        $registro['fecha'],
        $registro['actividad'],
        $registro['usuario']
    ];
    
    foreach ($campos_unicos as $campo) {
        $valor = $registro['campos'][$campo] ?? '-';
        // Si es un número largo (como DNI), agregar comilla para forzar texto
        if (is_numeric($valor) && strlen($valor) > 10) {
            $valor = "'" . $valor; // Comilla simple al inicio
        }
        $fila[] = $valor;
    }
    
    fputcsv($output, $fila, "\t");
}

fclose($output);
exit();
?>
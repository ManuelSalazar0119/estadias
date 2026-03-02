<?php
include_once("conexion.php");
require '../funciones/vendor/autoload.php';
use setasign\Fpdi\Fpdi;
$pdf = new Fpdi();
session_start();

// Verifica login
if (!isset($_SESSION['id_usuario'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit("No autorizado");
}


// Recibe parámetros
$id_actividad = isset($_GET['id_actividad']) ? intval($_GET['id_actividad']) : 0;
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : 0;

if ($id_actividad <= 0) {
    exit("Actividad inválida");
}

// Construir query
$sql = "
    SELECT p.ruta_pdf, r.fecha_registro
    FROM registro_pdfs p
    JOIN registros_actividad r ON p.id_registro = r.id_registro
    WHERE r.id_actividad = $id_actividad
      AND YEAR(r.fecha_registro) = $anio
";

if ($mes > 0) {
    $sql .= " AND MONTH(r.fecha_registro) = $mes";
}

$sql .= " ORDER BY r.fecha_registro ASC";

$res = $conn->query($sql);

$pdfFiles = [];
while ($row = $res->fetch_assoc()) {
    if (!empty($row['ruta_pdf']) && file_exists("../registro_PDFs/" . $row['ruta_pdf'])) {
        $pdfFiles[] = "../registro_PDFs/" . $row['ruta_pdf'];
    }
}

if (empty($pdfFiles)) {
    exit("No se encontraron PDFs para esta actividad/mes.");
}

// Crear PDF final
$pdf = new Fpdi();

foreach ($pdfFiles as $file) {
    $pageCount = $pdf->setSourceFile($file);
    for ($i = 1; $i <= $pageCount; $i++) {
        $tpl = $pdf->importPage($i);
        $size = $pdf->getTemplateSize($tpl);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tpl);
    }
}

// Nombre del archivo de descarga
$actividadName = "actividad_$id_actividad";
$mesName = ($mes > 0) ? "_mes_$mes" : "";
$filename = "PDFs_$actividadName$mesName.pdf";

// Forzar descarga
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$pdf->Output('D', $filename);
exit;

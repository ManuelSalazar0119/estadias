<?php
// descargar_pdf.php
require_once("../funciones/conexion.php"); // Ajusta ruta a tu conexión DB
session_start();

// -----------------------------
// 1. Validar parámetro
// -----------------------------
if (!isset($_GET['id_registro']) || !is_numeric($_GET['id_registro'])) {
    die("ID inválido.");
}

$id_registro = (int) $_GET['id_registro'];

// ----- DEPURACIÓN: mostrar el ID recibido -----
error_log("[DEBUG] ID recibido: $id_registro");

// -----------------------------
// 2. Consultar ruta en la BD
// -----------------------------
$sql = "SELECT ruta_pdf FROM registro_pdfs WHERE id_registro = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_registro);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

// ----- DEPURACIÓN: mostrar resultado de la BD -----
error_log("[DEBUG] Resultado BD: " . print_r($result, true));

if (!$result || empty($result['ruta_pdf'])) {
    die("Archivo no encontrado en la BD.");
}

// -----------------------------
// 3. Construir ruta completa
// -----------------------------
$directorio = dirname(__DIR__) . '/registro_PDFs/'; // un nivel arriba de interfaces/ o funciones/
$archivo = $directorio . $result['ruta_pdf'];

// ----- DEPURACIÓN: mostrar la ruta final que PHP intentará abrir -----
error_log("[DEBUG] Intentando abrir archivo en: $archivo");

// -----------------------------
// 4. Verificar si el archivo existe
// -----------------------------
if (!file_exists($archivo)) {
    error_log("[ERROR] Archivo NO encontrado en: $archivo");
    die("Archivo no encontrado en: $archivo");
} else {
    error_log("[DEBUG] Archivo encontrado correctamente");
}

// -----------------------------
// 5. Forzar descarga/visualización
// -----------------------------
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($archivo) . '"');
header('Content-Length: ' . filesize($archivo));
readfile($archivo);
exit;

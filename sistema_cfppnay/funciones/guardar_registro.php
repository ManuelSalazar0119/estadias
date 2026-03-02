<?php
session_start();
// CAMBIO NECESARIO: Ya que este archivo está dentro de la carpeta 'funciones', 
// no necesita salir de ella para encontrar conexion.php.
include_once("conexion.php"); 

$id_usuario = $_SESSION['id_usuario'];
$id_actividad = intval($_POST['id_actividad']);
$fecha = $_POST['fecha_registro'];
$id_zona = intval($_POST['id_zona']);
$id_municipio = intval($_POST['id_municipio']);
$observaciones = $_POST['observaciones_registro'] ?? "";

// Guardar en registros_actividad
$sql_reg = "INSERT INTO registros_actividad (id_actividad, id_usuario, fecha_registro, id_zona, id_municipio, observaciones_registro)
            VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql_reg);
$stmt->bind_param("iisiis", $id_actividad, $id_usuario, $fecha, $id_zona, $id_municipio, $observaciones);
$stmt->execute();
$id_registro = $stmt->insert_id;

// Guardar campos dinámicos
foreach ($_POST as $key => $value) {
    if (strpos($key, 'campo_') === 0) {
        $id_camposA = intval(substr($key, 6));
        $sql_val = "INSERT INTO valores_actividad (id_registro, id_camposA, valor) VALUES (?, ?, ?)";
        $stmt_val = $conn->prepare($sql_val);
        $stmt_val->bind_param("iis", $id_registro, $id_camposA, $value);
        $stmt_val->execute();
    }
}


if (isset($_FILES['archivo_pdf']) && $_FILES['archivo_pdf']['error'] === UPLOAD_ERR_OK) {
    $directorio = "../registro_PDFs/";

    // Crear carpeta si no existe
    if (!is_dir($directorio)) {
        mkdir($directorio, 0775, true);
    }

    // Validar tipo de archivo
    $tipoArchivo = mime_content_type($_FILES['archivo_pdf']['tmp_name']);
    if ($tipoArchivo !== 'application/pdf') {
        die("Error: El archivo debe ser un PDF válido.");
    }

    // Validar tamaño (ej: máximo 5 MB)
    $tamanioMax = 5 * 1024 * 1024;
    if ($_FILES['archivo_pdf']['size'] > $tamanioMax) {
        die("Error: El archivo excede el tamaño máximo permitido (5 MB).");
    }

    // Generar nombre único
    $nombreArchivo = "registro_" . $id_registro . "_" . time() . ".pdf";
    $rutaDestino = $directorio . $nombreArchivo;

    // --- VERIFICAR SI YA EXISTE UN PDF PARA ESTE REGISTRO ---
    $sql_check = "SELECT ruta_pdf FROM registro_pdfs WHERE id_registro = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id_registro);
    $stmt_check->execute();
    $resultado = $stmt_check->get_result()->fetch_assoc();

    if ($resultado) {
        // Si existe, eliminar archivo anterior
        $rutaAnterior = $directorio . $resultado['ruta_pdf'];
        if (file_exists($rutaAnterior)) {
            unlink($rutaAnterior);
        }

        // Actualizar referencia en BD
        $sql_pdf = "UPDATE registro_pdfs SET ruta_pdf = ?, fecha_subida = NOW() WHERE id_registro = ?";
        $stmt_pdf = $conn->prepare($sql_pdf);
        $stmt_pdf->bind_param("si", $nombreArchivo, $id_registro);
    } else {
        // Si no existe, insertar nuevo
        $sql_pdf = "INSERT INTO registro_pdfs (id_registro, ruta_pdf) VALUES (?, ?)";
        $stmt_pdf = $conn->prepare($sql_pdf);
        $stmt_pdf->bind_param("is", $id_registro, $nombreArchivo);
    }

    // --- MOVER ARCHIVO Y GUARDAR EN BD ---
    if (move_uploaded_file($_FILES['archivo_pdf']['tmp_name'], $rutaDestino)) {
        $stmt_pdf->execute();
    } else {
        die("Error: No se pudo guardar el archivo PDF.");
    }
}

// Redirección al finalizar
header("Location: ../interfaces/nuevo_registro.php?ok=1");
exit;
?>
<?php
// Conexión a la BD
$conexion = new mysqli("localhost", "root", "", "cefppenay");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

session_start(); 
$id_usuario = $_SESSION['id_usuario'] ?? 1; // Temporal: usuario 1 si no hay login

// Validar que recibimos el id de la actividad
if (!isset($_POST['id_actividad'])) {
    die("No se especificó actividad.");
}

$id_actividad = intval($_POST['id_actividad']);

// 1. Crear registro principal en actividad_registros
$stmt = $conexion->prepare("INSERT INTO registros_actividad (id_actividad, id_usuario, fecha_registro) VALUES (?, ?, NOW())");
$stmt->bind_param("ii", $id_actividad, $id_usuario);
$stmt->execute();
$registro_id = $stmt->insert_id;
$stmt->close();

// 2. Obtener campos esperados para la actividad
$sql_campos = "SELECT id_camposA, nombre_campo_actividad FROM campos_actividad WHERE id_actividad = ?";
$stmt_campos = $conexion->prepare($sql_campos);
$stmt_campos->bind_param("i", $id_actividad);
$stmt_campos->execute();
$result_campos = $stmt_campos->get_result();

while ($campo = $result_campos->fetch_assoc()) {
    $campo_id = $campo['id_camposA'];
    $nombre_campo = $campo['nombre_campo_actividad'];

    // El valor viene del POST con el nombre del campo como clave
    $valor = $_POST[$nombre_campo] ?? '';

    // Guardar en actividad_valores
    $stmt_valor = $conexion->prepare("INSERT INTO valores_actividad (id_registro, id_camposA, valor) VALUES (?, ?, ?)");
    $stmt_valor->bind_param("iis", $registro_id, $campo_id, $valor);
    $stmt_valor->execute();
    $stmt_valor->close();
}

$stmt_campos->close();

echo "✅ Actividad registrada correctamente.";
$conexion->close();
?>

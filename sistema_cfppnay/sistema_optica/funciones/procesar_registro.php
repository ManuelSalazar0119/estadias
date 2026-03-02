<?php
session_start();
include('conexion.php');

// Aseguramos la zona horaria correcta (ajusta según tu región)
date_default_timezone_set('America/Mexico_City'); // Aquí ajusta la zona horaria según tu región

if (!isset($_SESSION['id_usuario'])) {
    echo "Error: Usuario no autenticado.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['id_usuario'];
    $latitud = $_POST['latitud'] ?? '';
    $longitud = $_POST['longitud'] ?? '';
    $fecha_hora_utc = $_POST['fecha_hora'] ?? '';

    if ($latitud && $longitud && $fecha_hora_utc) {
        // Convertir la fecha recibida a la zona horaria correcta
        $fecha_hora = new DateTime($fecha_hora_utc, new DateTimeZone('UTC')); // fecha en UTC
        $fecha_hora->setTimezone(new DateTimeZone('America/Mexico_City')); // Convertimos a la zona horaria de México (ajusta según sea necesario)
        $fecha_hora = $fecha_hora->format('Y-m-d H:i:s'); // Convertir a formato de fecha y hora

        $sql = "INSERT INTO registros_entradas (id_usuario, latitud, longitud, fecha_hora) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("idds", $id_usuario, $latitud, $longitud, $fecha_hora);

        if ($stmt->execute()) {
            echo "Entrada registrada correctamente.";
        } else {
            echo "Error al registrar la entrada.";
        }
        $stmt->close();
    } else {
        echo "Datos incompletos.";
    }
} else {
    echo "Método no permitido.";
}
?>

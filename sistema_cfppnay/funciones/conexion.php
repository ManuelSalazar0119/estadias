<?php
$servername = "127.0.0.1"; // Cambiamos localhost por la IP directa
$username = "root"; // Por defecto en XAMPP
$password = ""; // Por defecto en XAMPP
$dbname = "cefppenay";
$port = 3307; // Agregamos el puerto correcto de tu MySQL

// Crear conexión (Nota que agregamos $port al final)
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>
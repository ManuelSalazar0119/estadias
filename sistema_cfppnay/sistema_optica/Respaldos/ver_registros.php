<?php
session_start();
include('../funciones/conexion.php');

if (!isset($_SESSION['id_usuario'])) {
    echo "Error: Usuario no autenticado.";
    exit();
}

$sql = "SELECT r.id, u.nombre_usuario, r.latitud, r.longitud, r.fecha_hora FROM registros_entradas r
        INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
        ORDER BY r.fecha_hora DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros de Entradas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
    <h2>Registros de Entradas</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Latitud</th>
            <th>Longitud</th>
            <th>Fecha y Hora</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['nombre_usuario']); ?></td>
                <td><?php echo htmlspecialchars($row['latitud']); ?></td>
                <td><?php echo htmlspecialchars($row['longitud']); ?></td>
                <td><?php echo htmlspecialchars($row['fecha_hora']); ?></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>

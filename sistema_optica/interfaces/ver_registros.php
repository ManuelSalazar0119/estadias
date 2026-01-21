<?php 
include('header.php'); 
setlocale(LC_TIME, 'es_ES.UTF-8'); // Establece la localización en español
?>
<?php
session_start();
include('../funciones/conexion.php');

if (!isset($_SESSION['id_usuario'])) {
    echo "Error: Usuario no autenticado.";
    exit();
}

// Obtener la fecha seleccionada o la de hoy
$fecha_filtro = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

$sql = "SELECT r.id, u.nombre_usuario, r.latitud, r.longitud, r.fecha_hora 
        FROM registros_entradas r
        INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
        WHERE DATE(r.fecha_hora) = ?
        ORDER BY r.fecha_hora DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $fecha_filtro);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros de Entradas</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
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
    /* Contenedor con scroll horizontal */
    .table-container {
        max-width: 100%; /* Ancho máximo para el contenedor */
        overflow-x: auto;  /* Habilita el scroll horizontal */
        -webkit-overflow-scrolling: touch; /* Mejora el scroll en dispositivos móviles */
    }
    #map {
        height: 400px;
        display: none;
        margin-top: 20px;
    }
</style>

</head>
<body>
    <h2>Registros de Entradas</h2>
    <p style="color:Green">Fecha actual: <?php echo strftime("%A %d de %B de %Y"); ?></p>
    <label for="fecha">Filtrar por fecha:</label>
    <input type="date" id="fecha" value="<?php echo $fecha_filtro; ?>">
    <button onclick="filtrarPorFecha()">Filtrar</button>

    <div class="table-container">
        <table>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Latitud</th>
                <th>Longitud</th>
                <th>Fecha y Hora</th>
                <th>Acci贸n</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['nombre_usuario']); ?></td>
                    <td><?php echo htmlspecialchars($row['latitud']); ?></td>
                    <td><?php echo htmlspecialchars($row['longitud']); ?></td>
                    <td><?php echo htmlspecialchars($row['fecha_hora']); ?></td>
                    <td>
                        <button onclick="verUbicacion(<?php echo $row['latitud']; ?>, <?php echo $row['longitud']; ?>)">Ver Ubicaci贸n</button>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
    
    <div id="map"></div>
    
    <script>
        function filtrarPorFecha() {
            const fecha = document.getElementById('fecha').value;
            window.location.href = "ver_registros.php?fecha=" + fecha;
        }

        function verUbicacion(lat, lon) {
            document.getElementById('map').style.display = 'block';
            var map = L.map('map').setView([lat, lon], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
            }).addTo(map);
            L.marker([lat, lon]).addTo(map).bindPopup('Ubicaci贸n del registro').openPopup();
        }
    </script>
</body>
</html>

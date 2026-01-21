<?php
include_once("../funciones/conexion.php");
session_start();

// Verifica login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /login.php");
    exit;
}

$rol = $_SESSION['rol'] ?? 'medico';
$nombre = $_SESSION['nombre_usuario'] ?? 'Usuario';

// Traer actividades
$actividades = [];
$res = $conn->query("SELECT id_actividad, nombre_actividad FROM actividades ORDER BY nombre_actividad");
while ($row = $res->fetch_assoc()) {
    $actividades[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas de Actividades</title>
    <link rel="stylesheet" href="../css/estadisticas.css?v=<?php echo rand(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="parent">
    <!-- Sidebar -->
    <div class="div1">
        <?php include_once("sidebar.php"); ?>
    </div>

    <!-- Header -->
    <div class="div2">
        <h1 style="margin:10px;">Panel de Estadísticas</h1>
    </div>

    <!-- Contenido -->
    <div class="div3">
        <div class="estadisticas-container">
            <h2>Estadísticas de Actividades</h2>

            <!-- Filtros -->
            <div class="filtros">
                <label for="actividad">Actividad:</label>
                <select id="actividad">
                    <?php foreach ($actividades as $act): ?>
                        <option value="<?= $act['id_actividad'] ?>"><?= htmlspecialchars($act['nombre_actividad']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="anio">Año:</label>
                <select id="anio">
                    <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                        <option value="<?= $y ?>"><?= $y ?></option>
                    <?php endfor; ?>
                </select>

                <label for="mes">Mes:</label>
                <select id="mes">
                    <option value="0">Todos</option>
                    <?php
                    $meses = [
                        1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
                        5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
                        9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'
                    ];
                    foreach ($meses as $num=>$nombre_mes): ?>
                        <option value="<?= $num ?>"><?= $nombre_mes ?></option>
                    <?php endforeach; ?>
                </select>

                <button id="btnVerEstadisticas">Ver estadísticas</button>
                <button id="btnUnirPDFs">Unir PDFs del mes</button>
            </div>

            <!-- Gráfica -->
            <div class="grafica">
                <canvas id="graficaActividad" width="400" height="200"></canvas>
            </div>

            <!-- Descargas -->
            <div class="descargas">
                <p id="infoDescarga"></p>
            </div>
        </div>
    </div>
</div>

<script>
// Inicializa gráfica
let ctx = document.getElementById('graficaActividad').getContext('2d');
let grafica = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [],
        datasets: [{
            label: 'Cantidad de registros',
            data: [],
            backgroundColor: '#007bff'
        }]
    },
    options: { responsive: true }
});

// Cargar datos
document.getElementById('btnVerEstadisticas').addEventListener('click', function() {
    const id_actividad = document.getElementById('actividad').value;
    const anio = document.getElementById('anio').value;
    const mes = document.getElementById('mes').value;

    fetch(`../funciones/get_estadisticas.php?id_actividad=${id_actividad}&anio=${anio}&mes=${mes}`)
        .then(r => r.json())
        .then(data => {
            grafica.data.labels = data.labels;
            grafica.data.datasets[0].data = data.valores;
            grafica.update();
        });
});

// Descargar PDFs unidos
document.getElementById('btnUnirPDFs').addEventListener('click', function() {
    const id_actividad = document.getElementById('actividad').value;
    const anio = document.getElementById('anio').value;
    const mes = document.getElementById('mes').value;

    window.open(`../funciones/unir_pdfs.php?id_actividad=${id_actividad}&anio=${anio}&mes=${mes}`, '_blank');
});
</script>
</body>
</html>

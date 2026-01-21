<?php
include_once("../funciones/conexion.php");
include_once("../funciones/funciones_reportes.php");
// Verifica si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /login.php');
    exit;
}
$rol = $_SESSION['rol'] ?? 'medico';
$nombre = $_SESSION['nombre_usuario'] ?? 'Usuario';

// Obtener áreas desde la base de datos
$areas = [];
$result_areas = $conn   ->query("SELECT id_area, nombre_area FROM areas");
while ($row = $result_areas->fetch_assoc()) {
    $areas[] = $row;
}
// Determina el área seleccionada (por GET o por defecto)
$id_area = isset($_GET['id_area']) ? intval($_GET['id_area']) : ($areas[0]['id_area'] ?? 0);
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('n');

// Consulta de actividades
$sql_actividades = "SELECT id_actividad, nombre_actividad FROM actividades ORDER BY nombre_actividad";
$result_actividades = $conn->query($sql_actividades);
$actividades = $result_actividades->fetch_all(MYSQLI_ASSOC);

// Cargar programación mensual y anual
$sql_mensual = "SELECT id_actividad, programado, unidad 
                FROM programacion_mensual 
                WHERE anio = ? AND mes = ?";
$stmt = $conn->prepare($sql_mensual);
$stmt->bind_param("ii", $anio, $mes);
$stmt->execute();
$res_mensual = $stmt->get_result();
$prog_mensual = [];
while ($row = $res_mensual->fetch_assoc()) {
    $prog_mensual[$row['id_actividad']] = $row;
}
$stmt->close();

$sql_anual = "SELECT id_actividad, programado, unidad 
              FROM programacion_anual 
              WHERE anio = ?";
$stmt = $conn->prepare($sql_anual);
$stmt->bind_param("i", $anio);
$stmt->execute();
$res_anual = $stmt->get_result();
$prog_anual = [];
while ($row = $res_anual->fetch_assoc()) {
    $prog_anual[$row['id_actividad']] = $row;
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control</title>
    <link rel="stylesheet" href="../css/reporte.css?v=<?php echo(rand()); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style type="text/css">
        .tg  {border-collapse:collapse;border-spacing:0;}
        .tg td{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:14px;
        overflow:hidden;padding:10px 5px;word-break:normal;}
        .tg th{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:14px;
        font-weight:normal;overflow:hidden;padding:10px 5px;word-break:normal;}
        .tg .tg-cly1{text-align:left;vertical-align:middle}
        .tg .tg-wa1i{font-weight:bold;text-align:center;vertical-align:middle}
        .tg .tg-qjdd{background-color:#D0E0E3;text-align:left;vertical-align:middle}
        .tg .tg-4bam{background-color:#FFF;text-align:center;vertical-align:bottom}
        .tg .tg-uzvj{border-color:inherit;font-weight:bold;text-align:center;vertical-align:middle}
        .tg .tg-yla0{font-weight:bold;text-align:left;vertical-align:middle}
        .tg .tg-nrix{text-align:center;vertical-align:middle}
        .tg .tg-g4z8{background-color:#D9EAD3;text-align:left;vertical-align:middle}
        .tg .tg-8d8j{text-align:center;vertical-align:bottom}
        .tg .tg-3yw8{background-color:#FF0;text-align:center;vertical-align:bottom}
    </style>
</head>
<body>    
        <div class="tabla-scroll-x">
            <table class="tg">
                <thead>
            <tr>
                <th class="tg-uzvj" rowspan="4">Acción/Actividad</th>
                <th class="tg-wa1i" rowspan="4">Unidad de Medida</th>
                <th class="tg-wa1i" colspan="7">Avance Físico</th>
                <th class="tg-wa1i" rowspan="4">% de avance anual </th>
                <th class="tg-wa1i" rowspan="4">% de avance acumulado</th>
            </tr>
            <tr>
                <th class="tg-wa1i" rowspan="3">Programado Anual</th>
                <th class="tg-wa1i" colspan="3">En el Mes</th>
                <th class="tg-wa1i" colspan="3">Acumulado al Mes</th>
            </tr>
            <tr>
                <th class="tg-wa1i" colspan="2" rowspan="2">Programado </th>
                <th class="tg-wa1i" rowspan="2">Realizado</th>
                <th class="tg-wa1i" colspan="2" rowspan="2">Programado </th>
                <th class="tg-wa1i" rowspan="2">Realizado </th>
            </tr>
            <tr>
            </tr></thead>
            <tbody>
            <tr>
                <td class="tg-yla0">Vigilancia</td>
                <td class="tg-yla0"> </td>
                <td class="tg-cly1"> </td>
                <td class="tg-wa1i" colspan="2">  </td>
                <td class="tg-wa1i"> </td>
                <td class="tg-wa1i" colspan="2">  </td>
                <td class="tg-wa1i"> </td>
                <td class="tg-nrix"> </td>
                <td class="tg-nrix"> </td>
            </tr>
            <tr>
                <td class="tg-qjdd">Realización de Pruebas Cervicales Comparativas</td>
                <td class="tg-g4z8">Unidades de Producción</td>
                <td class="tg-nrix">1,441</td>
                <?php echo '<td class="tg-4bam" colspan="2">'. obtenerProgramadoMensual($conn,8,'Unidades de Producción',$anio,$mes) .'</td>'; ?>
                <?php echo '<td class="tg-8d8j">' . obtenerUPPs($conn,8, $anio, $mes) . '</td>'; ?>
                <td class="tg-nrix" colspan="2">870</td>
                <td class="tg-nrix">918</td>
                <td class="tg-nrix">63.71%</td>
                <td class="tg-nrix">105.52%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Realización&nbsp;&nbsp;&nbsp;de Pruebas Cervicales Comparativas</td>
                <td class="tg-cly1">Cabeza</td>
                <td class="tg-nrix">2,408</td>
                <?php echo '<td class="tg-4bam" colspan="2">'. obtenerProgramadoMensual($conn,8,'Cabezas',$anio,$mes) .'</td>'; ?>
                <?php echo '<td class="tg-8d8j">' . obtenerCabezas($conn,8, $anio, $mes) . '</td>'; ?>
                <td class="tg-nrix" colspan="2">1,214</td>
                <td class="tg-nrix">1,612</td>
                <td class="tg-nrix">66.94%</td>
                <td class="tg-nrix">132.78%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Muestreo&nbsp;&nbsp;&nbsp;en Rastro</td>
                <td class="tg-cly1">Muestra</td>
                <td class="tg-nrix">80</td>
                <td class="tg-4bam" colspan="2">7</td>
                <td class="tg-8d8j">10</td>
                <td class="tg-nrix" colspan="2">48</td>
                <td class="tg-nrix">88</td>
                <td class="tg-nrix">110.00%</td>
                <td class="tg-nrix">183.33%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Diagnóstico&nbsp;&nbsp;&nbsp;Histopatológico</td>
                <td class="tg-cly1">Diagnóstico</td>
                <td class="tg-nrix">80</td>
                <td class="tg-4bam" colspan="2">7</td>
                <td class="tg-8d8j">10</td>
                <td class="tg-nrix" colspan="2">48</td>
                <td class="tg-nrix">88</td>
                <td class="tg-nrix">110.00%</td>
                <td class="tg-nrix">183.33%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Diagnóstico&nbsp;&nbsp;&nbsp;de Bacteriología</td>
                <td class="tg-cly1">Diagnóstico</td>
                <td class="tg-nrix">80</td>
                <td class="tg-4bam" colspan="2">7</td>
                <td class="tg-8d8j">10</td>
                <td class="tg-nrix" colspan="2">48</td>
                <td class="tg-nrix">88</td>
                <td class="tg-nrix">110.00%</td>
                <td class="tg-nrix">183.33%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Tipificación</td>
                <td class="tg-cly1">Diagnóstico</td>
                <td class="tg-nrix">15</td>
                <td class="tg-4bam" colspan="2">2</td>
                <td class="tg-8d8j">0</td>
                <td class="tg-nrix" colspan="2">11</td>
                <td class="tg-nrix">10</td>
                <td class="tg-nrix">66.67%</td>
                <td class="tg-nrix">90.91%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Diagnóstico&nbsp;&nbsp;&nbsp;PCR        </td>
                <td class="tg-cly1">Diagnóstico</td>
                <td class="tg-nrix">15</td>
                <td class="tg-4bam" colspan="2">2</td>
                <td class="tg-8d8j">0</td>
                <td class="tg-nrix" colspan="2">10</td>
                <td class="tg-nrix">1</td>
                <td class="tg-nrix">6.67%</td>
                <td class="tg-nrix">10.00%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Cabezas&nbsp;&nbsp;&nbsp;Sacrificadas</td>
                <td class="tg-cly1">Cabeza</td>
                <td class="tg-nrix">11,100</td>
                <td class="tg-4bam" colspan="2">935</td>
                <td class="tg-8d8j">933</td>
                <td class="tg-nrix" colspan="2">6,117</td>
                <td class="tg-nrix">5,827</td>
                <td class="tg-nrix">52.50%</td>
                <td class="tg-nrix">95.26%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Cabezas&nbsp;&nbsp;&nbsp;Inspeccionadas</td>
                <td class="tg-cly1">Cabeza</td>
                <td class="tg-nrix">10,608</td>
                <td class="tg-4bam" colspan="2">888</td>
                <td class="tg-8d8j">918</td>
                <td class="tg-nrix" colspan="2">5,876</td>
                <td class="tg-nrix">5,737</td>
                <td class="tg-nrix">54.08%</td>
                <td class="tg-nrix">97.63%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Inspección&nbsp;&nbsp;&nbsp;en Rastro</td>
                <td class="tg-cly1">Porcentaje</td>
                <td class="tg-nrix">100</td>
                <td class="tg-4bam" colspan="2">98</td>
                <td class="tg-8d8j">98</td>
                <td class="tg-nrix" colspan="2">98</td>
                <td class="tg-nrix">98</td>
                <td class="tg-nrix">98.00%</td>
                <td class="tg-nrix">100.00%</td>
            </tr>
            <tr>
                <td class="tg-g4z8">Barrido</td>
                <td class="tg-g4z8">Unidades de&nbsp;&nbsp;&nbsp;Producción</td>
                <td class="tg-nrix">289</td>
                <td class="tg-4bam" colspan="2">42</td>
                <td class="tg-3yw8">9</td>
                <td class="tg-nrix" colspan="2">147</td>
                <td class="tg-nrix">182</td>
                <td class="tg-nrix">62.98%</td>
                <td class="tg-nrix">123.81%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Barrido</td>
                <td class="tg-cly1">Cabezas</td>
                <td class="tg-nrix">9,419</td>
                <td class="tg-4bam" colspan="2">1,225</td>
                <td class="tg-3yw8">249</td>
                <td class="tg-nrix" colspan="2">4,720</td>
                <td class="tg-nrix">6,276</td>
                <td class="tg-nrix">66.63%</td>
                <td class="tg-nrix">132.97%</td>
            </tr>
            <tr>
                <td class="tg-g4z8">Realización&nbsp;&nbsp;&nbsp;Pruebas en Hatos Bovinos Cuarentena Definitiva</td>
                <td class="tg-g4z8">Unidades de&nbsp;&nbsp;&nbsp;Producción</td>
                <td class="tg-nrix">34</td>
                <td class="tg-4bam" colspan="2">2</td>
                <td class="tg-8d8j">1</td>
                <td class="tg-nrix" colspan="2">19</td>
                <td class="tg-nrix">13</td>
                <td class="tg-nrix">38.24%</td>
                <td class="tg-nrix">68.42%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Realización&nbsp;&nbsp;&nbsp;Pruebas en Hatos Bovinos Cuarentena Definitiva</td>
                <td class="tg-cly1">Cabezas</td>
                <td class="tg-nrix">3,634</td>
                <td class="tg-4bam" colspan="2">344</td>
                <td class="tg-8d8j">141</td>
                <td class="tg-nrix" colspan="2">2,232</td>
                <td class="tg-nrix">1,746</td>
                <td class="tg-nrix">48.05%</td>
                <td class="tg-nrix">78.23%</td>
            </tr>
            <tr>
                <td class="tg-g4z8">Realización&nbsp;&nbsp;&nbsp;Pruebas en Hatos Bovinos Cuarentena Precautoria</td>
                <td class="tg-g4z8">Unidades de&nbsp;&nbsp;&nbsp;Producción</td>
                <td class="tg-nrix">6</td>
                <td class="tg-4bam" colspan="2">0</td>
                <td class="tg-8d8j">0</td>
                <td class="tg-nrix" colspan="2">5</td>
                <td class="tg-nrix">4</td>
                <td class="tg-nrix">66.67%</td>
                <td class="tg-nrix">80.00%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Realización&nbsp;&nbsp;&nbsp;Pruebas en Hatos Bovinos Cuarentena Precautoria</td>
                <td class="tg-cly1">Cabezas</td>
                <td class="tg-nrix">506</td>
                <td class="tg-4bam" colspan="2">0</td>
                <td class="tg-8d8j">0</td>
                <td class="tg-nrix" colspan="2">451</td>
                <td class="tg-nrix">349</td>
                <td class="tg-nrix">68.97%</td>
                <td class="tg-nrix">77.38%</td>
            </tr>
            <tr>
                <td class="tg-g4z8">Realización&nbsp;&nbsp;&nbsp;de Pruebas en Hatos Bovinos Relacionados y Expuestos</td>
                <td class="tg-g4z8">Unidades de&nbsp;&nbsp;&nbsp;Producción</td>
                <td class="tg-nrix">21</td>
                <td class="tg-4bam" colspan="2">2</td>
                <td class="tg-8d8j">0</td>
                <td class="tg-nrix" colspan="2">13</td>
                <td class="tg-nrix">5</td>
                <td class="tg-nrix">23.81%</td>
                <td class="tg-nrix">38.46%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Realización&nbsp;&nbsp;&nbsp;de Pruebas en Hatos Bovinos Relacionados y Expuestos</td>
                <td class="tg-cly1">Cabezas</td>
                <td class="tg-nrix">1,104</td>
                <td class="tg-4bam" colspan="2">84</td>
                <td class="tg-8d8j">0</td>
                <td class="tg-nrix" colspan="2">779</td>
                <td class="tg-nrix">207</td>
                <td class="tg-nrix">18.75%</td>
                <td class="tg-nrix">26.57%</td>
            </tr>
            <tr>
                <td class="tg-g4z8">Identificación&nbsp;&nbsp;&nbsp;de Hato más Probable de Origen Bovinos</td>
                <td class="tg-g4z8">Caso</td>
                <td class="tg-nrix">4</td>
                <td class="tg-4bam" colspan="2">0</td>
                <td class="tg-8d8j">1</td>
                <td class="tg-nrix" colspan="2">1</td>
                <td class="tg-nrix">3</td>
                <td class="tg-nrix">75.00%</td>
                <td class="tg-nrix">300.00%</td>
            </tr>
            <tr>
                <td class="tg-g4z8">Realización&nbsp;&nbsp;&nbsp;de Prueba Anual en Zona de Amortiguamiento (EPNI)</td>
                <td class="tg-g4z8">Unidades de&nbsp;&nbsp;&nbsp;Producción</td>
                <td class="tg-nrix">148</td>
                <?php echo '<td class="tg-4bam" colspan="2">'. obtenerProgramadoMensual($conn,9,'Unidades de Producción',$anio,$mes) .'</td>'; ?>
                <?php echo '<td class="tg-3yw8">' . obtenerUPPs($conn,9, $anio, $mes) . '</td>'; ?>
                <td class="tg-nrix" colspan="2">70</td>
                <td class="tg-nrix">40</td>
                <td class="tg-nrix">27.03%</td>
                <td class="tg-nrix">57.14%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Realización&nbsp;&nbsp;&nbsp;de Prueba Anual en Zona de Amortiguamiento (EPNI)</td>
                <td class="tg-cly1">Cabezas</td>
                <td class="tg-nrix">3,469</td>
                <?php echo '<td class="tg-4bam" colspan="2">'. obtenerProgramadoMensual($conn,9,'Cabezas',$anio,$mes) .'</td>'; ?>
                <?php echo '<td class="tg-3yw8">' . obtenerCabezas($conn,9, $anio, $mes) . '</td>'; ?>
                <td class="tg-nrix" colspan="2">1,699</td>
                <td class="tg-nrix">1,623</td>
                <td class="tg-nrix">46.79%</td>
                <td class="tg-nrix">95.53%</td>
            </tr>
            <tr>
                <td class="tg-g4z8">Realización&nbsp;&nbsp;&nbsp;de Prueba Anual en Zona de Amortiguamiento (Control)        </td>
                <td class="tg-g4z8">Unidades de&nbsp;&nbsp;&nbsp;Producción</td>
                <td class="tg-nrix">372</td>
                <?php echo '<td class="tg-4bam" colspan="2">'. obtenerProgramadoMensual($conn,10,'Unidades de Producción',$anio,$mes) .'</td>'; ?>
                <?php echo '<td class="tg-3yw8">' . obtenerUPPs($conn,10, $anio, $mes) . '</td>'; ?>
                <td class="tg-nrix" colspan="2">159</td>
                <td class="tg-nrix">68</td>
                <td class="tg-nrix">18.28%</td>
                <td class="tg-nrix">42.77%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Realización&nbsp;&nbsp;&nbsp;de Prueba Anual en Zona de Amortiguamiento (Control)</td>
                <td class="tg-cly1">Cabezas</td>
                <td class="tg-nrix">9,465</td>
                <?php echo '<td class="tg-4bam" colspan="2">'. obtenerProgramadoMensual($conn,10,'Cabezas',$anio,$mes) .'</td>'; ?>
                <?php echo '<td class="tg-3yw8">' . obtenerCabezas($conn,10, $anio, $mes) . '</td>'; ?>
                <td class="tg-nrix" colspan="2">4,565</td>
                <td class="tg-nrix">1,652</td>
                <td class="tg-nrix">17.45%</td>
                <td class="tg-nrix">36.19%</td>
            </tr>
            <tr>
                <td class="tg-yla0">Medidas&nbsp;&nbsp;&nbsp;Zoosanitarias</td>
                <td class="tg-yla0"> </td>
                <td class="tg-cly1"> </td>
                <td class="tg-4bam" colspan="2">  </td>
                <td class="tg-8d8j"> </td>
                <td class="tg-nrix" colspan="2">  </td>
                <td class="tg-nrix"> </td>
                <td class="tg-nrix"> </td>
                <td class="tg-nrix"> </td>
            </tr>
            <tr>
                <td class="tg-g4z8">Desinfección&nbsp;&nbsp;&nbsp;de Instalaciones</td>
                <td class="tg-g4z8">Unidad de&nbsp;&nbsp;&nbsp;Producción</td>
                <td class="tg-nrix">34</td>
                <td class="tg-4bam" colspan="2">2</td>
                <td class="tg-8d8j">1</td>
                <td class="tg-nrix" colspan="2">19</td>
                <td class="tg-nrix">14</td>
                <td class="tg-nrix">41.18%</td>
                <td class="tg-nrix">73.68%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Supervisión&nbsp;&nbsp;&nbsp;a Rastro</td>
                <td class="tg-cly1">Evento</td>
                <td class="tg-nrix">240</td>
                <td class="tg-4bam" colspan="2">23</td>
                <td class="tg-8d8j">6</td>
                <td class="tg-nrix" colspan="2">126</td>
                <td class="tg-nrix">88</td>
                <td class="tg-nrix">36.67%</td>
                <td class="tg-nrix">69.84%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Supervisión&nbsp;&nbsp;&nbsp;de Pruebas</td>
                <td class="tg-cly1">Evento</td>
                <td class="tg-nrix">10</td>
                <td class="tg-4bam" colspan="2">1</td>
                <td class="tg-8d8j">0</td>
                <td class="tg-nrix" colspan="2">5</td>
                <td class="tg-nrix">3</td>
                <td class="tg-nrix">30.00%</td>
                <td class="tg-nrix">60.00%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Liberación&nbsp;&nbsp;&nbsp;de Cuarentena<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Definitiva</td>
                <td class="tg-cly1">Porcentaje</td>
                <td class="tg-nrix">100</td>
                <td class="tg-4bam" colspan="2">100</td>
                <td class="tg-8d8j">100</td>
                <td class="tg-nrix" colspan="2">100</td>
                <td class="tg-nrix">100</td>
                <td class="tg-nrix">100.00%</td>
                <td class="tg-nrix">100.00%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Liberación&nbsp;&nbsp;&nbsp;de Cuarentena<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Precautoria</td>
                <td class="tg-cly1">Porcentaje</td>
                <td class="tg-nrix">100</td>
                <td class="tg-4bam" colspan="2">100</td>
                <td class="tg-8d8j">100</td>
                <td class="tg-nrix" colspan="2">100</td>
                <td class="tg-nrix">100</td>
                <td class="tg-nrix">100.00%</td>
                <td class="tg-nrix">100.00%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Implementación&nbsp;&nbsp;&nbsp;de Cuarentenas Definitivas</td>
                <td class="tg-cly1">Porcentaje</td>
                <td class="tg-nrix">100</td>
                <td class="tg-4bam" colspan="2">100</td>
                <td class="tg-8d8j">100</td>
                <td class="tg-nrix" colspan="2">100</td>
                <td class="tg-nrix">100</td>
                <td class="tg-nrix">100.00%</td>
                <td class="tg-nrix">100.00%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Implementación&nbsp;&nbsp;&nbsp;de Cuarentenas Precautorias</td>
                <td class="tg-cly1">Porcentaje</td>
                <td class="tg-nrix">100</td>
                <td class="tg-4bam" colspan="2">100</td>
                <td class="tg-8d8j">100</td>
                <td class="tg-nrix" colspan="2">100</td>
                <td class="tg-nrix">100</td>
                <td class="tg-nrix">100.00%</td>
                <td class="tg-nrix">100.00%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Referenciación&nbsp;&nbsp;&nbsp;Geográfica de las Unidades de Producción Atendidas</td>
                <td class="tg-cly1">Reporte&nbsp;&nbsp;&nbsp;(Archivo Shapefile)</td>
                <td class="tg-nrix">12</td>
                <td class="tg-4bam" colspan="2">1</td>
                <td class="tg-8d8j">1</td>
                <td class="tg-nrix" colspan="2">7</td>
                <td class="tg-nrix">7</td>
                <td class="tg-nrix">58.33%</td>
                <td class="tg-nrix">100.00%</td>
            </tr>
            <tr>
                <td class="tg-yla0">Actualización&nbsp;&nbsp;&nbsp;Técnica</td>
                <td class="tg-yla0"> </td>
                <td class="tg-cly1"> </td>
                <td class="tg-4bam" colspan="2">  </td>
                <td class="tg-8d8j"> </td>
                <td class="tg-nrix" colspan="2">  </td>
                <td class="tg-nrix"> </td>
                <td class="tg-nrix"> </td>
                <td class="tg-nrix"> </td>
            </tr>
            <tr>
                <td class="tg-g4z8">Actualización&nbsp;&nbsp;&nbsp;para productores	</td>
                <td class="tg-g4z8">Persona</td>
                <td class="tg-nrix">150</td>
                <td class="tg-4bam" colspan="2">15</td>
                <td class="tg-8d8j">0</td>
                <td class="tg-nrix" colspan="2">90</td>
                <td class="tg-nrix">233</td>
                <td class="tg-nrix">155.33%</td>
                <td class="tg-nrix">258.89%</td>
            </tr>
            <tr>
                <td class="tg-cly1">Actualización&nbsp;&nbsp;&nbsp;para productores	</td>
                <td class="tg-cly1">Evento</td>
                <td class="tg-nrix">10</td>
                <td class="tg-4bam" colspan="2">1</td>
                <td class="tg-8d8j">0</td>
                <td class="tg-nrix" colspan="2">6</td>
                <td class="tg-nrix">7</td>
                <td class="tg-nrix">70.00%</td>
                <td class="tg-nrix">116.67%</td>
            </tr>
            <tr>
                <td class="tg-g4z8">Actualización&nbsp;&nbsp;&nbsp;para personal del OASA</td>
                <td class="tg-g4z8">Persona</td>
                <td class="tg-nrix">20</td>
                <td class="tg-4bam" colspan="2">20</td>
                <td class="tg-8d8j">0</td>
                <td class="tg-nrix" colspan="2">20</td>
                <td class="tg-nrix">0</td>
                <td class="tg-nrix">0.00%</td>
                <td class="tg-nrix">0.00%</td>
            </tr>
            <tr>
                <td class="tg-g4z8">Actualización&nbsp;&nbsp;&nbsp;para personal del OASA</td>
                <td class="tg-cly1">Evento</td>
                <td class="tg-nrix">1</td>
                <td class="tg-nrix" colspan="2">1</td>
                <td class="tg-nrix">0</td>
                <td class="tg-nrix" colspan="2">1</td>
                <td class="tg-nrix">0</td>
                <td class="tg-nrix">0.00%</td>
                <td class="tg-nrix">0%</td>
            </tr>
            </tbody>
        </table>
    </div>
    
    
</main>
</body>
</html>
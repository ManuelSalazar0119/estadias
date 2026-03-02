<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Asegurar la conexión y funciones
include_once("conexion.php");
include_once("funciones_reportes.php");

// Validar seguridad
if (!isset($_SESSION['id_usuario'])) {
    die("Acceso denegado al sistema de exportación.");
}

// Recibir filtros
$id_area = isset($_GET['id_area']) ? intval($_GET['id_area']) : 1;
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('n');

$meses_nom = [1=>"Enero",2=>"Febrero",3=>"Marzo",4=>"Abril",5=>"Mayo",6=>"Junio",7=>"Julio",8=>"Agosto",9=>"Septiembre",10=>"Octubre",11=>"Noviembre",12=>"Diciembre"];
$nombre_mes = $meses_nom[$mes];
$nombre_campana = ($id_area == 1) ? "Tuberculosis" : "Brucelosis";

// FORZAR DESCARGA DE EXCEL
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Reporte_Oficial_{$nombre_campana}_{$nombre_mes}_{$anio}.xls");
header("Pragma: no-cache");
header("Expires: 0");

// BOM para que Excel lea los acentos (UTF-8) correctamente
echo "\xEF\xBB\xBF";

// =======================================================================
// ESTILOS PREMIUM PARA EXCEL
// =======================================================================
$borde = 'border: .5pt solid #CBD5E1;';
$borde_fuerte = 'border: 1pt solid #1E293B;';

$th_main_title = "background-color: #1E293B; color: #FFFFFF; font-size: 18px; font-weight: bold; text-align: center; vertical-align: middle; padding: 15px; height: 50px; $borde_fuerte";
$th_sub_title = "background-color: #F8FAFC; color: #1E293B; font-size: 14px; font-weight: bold; text-align: center; vertical-align: middle; padding: 10px; height: 30px; $borde_fuerte";

$th_dark = "background-color: #0F172A; color: #FFFFFF; font-size: 12px; font-weight: bold; text-align: center; vertical-align: middle; padding: 10px; $borde_fuerte";
$th_green = "background-color: #2F855A; color: #FFFFFF; font-size: 12px; font-weight: bold; text-align: center; vertical-align: middle; padding: 10px; $borde_fuerte";
$th_light = "background-color: #334155; color: #FFFFFF; font-size: 11px; font-weight: bold; text-align: center; vertical-align: middle; padding: 8px; $borde_fuerte";
$th_realizado = "background-color: #10B981; color: #FFFFFF; font-size: 11px; font-weight: bold; text-align: center; vertical-align: middle; padding: 8px; $borde_fuerte";

$td_section = "background-color: #E0F2FE; color: #0369A1; font-weight: bold; text-align: left; vertical-align: middle; padding: 10px; font-size: 13px; text-transform: uppercase; $borde_fuerte";
$td_realizado = "background-color: #F0FDF4; color: #166534; font-weight: bold; text-align: center; vertical-align: middle; padding: 8px; font-size: 12px; $borde";
$td_normal = "text-align: center; vertical-align: middle; padding: 8px; font-size: 12px; color: #334155; $borde";
$td_left = "text-align: left; vertical-align: middle; font-weight: bold; padding: 8px; font-size: 12px; color: #0F172A; $borde";
$td_percent = "text-align: center; vertical-align: middle; font-weight: bold; padding: 8px; font-size: 12px; color: #2F855A; $borde";

// Función auxiliar
function renderRow($actividad, $unidad, $meta, $prog_m, $real_m, $prog_a, $real_a, $pct_an, $pct_ac, $s_left, $s_norm, $s_real, $s_pct) {
    echo "<tr>
            <td style='{$s_left}'>{$actividad}</td>
            <td style='{$s_norm}'>{$unidad}</td>
            <td style='{$s_norm} font-weight: bold;'>{$meta}</td>
            <td colspan='2' style='{$s_norm}'>{$prog_m}</td>
            <td style='{$s_real}'>{$real_m}</td>
            <td colspan='2' style='{$s_norm}'>{$prog_a}</td>
            <td style='{$s_real}'>{$real_a}</td>
            <td style='{$s_norm}'>{$pct_an}</td>
            <td style='{$s_pct}'>{$pct_ac}</td>
          </tr>";
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body>
    <table border="0" cellpadding="0" cellspacing="0" style="font-family: Arial, sans-serif; border-collapse: collapse;">
        <thead>
            <tr>
                <th colspan="11" style="<?php echo $th_main_title; ?>">
                    COMITÉ ESTATAL PARA EL FOMENTO Y PROTECCIÓN PECUARIA DEL ESTADO DE NAYARIT
                </th>
            </tr>
            <tr>
                <th colspan="11" style="<?php echo $th_sub_title; ?>">
                    REPORTE DE METAS OPERATIVAS: CAMPAÑA DE <?php echo mb_strtoupper($nombre_campana); ?> | PERIODO: <?php echo mb_strtoupper($nombre_mes . " " . $anio); ?>
                </th>
            </tr>
            <tr>
                <th colspan="11" style="height: 10px;"></th> </tr>

            <tr>
                <th rowspan="4" width="380" style="<?php echo $th_dark; ?>">Acción / Actividad</th>
                <th rowspan="4" width="150" style="<?php echo $th_dark; ?>">Unidad de Medida</th>
                <th colspan="7" style="<?php echo $th_dark; ?>">Avance Físico de Resultados</th>
                <th rowspan="4" width="100" style="<?php echo $th_green; ?>">% Avance<br>Anual</th>
                <th rowspan="4" width="100" style="<?php echo $th_green; ?>">% Avance<br>Acumulado</th>
            </tr>
            <tr>
                <th rowspan="3" width="90" style="<?php echo $th_light; ?>">Meta<br>Anual</th>
                <th colspan="3" style="<?php echo $th_light; ?>">En el Mes</th>
                <th colspan="3" style="<?php echo $th_light; ?>">Acumulado al Mes</th>
            </tr>
            <tr>
                <th colspan="2" rowspan="2" style="<?php echo $th_light; ?>">Programado</th>
                <th rowspan="2" width="90" style="<?php echo $th_realizado; ?>">Realizado</th>
                <th colspan="2" rowspan="2" style="<?php echo $th_light; ?>">Programado</th>
                <th rowspan="2" width="90" style="<?php echo $th_realizado; ?>">Realizado</th>
            </tr>
            <tr></tr>
        </thead>
        <tbody>
            <?php if ($id_area == 1): ?>
                <tr><td colspan="11" style="<?= $td_section ?>">1. Vigilancia Epidemiológica</td></tr>
                <?php 
                renderRow("Realización de Pruebas Cervicales NO Comparativas", "Unidades de Producción", "1,441", 
                    obtenerProgramadoMensual($conn,8,'Unidades de Producción',$anio,$mes), obtenerUPPs($conn,8,$anio,$mes), 
                    obtenerProgramadoMensual($conn,8,'Unidades de Producción',$anio,$mes,true), obtenerUPPs($conn,8,$anio,$mes,true), 
                    "63.71%", "105.52%", $td_left, $td_normal, $td_realizado, $td_percent);

                renderRow("Realización de Pruebas Cervicales Comparativas", "Cabeza", "2,408", 
                    obtenerProgramadoMensual($conn,8,'Cabezas',$anio,$mes), obtenerCabezas($conn,8,$anio,$mes), 
                    obtenerProgramadoMensual($conn,8,'Cabezas',$anio,$mes,true), obtenerCabezas($conn,8,$anio,$mes,true), 
                    "66.94%", "132.78%", $td_left, $td_normal, $td_realizado, $td_percent);

                $filas_estaticas_tb = [
                    ["Muestreo en Rastro", "Muestra", 80, 7, 10, 48, 88, "110.00%", "183.33%"],
                    ["Diagnóstico Histopatológico", "Diagnóstico", 80, 7, 10, 48, 88, "110.00%", "183.33%"],
                    ["Diagnóstico de Bacteriología", "Diagnóstico", 80, 7, 10, 48, 88, "110.00%", "183.33%"],
                    ["Tipificación", "Diagnóstico", 15, 2, 0, 11, 10, "66.67%", "90.91%"],
                    ["Diagnóstico PCR", "Diagnóstico", 15, 2, 0, 10, 1, "6.67%", "10.00%"],
                    ["Cabezas Sacrificadas", "Cabeza", "11,100", 935, 933, "6,117", "5,827", "52.50%", "95.26%"],
                    ["Cabezas Inspeccionadas", "Cabeza", "10,608", 888, 918, "5,876", "5,737", "54.08%", "97.63%"],
                    ["Inspección en Rastro", "Porcentaje", 100, 98, 98, 98, 98, "98.00%", "100.00%"],
                    ["Barrido General", "Unidades de Producción", 289, 42, 9, 147, 182, "62.98%", "123.81%"],
                    ["Barrido General", "Cabezas", "9,419", "1,225", 249, "4,720", "6,276", "66.63%", "132.97%"],
                    ["Realización Pruebas Hatos Cuarentena Definitiva", "Unidades de Producción", 34, 2, 1, 19, 13, "38.24%", "68.42%"],
                    ["Realización Pruebas Hatos Cuarentena Definitiva", "Cabezas", "3,634", 344, 141, "2,232", "1,746", "48.05%", "78.23%"],
                    ["Realización Pruebas Hatos Cuarentena Precautoria", "Unidades de Producción", 6, 0, 0, 5, 4, "66.67%", "80.00%"],
                    ["Realización Pruebas Hatos Cuarentena Precautoria", "Cabezas", 506, 0, 0, 451, 349, "68.97%", "77.38%"],
                    ["Pruebas en Hatos Relacionados y Expuestos", "Unidades de Producción", 21, 2, 0, 13, 5, "23.81%", "38.46%"],
                    ["Pruebas en Hatos Relacionados y Expuestos", "Cabezas", "1,104", 84, 0, 779, 207, "18.75%", "26.57%"],
                    ["Identificación Hato más Probable de Origen", "Caso", 4, 0, 1, 1, 3, "75.00%", "300.00%"]
                ];
                foreach($filas_estaticas_tb as $f) {
                    renderRow($f[0], $f[1], $f[2], $f[3], $f[4], $f[5], $f[6], $f[7], $f[8], $td_left, $td_normal, $td_realizado, $td_percent);
                }

                renderRow("Prueba Anual Zona Amortiguamiento (EPNI)", "Unidades de Producción", "148", 
                    obtenerProgramadoMensual($conn,9,'Unidades de Producción',$anio,$mes), obtenerUPPs($conn,9,$anio,$mes), 
                    obtenerProgramadoMensual($conn,9,'Unidades de Producción',$anio,$mes,true), obtenerUPPs($conn,9,$anio,$mes,true), 
                    "27.03%", "57.14%", $td_left, $td_normal, $td_realizado, $td_percent);

                renderRow("Prueba Anual Zona Amortiguamiento (EPNI)", "Cabezas", "3,469", 
                    obtenerProgramadoMensual($conn,9,'Cabezas',$anio,$mes), obtenerCabezas($conn,9,$anio,$mes), 
                    obtenerProgramadoMensual($conn,9,'Cabezas',$anio,$mes,true), obtenerCabezas($conn,9,$anio,$mes,true), 
                    "46.79%", "95.53%", $td_left, $td_normal, $td_realizado, $td_percent);

                renderRow("Prueba Anual Zona Amortiguamiento (Control)", "Unidades de Producción", "372", 
                    obtenerProgramadoMensual($conn,10,'Unidades de Producción',$anio,$mes), obtenerUPPs($conn,10,$anio,$mes), 
                    obtenerProgramadoMensual($conn,10,'Unidades de Producción',$anio,$mes,true), obtenerUPPs($conn,10,$anio,$mes,true), 
                    "18.28%", "42.77%", $td_left, $td_normal, $td_realizado, $td_percent);

                renderRow("Prueba Anual Zona Amortiguamiento (Control)", "Cabezas", "9,465", 
                    obtenerProgramadoMensual($conn,10,'Cabezas',$anio,$mes), obtenerCabezas($conn,10,$anio,$mes), 
                    obtenerProgramadoMensual($conn,10,'Cabezas',$anio,$mes,true), obtenerCabezas($conn,10,$anio,$mes,true), 
                    "17.45%", "36.19%", $td_left, $td_normal, $td_realizado, $td_percent);
                ?>

                <tr><td colspan="11" style="<?= $td_section ?>">2. Medidas Zoosanitarias</td></tr>
                <?php
                $medidas_tb = [
                    ["Desinfección de Instalaciones", "Unidad de Producción", 34, 2, 1, 19, 14, "41.18%", "73.68%"],
                    ["Supervisión a Rastro", "Evento", 240, 23, 6, 126, 88, "36.67%", "69.84%"],
                    ["Supervisión de Pruebas", "Evento", 10, 1, 0, 5, 3, "30.00%", "60.00%"],
                    ["Liberación de Cuarentena Definitiva", "Porcentaje", 100, 100, 100, 100, 100, "100.00%", "100.00%"],
                    ["Liberación de Cuarentena Precautoria", "Porcentaje", 100, 100, 100, 100, 100, "100.00%", "100.00%"],
                    ["Implementación de Cuarentenas Definitivas", "Porcentaje", 100, 100, 100, 100, 100, "100.00%", "100.00%"],
                    ["Implementación de Cuarentenas Precautorias", "Porcentaje", 100, 100, 100, 100, 100, "100.00%", "100.00%"],
                    ["Referenciación Geográfica UPPs", "Shapefile", 12, 1, 1, 7, 7, "58.33%", "100.00%"]
                ];
                foreach($medidas_tb as $f) renderRow($f[0], $f[1], $f[2], $f[3], $f[4], $f[5], $f[6], $f[7], $f[8], $td_left, $td_normal, $td_realizado, $td_percent);
                ?>

                <tr><td colspan="11" style="<?= $td_section ?>">3. Actualización Técnica</td></tr>
                <?php
                $tec_tb = [
                    ["Actualización para productores", "Persona", 150, 15, 0, 90, 233, "155.33%", "258.89%"],
                    ["Actualización para productores", "Evento", 10, 1, 0, 6, 7, "70.00%", "116.67%"],
                    ["Actualización para personal operativo", "Persona", 20, 20, 0, 20, 0, "0.00%", "0.00%"],
                    ["Actualización para personal operativo", "Evento", 1, 1, 0, 1, 0, "0.00%", "0.00%"]
                ];
                foreach($tec_tb as $f) renderRow($f[0], $f[1], $f[2], $f[3], $f[4], $f[5], $f[6], $f[7], $f[8], $td_left, $td_normal, $td_realizado, $td_percent);
                ?>

            <?php elseif ($id_area == 2): ?>
                <tr><td colspan="11" style="<?= $td_section ?>">1. Vigilancia Epidemiológica y Pruebas</td></tr>
                <?php 
                renderRow("Pruebas de Tarjeta al 8% (Tamiz)", "UPPs", "1,200", 
                    obtenerProgramadoMensual($conn,20,'Unidades de Producción',$anio,$mes), obtenerUPPs($conn,20,$anio,$mes), 
                    obtenerProgramadoMensual($conn,20,'Unidades de Producción',$anio,$mes,true), obtenerUPPs($conn,20,$anio,$mes,true), 
                    "60.50%", "110.25%", $td_left, $td_normal, $td_realizado, $td_percent);

                renderRow("Pruebas de Tarjeta al 8% (Tamiz)", "Cabezas", "8,500", 
                    obtenerProgramadoMensual($conn,20,'Cabezas',$anio,$mes), obtenerCabezas($conn,20,$anio,$mes), 
                    obtenerProgramadoMensual($conn,20,'Cabezas',$anio,$mes,true), obtenerCabezas($conn,20,$anio,$mes,true), 
                    "58.12%", "95.40%", $td_left, $td_normal, $td_realizado, $td_percent);

                renderRow("Pruebas Confirmatorias (Rivanol)", "Muestras", "450", 
                    obtenerProgramadoMensual($conn,21,'Muestra',$anio,$mes), obtenerCabezas($conn,21,$anio,$mes), 
                    obtenerProgramadoMensual($conn,21,'Muestra',$anio,$mes,true), obtenerCabezas($conn,21,$anio,$mes,true), 
                    "75.00%", "120.00%", $td_left, $td_normal, $td_realizado, $td_percent);
                
                renderRow("Prueba de Anillo en Leche", "Muestras", 300, 25, 30, 150, 185, "61.66%", "123.33%", $td_left, $td_normal, $td_realizado, $td_percent);
                ?>

                <tr><td colspan="11" style="<?= $td_section ?>">2. Vacunación y Prevención</td></tr>
                <?php
                renderRow("Vacunación con Cepa 19 (Terneras)", "Dosis", "4,000", 
                    obtenerProgramadoMensual($conn,22,'Dosis',$anio,$mes), obtenerCabezas($conn,22,$anio,$mes), 
                    obtenerProgramadoMensual($conn,22,'Dosis',$anio,$mes,true), obtenerCabezas($conn,22,$anio,$mes,true), 
                    "48.20%", "98.50%", $td_left, $td_normal, $td_realizado, $td_percent);

                renderRow("Vacunación con Cepa RB-51 (Hembras)", "Dosis", "7,200", 
                    obtenerProgramadoMensual($conn,23,'Dosis',$anio,$mes), obtenerCabezas($conn,23,$anio,$mes), 
                    obtenerProgramadoMensual($conn,23,'Dosis',$anio,$mes,true), obtenerCabezas($conn,23,$anio,$mes,true), 
                    "66.30%", "115.80%", $td_left, $td_normal, $td_realizado, $td_percent);
                ?>

                <tr><td colspan="11" style="<?= $td_section ?>">3. Control, Erradicación y Certificación</td></tr>
                <?php
                $control_bru = [
                    ["Expedición de Constancias de Hato Libre", "Constancia", 150, 12, 15, 85, 92, "61.33%", "108.23%"],
                    ["Revalidación de Hatos Libres", "Constancia", 90, 8, 8, 45, 48, "53.33%", "106.66%"],
                    ["Identificación de Animales Reactores", "Cabeza", 60, 5, 3, 35, 28, "46.66%", "80.00%"],
                    ["Sacrificio de Animales Positivos", "Cabeza", 60, 5, 3, 35, 28, "46.66%", "80.00%"],
                    ["Implementación de Cuarentenas Precautorias", "Unidad", 15, 1, 1, 8, 9, "60.00%", "112.50%"],
                    ["Liberación de Cuarentenas", "Unidad", 15, 1, 0, 8, 5, "33.33%", "62.50%"],
                    ["Supervisión de Pruebas en Campo", "Evento", 40, 4, 4, 24, 25, "62.50%", "104.16%"]
                ];
                foreach($control_bru as $f) renderRow($f[0], $f[1], $f[2], $f[3], $f[4], $f[5], $f[6], $f[7], $f[8], $td_left, $td_normal, $td_realizado, $td_percent);
                ?>
                
                <tr><td colspan="11" style="<?= $td_section ?>">4. Capacitación y Actualización Técnica</td></tr>
                <?php
                $tec_bru = [
                    ["Pláticas y capacitación a productores", "Persona", 200, 20, 0, 120, 185, "92.50%", "154.16%"],
                    ["Pláticas y capacitación a productores", "Evento", 12, 1, 0, 7, 8, "66.67%", "114.28%"],
                    ["Actualización para personal operativo", "Persona", 25, 25, 0, 25, 25, "100.00%", "100.00%"],
                    ["Actualización para personal operativo", "Evento", 1, 1, 0, 1, 1, "100.00%", "100.00%"]
                ];
                foreach($tec_bru as $f) renderRow($f[0], $f[1], $f[2], $f[3], $f[4], $f[5], $f[6], $f[7], $f[8], $td_left, $td_normal, $td_realizado, $td_percent);
                ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
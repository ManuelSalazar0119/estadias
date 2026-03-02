<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once("conexion.php");

// Validar seguridad
if (!isset($_SESSION['id_usuario'])) { 
    die("Acceso denegado al sistema de exportación."); 
}

// Filtros
$id_actividad = isset($_GET['id_actividad']) ? intval($_GET['id_actividad']) : 1;
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
$mes_num = isset($_GET['mes']) ? intval($_GET['mes']) : 0;

$meses_nom = [1=>"Enero",2=>"Febrero",3=>"Marzo",4=>"Abril",5=>"Mayo",6=>"Junio",7=>"Julio",8=>"Agosto",9=>"Septiembre",10=>"Octubre",11=>"Noviembre",12=>"Diciembre"];
$texto_mes = ($mes_num > 0) ? $meses_nom[$mes_num] : "TODOS LOS MESES";

// FORZAR DESCARGA
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Registros_Actividad_{$id_actividad}_{$anio}.xls");
header("Pragma: no-cache");
header("Expires: 0");

// BOM UTF-8 (Vital para la Ñ y acentos)
echo "\xEF\xBB\xBF";

// 1. OBTENER NOMBRE DE LA ACTIVIDAD PARA EL TÍTULO
$nombre_actividad = "Registro de Actividades";
$sql_act = $conn->query("SELECT nombre_actividad FROM actividades WHERE id_actividad = $id_actividad");
if($sql_act && $row_act = $sql_act->fetch_assoc()) {
    $nombre_actividad = mb_strtoupper($row_act['nombre_actividad']);
}

// 2. EJECUTAR CONSULTA DE REGISTROS
$select_campos = "SELECT r.id_registro, DATE_FORMAT(r.fecha_registro, '%d/%m/%Y') as fecha_formateada, u.nombre_usuario, c.nombre_campo_actividad, v.valor ";
$from_where = "FROM registros_actividad r JOIN actividades a ON r.id_actividad = a.id_actividad JOIN usuarios u ON r.id_usuario = u.id_usuario JOIN valores_actividad v ON r.id_registro = v.id_registro JOIN campos_actividad c ON v.id_camposA = c.id_camposA WHERE r.id_actividad = $id_actividad AND YEAR(r.fecha_registro) = $anio";

if ($mes_num > 0) { 
    $from_where .= " AND MONTH(r.fecha_registro) = $mes_num"; 
}
$order_by_sql = " ORDER BY r.fecha_registro DESC, r.id_registro, c.id_camposA";

$res = $conn->query($select_campos . $from_where . $order_by_sql);

$registros = [];
$campos_unicos = [];

if($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $id = $row['id_registro'];
        if (!isset($registros[$id])) {
            $registros[$id] = [
                'fecha' => $row['fecha_formateada'],
                'usuario' => $row['nombre_usuario'],
                'campos' => []
            ];
        }
        $registros[$id]['campos'][$row['nombre_campo_actividad']] = $row['valor'];
        if (!isset($campos_unicos[$row['nombre_campo_actividad']])) {
            $campos_unicos[$row['nombre_campo_actividad']] = true;
        }
    }
}
$campos_unicos = array_keys($campos_unicos);
$total_columnas = count($campos_unicos) + 2; // 2 fijas (Fecha, Responsable) + dinámicas

// ESTILOS EN LÍNEA
$borde = 'border: .5pt solid #CBD5E1;';
$borde_fuerte = 'border: 1pt solid #1E293B;';
$th_main_title = "background-color: #1E293B; color: #FFFFFF; font-size: 18px; font-weight: bold; text-align: center; vertical-align: middle; padding: 15px; height: 50px; $borde_fuerte";
$th_sub_title = "background-color: #F8FAFC; color: #1E293B; font-size: 14px; font-weight: bold; text-align: center; vertical-align: middle; padding: 10px; height: 30px; $borde_fuerte";
$th_col = "background-color: #2F855A; color: #FFFFFF; font-weight: bold; padding: 12px; text-align: center; font-size: 13px; text-transform: uppercase; $borde_fuerte";
$td_data = "padding: 10px; font-size: 12px; color: #334155; text-align: center; vertical-align: middle; $borde";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif;">
        <thead>
            <tr>
                <th colspan="<?= $total_columnas ?>" style="<?= $th_main_title ?>">
                    COMITÉ ESTATAL PARA EL FOMENTO Y PROTECCIÓN PECUARIA DEL ESTADO DE NAYARIT
                </th>
            </tr>
            <tr>
                <th colspan="<?= $total_columnas ?>" style="<?= $th_sub_title ?>">
                    REGISTRO DE CAMPO: <?= $nombre_actividad ?> | PERIODO: <?= $texto_mes ?> <?= $anio ?>
                </th>
            </tr>
            <tr><th colspan="<?= $total_columnas ?>" style="height: 15px;"></th></tr>
            <tr>
                <th width="150" style="<?= $th_col ?>">Fecha de Registro</th>
                <th width="250" style="<?= $th_col ?>">Responsable</th>
                <?php foreach($campos_unicos as $campo): ?>
                    <th width="200" style="<?= $th_col ?>"><?= htmlspecialchars($campo) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (count($registros) > 0): ?>
                <?php foreach($registros as $reg): ?>
                    <tr>
                        <td style="<?= $td_data ?> font-weight: bold;"><?= htmlspecialchars($reg['fecha']) ?></td>
                        <td style="<?= $td_data ?> text-align: left;"><?= htmlspecialchars($reg['usuario']) ?></td>
                        <?php foreach($campos_unicos as $campo): ?>
                            <td style="<?= $td_data ?>"><?= htmlspecialchars($reg['campos'][$campo] ?? '-') ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?= $total_columnas ?>" style="text-align: center; padding: 20px; font-weight: bold; color: #64748B;">No se encontraron registros para el periodo seleccionado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
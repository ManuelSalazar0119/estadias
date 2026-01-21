<?php
// funciones/exportar_excel.php - VERSIÓN CON DISEÑO DEL PANEL
include_once("conexion.php");
include("../funciones/vendor/autoload.php");
session_start();

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /login.php');
    exit;
}

$rol = $_SESSION['rol'] ?? 'medico';
$nombre = $_SESSION['nombre_usuario'] ?? 'Usuario';

// Obtener parámetros de filtro
$id_actividad = isset($_GET['id_actividad']) ? intval($_GET['id_actividad']) : 0;
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : 0;

// Verificar si es solicitud de descarga directa
$descargar = isset($_GET['descargar']) ? $_GET['descargar'] : false;

// Solo si es descarga directa, mostrar el Excel puro
if ($descargar === 'true') {
    // VALIDACIONES PARA DESCARGA...
    if ($id_actividad == 0) {
        die("Error: No se ha seleccionado una actividad para exportar.");
    }

    // Obtener nombre de la actividad
    $nombre_actividad = "Actividad";
    $res_nombre = $conn->query("SELECT nombre_actividad FROM actividades WHERE id_actividad = $id_actividad");
    if ($row_nombre = $res_nombre->fetch_assoc()) {
        $nombre_actividad = $row_nombre['nombre_actividad'];
    }

    // Obtener la MISMA consulta que en panel_control.php
    $sql = "
    SELECT 
        r.id_registro,
        DATE_FORMAT(r.fecha_registro, '%Y-%m-%d') as fecha_formateada,
        a.nombre_actividad,
        u.nombre_usuario,
        c.nombre_campo_actividad,
        v.valor
    FROM registros_actividad r
    JOIN actividades a ON r.id_actividad = a.id_actividad
    JOIN usuarios u ON r.id_usuario = u.id_usuario
    JOIN valores_actividad v ON r.id_registro = v.id_registro
    JOIN campos_actividad c ON v.id_camposA = c.id_camposA
    WHERE r.id_actividad = $id_actividad
    AND YEAR(r.fecha_registro) = $anio
    ";

    if ($mes > 0) {
        $sql .= " AND MONTH(r.fecha_registro) = $mes";
    }

    $sql .= "
    ORDER BY r.fecha_registro DESC, r.id_registro, c.id_camposA
    ";

    $res = $conn->query($sql);

    if (!$res) {
        die("Error en la consulta: " . $conn->error);
    }

    // Armar el arreglo de registros
    $registros = [];
    $campos_unicos = [];

    while ($row = $res->fetch_assoc()) {
        $id = $row['id_registro'];
        if (!isset($registros[$id])) {
            $registros[$id] = [
                'fecha' => $row['fecha_formateada'],
                'actividad' => $row['nombre_actividad'],
                'usuario' => $row['nombre_usuario'],
                'campos' => []
            ];
        }
        $registros[$id]['campos'][$row['nombre_campo_actividad']] = $row['valor'];
        
        if (!in_array($row['nombre_campo_actividad'], $campos_unicos)) {
            $campos_unicos[] = $row['nombre_campo_actividad'];
        }
    }

    // Si no hay datos
    if (empty($registros)) {
        header('Content-Type: text/html; charset=utf-8');
        echo "<h2>No hay datos para exportar con los filtros seleccionados</h2>";
        echo "<p><a href='javascript:history.back()'>← Volver al panel</a></p>";
        exit;
    }

    // Configurar nombre del archivo CON EXTENSIÓN .XLS
    $nombre_safe = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $nombre_actividad);
    $nombre_archivo = "export_" . $nombre_safe . "_" . 
                     ($mes > 0 ? sprintf("%02d", $mes) . "_" : "") . 
                     $anio . "_" . date('Y-m-d') . ".xls";

    // Headers para Excel .XLS
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Limpiar buffer
    if (ob_get_length()) {
        ob_end_clean();
    }

    // Generar Excel como HTML (funciona perfectamente)
    ?>
    <html xmlns:o="urn:schemas-microsoft-com:office:office"
          xmlns:x="urn:schemas-microsoft-com:office:excel"
          xmlns="http://www.w3.org/TR/REC-html40">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <!--[if gte mso 9]>
        <xml>
            <x:ExcelWorkbook>
                <x:ExcelWorksheets>
                    <x:ExcelWorksheet>
                        <x:Name><?php echo htmlspecialchars($nombre_actividad); ?></x:Name>
                        <x:WorksheetOptions>
                            <x:DisplayGridlines/>
                        </x:WorksheetOptions>
                    </x:ExcelWorksheet>
                </x:ExcelWorksheets>
            </x:ExcelWorkbook>
        </xml>
        <![endif]-->
        <style>
            table {
                border-collapse: collapse;
                width: 100%;
                font-family: Arial, sans-serif;
                font-size: 11px;
            }
            th {
                background-color: #2c3e50;
                color: white;
                font-weight: bold;
                padding: 8px;
                text-align: center;
                border: 1px solid #ddd;
                white-space: nowrap;
            }
            td {
                padding: 6px;
                border: 1px solid #ddd;
                text-align: left;
                vertical-align: top;
            }
            .fila-par {
                background-color: #f8f9fa;
            }
            .fila-impar {
                background-color: white;
            }
            .info-header {
                background-color: #e8f5e9;
                padding: 10px;
                margin-bottom: 15px;
                border: 1px solid #c8e6c9;
                font-size: 11px;
            }
            .total-row {
                background-color: #4CAF50 !important;
                color: white;
                font-weight: bold;
            }
        </style>
    </head>
    <body>

    <div class="info-header">
        <strong>INFORMACIÓN DE EXPORTACIÓN</strong><br>
        <strong>Actividad:</strong> <?php echo htmlspecialchars($nombre_actividad); ?><br>
        <strong>Año:</strong> <?php echo $anio; ?><br>
        <strong>Mes:</strong> <?php echo ($mes > 0 ? date('F', mktime(0, 0, 0, $mes, 1)) : 'Todos'); ?><br>
        <strong>Total registros:</strong> <?php echo count($registros); ?><br>
        <strong>Fecha exportación:</strong> <?php echo date('Y-m-d H:i:s'); ?><br>
        <strong>Exportado por:</strong> <?php echo htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario'); ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>FECHA</th>
                <th>ACTIVIDAD</th>
                <th>USUARIO</th>
                <?php foreach ($campos_unicos as $campo): ?>
                <th><?php echo strtoupper(htmlspecialchars($campo)); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php 
            $contador = 0;
            foreach ($registros as $id => $registro): 
                $clase_fila = ($contador % 2 == 0) ? 'fila-par' : 'fila-impar';
                $contador++;
            ?>
            <tr class="<?php echo $clase_fila; ?>">
                <td><?php echo htmlspecialchars($registro['fecha']); ?></td>
                <td><?php echo htmlspecialchars($registro['actividad']); ?></td>
                <td><?php echo htmlspecialchars($registro['usuario']); ?></td>
                
                <?php foreach ($campos_unicos as $campo): ?>
                <td>
                    <?php 
                    $valor = $registro['campos'][$campo] ?? '-';
                    echo htmlspecialchars($valor);
                    ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
            
            <tr class="total-row">
                <td colspan="3" style="text-align: right; padding-right: 20px;">TOTAL DE REGISTROS EXPORTADOS:</td>
                <td colspan="<?php echo count($campos_unicos); ?>" style="text-align: left; padding-left: 20px;">
                    <?php echo count($registros); ?> registro(s)
                </td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 20px; padding: 10px; border-top: 1px solid #ddd; font-size: 10px; color: #666; text-align: center;">
        Sistema de Control de Actividades - Generado automáticamente - <?php echo date('Y'); ?>
    </div>

    </body>
    </html>
    <?php
    exit();
}
// FIN DE LA PARTE DE DESCARGA

// ==============================================
// A PARTIR DE AQUÍ: VISTA WEB CON DISEÑO DEL PANEL
// ==============================================

// Obtener nombre de la actividad para mostrar
$nombre_actividad_actual = 'Exportar Datos';
if ($id_actividad > 0) {
    $res_nombre = $conn->query("SELECT nombre_actividad FROM actividades WHERE id_actividad = $id_actividad");
    if ($row_nombre = $res_nombre->fetch_assoc()) {
        $nombre_actividad_actual = $row_nombre['nombre_actividad'];
    }
}

// Obtener nombre del mes
$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
$mes_nombre = $mes > 0 ? $meses[$mes] : 'Todos';

// Obtener actividades para los filtros
$actividades = [];
$res_acts = $conn->query("SELECT id_actividad, nombre_actividad FROM actividades ORDER BY nombre_actividad");
while ($row = $res_acts->fetch_assoc()) {
    $actividades[] = $row;
}

// URL para descargar Excel
$descarga_url = "exportar_excel.php?id_actividad=" . $id_actividad . 
                "&anio=" . $anio . "&mes=" . $mes . "&descargar=true";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exportar a Excel - Sistema Activo</title>
    <link rel="stylesheet" href="../css/panel_control.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/sidebar_unificado.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="panel-control">
    <!-- Header Superior -->
    <header class="panel-header">
        <!-- IZQUIERDA: Botón Menú + Título -->
        <?php 
        // Incluir el sidebar izquierdo si existe
        if (file_exists('../interfaces/sidebar_left.php')) {
            include '../interfaces/sidebar_left.php';
        } else {
            echo '<div class="header-left">';
            echo '<div class="page-title">';
            echo '<i class="fas fa-file-excel"></i>';
            echo '<span>Exportar a Excel</span>';
            echo '</div>';
            echo '</div>';
        }
        ?>
        
        <!-- DERECHA: Filtros activos + Total -->
        <div class="header-right">
            <!-- Filtros activos -->
            <div class="active-filters-header">
                <span class="filter-badge">
                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($nombre_actividad_actual); ?>
                </span>
                <span class="filter-badge">
                    <i class="fas fa-calendar"></i> <?php echo $mes_nombre . ' ' . $anio; ?>
                </span>
            </div>
            
            <!-- Contador total (puedes calcularlo si necesitas) -->
            <div class="total-counter">
                <div class="counter-icon">
                    <i class="fas fa-file-excel"></i>
                </div>
                <div class="counter-info">
                    <span class="counter-label">EXPORTAR</span>
                    <span class="counter-value">XLS</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Layout Principal -->
    <div class="panel-layout">
        <!-- Sidebar -->
        <aside class="panel-sidebar">
            <nav class="sidebar-nav">
                <a href="../interfaces/panel_control.php" class="sidebar-link">
                    <i class="fas fa-chart-line"></i>
                    <span>Panel de Control</span>
                </a>
                <a href="exportar_excel.php" class="sidebar-link active">
                    <i class="fas fa-file-excel"></i>
                    <span>Exportar a Excel</span>
                </a>
                <a href="../interfaces/reporte_mensual.php" class="sidebar-link">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reportes</span>
                </a>
                <div class="sidebar-divider"></div>
                
                <?php if ($rol === 'admin'): ?>
                <a href="../interfaces/configuracion_sistema.php" class="sidebar-link">
                    <i class="fas fa-sliders-h"></i>
                    <span>Configuración Sistema</span>
                </a>
                <?php endif; ?>
            </nav>
            
            <div class="sidebar-footer">
                <div class="system-status">
                    <div class="status-indicator active"></div>
                    <span>Sistema Activo</span>
                </div>
                <small>v2.1.0</small>
            </div>
        </aside>

        <!-- Contenido Principal -->
        <main class="panel-content">
            <!-- Filtros -->
            <div class="filters-header">
                <div class="filters-info">
                    <div class="current-filters">
                        <span class="filter-badge">
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($nombre_actividad_actual); ?>
                        </span>
                        <span class="filter-badge">
                            <i class="fas fa-calendar"></i> <?php echo $mes_nombre . ' ' . $anio; ?>
                        </span>
                    </div>
                    
                    <div class="filters-stats">
                        <div class="stat-card">
                            <div class="stat-icon total">
                                <i class="fas fa-download"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label">Formato</span>
                                <span class="stat-value">Excel</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="filters-actions">
                    <a href="<?php echo $descarga_url; ?>" 
                       class="btn btn-export"
                       onclick="return confirm('¿Descargar archivo Excel con los filtros actuales?');">
                        <i class="fas fa-download"></i>
                        Descargar Excel
                    </a>
                </div>
            </div>

            <!-- Panel de Filtros -->
            <div class="advanced-filters show">
                <form method="get" class="filter-form" id="exportFilterForm">
                    <div class="filter-group">
                        <label for="id_actividad" class="filter-label">
                            <i class="fas fa-tasks"></i> Actividad
                        </label>
                        <select id="id_actividad" name="id_actividad" class="filter-select">
                            <option value="0" <?= $id_actividad == 0 ? 'selected' : '' ?>>Seleccionar actividad</option>
                            <?php foreach ($actividades as $act): ?>
                                <option value="<?= $act['id_actividad'] ?>" 
                                    <?= $act['id_actividad'] == $id_actividad ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($act['nombre_actividad']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="mes" class="filter-label">
                            <i class="fas fa-calendar-alt"></i> Mes
                        </label>
                        <select id="mes" name="mes" class="filter-select">
                            <option value="0" <?= $mes == 0 ? 'selected' : '' ?>>Todos los meses</option>
                            <?php foreach ($meses as $num => $nombre_mes): ?>
                                <option value="<?= $num ?>" <?= $num == $mes ? 'selected' : '' ?>>
                                    <?= $nombre_mes ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="anio" class="filter-label">
                            <i class="fas fa-calendar"></i> Año
                        </label>
                        <select id="anio" name="anio" class="filter-select">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == $anio ? 'selected' : '' ?>>
                                    <?= $y ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Aplicar Filtros
                        </button>
                    </div>
                    
                    <div class="filter-group">
                        <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                            <i class="fas fa-redo"></i>
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Contenido de Exportación -->
            <div class="data-table-container">
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-file-excel"></i>
                    </div>
                    <h3>Exportación a Excel</h3>
                    <p>Configura los filtros y descarga los datos en formato Excel (.xls)</p>
                    
                    <div style="margin-top: 2rem; text-align: left; max-width: 600px; margin: 2rem auto;">
                        <h4>Información de la exportación:</h4>
                        <ul style="list-style: none; padding: 0; margin: 1rem 0;">
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                                <i class="fas fa-check-circle" style="color: #4CAF50; margin-right: 10px;"></i>
                                <strong>Actividad:</strong> <?php echo htmlspecialchars($nombre_actividad_actual); ?>
                            </li>
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                                <i class="fas fa-check-circle" style="color: #4CAF50; margin-right: 10px;"></i>
                                <strong>Período:</strong> <?php echo $mes_nombre . ' ' . $anio; ?>
                            </li>
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                                <i class="fas fa-check-circle" style="color: #4CAF50; margin-right: 10px;"></i>
                                <strong>Formato:</strong> Microsoft Excel (.xls)
                            </li>
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                                <i class="fas fa-check-circle" style="color: #4CAF50; margin-right: 10px;"></i>
                                <strong>Compatibilidad:</strong> Excel 97-2003
                            </li>
                        </ul>
                    </div>
                    
                    <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center;">
                        <a href="<?php echo $descarga_url; ?>" class="btn btn-export">
                            <i class="fas fa-download"></i>
                            Descargar Excel
                        </a>
                        <a href="../interfaces/panel_control.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Volver al Panel
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function clearFilters() {
            document.getElementById('id_actividad').value = '0';
            document.getElementById('mes').value = '0';
            document.getElementById('anio').value = '<?php echo date("Y"); ?>';
            document.getElementById('exportFilterForm').submit();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Auto-submit cuando cambian los filtros
            const filterSelects = document.querySelectorAll('.filter-select');
            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    setTimeout(() => {
                        document.getElementById('exportFilterForm').submit();
                    }, 300);
                });
            });
        });
    </script>
</body>
</html>
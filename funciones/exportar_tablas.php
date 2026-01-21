<?php
// ==============================================
// PARTE 1: VISTA WEB CON DISEÑO DEL PANEL
// ==============================================
require '../funciones/vendor/autoload.php';
include_once("../funciones/conexion.php");
include_once("../funciones/funciones_reportes.php");
session_start();

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /login.php');
    exit;
}

$rol = $_SESSION['rol'] ?? 'medico';
$nombre = $_SESSION['nombre_usuario'] ?? 'Usuario';

// Verificar si es solicitud de descarga
$descargar = isset($_GET['descargar']) ? $_GET['descargar'] : false;

// Si no es descarga, mostrar vista web con diseño
if ($descargar !== 'true') {
    // Obtener parámetros de filtro
    $id_area = isset($_GET['id_area']) ? intval($_GET['id_area']) : 0;
    $anio    = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
    $mes     = isset($_GET['mes']) ? intval($_GET['mes']) : date('m');
    $tipo_reporte = isset($_GET['tipo_reporte']) ? $_GET['tipo_reporte'] : 'TB';

    // Obtener nombre del área
    $nombre_area = 'General';
    if ($id_area > 0) {
        $res_area = $conn->query("SELECT nombre_area FROM areas WHERE id_area = $id_area");
        if ($row_area = $res_area->fetch_assoc()) {
            $nombre_area = $row_area['nombre_area'];
        }
    }

    // Obtener nombre del mes
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    $mes_nombre = $mes > 0 ? $meses[$mes] : 'Todos';

    // Obtener áreas para filtro
    $areas = [];
    $res_areas = $conn->query("SELECT id_area, nombre_area FROM areas ORDER BY nombre_area");
    while ($row = $res_areas->fetch_assoc()) {
        $areas[] = $row;
    }

    // Obtener datos para mostrar en vista previa
    $datos_vista = obtenerDatosParaVista($conn, $id_area, $anio, $mes, $tipo_reporte);

    // URL para descargar Excel
    $descarga_url = "exportar_tablas.php?id_area=" . $id_area . 
                   "&anio=" . $anio . "&mes=" . $mes . 
                   "&tipo_reporte=" . $tipo_reporte . "&descargar=true";

    // Mostrar vista con diseño del panel
    mostrarVistaConDiseño($areas, $nombre_area, $mes_nombre, $anio, $tipo_reporte, $datos_vista, $descarga_url);
    exit();
}

// ==============================================
// PARTE 2: GENERAR EXCEL (descargar=true)
// ==============================================
// Si llega aquí, es porque descargar=true
// El resto del código original para generar Excel...

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// =====================
// PARÁMETROS
// =====================
$id_area = intval($_GET['id_area'] ?? 0);
$anio    = intval($_GET['anio'] ?? date('Y'));
$mes     = intval($_GET['mes'] ?? date('m'));
$tipo_reporte = $_GET['tipo_reporte'] ?? 'TB';

// =====================
// CONSULTA ACTIVIDADES CON UNIDADES
// =====================
$sql_actividades = "
    SELECT pm.id_actividad, pm.unidad, a.nombre_actividad
    FROM programacion_mensual pm
    INNER JOIN actividades a ON pm.id_actividad = a.id_actividad
    INNER JOIN grupo_actividad_detalle gad ON gad.id_actividad = pm.id_actividad
    WHERE a.id_area = ?
      AND pm.anio = ?
      AND pm.mes = ?
    ORDER BY a.nombre_actividad, pm.unidad
";
$stmt = $conn->prepare($sql_actividades);
$stmt->bind_param("iii", $id_area, $anio, $mes);
$stmt->execute();
$result_actividades = $stmt->get_result();
$actividades = $result_actividades->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// =====================
// CONSULTA PROGRAMACIÓN ANUAL
// =====================
$sql_anual = "SELECT id_actividad, programado 
              FROM programacion_anual 
              WHERE anio = ?";
$stmt = $conn->prepare($sql_anual);
$stmt->bind_param("i", $anio);
$stmt->execute();
$res_anual = $stmt->get_result();
$prog_anual = [];
while($row = $res_anual->fetch_assoc()){
    $prog_anual[$row['id_actividad']] = $row['programado'];
}
$stmt->close();

// =====================
// SPREADSHEET
// =====================
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Reporte Mensual");

// =====================
// ESTILOS
// =====================
$estiloCabecera = [
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];
$estiloNormal = [
    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];
$estiloCentro = [
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];

// =====================
// CABECERA CON MERGES
// =====================
$sheet->mergeCells('A1:A4');
$sheet->mergeCells('B1:B4');
$sheet->mergeCells('C1:I1'); // Avance Físico
$sheet->mergeCells('J1:J4');
$sheet->mergeCells('K1:K4');

$sheet->mergeCells('C2:C4'); // Programado Anual
$sheet->mergeCells('D2:F2'); // En el Mes
$sheet->mergeCells('G2:I2'); // Acumulado al Mes

$sheet->mergeCells('D3:E4'); // Programado
$sheet->mergeCells('F3:F4'); // Realizado
$sheet->mergeCells('G3:H4'); // Programado acumulado
$sheet->mergeCells('I3:I4'); // Realizado acumulado

// Valores cabecera
$sheet->setCellValue('A1', 'Acción/Actividad');
$sheet->setCellValue('B1', 'Unidad de Medida');
$sheet->setCellValue('C1', 'Avance Físico');
$sheet->setCellValue('C2', 'Programado Anual');
$sheet->setCellValue('D2', 'En el Mes');
$sheet->setCellValue('D3', 'Programado');
$sheet->setCellValue('F3', 'Realizado');
$sheet->setCellValue('G2', 'Acumulado al Mes');
$sheet->setCellValue('G3', 'Programado');
$sheet->setCellValue('I3', 'Realizado');
$sheet->setCellValue('J1', '% de avance anual');
$sheet->setCellValue('K1', '% de avance acumulado');

// Aplicar estilo cabecera
$sheet->getStyle('A1:K4')->applyFromArray($estiloCabecera);

// =====================
// FILAS AUTOMÁTICAS
// =====================
$fila = 5;
foreach($actividades as $act){
    $id = $act['id_actividad'];
    $unidad = $act['unidad'];

    // Valores desde funciones
    $programado_anual  = $prog_anual[$id] ?? 0;
    $programado_mes    = obtenerProgramadoMensual($conn,$id,$unidad,$anio,$mes);

    // Dependiendo de la unidad, se llama a la función correcta
    $realizado_mes = 0;
    if($unidad == 'Unidades de Producción') $realizado_mes = obtenerUPPs($conn,$id,$anio,$mes);
    elseif($unidad == 'Cabeza') $realizado_mes = obtenerCabezas($conn,$id,$anio,$mes);
    // aquí puedes agregar más condiciones según tus unidades

    // Acumulado al mes (ejemplo: se puede usar la misma función con acumulado)
    $programado_acum = $programado_mes; // reemplaza con tu función de acumulado si la tienes
    $realizado_acum  = $realizado_mes;  // idem

    $porc_anual = $programado_anual>0 ? round($programado_mes/$programado_anual*100,2) : 0;
    $porc_acum  = $programado_anual>0 ? round($programado_acum/$programado_anual*100,2) : 0;

    // Escribir en hoja
    $sheet->setCellValue("A$fila", $act['nombre_actividad']);
    $sheet->setCellValue("B$fila", $unidad);
    $sheet->setCellValue("C$fila", $programado_anual);
    $sheet->setCellValue("D$fila", $programado_mes);
    $sheet->setCellValue("F$fila", $realizado_mes);
    $sheet->setCellValue("G$fila", $programado_acum);
    $sheet->setCellValue("I$fila", $realizado_acum);
    $sheet->setCellValue("J$fila", $porc_anual.'%');
    $sheet->setCellValue("K$fila", $porc_acum.'%');

    // Estilos
    $sheet->getStyle("A$fila")->applyFromArray($estiloNormal);
    foreach(['B','C','D','F','G','I','J','K'] as $col){
        $sheet->getStyle($col.$fila)->applyFromArray($estiloCentro);
    }

    $fila++;
}

// Ajustar ancho de columnas
foreach(range('A','K') as $col){
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// =====================
// EXPORTAR
// =====================
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="reporte_' . $tipo_reporte . '_' . $anio . '_' . sprintf("%02d", $mes) . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;

// ==============================================
// FUNCIONES AUXILIARES
// ==============================================

function obtenerDatosParaVista($conn, $id_area, $anio, $mes, $tipo_reporte) {
    // Esta función obtiene datos para mostrar en la vista previa
    // Puedes adaptarla según tus necesidades
    
    $datos = [];
    
    // Ejemplo de datos de prueba (reemplaza con tu consulta real)
    if ($tipo_reporte == 'TB') {
        $datos = [
            ['Vigilancia', '', '', '', '', '', '', '', '', '', '', ''],
            ['Realización de Pruebas Cervicales Comparativas','Unidades de Producción','1,441','2323','1','','870','918','63.71%','105.52%'],
            ['Realización de Pruebas Cervicales Comparativas','Cabeza','2,408','170','170','','1,214','1,612','66.94%','132.78%'],
            ['Muestreo en Rastro','Muestra','80','7','10','','10','48','110.00%','183.33%'],
            ['Diagnóstico Histopatológico','Diagnóstico','80','7','10','','10','48','110.00%','183.33%'],
            ['Diagnóstico de Bacteriología','Diagnóstico','80','7','10','','10','48','110.00%','183.33%'],
            ['Tipificación','Diagnóstico','15','2','0','','0','11','66.67%','90.91%'],
            ['Diagnóstico PCR','Diagnóstico','15','2','0','','0','10','6.67%','10.00%'],
            ['Cabezas Sacrificadas','Cabeza','11,100','935','933','','933','6,117','52.50%','95.26%'],
            ['Cabezas Inspeccionadas','Cabeza','10,608','888','918','','918','5,876','54.08%','97.63%'],
            ['Inspección en Rastro','Porcentaje','100','98','98','','98','98','98.00%','100.00%'],
            ['Barrido','Unidades de Producción','289','42','9','','9','147','62.98%','123.81%'],
            ['Barrido','Cabezas','9,419','1,225','249','','249','4,720','66.63%','132.97%'],
            ['Realización Pruebas en Hatos Bovinos Cuarentena','Unidades de Producción','34','14','10','','10','39','38.24%','68.42%']
        ];
    }
    
    return $datos;
}

function mostrarVistaConDiseño($areas, $nombre_area, $mes_nombre, $anio, $tipo_reporte, $datos_vista, $descarga_url) {
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
        <style>
            /* Estilos específicos para la tabla de exportación */
            .export-table-container {
                background: white;
                border-radius: var(--radius-lg);
                overflow: hidden;
                box-shadow: var(--shadow-sm);
                border: 1px solid var(--gray-200);
                margin-top: 1rem;
            }
            
            .export-table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0;
                min-width: 1200px;
            }
            
            .export-table th {
                background: var(--gray-50);
                padding: 1rem;
                text-align: center;
                font-weight: 600;
                color: var(--gray-700);
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                border-bottom: 2px solid var(--gray-200);
                border-right: 1px solid var(--gray-200);
                vertical-align: middle;
            }
            
            .export-table th:last-child {
                border-right: none;
            }
            
            .export-table td {
                padding: 0.75rem;
                border-bottom: 1px solid var(--gray-100);
                border-right: 1px solid var(--gray-100);
                text-align: center;
                vertical-align: middle;
            }
            
            .export-table td:last-child {
                border-right: none;
            }
            
            .export-table tbody tr:hover {
                background: var(--gray-50);
            }
            
            .export-table .header-group {
                background: var(--primary-color);
                color: white;
                font-weight: 600;
            }
            
            .export-table .sub-header {
                background: var(--gray-100);
                font-weight: 500;
                font-size: 0.8rem;
            }
            
            .percentage-cell {
                font-weight: 600;
                border-radius: var(--radius-sm);
                padding: 0.25rem 0.5rem;
                display: inline-block;
                min-width: 60px;
            }
            
            .percentage-cell.high {
                background: #d1fae5;
                color: #065f46;
            }
            
            .percentage-cell.medium {
                background: #fef3c7;
                color: #92400e;
            }
            
            .percentage-cell.low {
                background: #fee2e2;
                color: #991b1b;
            }
            
            .export-actions {
                display: flex;
                justify-content: flex-end;
                align-items: center;
                gap: 1rem;
                margin-bottom: 1.5rem;
            }
            
            .export-info {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.75rem 1rem;
                background: var(--gray-50);
                border-radius: var(--radius-md);
                border: 1px solid var(--gray-200);
            }
            
            .export-info i {
                color: var(--primary-color);
            }
            
            .tipo-reporte-badges {
                display: flex;
                gap: 0.5rem;
                margin-top: 0.5rem;
            }
            
            .tipo-reporte-btn {
                padding: 0.5rem 1rem;
                border: 1px solid var(--gray-300);
                border-radius: var(--radius-md);
                background: white;
                cursor: pointer;
                font-size: 0.875rem;
                transition: all var(--transition-fast);
            }
            
            .tipo-reporte-btn.active {
                background: var(--primary-color);
                color: white;
                border-color: var(--primary-color);
            }
            
            .tipo-reporte-btn:hover {
                background: var(--gray-100);
            }
            
            .tipo-reporte-btn.active:hover {
                background: var(--primary-dark);
            }
        </style>
    </head>
    <body class="panel-control">
        <!-- Header Superior -->
        <header class="panel-header">
            <!-- IZQUIERDA: Botón Menú + Título -->
            <div class="header-left">
                <div class="page-title">
                    <i class="fas fa-file-excel"></i>
                    <span>Exportar a Excel</span>
                </div>
            </div>
            
            <!-- DERECHA: Filtros activos + Total -->
            <div class="header-right">
                <!-- Filtros activos -->
                <div class="active-filters-header">
                    <span class="filter-badge">
                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($nombre_area); ?>
                    </span>
                    <span class="filter-badge">
                        <i class="fas fa-calendar"></i> <?php echo $mes_nombre . ' ' . $anio; ?>
                    </span>
                    <span class="filter-badge">
                        <i class="fas fa-file-medical"></i> <?php echo htmlspecialchars($tipo_reporte); ?>
                    </span>
                </div>
                
                <!-- Contador total -->
                <div class="total-counter">
                    <div class="counter-icon">
                        <i class="fas fa-table"></i>
                    </div>
                    <div class="counter-info">
                        <span class="counter-label">FILAS</span>
                        <span class="counter-value"><?php echo count($datos_vista); ?></span>
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
                    <a href="exportar_tablas.php" class="sidebar-link active">
                        <i class="fas fa-file-excel"></i>
                        <span>Exportar Reportes</span>
                    </a>
                    <a href="../interfaces/reporte_mensual.php" class="sidebar-link">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reportes</span>
                    </a>
                    <a href="../interfaces/exportar_excel.php" class="sidebar-link">
                        <i class="fas fa-file-export"></i>
                        <span>Exportar Simple</span>
                    </a>
                    <div class="sidebar-divider"></div>
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
                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($nombre_area); ?>
                            </span>
                            <span class="filter-badge">
                                <i class="fas fa-calendar"></i> <?php echo $mes_nombre . ' ' . $anio; ?>
                            </span>
                            <span class="filter-badge">
                                <i class="fas fa-file-medical"></i> <?php echo htmlspecialchars($tipo_reporte); ?>
                            </span>
                        </div>
                        
                        <div class="filters-stats">
                            <div class="stat-card">
                                <div class="stat-icon total">
                                    <i class="fas fa-table"></i>
                                </div>
                                <div class="stat-info">
                                    <span class="stat-label">Registros</span>
                                    <span class="stat-value"><?php echo count($datos_vista); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="filters-actions">
                        <div class="export-actions">
                            <div class="export-info">
                                <i class="fas fa-info-circle"></i>
                                <span>Listo para exportar <?php echo count($datos_vista); ?> registros a Excel</span>
                            </div>
                            <a href="<?php echo $descarga_url; ?>" 
                               class="btn btn-export"
                               onclick="return confirmExport(<?php echo count($datos_vista); ?>);">
                                <i class="fas fa-download"></i>
                                Descargar Excel (XLSX)
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Panel de Filtros -->
                <div class="advanced-filters show">
                    <form method="get" class="filter-form" id="exportFilterForm">
                        <div class="filter-group">
                            <label for="id_area" class="filter-label">
                                <i class="fas fa-map-marker-alt"></i> Área
                            </label>
                            <select id="id_area" name="id_area" class="filter-select">
                                <option value="0" <?= $id_area == 0 ? 'selected' : '' ?>>Todas las áreas</option>
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?= $area['id_area'] ?>" 
                                        <?= $area['id_area'] == $id_area ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($area['nombre_area']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="mes" class="filter-label">
                                <i class="fas fa-calendar-alt"></i> Mes
                            </label>
                            <select id="mes" name="mes" class="filter-select">
                                <option value="0">Todos los meses</option>
                                <?php 
                                $meses = [
                                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                                ];
                                foreach ($meses as $num => $nombre_mes): 
                                ?>
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
                            <label class="filter-label">
                                <i class="fas fa-file-medical"></i> Tipo de Reporte
                            </label>
                            <div class="tipo-reporte-badges">
                                <button type="button" class="tipo-reporte-btn <?= $tipo_reporte == 'TB' ? 'active' : '' ?>" 
                                        onclick="setTipoReporte('TB')">
                                    Tuberculosis
                                </button>
                                <button type="button" class="tipo-reporte-btn <?= $tipo_reporte == 'BRU' ? 'active' : '' ?>" 
                                        onclick="setTipoReporte('BRU')">
                                    Brucelosis
                                </button>
                            </div>
                            <input type="hidden" id="tipo_reporte" name="tipo_reporte" value="<?= $tipo_reporte ?>">
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

                <!-- Tabla de Exportación (Vista Previa) -->
                <div class="export-table-container">
                    <div class="table-responsive">
                        <table class="export-table">
                            <thead>
                                <!-- Primera fila de encabezados principales -->
                                <tr>
                                    <th rowspan="2">Acción/Actividad</th>
                                    <th rowspan="2">Unidad de Medida</th>
                                    <th colspan="2" class="header-group">Avance Físico</th>
                                    <th colspan="4" class="header-group">Programado vs Realizado</th>
                                    <th rowspan="2">% de avance anual</th>
                                    <th rowspan="2">% de avance acumulado</th>
                                </tr>
                                <!-- Segunda fila de subencabezados -->
                                <tr>
                                    <!-- Subcolumnas de Avance Físico -->
                                    <th class="sub-header">Programado Anual</th>
                                    <th class="sub-header">En el Mes</th>
                                    
                                    <!-- Subcolumnas de Programado vs Realizado -->
                                    <th class="sub-header">Programado</th>
                                    <th class="sub-header">Realizado</th>
                                    <th class="sub-header">Programado</th>
                                    <th class="sub-header">Realizado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($datos_vista as $index => $row): ?>
                                <tr>
                                    <td style="text-align: left; font-weight: 500;"><?php echo htmlspecialchars($row[0]); ?></td>
                                    <td><?php echo htmlspecialchars($row[1]); ?></td>
                                    <td><?php echo $row[2]; ?></td>
                                    <td><?php echo $row[3]; ?></td>
                                    <td><?php echo $row[4]; ?></td>
                                    <td><?php echo $row[5]; ?></td>
                                    <td><?php echo $row[6]; ?></td>
                                    <td><?php echo $row[7]; ?></td>
                                    <td>
                                        <?php 
                                        if (!empty($row[8])) {
                                            $porcentaje = str_replace('%', '', $row[8]);
                                            $porcentaje_num = floatval($porcentaje);
                                            
                                            if ($porcentaje_num >= 100) {
                                                $clase = 'high';
                                            } elseif ($porcentaje_num >= 80) {
                                                $clase = 'medium';
                                            } else {
                                                $clase = 'low';
                                            }
                                            ?>
                                            <span class="percentage-cell <?php echo $clase; ?>">
                                                <?php echo $row[8]; ?>
                                            </span>
                                            <?php
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if (!empty($row[9])) {
                                            $porcentaje = str_replace('%', '', $row[9]);
                                            $porcentaje_num = floatval($porcentaje);
                                            
                                            if ($porcentaje_num >= 100) {
                                                $clase = 'high';
                                            } elseif ($porcentaje_num >= 80) {
                                                $clase = 'medium';
                                            } else {
                                                $clase = 'low';
                                            }
                                            ?>
                                            <span class="percentage-cell <?php echo $clase; ?>">
                                                <?php echo $row[9]; ?>
                                            </span>
                                            <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Información del export -->
                <div class="export-info-full" style="margin-top: 1.5rem; padding: 1rem; background: var(--gray-50); border-radius: var(--radius-md); border: 1px solid var(--gray-200);">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <i class="fas fa-info-circle" style="color: var(--primary-color); font-size: 1.25rem;"></i>
                        <div>
                            <strong>Información de exportación:</strong>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.5rem; margin-top: 0.5rem;">
                                <span><strong>Área:</strong> <?php echo htmlspecialchars($nombre_area); ?></span>
                                <span><strong>Período:</strong> <?php echo $mes_nombre . ' ' . $anio; ?></span>
                                <span><strong>Tipo de reporte:</strong> <?php echo htmlspecialchars($tipo_reporte); ?></span>
                                <span><strong>Total de filas:</strong> <?php echo count($datos_vista); ?></span>
                                <span><strong>Formato:</strong> Excel (.xlsx)</span>
                                <span><strong>Fecha de consulta:</strong> <?php echo date('d/m/Y H:i:s'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <script>
            function confirmExport(total) {
                if (total === 0) {
                    alert('No hay registros para exportar.');
                    return false;
                }
                
                return confirm(`¿Descargar ${total} registro(s) a Excel?\n\nÁrea: ${'<?php echo addslashes($nombre_area); ?>'}\nPeríodo: ${'<?php echo addslashes($mes_nombre); ?>'} ${'<?php echo $anio; ?>'}\nTipo: ${'<?php echo addslashes($tipo_reporte); ?>'}`);
            }

            function clearFilters() {
                document.getElementById('id_area').value = '0';
                document.getElementById('mes').value = '0';
                document.getElementById('anio').value = '<?php echo date("Y"); ?>';
                document.getElementById('tipo_reporte').value = 'TB';
                // Actualizar botones de tipo
                document.querySelectorAll('.tipo-reporte-btn').forEach(btn => {
                    btn.classList.remove('active');
                    if (btn.textContent.trim() === 'Tuberculosis') {
                        btn.classList.add('active');
                    }
                });
                document.getElementById('exportFilterForm').submit();
            }

            function setTipoReporte(tipo) {
                document.getElementById('tipo_reporte').value = tipo;
                // Actualizar clases de botones
                document.querySelectorAll('.tipo-reporte-btn').forEach(btn => {
                    btn.classList.remove('active');
                    if ((tipo === 'TB' && btn.textContent.trim() === 'Tuberculosis') ||
                        (tipo === 'BRU' && btn.textContent.trim() === 'Brucelosis')) {
                        btn.classList.add('active');
                    }
                });
                // Auto-submit
                setTimeout(() => {
                    document.getElementById('exportFilterForm').submit();
                }, 300);
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
    <?php
}
?>
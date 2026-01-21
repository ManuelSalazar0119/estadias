<?php
// interfaces/exportar_excel_diseno.php
include_once("../funciones/conexion.php");
session_start();


// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /login.php');
    exit;
}

$rol = $_SESSION['rol'] ?? 'medico';
$nombre = $_SESSION['nombre_usuario'] ?? 'Usuario';

// Obtener parámetros
$id_area = isset($_GET['id_area']) ? intval($_GET['id_area']) : 0;
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : 0;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'TB';

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

// Datos de ejemplo (REEMPLAZA CON TU CONSULTA REAL)
$datos = [
    ['Vigilancia', '', '', '', '', '', '', '', '', ''],
    ['Realización de Pruebas Cervicales Comparativas', 'Unidades de Producción', '1,441', '0', '0', '870', '918', '63.71%', '105.52%'],
    ['Realización de Pruebas Cervicales Comparativas', 'Cabeza', '2,408', '0', '0', '1,214', '1,612', '66.94%', '132.78%'],
    ['Muestreo en Rastro', 'Muestra', '80', '7', '10', '48', '88', '110.00%', '183.33%'],
    ['Diagnóstico Histopatológico', 'Diagnóstico', '80', '7', '10', '48', '88', '110.00%', '183.33%'],
    ['Diagnóstico de Bacteriología', 'Diagnóstico', '80', '7', '10', '48', '88', '110.00%', '183.33%'],
    ['Tipificación', 'Diagnóstico', '15', '2', '0', '11', '10', '66.67%', '90.91%'],
    ['Diagnóstico PCR', 'Diagnóstico', '15', '2', '0', '10', '1', '6.67%', '10.00%'],
    ['Cabezas Sacrificadas', 'Cabeza', '11,100', '935', '933', '6,117', '5,827', '52.50%', '95.26%'],
    ['Cabezas Inspeccionadas', 'Cabeza', '10,608', '888', '918', '5,876', '5,737', '54.08%', '97.63%'],
    ['Inspección en Rastro', 'Porcentaje', '100', '98', '98', '98', '98', '98.00%', '100.00%'],
    ['Barrido', 'Unidades de Producción', '289', '42', '9', '147', '182', '62.98%', '123.81%'],
    ['Barrido', 'Cabezas', '9,419', '1,225', '249', '4,720', '6,276', '66.63%', '132.97%'],
];

$total_registros = count($datos) - 1; // Restamos la fila de "Vigilancia"
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
            border-collapse: collapse;
            min-width: 1200px;
        }
        
        .export-table th {
            background: var(--gray-50);
            padding: 12px 8px;
            text-align: center;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border: 1px solid var(--gray-300);
            vertical-align: middle;
        }
        
        .export-table td {
            padding: 10px 8px;
            border: 1px solid var(--gray-200);
            text-align: center;
            vertical-align: middle;
            font-size: 12px;
        }
        
        .export-table tbody tr:hover {
            background: var(--gray-50);
        }
        
        /* Encabezados combinados */
        .header-combinado {
            background: var(--primary-color);
            color: white;
            font-weight: 600;
        }
        
        .sub-header {
            background: var(--gray-100);
            font-weight: 500;
            font-size: 10px;
        }
        
        /* Estilos para porcentajes */
        .porcentaje {
            font-weight: 600;
            border-radius: 4px;
            padding: 3px 8px;
            display: inline-block;
            min-width: 70px;
        }
        
        .porcentaje-alto {
            background: #d1fae5;
            color: #065f46;
        }
        
        .porcentaje-medio {
            background: #fef3c7;
            color: #92400e;
        }
        
        .porcentaje-bajo {
            background: #fee2e2;
            color: #991b1b;
        }
        
        /* Estilo para fila de grupo */
        .fila-grupo {
            background: var(--gray-50);
            font-weight: 600;
            text-align: left;
        }
        
        .fila-grupo td {
            font-weight: 600;
            color: var(--gray-800);
        }
        
        /* Botón flotante para descargar */
        .btn-descargar-flotante {
            position: fixed;
            right: 20px;
            bottom: 20px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            cursor: pointer;
            box-shadow: var(--shadow-lg);
            z-index: 1000;
        }
        
        .btn-descargar-flotante:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .export-table-container {
                overflow-x: auto;
                border-radius: var(--radius-md);
            }
            
            .btn-descargar-flotante {
                bottom: 70px;
                right: 10px;
                padding: 10px 20px;
            }
            
            .btn-descargar-flotante span {
                display: none;
            }
            
            .btn-descargar-flotante i {
                margin: 0;
            }
        }
    </style>
</head>
<body class="panel-control">
    <!-- Header Superior -->
    <header class="panel-header">
        <!-- IZQUIERDA: Título -->
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
                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($nombre_area); ?>
                </span>
                <span class="filter-badge">
                    <i class="fas fa-calendar"></i> <?php echo $mes_nombre . ' ' . $anio; ?>
                </span>
                <span class="filter-badge">
                    <i class="fas fa-virus"></i> <?php echo htmlspecialchars($tipo); ?>
                </span>
            </div>
            
            <!-- Contador total -->
            <div class="total-counter">
                <div class="counter-icon">
                    <i class="fas fa-table"></i>
                </div>
                <div class="counter-info">
                    <span class="counter-label">REGISTROS</span>
                    <span class="counter-value"><?php echo $total_registros; ?></span>
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
                <a href="exportar_excel_diseno.php" class="sidebar-link active">
                    <i class="fas fa-file-excel"></i>
                    <span>Exportar a Excel</span>
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
                <a href="../interfaces/nuevo_registro.php" class="sidebar-link">
                    <i class="fas fa-plus-circle"></i>
                    <span>Nuevo Registro</span>
                </a>
                <a href="../interfaces/registro_nueva_actividad.php" class="sidebar-link">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Nueva Actividad</span>
                </a>
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
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($nombre_area); ?>
                        </span>
                        <span class="filter-badge">
                            <i class="fas fa-calendar"></i> <?php echo $mes_nombre . ' ' . $anio; ?>
                        </span>
                        <span class="filter-badge">
                            <i class="fas fa-virus"></i> <?php echo htmlspecialchars($tipo); ?>
                        </span>
                    </div>
                    
                    <div class="filters-stats">
                        <div class="stat-card">
                            <div class="stat-icon total">
                                <i class="fas fa-table"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label">Filas</span>
                                <span class="stat-value"><?php echo $total_registros; ?></span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon total" style="background: #4CAF50;">
                                <i class="fas fa-download"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label">Formato</span>
                                <span class="stat-value">XLSX</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="filters-actions">
                    <button onclick="descargarExcel()" class="btn btn-export">
                        <i class="fas fa-download"></i>
                        Descargar Excel
                    </button>
                    <button onclick="imprimirTabla()" class="btn btn-secondary">
                        <i class="fas fa-print"></i>
                        Imprimir
                    </button>
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
                            <?php 
                            $areas = $conn->query("SELECT id_area, nombre_area FROM areas ORDER BY nombre_area");
                            while ($area = $areas->fetch_assoc()): 
                            ?>
                                <option value="<?= $area['id_area'] ?>" 
                                    <?= $area['id_area'] == $id_area ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($area['nombre_area']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="mes" class="filter-label">
                            <i class="fas fa-calendar-alt"></i> Mes
                        </label>
                        <select id="mes" name="mes" class="filter-select">
                            <option value="0">Todos los meses</option>
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
                        <label for="tipo" class="filter-label">
                            <i class="fas fa-virus"></i> Tipo
                        </label>
                        <select id="tipo" name="tipo" class="filter-select">
                            <option value="TB" <?= $tipo == 'TB' ? 'selected' : '' ?>>Tuberculosis</option>
                            <option value="BRU" <?= $tipo == 'BRU' ? 'selected' : '' ?>>Brucelosis</option>
                            <option value="AMBOS" <?= $tipo == 'AMBOS' ? 'selected' : '' ?>>Ambos</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Aplicar Filtros
                        </button>
                    </div>
                    
                    <div class="filter-group">
                        <button type="button" class="btn btn-secondary" onclick="limpiarFiltros()">
                            <i class="fas fa-redo"></i>
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla de Exportación -->
            <div class="export-table-container">
                <div class="table-responsive">
                    <table class="export-table">
                        <thead>
                            <!-- Primera fila de encabezados -->
                            <tr>
                                <th rowspan="2">Acción/Actividad</th>
                                <th rowspan="2">Unidad de Medida</th>
                                <th colspan="2" class="header-combinado">Avance Físico</th>
                                <th colspan="4" class="header-combinado">Programado vs Realizado</th>
                                <th rowspan="2">% de avance anual</th>
                                <th rowspan="2">% de avance acumulado</th>
                            </tr>
                            <!-- Segunda fila de subencabezados -->
                            <tr>
                                <th class="sub-header">Programado Anual</th>
                                <th class="sub-header">En el Mes</th>
                                <th class="sub-header">Programado</th>
                                <th class="sub-header">Realizado</th>
                                <th class="sub-header">Programado</th>
                                <th class="sub-header">Realizado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($datos as $index => $fila): ?>
                            <tr class="<?= $index == 0 ? 'fila-grupo' : '' ?>">
                                <td style="text-align: left;"><?php echo htmlspecialchars($fila[0]); ?></td>
                                <td><?php echo htmlspecialchars($fila[1]); ?></td>
                                <td><?php echo $fila[2]; ?></td>
                                <td><?php echo $fila[3]; ?></td>
                                <td><?php echo $fila[4]; ?></td>
                                <td><?php echo $fila[5]; ?></td>
                                <td><?php echo $fila[6]; ?></td>
                                <td>
                                    <?php if (!empty($fila[7])): 
                                        $porcentaje = str_replace('%', '', $fila[7]);
                                        $numero = floatval($porcentaje);
                                        $clase = ($numero >= 100) ? 'porcentaje-alto' : (($numero >= 80) ? 'porcentaje-medio' : 'porcentaje-bajo');
                                    ?>
                                        <span class="porcentaje <?php echo $clase; ?>">
                                            <?php echo $fila[7]; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($fila[8])): 
                                        $porcentaje = str_replace('%', '', $fila[8]);
                                        $numero = floatval($porcentaje);
                                        $clase = ($numero >= 100) ? 'porcentaje-alto' : (($numero >= 80) ? 'porcentaje-medio' : 'porcentaje-bajo');
                                    ?>
                                        <span class="porcentaje <?php echo $clase; ?>">
                                            <?php echo $fila[8]; ?>
                                        </span>
                                    <?php endif; ?>
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
                            <span><strong>Tipo de reporte:</strong> <?php echo htmlspecialchars($tipo); ?></span>
                            <span><strong>Total de registros:</strong> <?php echo $total_registros; ?></span>
                            <span><strong>Generado por:</strong> <?php echo htmlspecialchars($nombre); ?></span>
                            <span><strong>Fecha:</strong> <?php echo date('d/m/Y H:i:s'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Botón flotante para descargar -->
    <button class="btn-descargar-flotante" onclick="descargarExcel()">
        <i class="fas fa-download"></i>
        <span>Descargar Excel</span>
    </button>

    <script>
        function descargarExcel() {
            if (confirm('¿Descargar reporte a Excel?')) {
                // Aquí rediriges al archivo que genera el Excel real
                window.location.href = '../funciones/exportar_tablas.php?descargar=true&id_area=<?php echo $id_area; ?>&anio=<?php echo $anio; ?>&mes=<?php echo $mes; ?>&tipo=<?php echo $tipo; ?>';
            }
        }
        
        function imprimirTabla() {
            window.print();
        }
        
        function limpiarFiltros() {
            document.getElementById('id_area').value = '0';
            document.getElementById('mes').value = '0';
            document.getElementById('anio').value = '<?php echo date("Y"); ?>';
            document.getElementById('tipo').value = 'TB';
            document.getElementById('exportFilterForm').submit();
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-submit cuando cambian los filtros
            document.querySelectorAll('.filter-select').forEach(select => {
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
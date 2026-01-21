<?php
include_once("../funciones/conexion.php");
include("../funciones/vendor/autoload.php");
header('Content-Type: text/html; charset=utf-8');
session_start();

// Verifica si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /login.php');
    exit;
}

$rol = $_SESSION['rol'] ?? 'medico';
$nombre = $_SESSION['nombre_usuario'] ?? 'Usuario';

// Verificar columnas disponibles dinámicamente
$columnas_actividades = [];
$check_columns = $conn->query("SHOW COLUMNS FROM actividades");
while ($col = $check_columns->fetch_assoc()) {
    $columnas_actividades[] = $col['Field'];
}

// Construir consulta dinámicamente
$campos_select = ['id_actividad', 'nombre_actividad'];
if (in_array('icono', $columnas_actividades)) {
    $campos_select[] = 'icono';
}
if (in_array('color', $columnas_actividades)) {
    $campos_select[] = 'color';
}
if (in_array('orden', $columnas_actividades)) {
    $campos_select[] = 'orden';
}

$sql_select = implode(', ', $campos_select);
$order_by = in_array('orden', $columnas_actividades) ? 'ORDER BY orden, nombre_actividad' : 'ORDER BY nombre_actividad';

// Obtener actividades para los filtros
$actividades = [];
$res_acts = $conn->query("SELECT $sql_select FROM actividades $order_by");
while ($row = $res_acts->fetch_assoc()) {
    if (!isset($row['icono'])) $row['icono'] = 'clipboard-check';
    if (!isset($row['color'])) $row['color'] = '#388e3c';
    if (!isset($row['orden'])) $row['orden'] = 0;
    $actividades[] = $row;
}

// Obtener filtros actuales
$id_actividad = isset($_GET['id_actividad']) ? intval($_GET['id_actividad']) : 1;
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
$mes_num = isset($_GET['mes']) ? intval($_GET['mes']) : 0;

// Obtener nombre del mes para mostrar
$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
$mes_nombre = $mes_num > 0 ? $meses[$mes_num] : 'Todos';

// Buscar actividad actual
$nombre_actividad_actual = 'PCC';
$icono_actividad_actual = 'clipboard-check';
$color_actividad_actual = '#388e3c';
foreach ($actividades as $act) {
    if ($act['id_actividad'] == $id_actividad) {
        $nombre_actividad_actual = $act['nombre_actividad'];
        $icono_actividad_actual = $act['icono'];
        $color_actividad_actual = $act['color'];
        break;
    }
}

// Verificar columnas en otras tablas
$columnas_usuarios = [];
$check_usuarios = $conn->query("SHOW COLUMNS FROM usuarios");
while ($col = $check_usuarios->fetch_assoc()) {
    $columnas_usuarios[] = $col['Field'];
}

// Construir consulta principal dinámicamente
$select_campos = "
SELECT 
    r.id_registro,
    DATE_FORMAT(r.fecha_registro, '%d/%m/%Y') as fecha_formateada,
    a.nombre_actividad,
    u.nombre_usuario,
    c.nombre_campo_actividad,
    v.valor,
    p.ruta_pdf
";

if (in_array('color', $columnas_actividades)) {
    $select_campos .= ", a.color";
}
if (in_array('avatar', $columnas_usuarios)) {
    $select_campos .= ", u.avatar";
}

$from_where = "
FROM registros_actividad r
JOIN actividades a ON r.id_actividad = a.id_actividad
JOIN usuarios u ON r.id_usuario = u.id_usuario
JOIN valores_actividad v ON r.id_registro = v.id_registro
JOIN campos_actividad c ON v.id_camposA = c.id_camposA
LEFT JOIN registro_pdfs p ON r.id_registro = p.id_registro
WHERE r.id_actividad = $id_actividad
AND YEAR(r.fecha_registro) = $anio
";

if ($mes_num > 0) {
    $from_where .= " AND MONTH(r.fecha_registro) = $mes_num";
}

$order_by_sql = "
ORDER BY r.fecha_registro DESC, r.id_registro, c.id_camposA
";

$sql = $select_campos . $from_where . $order_by_sql;
$res = $conn->query($sql);

// Armar el arreglo de registros
$registros = [];
$campos_unicos = [];
while ($row = $res->fetch_assoc()) {
    $id = $row['id_registro'];
    if (!isset($registros[$id])) {
        $registros[$id] = [
            'id_registro' => $id,
            'fecha' => $row['fecha_formateada'],
            'actividad' => $row['nombre_actividad'],
            'color' => $row['color'] ?? $color_actividad_actual,
            'usuario' => $row['nombre_usuario'],
            'pdf' => $row['ruta_pdf'] ?? null,
            'campos' => []
        ];
        
        if (isset($row['avatar'])) {
            $registros[$id]['avatar'] = $row['avatar'];
        }
    }
    $registros[$id]['campos'][$row['nombre_campo_actividad']] = $row['valor'];
    
    if (!isset($campos_unicos[$row['nombre_campo_actividad']])) {
        $campos_unicos[$row['nombre_campo_actividad']] = true;
    }
}
$registros = array_values($registros);
$campos_unicos = array_keys($campos_unicos);

// Estadísticas
$total_filtrados = count($registros);
$export_url = "../interfaces/exportar_excel_web.php?id_actividad=" . $id_actividad . 
              "&anio=" . $anio . "&mes=" . $mes_num;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Sistema Activo</title>
    <link rel="stylesheet" href="../css/panel_control.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/sidebar_unificado.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="panel-control">
    <!-- Header Superior -->
    <!-- REEMPLAZA TODO TU HEADER ACTUAL CON ESTO: -->

<!-- Header Superior Simplificado -->
<!-- Header Simplificado -->
<!-- Header Superior -->
<header class="panel-header">
    <!-- IZQUIERDA: Botón Menú + Título -->
    <?php include 'sidebar_left.php'; ?>
    
    <!-- DERECHA: Filtros activos + Usuario -->
    <div class="header-right">
        <!-- Filtros activos -->
        <div class="active-filters-header">
            <span class="filter-badge">
                <i class="fas fa-tag"></i> PCC
            </span>
            <span class="filter-badge">
                <i class="fas fa-calendar"></i> Septiembre 2025
            </span>
        </div>
        
        <!-- Contador total -->
        <div class="total-counter">
            <div class="counter-icon">
                <i class="fas fa-database"></i>
            </div>
            <div class="counter-info">
                <span class="counter-label">TOTAL</span>
                <span class="counter-value">1</span>
            </div>
        </div>
    </div>
</header>

    <!-- Layout Principal -->
    <div class="panel-layout">
        <!-- Sidebar con solo navegación general -->
        <aside class="panel-sidebar">
            <nav class="sidebar-nav">
                <!-- Solo enlaces de navegación general -->
                <a href="../interfaces/reporte_mensual.php" class="sidebar-link">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reportes</span>
                </a>
                <a href="comprobaciones.php" class="sidebar-link">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Comprobaciones Físicas</span>
                </a>
                <a href="usuarios.php" class="sidebar-link">
                    <i class="fas fa-users"></i>
                    <span>Usuarios</span>
                </a>
                <div class="sidebar-divider"></div>
                
                <?php if ($rol === 'admin'): ?>
                <a href="configuracion_sistema.php" class="sidebar-link">
                    <i class="fas fa-sliders-h"></i>
                    <span>Configuración Sistema</span>
                </a>
                <a href="backup.php" class="sidebar-link">
                    <i class="fas fa-database"></i>
                    <span>Backup</span>
                </a>
                <a href="logs.php" class="sidebar-link">
                    <i class="fas fa-history"></i>
                    <span>Registros de Actividad</span>
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
            <!-- Filtros Siempre Visibles (Panel de Filtros Abierto Permanentemente) -->
            <div class="filters-header">
                <div class="filters-info">
                    <div class="current-filters">
                        <span class="filter-badge">
                            <i class="fas fa-<?php echo $icono_actividad_actual; ?>"></i>
                            <?php echo htmlspecialchars($nombre_actividad_actual); ?>
                        </span>
                        <span class="filter-badge">
                            <i class="fas fa-calendar"></i>
                            <?php echo $mes_nombre . ' ' . $anio; ?>
                        </span>
                    </div>
                    
                    <div class="filters-stats">
                        <div class="stat-card">
                            <div class="stat-icon total">
                                <i class="fas fa-database"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label">Total</span>
                                <span class="stat-value"><?php echo $total_filtrados; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="filters-actions">
                    <a href="<?php echo $export_url; ?>" 
                       class="btn btn-export"
                       onclick="return confirmExport(<?= $total_filtrados ?>);">
                        <i class="fas fa-file-excel"></i>
                        Exportar Excel (<?= $total_filtrados ?>)
                    </a>
                </div>
            </div>

            <!-- Panel de Filtros Avanzados - SIEMPRE VISIBLE -->
            <div class="advanced-filters show" id="filtersPanel">
                <form method="get" class="filter-form" id="mainFilterForm">
                    <div class="filter-group">
                        <label for="id_actividad" class="filter-label">
                            <i class="fas fa-tasks"></i> Actividad
                        </label>
                        <select id="id_actividad" name="id_actividad" class="filter-select">
                            <?php foreach ($actividades as $act): ?>
                                <option value="<?= $act['id_actividad'] ?>" 
                                    <?= $act['id_actividad'] == $id_actividad ? 'selected' : '' ?>
                                    data-icon="fa-<?php echo htmlspecialchars($act['icono']); ?>"
                                    data-color="<?php echo htmlspecialchars($act['color']); ?>">
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
                            <option value="0" <?= $mes_num == 0 ? 'selected' : '' ?>>Todos los meses</option>
                            <?php foreach ($meses as $num => $nombre_mes): ?>
                                <option value="<?= $num ?>" <?= $num == $mes_num ? 'selected' : '' ?>>
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

            <!-- Tabla de Registros -->
            <div class="data-table-container">
                <?php if (count($registros) > 0): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="text-center">Acciones</th>
                                    <th>Fecha</th>
                                    <th>Usuario</th>
                                    <?php foreach ($campos_unicos as $campo): ?>
                                        <th><?= htmlspecialchars($campo) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registros as $reg): ?>
                                    <tr>
                                        <td class="actions-cell">
                                            <div class="action-buttons">
                                                <?php if (!empty($reg['pdf'])): ?>
                                                    <a href="descargar_pdf.php?id_registro=<?= $reg['id_registro'] ?>" 
                                                       class="btn-icon btn-pdf" 
                                                       target="_blank"
                                                       title="Ver PDF">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <!-- SOLO EDITAR, NO VER DETALLES -->
                                                <?php if ($rol !== 'medico'): ?>
                                                <button class="btn-icon btn-edit" 
                                                        onclick="editRecord(<?= $reg['id_registro'] ?>)"
                                                        title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="date-cell">
                                                <i class="fas fa-calendar-alt date-icon"></i>
                                                <span class="date"><?= htmlspecialchars($reg['fecha']) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="user-cell">
                                                <?php if (isset($reg['avatar']) && !empty($reg['avatar'])): ?>
                                                <img src="<?php echo htmlspecialchars($reg['avatar']); ?>" 
                                                     alt="<?php echo htmlspecialchars($reg['usuario']); ?>"
                                                     class="user-avatar-small">
                                                <?php else: ?>
                                                <div class="user-avatar-placeholder">
                                                    <?php echo strtoupper(substr($reg['usuario'], 0, 1)); ?>
                                                </div>
                                                <?php endif; ?>
                                                <span><?= htmlspecialchars($reg['usuario']) ?></span>
                                            </div>
                                        </td>
                                        <?php foreach ($campos_unicos as $campo): ?>
                                            <td class="truncate-text" title="<?= htmlspecialchars($reg['campos'][$campo] ?? '-') ?>">
                                                <?= htmlspecialchars($reg['campos'][$campo] ?? '-') ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <h3>No se encontraron registros</h3>
                        <p>No hay registros que coincidan con los filtros seleccionados.</p>
                        <button class="btn btn-primary" onclick="window.location.href='registrar_actividad.php'">
                            <i class="fas fa-plus"></i>
                            Registrar Nueva Actividad
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Botón Flotante para Médicos -->
    <?php if ($rol === 'medico'): ?>
        <button class="floating-btn" onclick="window.location.href='registrar_actividad.php'">
            <i class="fas fa-plus"></i>
            <span>Nueva Actividad</span>
        </button>
    <?php endif; ?>

    <script>
        function confirmExport(total) {
            if (total === 0) {
                alert('No hay registros para exportar.');
                return false;
            }
            
            return confirm(`¿Exportar ${total} registro(s) a Excel?\n\nActividad: ${'<?php echo addslashes($nombre_actividad_actual); ?>'}\nPeríodo: ${'<?php echo addslashes($mes_nombre); ?>'} ${'<?php echo $anio; ?>'}`);
        }

        function editRecord(id) {
            // Implementar edición
            console.log('Editar registro:', id);
            alert('Función de edición para el registro ' + id);
        }

        function clearFilters() {
            // Limpiar los filtros sin recargar la página
            document.getElementById('id_actividad').value = '1';
            document.getElementById('mes').value = '0';
            document.getElementById('anio').value = '<?php echo date("Y"); ?>';
            document.getElementById('mainFilterForm').submit();
        }

        // Auto-submit cuando cambian los filtros (mantiene panel abierto)
        document.addEventListener('DOMContentLoaded', function() {
            const filterSelects = document.querySelectorAll('.filter-select');
            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    // Pequeño delay para mejor UX
                    setTimeout(() => {
                        document.getElementById('mainFilterForm').submit();
                    }, 300);
                });
            });

            // Toggle sidebar
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            const sidebar = document.querySelector('.panel-sidebar');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                });
            }

            // Toggle user dropdown
            const userAvatar = document.querySelector('.user-avatar');
            const userDropdown = document.querySelector('.user-dropdown');
            
            if (userAvatar) {
                userAvatar.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userDropdown.classList.toggle('show');
                });
                
                document.addEventListener('click', function() {
                    userDropdown.classList.remove('show');
                });
            }

            // Tabla responsive
            const table = document.querySelector('.data-table');
            if (table && window.innerWidth < 768) {
                table.classList.add('table-mobile');
            }

            window.addEventListener('resize', function() {
                if (table) {
                    if (window.innerWidth < 768) {
                        table.classList.add('table-mobile');
                    } else {
                        table.classList.remove('table-mobile');
                    }
                }
            });
        });
    </script>
</body>
</html>
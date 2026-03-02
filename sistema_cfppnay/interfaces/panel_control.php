<?php
include_once("../funciones/conexion.php");
// Ajusta la ruta del autoload si es necesario
if (file_exists("../funciones/vendor/autoload.php")) {
    include("../funciones/vendor/autoload.php");
} elseif (file_exists("../vendor/autoload.php")) {
    include("../vendor/autoload.php");
}

header('Content-Type: text/html; charset=utf-8');
session_start();

// Verifica login
if (!isset($_SESSION['id_usuario'])) {
    header('Location: http://localhost/login.php');
    exit;
}

$rol = $_SESSION['rol'] ?? 'medico';
$nombre = $_SESSION['nombre_usuario'] ?? 'Usuario';

// Verificar columnas disponibles dinámicamente
$columnas_actividades = [];
if(isset($conn)){
    $check_columns = $conn->query("SHOW COLUMNS FROM actividades");
    while ($col = $check_columns->fetch_assoc()) {
        $columnas_actividades[] = $col['Field'];
    }
} else {
    die("Error de conexión");
}

// Construir consulta dinámicamente
$campos_select = ['id_actividad', 'nombre_actividad'];
if (in_array('icono', $columnas_actividades)) $campos_select[] = 'icono';
if (in_array('color', $columnas_actividades)) $campos_select[] = 'color';
if (in_array('orden', $columnas_actividades)) $campos_select[] = 'orden';

$sql_select = implode(', ', $campos_select);
$order_by = in_array('orden', $columnas_actividades) ? 'ORDER BY orden, nombre_actividad' : 'ORDER BY nombre_actividad';

// Obtener actividades
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

// Nombre del mes
$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
$mes_nombre = $mes_num > 0 ? $meses[$mes_num] : 'Todos los meses';

// Actividad actual
$nombre_actividad_actual = 'Actividad';
foreach ($actividades as $act) {
    if ($act['id_actividad'] == $id_actividad) {
        $nombre_actividad_actual = $act['nombre_actividad'];
        break;
    }
}

// Verificar columnas usuarios
$columnas_usuarios = [];
$check_usuarios = $conn->query("SHOW COLUMNS FROM usuarios");
while ($col = $check_usuarios->fetch_assoc()) {
    $columnas_usuarios[] = $col['Field'];
}

// Consulta principal
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
if (in_array('color', $columnas_actividades)) $select_campos .= ", a.color";
if (in_array('avatar', $columnas_usuarios)) $select_campos .= ", u.avatar";

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
$order_by_sql = " ORDER BY r.fecha_registro DESC, r.id_registro, c.id_camposA";
$sql = $select_campos . $from_where . $order_by_sql;
$res = $conn->query($sql);

// Armar registros
$registros = [];
$campos_unicos = [];
while ($row = $res->fetch_assoc()) {
    $id = $row['id_registro'];
    if (!isset($registros[$id])) {
        $registros[$id] = [
            'id_registro' => $id,
            'fecha' => $row['fecha_formateada'],
            'actividad' => $row['nombre_actividad'],
            'color' => $row['color'] ?? '#388e3c',
            'usuario' => $row['nombre_usuario'],
            'pdf' => $row['ruta_pdf'] ?? null,
            'campos' => [],
            'avatar' => $row['avatar'] ?? null
        ];
    }
    $registros[$id]['campos'][$row['nombre_campo_actividad']] = $row['valor'];
    if (!isset($campos_unicos[$row['nombre_campo_actividad']])) {
        $campos_unicos[$row['nombre_campo_actividad']] = true;
    }
}
$registros = array_values($registros);
$campos_unicos = array_keys($campos_unicos);
$total_filtrados = count($registros);
$export_url = "../funciones/exportar_excel.php?id_actividad=" . $id_actividad . "&anio=" . $anio . "&mes=" . $mes_num;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Sistema Activo</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/sidebar_unificado.css?v=<?php echo time(); ?>">

    <style>
        /* =========================================================
           VARIABLES PREMIUM
           ========================================================= */
        :root {
            --bg-body: #f1f5f9; /* Un tono un poco más fresco y moderno */
            --surface-white: #ffffff;
            --primary-color: #2F855A;
            --primary-light: #dcfce7;
            --primary-hover: #276749;
            --excel-green: #10b981;
            --excel-hover: #059669;
            --text-dark: #0f172a;
            --text-regular: #334155;
            --text-muted: #64748b;
            --border-light: #e2e8f0;
            --border-focus: #cbd5e1;
            
            /* Sombras avanzadas multicapa */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
            --shadow-floating: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            
            --radius-md: 10px;
            --radius-lg: 16px;
            --radius-pill: 50px;
        }

        /* CUSTOM SCROLLBAR PARA LA TABLA Y PAGINA */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-dark);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* =========================================================
           ANIMACIONES BASE
           ========================================================= */
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulseSoft {
            0% { box-shadow: 0 0 0 0 rgba(47, 133, 90, 0.4); }
            70% { box-shadow: 0 0 0 6px rgba(47, 133, 90, 0); }
            100% { box-shadow: 0 0 0 0 rgba(47, 133, 90, 0); }
        }

        /* HEADER SUPERIOR (GLASSMORPHISM) */
        .panel-header {
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            padding: 12px 30px; border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            position: sticky; top: 0; z-index: 100; box-shadow: var(--shadow-sm);
        }

        .header-logo { display: flex; align-items: center; gap: 15px; }
        .header-logo img { height: 42px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1)); }
        .header-logo h2 { margin: 0; font-size: 1.1rem; font-weight: 800; letter-spacing: -0.5px; color: var(--text-dark); }

        /* PERFIL DE USUARIO EN EL HEADER */
        .header-right { display: flex; align-items: center; gap: 20px; }
        .user-profile-btn {
            display: flex; align-items: center; gap: 12px; background: #fff; border: 1px solid var(--border-light);
            padding: 5px 14px 5px 5px; border-radius: var(--radius-pill); cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: var(--shadow-sm);
        }
        .user-profile-btn:hover { border-color: var(--border-focus); box-shadow: var(--shadow-md); transform: translateY(-1px); }
        .user-profile-btn img { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: var(--shadow-sm); }
        .user-info-text { display: flex; flex-direction: column; text-align: left; }
        .user-info-text span:first-child { font-size: 0.85rem; font-weight: 700; color: var(--text-dark); }
        .user-info-text span:last-child { font-size: 0.65rem; font-weight: 700; color: var(--primary-color); text-transform: uppercase; letter-spacing: 0.5px; }
        
        .user-dropdown-menu {
            display: none; position: absolute; right: 0; top: calc(100% + 15px); background: var(--surface-white);
            border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 8px;
            box-shadow: var(--shadow-floating); min-width: 200px; z-index: 1000;
            transform-origin: top right; animation: fadeSlideUp 0.2s ease-out;
        }
        .user-dropdown-menu.show { display: block; }
        .dropdown-item {
            display: flex; align-items: center; gap: 12px; padding: 10px 14px; color: var(--text-regular);
            text-decoration: none; font-size: 0.9rem; font-weight: 600; border-radius: 6px; transition: all 0.2s;
        }
        .dropdown-item i { width: 16px; text-align: center; color: var(--text-muted); transition: 0.2s; }
        .dropdown-item:hover { background: #f8fafc; color: var(--primary-color); padding-left: 18px; }
        .dropdown-item:hover i { color: var(--primary-color); }
        .dropdown-item.logout-text:hover { background: #fef2f2; color: #ef4444; }
        .dropdown-item.logout-text:hover i { color: #ef4444; }

        /* LAYOUT PRINCIPAL */
        .panel-layout { display: flex; flex: 1; }

        /* SIDEBAR */
        .panel-sidebar { width: 260px; background: var(--surface-white); border-right: 1px solid var(--border-light); display: flex; flex-direction: column; z-index: 90; }
        .sidebar-nav { padding: 25px 15px; flex: 1; }
        .sidebar-section-title { color: #94a3b8; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; margin: 25px 0 10px 15px; }
        .sidebar-link {
            display: flex; align-items: center; gap: 12px; padding: 12px 16px; color: var(--text-regular);
            text-decoration: none; font-size: 0.9rem; font-weight: 600; border-radius: 8px; margin-bottom: 4px;
            transition: all 0.2s ease; border-left: 3px solid transparent;
        }
        .sidebar-link i { font-size: 1.1rem; color: #94a3b8; width: 24px; text-align: center; transition: all 0.2s; }
        .sidebar-link:hover { background: #f8fafc; color: var(--text-dark); border-left-color: #cbd5e1; }
        .sidebar-link:hover i { color: var(--text-dark); transform: scale(1.1); }
        .sidebar-link.active { background: var(--primary-light); color: var(--primary-hover); border-left-color: var(--primary-color); }
        .sidebar-link.active i { color: var(--primary-color); }

        .panel-content { flex: 1; padding: 35px 40px; overflow-x: hidden; }

        /* =========================================================
           ENCABEZADO DE LA VISTA (TÍTULO Y STATS)
           ========================================================= */
        .view-header {
            display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px;
            animation: fadeSlideUp 0.4s ease-out; flex-wrap: wrap; gap: 20px;
        }
        .view-title h1 { margin: 0 0 8px 0; font-size: 1.8rem; font-weight: 800; color: var(--text-dark); letter-spacing: -0.5px; }
        .view-title p { margin: 0; color: var(--text-muted); font-size: 0.95rem; font-weight: 500; }
        
        .stats-container { display: flex; gap: 15px; }
        .stat-badge {
            display: flex; align-items: center; gap: 12px; background: var(--surface-white); border: 1px solid var(--border-light);
            padding: 10px 16px; border-radius: 12px; box-shadow: var(--shadow-sm); transition: all 0.3s ease;
        }
        .stat-badge:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); border-color: var(--border-focus); }
        .stat-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
        .stat-icon.blue { background: #e0f2fe; color: #0284c7; }
        .stat-icon.green { background: #dcfce7; color: #16a34a; }
        .stat-text { display: flex; flex-direction: column; }
        .stat-label { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; }
        .stat-val { font-size: 1.1rem; font-weight: 800; color: var(--text-dark); line-height: 1.2; }

        /* =========================================================
           TARJETA DE FILTROS (BARRA DE HERRAMIENTAS PREMIUM)
           ========================================================= */
        .advanced-filters {
            background: var(--surface-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-md);
            padding: 20px 25px; margin-bottom: 30px; border: 1px solid rgba(226, 232, 240, 0.8);
            animation: fadeSlideUp 0.5s ease-out; animation-fill-mode: both; animation-delay: 0.1s;
        }

        .filter-form { display: flex; align-items: flex-end; gap: 20px; flex-wrap: wrap; }
        
        .filter-group { display: flex; flex-direction: column; gap: 8px; position: relative; }
        .filter-label { font-size: 0.75rem; font-weight: 800; color: var(--text-regular); text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px; }
        .filter-label i { color: #94a3b8; }
        
        .filter-select {
            padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border-focus);
            background: #fff; color: var(--text-dark); font-size: 0.95rem; font-weight: 600; font-family: 'Inter';
            min-width: 160px; outline: none; transition: all 0.2s; cursor: pointer; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 12px center; padding-right: 40px;
        }
        .filter-select:hover { border-color: #94a3b8; }
        .filter-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 4px var(--primary-light); }

        /* BOTONES */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 0 20px; border-radius: 8px; font-size: 0.9rem; font-weight: 700; border: none; cursor: pointer;
            height: 42px; text-decoration: none; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); letter-spacing: 0.3px;
        }
        .btn-primary { background: var(--text-dark); color: #fff; box-shadow: 0 4px 6px rgba(15, 23, 42, 0.1); }
        .btn-primary:hover { background: #000; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(15, 23, 42, 0.15); }
        .btn-primary:active { transform: translateY(0); }
        
        .btn-secondary { background: #f1f5f9; color: var(--text-regular); border: 1px solid var(--border-focus); }
        .btn-secondary:hover { background: #e2e8f0; color: var(--text-dark); }

        .btn-excel { background: var(--excel-green); color: white; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.15); }
        .btn-excel:hover { background: var(--excel-hover); transform: translateY(-2px); box-shadow: 0 6px 12px rgba(16, 185, 129, 0.25); }
        
        .push-right { margin-left: auto; }

        /* =========================================================
           TABLA DE DATOS PREMIUM
           ========================================================= */
        .data-table-container {
            background: var(--surface-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-md);
            border: 1px solid rgba(226, 232, 240, 0.8); overflow: hidden;
            animation: fadeSlideUp 0.5s ease-out; animation-fill-mode: both; animation-delay: 0.2s;
        }

        .table-responsive { overflow-x: auto; max-height: calc(100vh - 280px); }
        
        .data-table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: left; }
        
        /* THEAD FLOTANTE */
        .data-table th {
            background: rgba(248, 250, 252, 0.95); backdrop-filter: blur(8px);
            padding: 16px 20px; font-size: 0.75rem; font-weight: 800; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid var(--border-light);
            position: sticky; top: 0; z-index: 10; white-space: nowrap;
        }

        /* FILAS */
        .data-table td {
            padding: 16px 20px; font-size: 0.9rem; font-weight: 500; color: var(--text-regular);
            border-bottom: 1px solid #f1f5f9; vertical-align: middle; transition: background 0.2s;
            white-space: nowrap;
        }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background-color: #f8fafc; color: var(--text-dark); }

        /* Celdas especiales */
        .date-badge { display: inline-flex; align-items: center; gap: 8px; background: #f1f5f9; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; color: var(--text-regular); border: 1px solid var(--border-light); }
        .date-badge i { color: var(--primary-color); }
        
        .user-cell { display: flex; align-items: center; gap: 12px; font-weight: 600; }
        .user-avatar-small { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border-light); }
        
        /* Botones de acción en tabla */
        .action-buttons { display: flex; gap: 10px; justify-content: center; }
        .btn-icon { width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; }
        
        .btn-pdf { background: #fef2f2; color: #ef4444; border: 1px solid #fee2e2; }
        .btn-pdf:hover { background: #ef4444; color: white; border-color: #ef4444; transform: translateY(-2px); box-shadow: 0 4px 6px rgba(239, 68, 68, 0.2); }
        
        .btn-edit { background: #e0f2fe; color: #0284c7; border: 1px solid #e0f2fe; }
        .btn-edit:hover { background: #0284c7; color: white; border-color: #0284c7; transform: translateY(-2px); box-shadow: 0 4px 6px rgba(2, 132, 199, 0.2); }

        /* ESTADO VACÍO ANIMADO */
        .empty-state { text-align: center; padding: 80px 20px; animation: fadeSlideUp 0.5s ease-out; }
        .empty-illustration { position: relative; width: 80px; height: 80px; margin: 0 auto 20px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .empty-illustration i { font-size: 36px; color: #94a3b8; position: relative; z-index: 2; }
        .empty-illustration::after { content: ''; position: absolute; top: -10px; left: -10px; right: -10px; bottom: -10px; border-radius: 50%; border: 2px dashed #cbd5e1; animation: spin 10s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        .empty-state h3 { font-size: 1.2rem; font-weight: 800; color: var(--text-dark); margin: 0 0 8px 0; }
        .empty-state p { color: var(--text-muted); font-size: 0.95rem; margin: 0; max-width: 300px; margin: 0 auto; line-height: 1.5; }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .filter-form { gap: 15px; }
            .push-right { margin-left: 0; width: 100%; display: flex; }
            .btn-excel { flex: 1; }
        }
        @media (max-width: 768px) {
            .panel-sidebar { display: none; }
            .panel-content { padding: 20px; }
            .view-header { flex-direction: column; align-items: flex-start; }
            .stats-container { width: 100%; }
            .stat-badge { flex: 1; }
            .header-right .user-info-text, .header-right .fa-chevron-down { display: none; }
            .filter-group { width: 100%; }
            .filter-select { width: 100%; min-width: 100%; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>

<header class="panel-header">
    <div class="header-logo">
        <img src="../imagenes/logoPng.png" alt="CEFPPNAY">
        <h2>Panel de Control</h2>
    </div>
    
  <div class="header-right">
        <div style="position:relative;">
            <button class="user-profile-btn" id="userBtn">
                
                <img src="../imagenes/empresarial.jpg" alt="Foto de perfil">
                
                <div class="user-info-text">
                    <span><?= htmlspecialchars($nombre) ?></span>
                    <span><?= htmlspecialchars($rol) ?></span>
                </div>
                <i class="fas fa-chevron-down" style="color: var(--text-muted); font-size:12px;"></i>
            </button>
            <div class="user-dropdown-menu" id="userMenu">
                <a href="#" class="dropdown-item"><i class="fas fa-key"></i> Cambiar Contraseña</a>
                <div style="height: 1px; background: var(--border-light); margin: 5px 0;"></div>
                <a href="../funciones/logout.php" class="dropdown-item logout-text"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </div>
        </div>
    </div>
</header>

<div class="panel-layout">
    <aside class="panel-sidebar">
        <nav class="sidebar-nav">
            <div class="sidebar-section-title">ACTIVIDADES</div>
            <a href="panel_control.php" class="sidebar-link active">
                <i class="fas fa-home"></i> <span>Inicio</span>
            </a>
            <a href="registro_nueva_actividad.php" class="sidebar-link">
                <i class="fas fa-plus-circle"></i> <span>Crear Actividades</span>
            </a>
            <a href="nuevo_registro.php" class="sidebar-link">
                <i class="fas fa-file-medical"></i> <span>Formulario Médico</span>
            </a>

            <div class="sidebar-section-title">REPORTES</div>
            <a href="estadisticas.php" class="sidebar-link">
                <i class="fas fa-chart-bar"></i> <span>Estadísticas</span>
            </a>
            <a href="reporte_mensual.php" class="sidebar-link">
                <i class="fas fa-file-alt"></i> <span>Reporte General</span>
            </a>
            <a href="carga_programacion.php" class="sidebar-link">
                <i class="fas fa-upload"></i> <span>Carga Programación</span>
            </a>

            <div class="sidebar-section-title">ADMINISTRACIÓN</div>
            <a href="comprobaciones.php" class="sidebar-link">
                <i class="fas fa-clipboard-check"></i> <span>Comprobaciones</span>
            </a>
            <a href="usuarios.php" class="sidebar-link">
                <i class="fas fa-users"></i> <span>Usuarios</span>
            </a>

            <?php if ($rol === 'admin'): ?>
            <div style="height: 1px; background: var(--border-light); margin: 15px 15px;"></div>
            <a href="configuracion_sistema.php" class="sidebar-link">
                <i class="fas fa-sliders-h"></i> <span>Configuración</span>
            </a>
            <a href="backup.php" class="sidebar-link">
                <i class="fas fa-database"></i> <span>Backup</span>
            </a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="panel-content">
        
        <div class="view-header">
            <div class="view-title">
                <h1>Registros Operativos</h1>
                <p>Gestión y visualización de datos de campo ingresados.</p>
            </div>
            
            <div class="stats-container">
                <div class="stat-badge">
                    <div class="stat-icon blue"><i class="fas fa-layer-group"></i></div>
                    <div class="stat-text">
                        <span class="stat-label">Actividad Activa</span>
                        <span class="stat-val"><?= htmlspecialchars($nombre_actividad_actual) ?></span>
                    </div>
                </div>
                <div class="stat-badge" style="animation-delay: 0.1s;">
                    <div class="stat-icon green"><i class="fas fa-database"></i></div>
                    <div class="stat-text">
                        <span class="stat-label">Total de Registros</span>
                        <span class="stat-val"><?= $total_filtrados ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="advanced-filters">
            <form method="get" class="filter-form" id="mainFilterForm">
                
                <div class="filter-group">
                    <label for="id_actividad" class="filter-label"><i class="fas fa-list-ul"></i> Filtrar Actividad</label>
                    <select id="id_actividad" name="id_actividad" class="filter-select">
                        <?php foreach ($actividades as $act): ?>
                            <option value="<?= $act['id_actividad'] ?>" <?= $act['id_actividad'] == $id_actividad ? 'selected' : '' ?>>
                                <?= htmlspecialchars($act['nombre_actividad']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="mes" class="filter-label"><i class="fas fa-calendar-day"></i> Periodo (Mes)</label>
                    <select id="mes" name="mes" class="filter-select">
                        <option value="0" <?= $mes_num == 0 ? 'selected' : '' ?>>Todos los meses</option>
                        <?php foreach ($meses as $num => $nombre_mes): ?>
                            <option value="<?= $num ?>" <?= $num == $mes_num ? 'selected' : '' ?>><?= $nombre_mes ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="anio" class="filter-label"><i class="fas fa-calendar-alt"></i> Año</label>
                    <select id="anio" name="anio" class="filter-select" style="min-width: 100px;">
                        <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?= $y ?>" <?= $y == $anio ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Aplicar Filtros
                    </button>
                </div>
                
                <div class="filter-group">
                    <button type="button" class="btn btn-secondary" onclick="clearFilters()" title="Borrar filtros">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>

                <div class="filter-group push-right">
                    <a href="<?= $export_url; ?>" class="btn btn-excel" onclick="return confirmExport(<?= $total_filtrados ?>);">
                        <i class="fas fa-file-excel"></i> Descargar Excel
                    </a>
                </div>
            </form>
        </div>

        <div class="data-table-container">
            <?php if (count($registros) > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 100px; border-top-left-radius: 10px;">Acciones</th>
                                <th style="width: 160px;">Fecha Ingreso</th>
                                <th style="width: 250px;">Usuario Responsable</th>
                                <?php foreach ($campos_unicos as $idx => $campo): ?>
                                    <th <?= ($idx === count($campos_unicos) - 1) ? 'style="border-top-right-radius: 10px;"' : '' ?>>
                                        <?= htmlspecialchars($campo) ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registros as $reg): ?>
                                <tr>
                                    <td class="actions-cell">
                                        <div class="action-buttons">
                                            <?php if (!empty($reg['pdf'])): ?>
                                                <a href="descargar_pdf.php?id_registro=<?= $reg['id_registro'] ?>" class="btn-icon btn-pdf" target="_blank" title="Ver Documento Comprobatorio">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($rol !== 'medico'): ?>
                                                <button class="btn-icon btn-edit" onclick="editRecord(<?= $reg['id_registro'] ?>)" title="Editar Información">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="date-badge">
                                            <i class="fas fa-calendar-check date-icon"></i>
                                            <span><?= htmlspecialchars($reg['fecha']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-cell">
                                            <?php if (!empty($reg['avatar'])): ?>
                                                <img src="<?= htmlspecialchars($reg['avatar']); ?>" class="user-avatar-small">
                                            <?php else: ?>
                                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($reg['usuario']) ?>&background=10b981&color=fff&bold=true" class="user-avatar-small" alt="Avatar">
                                            <?php endif; ?>
                                            <span><?= htmlspecialchars($reg['usuario']) ?></span>
                                        </div>
                                    </td>
                                    <?php foreach ($campos_unicos as $campo): ?>
                                        <td><?= htmlspecialchars($reg['campos'][$campo] ?? '-') ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-illustration">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h3>Bandeja Limpia</h3>
                    <p>No se encontraron registros ingresados para la actividad y periodo que seleccionaste.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
    // CONTROL DEL MENÚ USUARIO DESPLEGABLE CON ANIMACIÓN SUAVE
    const userBtn = document.getElementById('userBtn');
    const userMenu = document.getElementById('userMenu');
    
    if(userBtn && userMenu){
        userBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userMenu.classList.toggle('show');
            const icon = userBtn.querySelector('.fa-chevron-down');
            if(userMenu.classList.contains('show')) {
                icon.style.transform = 'rotate(180deg)';
                icon.style.transition = '0.3s';
            } else {
                icon.style.transform = 'rotate(0deg)';
            }
        });
        document.addEventListener('click', (e) => {
            if (!userBtn.contains(e.target) && !userMenu.contains(e.target)) {
                userMenu.classList.remove('show');
                userBtn.querySelector('.fa-chevron-down').style.transform = 'rotate(0deg)';
            }
        });
    }

    // FUNCIONES DE EXPORTACIÓN Y LIMPIEZA
    function confirmExport(total) {
        if (total === 0) {
            alert('No hay registros en pantalla para exportar al documento de Excel.');
            return false;
        }
        return confirm('Se generará un reporte en Excel con ' + total + ' registros. ¿Deseas continuar?');
    }

    function clearFilters() {
        document.getElementById('id_actividad').selectedIndex = 0; // Vuelve al primero de la lista
        document.getElementById('mes').value = '0';
        document.getElementById('anio').value = '<?= date("Y"); ?>';
        document.getElementById('mainFilterForm').submit();
    }
</script>
</body>
</html>
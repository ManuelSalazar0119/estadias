<?php
session_start();
include("../funciones/conexion.php");

// Verifica permisos
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit;
}

$nombre = $_SESSION['nombre_usuario'] ?? 'Usuario';
$rol = $_SESSION['rol_usuario'] ?? ($_SESSION['rol'] ?? 'Invitado');

// Obtener áreas/campañas
$areas = [];
$res_areas = $conn->query("SELECT id_area, nombre_area FROM areas");
if ($res_areas) {
    while ($row = $res_areas->fetch_assoc()) {
        $areas[] = $row;
    }
}

// Filtros actuales
$id_area = isset($_GET['id_area']) ? intval($_GET['id_area']) : ($areas[0]['id_area'] ?? 1);
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : 0; // 0 = Todos los meses por defecto

$meses_nom = [
    0 => '-- Todos los meses --',
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

// OBTENER LAS COMPROBACIONES (Solo registros que tengan PDF)
$sql = "
    SELECT 
        a.id_actividad,
        a.nombre_actividad,
        r.id_registro, 
        DATE_FORMAT(r.fecha_registro, '%d/%m/%Y') as fecha,
        u.nombre_usuario,
        p.ruta_pdf
    FROM registros_actividad r
    JOIN actividades a ON r.id_actividad = a.id_actividad
    JOIN usuarios u ON r.id_usuario = u.id_usuario
    JOIN registro_pdfs p ON r.id_registro = p.id_registro
    WHERE a.id_area = $id_area 
    AND YEAR(r.fecha_registro) = $anio 
";

if ($mes > 0) {
    $sql .= " AND MONTH(r.fecha_registro) = $mes ";
}

$sql .= " ORDER BY a.nombre_actividad ASC, r.fecha_registro DESC";

$res_comprobaciones = $conn->query($sql);

if (!$res_comprobaciones) {
    die("<div style='padding:20px; background:#fee2e2; color:#ef4444; border-radius:8px; font-family:sans-serif; margin: 20px;'>
            <strong>Error SQL en la Base de Datos:</strong> " . $conn->error . "
         </div>");
}

$comprobaciones_agrupadas = [];
$total_evidencias = 0;

if ($res_comprobaciones->num_rows > 0) {
    while ($row = $res_comprobaciones->fetch_assoc()) {
        $comprobaciones_agrupadas[$row['nombre_actividad']][] = $row;
        $total_evidencias++;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobaciones Físicas - CEFPPNAY</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebar_unificado.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* =========================================================
           VARIABLES PREMIUM
           ========================================================= */
        :root {
            --bg-body: #f1f5f9;
            --surface-white: #ffffff;
            --primary-color: #2F855A;
            --primary-light: #dcfce7;
            --primary-hover: #276749;
            --text-dark: #0f172a;
            --text-regular: #334155;
            --text-muted: #64748b;
            --border-light: #e2e8f0;
            --border-focus: #cbd5e1;
            
            /* Sombras multicapa */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
            --shadow-floating: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            
            --radius-md: 10px;
            --radius-lg: 16px;
            --radius-pill: 50px;
            
            --danger-color: #ef4444;
            --danger-light: #fee2e2;
        }

        /* CUSTOM SCROLLBAR */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        body {
            margin: 0; font-family: 'Inter', sans-serif; background-color: var(--bg-body);
            color: var(--text-dark); display: flex; flex-direction: column; min-height: 100vh; overflow-x: hidden;
        }

        /* ANIMACIONES */
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
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

        /* LAYOUT PRINCIPAL CON SIDEBAR */
        .panel-layout { display: flex; flex: 1; }

        .panel-sidebar { width: 260px; background: var(--surface-white); border-right: 1px solid var(--border-light); display: flex; flex-direction: column; z-index: 90; flex-shrink: 0; }
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

        /* ========================================================
           GRID DIVIDIDO EN DOS COLUMNAS
           ======================================================== */
        .split-layout { display: grid; grid-template-columns: 2.2fr 1fr; gap: 30px; align-items: start; max-width: 1450px; margin: 0 auto; }
        .left-column { min-width: 0; }
        .right-column { min-width: 0; }

        /* ========================================================
           BARRA DE FILTROS PREMIUM
           ======================================================== */
        .advanced-filters {
            background: var(--surface-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-md);
            padding: 24px 30px; margin-bottom: 30px; border: 1px solid rgba(226, 232, 240, 0.8);
            display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end; justify-content: space-between;
            animation: fadeSlideUp 0.4s ease-out;
        }

        .filters-left { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; }
        
        .filter-group { display: flex; flex-direction: column; gap: 8px; position: relative; }
        .filter-label { font-size: 0.75rem; font-weight: 800; color: var(--text-regular); text-transform: uppercase; letter-spacing: 0.5px; }
        
        .filter-select {
            padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border-focus);
            background: #fff; color: var(--text-dark); font-size: 0.95rem; font-weight: 600; font-family: 'Inter', sans-serif;
            min-width: 140px; outline: none; transition: all 0.2s; cursor: pointer; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 12px center; padding-right: 40px; height: 44px;
        }
        .filter-select:hover { border-color: #94a3b8; }
        .filter-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 4px var(--primary-light); }
        
        /* BOTONES PREMIUM */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 0 20px; border-radius: 8px; font-size: 0.9rem; font-weight: 700; border: none; cursor: pointer;
            height: 44px; text-decoration: none; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); letter-spacing: 0.3px;
        }
        .btn-primary { background: var(--text-dark); color: #fff; box-shadow: 0 4px 6px rgba(15, 23, 42, 0.1); }
        .btn-primary:hover { background: #000; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(15, 23, 42, 0.15); }
        .btn-primary:active { transform: translateY(0); }
        
        .btn-excel { background: #10b981; color: white; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.15); }
        .btn-excel:hover { background: #059669; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(16, 185, 129, 0.25); }

        /* ========================================================
           TARJETAS DE ACTIVIDADES (PDFs)
           ======================================================== */
        .header-title-box { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; animation: fadeSlideUp 0.5s ease-out; }
        .header-title-box h2 { margin: 0; font-size: 1.5rem; font-weight: 800; color: var(--text-dark); display: flex; align-items: center; gap: 10px; letter-spacing: -0.5px; }
        .header-title-box .badge-total { background: var(--text-dark); color: white; padding: 6px 16px; border-radius: 50px; font-size: 0.85rem; font-weight: 700; box-shadow: var(--shadow-sm); }

        .activity-card {
            background: var(--surface-white); border-radius: var(--radius-lg); border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: var(--shadow-md); margin-bottom: 25px; overflow: hidden; animation: fadeSlideUp 0.5s ease-out;
        }
        .activity-header {
            background: #f8fafc; padding: 20px 25px; border-bottom: 1px solid var(--border-light);
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;
        }
        .activity-header h3 { margin: 0; font-size: 1.15rem; font-weight: 800; color: var(--text-dark); display: flex; align-items: center; gap: 10px; }
        .activity-header h3 i { color: var(--primary-color); }
        .badge-count { background: var(--primary-light); color: var(--primary-hover); padding: 4px 12px; border-radius: 50px; font-size: 0.8rem; font-weight: 800; }
        
        .modern-table { width: 100%; border-collapse: collapse; text-align: left; }
        .modern-table thead th {
            background: #ffffff; color: var(--text-muted); font-size: 0.75rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: 0.5px; padding: 14px 25px; border-bottom: 2px solid var(--border-light);
        }
        .modern-table tbody td {
            padding: 16px 25px; font-size: 0.95rem; color: var(--text-regular); font-weight: 500;
            border-bottom: 1px solid #f1f5f9; vertical-align: middle; transition: background 0.2s;
        }
        .modern-table tr:last-child td { border-bottom: none; }
        .modern-table tbody tr:hover td { background-color: #f8fafc; color: var(--text-dark); }
        
        /* Botones PDF dentro de la tabla y card */
        .btn-pdf {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: var(--danger-light);
            color: var(--danger-color); padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 0.85rem;
            font-weight: 700; transition: all 0.2s; cursor: pointer; border: 1px solid var(--danger-light);
        }
        .btn-pdf:hover { background: var(--danger-color); color: white; border-color: var(--danger-color); transform: translateY(-2px); box-shadow: 0 4px 6px rgba(239, 68, 68, 0.2); }
        
        .btn-unir-pdf {
            background: var(--danger-color); color: white; border: none; padding: 10px 20px; border-radius: 8px;
            font-size: 0.9rem; font-weight: 700; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px;
            box-shadow: 0 4px 6px rgba(239, 68, 68, 0.15);
        }
        .btn-unir-pdf:hover { background: #b91c1c; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(239, 68, 68, 0.25); }

        /* ESTADO VACÍO ANIMADO */
        .empty-state { text-align: center; padding: 80px 20px; background: var(--surface-white); border-radius: var(--radius-lg); border: 1px dashed var(--border-focus); animation: fadeSlideUp 0.5s ease-out; }
        .empty-illustration { position: relative; width: 80px; height: 80px; margin: 0 auto 20px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .empty-illustration i { font-size: 36px; color: #94a3b8; position: relative; z-index: 2; }
        .empty-illustration::after { content: ''; position: absolute; top: -10px; left: -10px; right: -10px; bottom: -10px; border-radius: 50%; border: 2px dashed #cbd5e1; animation: spin 10s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        .empty-state h3 { font-size: 1.2rem; font-weight: 800; color: var(--text-dark); margin: 0 0 8px 0; }
        .empty-state p { color: var(--text-muted); font-size: 0.95rem; margin: 0; max-width: 350px; margin: 0 auto; line-height: 1.5; }

        /* ========================================================
           PANEL LATERAL DE INSTRUCCIONES PREMIUM
           ======================================================== */
        .instructions-card {
            background: var(--surface-white); border-radius: var(--radius-lg); border: 1px solid rgba(226, 232, 240, 0.8);
            padding: 2.5rem 2rem; box-shadow: var(--shadow-md); position: sticky; top: 100px;
            animation: fadeSlideUp 0.5s ease-out; animation-fill-mode: both; animation-delay: 0.1s;
        }

        .instructions-card h3 {
            color: var(--text-dark); font-size: 1.3rem; font-weight: 800; display: flex; align-items: center;
            gap: 12px; margin-top: 0; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 2px solid var(--border-light);
            letter-spacing: -0.5px;
        }

        .step-item { display: flex; gap: 16px; margin-bottom: 1.8rem; align-items: flex-start; }
        .step-number {
            background: linear-gradient(135deg, #e0f2fe, #bae6fd); color: #0284c7; width: 34px; height: 34px;
            border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800;
            font-size: 0.95rem; flex-shrink: 0; box-shadow: var(--shadow-sm); border: 1px solid #7dd3fc;
        }
        .step-content h4 { margin: 0 0 6px 0; font-size: 1rem; color: var(--text-dark); font-weight: 700; }
        .step-content p { margin: 0; font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; }

        .info-alert {
            background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 16px;
            display: flex; gap: 14px; margin-top: 2rem; align-items: flex-start; box-shadow: var(--shadow-sm);
        }
        .info-alert i { color: #16a34a; font-size: 1.3rem; margin-top: 2px; }
        .info-alert p { margin: 0; font-size: 0.9rem; color: #15803d; line-height: 1.5; font-weight: 500; }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .split-layout { grid-template-columns: 1fr; }
            .instructions-card { position: relative; top: 0; margin-top: 20px; }
        }
        @media (max-width: 768px) {
            .panel-sidebar { display: none; }
            .header-right .user-info-text, .header-right .fa-chevron-down { display: none; }
            .advanced-filters { padding: 1.5rem; }
            .filter-select { width: 100%; min-width: 100%; }
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
            <a href="panel_control.php" class="sidebar-link">
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
            <a href="comprobaciones.php" class="sidebar-link active">
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
        
        <div class="advanced-filters">
            <form method="GET" action="comprobaciones.php" class="filters-left" style="margin: 0;">
                <div class="filter-group">
                    <label class="filter-label">Año</label>
                    <select name="anio" class="filter-select">
                        <?php for($y=2020; $y<=2030; $y++): ?>
                            <option value="<?= $y ?>" <?= ($y == $anio) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">Mes</label>
                    <select name="mes" class="filter-select">
                        <?php foreach ($meses_nom as $num => $nom): ?>
                            <option value="<?= $num ?>" <?= ($num == $mes) ? 'selected' : '' ?>><?= $nom ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">Campaña / Área</label>
                    <select name="id_area" class="filter-select" style="min-width: 220px;">
                        <?php foreach ($areas as $area): ?>
                            <option value="<?= $area['id_area'] ?>" <?= ($area['id_area'] == $id_area) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($area['nombre_area']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
            </form>

            <div style="display: flex; gap: 15px; margin-top: 10px;">
                <a href="../funciones/exportar_tablas.php?descargar=true&id_area=<?= $id_area ?>&anio=<?= $anio ?>&mes=<?= $mes ?>" class="btn btn-excel">
                    <i class="fas fa-file-excel"></i> IFT Mensual
                </a>
            </div>
        </div>

        <div class="split-layout">
            <div class="left-column">
                <div class="header-title-box">
                    <h2>
                        <i class="fas fa-folder-open"></i>
                        Dictámenes: <?php echo $meses_nom[$mes] . " " . $anio; ?>
                    </h2>
                    <span class="badge-total">
                        <?= $total_evidencias ?> Documentos
                    </span>
                </div>

                <?php if ($total_evidencias > 0): ?>
                    <?php foreach ($comprobaciones_agrupadas as $actividad => $registros): ?>
                        <div class="activity-card">
                            <div class="activity-header">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <h3 style="font-size:1rem;"><i class="fas fa-microscope"></i> <?= htmlspecialchars($actividad) ?></h3>
                                    <span class="badge-count"><?= count($registros) ?> archivos</span>
                                </div>
                                <button class="btn-unir-pdf" onclick="unirPDFs(<?= $registros[0]['id_actividad'] ?>, <?= $anio ?>, <?= $mes ?>)">
                                    <i class="fas fa-layer-group"></i> Descargar Comprobaciones
                                </button>
                            </div>
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th style="width: 15%;">ID</th>
                                        <th style="width: 25%;">Fecha</th>
                                        <th style="width: 40%;">Médico Responsable</th>
                                        <th style="width: 20%; text-align: center;">Documento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registros as $reg): ?>
                                    <tr>
                                        <td style="color: var(--text-muted); font-weight: 700;">#<?= $reg['id_registro'] ?></td>
                                        <td>
                                            <div style="display: inline-flex; align-items: center; gap: 8px; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                                                <i class="far fa-calendar-alt" style="color: var(--primary-color);"></i> <?= $reg['fecha'] ?>
                                            </div>
                                        </td>
                                        <td style="font-weight: 600; color: var(--text-dark);"><?= htmlspecialchars($reg['nombre_usuario']) ?></td>
                                        <td style="text-align: center;">
                                            <a href="descargar_pdf.php?id_registro=<?= $reg['id_registro'] ?>" target="_blank" class="btn-pdf">
                                                <i class="fas fa-file-pdf"></i> Ver PDF
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-illustration">
                            <i class="fas fa-file-excel"></i>
                        </div>
                        <h3>Sin evidencias documentales</h3>
                        <p>No se encontraron PDFs subidos para la campaña y los filtros seleccionados.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="right-column">
                <div class="instructions-card">
                    <h3><i class="fas fa-clipboard-check" style="color: #0ea5e9;"></i> Guía de Auditoría</h3>
                    
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>IFT Mensual</h4>
                            <p>Descarga el reporte Excel general en la barra superior para tener la base numérica del mes seleccionado.</p>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Agrupar Archivos</h4>
                            <p>Usa el botón rojo <b>"Descargar Comprobaciones"</b> en cada actividad para obtener un solo PDF combinado con todos los dictámenes que respaldan esos números.</p>
                        </div>
                    </div>

                    <div class="info-alert">
                        <i class="fas fa-shield-alt"></i>
                        <p>Los archivos aquí mostrados corresponden únicamente a registros operativos que incluyen evidencia documental adjunta en formato PDF.</p>
                    </div>
                </div>
            </div>

        </div> </main>
</div>

<script>
    // Control del Menú Usuario Desplegable con Animación
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

    // Auto-submit en los filtros para refrescar rápido
    document.querySelectorAll('.filter-select').forEach(select => {
        select.addEventListener('change', () => { select.closest('form').submit(); });
    });

    function unirPDFs(id_actividad, anio, mes) {
        Swal.fire({
            title: 'Procesando...',
            text: 'Uniendo dictámenes en un solo archivo PDF. Por favor espere...',
            icon: 'info',
            showConfirmButton: false,
            allowOutsideClick: false,
            customClass: { popup: 'content-card' },
            didOpen: () => { Swal.showLoading(); }
        });
        window.location.href = `../funciones/unir_pdfs.php?id_actividad=${id_actividad}&anio=${anio}&mes=${mes}`;
        setTimeout(() => { Swal.close(); }, 3500);
    }
</script>
</body>
</html>
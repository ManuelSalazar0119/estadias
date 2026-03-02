<?php
include_once("../funciones/conexion.php");
header('Content-Type: text/html; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../login.php');
    exit;
}

$rol = $_SESSION['rol'] ?? 'medico';
$nombreU = $_SESSION['nombre_usuario'] ?? 'Usuario';

// Datos iniciales para la vista (aunque la carga principal la hará AJAX)
$tipo = $_GET['tipo'] ?? 'anual';
$anio = (int)($_GET['anio'] ?? date("Y"));
$mes  = isset($_GET['mes']) ? (int)$_GET['mes'] : null;

// Nombres de meses para el select
$nombres_meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carga de Programación - CEFPPNAY</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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

        /* ESTILOS DEL CONTENIDO (Columna Izquierda) */
        .content-card {
            background: var(--surface-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-md);
            border: 1px solid rgba(226, 232, 240, 0.8); padding: 2.5rem; width: 100%; box-sizing: border-box;
            animation: fadeSlideUp 0.4s ease-out;
        }

        .form-header {
            display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;
            padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-light); flex-wrap: wrap; gap: 15px;
        }
        .header-title-box h2 { margin: 0 0 8px 0; font-size: 1.8rem; font-weight: 800; color: var(--text-dark); letter-spacing: -0.5px; display: flex; align-items: center; gap: 12px; }
        .header-title-box h2 i { color: var(--primary-color); font-size: 1.6rem; }
        .header-title-box p { margin: 0; font-size: 0.95rem; color: var(--text-muted); font-weight: 500; }

        /* Controles de Segmento (Anual / Mensual) PREMIUM */
        .segmented-control {
            display: inline-flex; background: #f1f5f9; padding: 6px; border-radius: 12px;
            border: 1px solid var(--border-light); box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }
        .segmented-label {
            padding: 10px 24px; border-radius: 8px; font-size: 0.9rem; font-weight: 700; color: var(--text-muted);
            cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 8px; user-select: none;
        }
        .segmented-label input[type="radio"] { display: none; }
        .segmented-label:hover { color: var(--text-dark); }
        .segmented-label:has(input:checked) {
            background: #ffffff; color: var(--primary-color);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        /* Filtros Compactos */
        .filters-toolbar { display: flex; gap: 20px; margin-bottom: 25px; flex-wrap: wrap; }
        .filter-group { display: flex; flex-direction: column; gap: 8px; position: relative; }
        .filter-label { font-size: 0.75rem; font-weight: 800; color: var(--text-regular); text-transform: uppercase; letter-spacing: 0.5px; }
        
        .filter-select {
            padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border-focus);
            background: #fff; color: var(--text-dark); font-size: 0.95rem; font-weight: 600; font-family: 'Inter', sans-serif;
            min-width: 160px; outline: none; transition: all 0.2s; cursor: pointer; appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 12px center; padding-right: 40px; height: 46px;
        }
        .filter-select:hover { border-color: #94a3b8; }
        .filter-select:focus { border-color: var(--primary-color); box-shadow: 0 0 0 4px var(--primary-light); }

        /* Botón Guardar */
        .btn-primary {
            background: var(--text-dark); color: #ffffff; border: none; padding: 12px 28px;
            border-radius: 8px; font-family: 'Inter', sans-serif; font-size: 0.95rem; font-weight: 700;
            cursor: pointer; display: inline-flex; align-items: center; gap: 10px; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px rgba(15, 23, 42, 0.1); letter-spacing: 0.3px; height: 46px;
        }
        .btn-primary:hover { background: #000; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(15, 23, 42, 0.15); }
        .btn-primary:active { transform: translateY(0); }
        .btn-primary:disabled { background: #94a3b8; cursor: not-allowed; transform: none; box-shadow: none; }

        /* ========================================================
           TABLA DE PROGRAMACIÓN PREMIUM Y AJUSTADA
           ======================================================== */
        .table-responsive { border: 1px solid var(--border-light); border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm); }
        
        .modern-table { 
            width: 100%; 
            border-collapse: collapse; 
            text-align: left; 
            background: #ffffff; 
            table-layout: fixed; /* Forzar el diseño de columnas para evitar desfases */
        }
        
        .modern-table thead th {
            background: rgba(248, 250, 252, 0.95); padding: 16px 20px; font-size: 0.75rem; font-weight: 800;
            color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid var(--border-light);
        }
        
        .modern-table tbody td {
            padding: 12px 20px; font-size: 0.95rem; font-weight: 500; color: var(--text-regular);
            border-bottom: 1px solid #f1f5f9; vertical-align: middle; transition: background 0.2s;
        }
        
        .modern-table tr:last-child td { border-bottom: none; }
        .modern-table tbody tr:hover td { background-color: #f8fafc; color: var(--text-dark); }

        /* Estilos globales para los inputs generados por AJAX para que quepan perfectos en la celda */
        #tablaProgramacion input[type="text"],
        #tablaProgramacion input[type="number"] {
            width: 100%;
            box-sizing: border-box;
            padding: 10px 14px;
            border: 1px solid var(--border-focus);
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            color: var(--text-dark);
            font-weight: 600;
            background-color: #ffffff;
            transition: all 0.2s ease;
            outline: none;
        }
        
        #tablaProgramacion input[type="text"]:hover,
        #tablaProgramacion input[type="number"]:hover {
            border-color: #94a3b8;
        }

        #tablaProgramacion input[type="text"]:focus,
        #tablaProgramacion input[type="number"]:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

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
            .form-card { padding: 1.5rem; }
            .segmented-control { width: 100%; display: flex; }
            .segmented-label { flex: 1; justify-content: center; text-align: center; padding: 10px; }
            .filters-toolbar { flex-direction: column; }
            .filter-group { width: 100%; }
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
                    <span><?= htmlspecialchars($nombreU) ?></span>
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
            <a href="carga_programacion.php" class="sidebar-link active">
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
        
        <div class="split-layout">
            
            <div class="left-column">
                <form id="formProgramacion" action="../funciones/guardar_programacion.php" method="post">
                    <div class="content-card">
                        
                        <div class="form-header">
                            <div class="header-title-box">
                                <h2><i class="fas fa-bullseye"></i> Configuración de Metas</h2>
                                <p>Establezca los objetivos físicos operativos para el periodo seleccionado.</p>
                            </div>
                            
                            <div class="segmented-control">
                                <label class="segmented-label">
                                    <input type="radio" name="tipo" value="anual" <?= $tipo==='anual'?'checked':'' ?> onchange="toggleMes()"> 
                                    <i class="fas fa-calendar"></i> Meta Anual
                                </label>
                                <label class="segmented-label">
                                    <input type="radio" name="tipo" value="mensual" <?= $tipo==='mensual'?'checked':'' ?> onchange="toggleMes()"> 
                                    <i class="fas fa-calendar-alt"></i> Meta Mensual
                                </label>
                            </div>
                        </div>

                        <div class="filters-toolbar">
                            <div class="filter-group" style="width: 180px;">
                                <label class="filter-label">Año de Ejercicio</label>
                                <select name="anio" class="filter-select" style="width:100%;">
                                    <?php for($y=date("Y")-1;$y<=date("Y")+1;$y++): ?>
                                        <option value="<?= $y ?>" <?= $y==$anio?'selected':'' ?>><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="filter-group" id="mesSelect" style="width: 240px; display:<?= $tipo==='mensual'?'flex':'none' ?>">
                                <label class="filter-label">Mes a Programar</label>
                                <select name="mes" class="filter-select" style="width:100%;">
                                    <?php foreach($nombres_meses as $num=>$nombre): ?>
                                        <option value="<?= $num ?>" <?= $mes==$num?'selected':'' ?>><?= $nombre ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40%;">Nombre de la Actividad</th>
                                        <th style="width: 30%;">Unidad de Medida</th>
                                        <th style="width: 30%;">Meta Programada</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaProgramacion">
                                    <tr>
                                        <td colspan="3" style="text-align:center; padding:60px; color:var(--text-muted);">
                                            <i class="fas fa-circle-notch fa-spin" style="font-size:32px; color:var(--primary-color); margin-bottom:15px; display:block;"></i>
                                            <span style="font-weight:600;">Cargando matriz de programación...</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div style="margin-top: 2rem; text-align: right; border-top: 1px solid var(--border-light); padding-top: 1.5rem;">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> Guardar Programación
                            </button>
                        </div>
                        
                    </div>
                </form>
            </div> <div class="right-column">
                <div class="instructions-card">
                    <h3><i class="fas fa-route" style="color: #0ea5e9;"></i> Guía de Planificación</h3>
                    
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>Seleccione el Tipo de Meta</h4>
                            <p>Elija en la parte superior si va a registrar los objetivos generales de todo el <b>Año</b> o las metas específicas de un <b>Mes</b> en particular.</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Defina el Periodo</h4>
                            <p>Ajuste el Año de Ejercicio (y el Mes si eligió la opción Mensual) en los menús desplegables. La matriz se actualizará automáticamente.</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Capture las Cantidades</h4>
                            <p>Ingrese los valores numéricos correspondientes a cada actividad. Deje en 0 o vacío las actividades que no estén programadas.</p>
                        </div>
                    </div>

                    <div class="info-alert">
                        <i class="fas fa-calculator"></i>
                        <p><b>Nota Analítica:</b> Estos números se utilizarán como base (El 100%) para calcular el Porcentaje de Avance en el panel de Reportes Estadísticos.</p>
                    </div>
                </div>
            </div> </div> </main>
</div>

<script>
// 1. Lógica del Dropdown de Usuario con Animación
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

// 2. Lógica del Formulario (AJAX) intacta con SweetAlert
document.getElementById("formProgramacion").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    btn.disabled = true;

    let formData = new FormData(this);
    fetch(this.action, {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        Swal.fire({
            title: '¡Guardado Correctamente!',
            text: 'Las metas de programación se han actualizado exitosamente.',
            icon: 'success',
            confirmButtonColor: '#1e293b',
            confirmButtonText: 'Aceptar',
            customClass: { popup: 'content-card', confirmButton: 'btn-primary' }
        });
    })
    .catch(error => {
        Swal.fire({
            title: 'Error de Conexión',
            text: 'No se pudo guardar la información. Verifique su red.',
            icon: 'error',
            confirmButtonColor: '#ef4444',
            customClass: { popup: 'content-card', confirmButton: 'btn-primary' }
        });
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});

function toggleMes() {
    const tipo = document.querySelector('input[name="tipo"]:checked').value;
    document.getElementById('mesSelect').style.display = (tipo==='mensual')?'flex':'none';
    actualizarTablaAJAX();
}

function actualizarTablaAJAX() {
    const tipo = document.querySelector('input[name="tipo"]:checked').value;
    const anio = document.querySelector('select[name="anio"]').value;
    const mes  = document.querySelector('select[name="mes"]').value;

    document.getElementById("tablaProgramacion").innerHTML = '<tr><td colspan="3" style="text-align:center; padding:60px; color:var(--text-muted);"><i class="fas fa-circle-notch fa-spin" style="font-size:32px; color:var(--primary-color); margin-bottom:15px; display:block;"></i><span style="font-weight:600;">Actualizando matriz...</span></td></tr>';

    let url = '../funciones/tabla_programacion.php?tipo='+tipo+'&anio='+anio;
    if(tipo==='mensual') url += '&mes='+mes;

    fetch(url)
    .then(res => res.text())
    .then(html => {
        document.getElementById("tablaProgramacion").innerHTML = html;
        // La estilización de los inputs ahora es automática vía CSS (#tablaProgramacion input)
    })
    .catch(err => {
        document.getElementById("tablaProgramacion").innerHTML = '<tr><td colspan="3" style="text-align:center; padding:40px; color:#ef4444; font-weight:600;"><i class="fas fa-exclamation-triangle" style="font-size:24px; margin-bottom:10px; display:block;"></i> Error al cargar datos.</td></tr>';
    });
}

// Event Listeners
document.addEventListener("DOMContentLoaded", actualizarTablaAJAX);
document.querySelector('select[name="anio"]').addEventListener('change', actualizarTablaAJAX);
document.querySelector('select[name="mes"]').addEventListener('change', actualizarTablaAJAX);
document.querySelectorAll('input[name="tipo"]').forEach(radio => radio.addEventListener('change', toggleMes));
</script>

</body>
</html>
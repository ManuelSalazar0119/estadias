<?php
// Solución al Notice rojo: Verificamos si la sesión ya existe antes de iniciarla
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("../funciones/conexion.php");
include_once("../funciones/funciones_reportes.php");

// Verifica si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../login.php');
    exit;
}
$rol = $_SESSION['rol_usuario'] ?? ($_SESSION['rol'] ?? 'medico');
$nombre = $_SESSION['nombre_usuario'] ?? 'Usuario';

// Obtener áreas desde la base de datos
$areas = [];
$result_areas = $conn->query("SELECT id_area, nombre_area FROM areas");
while ($row = $result_areas->fetch_assoc()) {
    $areas[] = $row;
}

// Determina el área seleccionada (por GET o por defecto)
$id_area = isset($_GET['id_area']) ? intval($_GET['id_area']) : ($areas[0]['id_area'] ?? 0);
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('n');

$nombres_meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
$mes_nombre = $mes > 0 ? $nombres_meses[$mes] : 'Todos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Mensual - Sistema Activo</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/sidebar_unificado.css?v=<?php echo time(); ?>">

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
            --excel-green: #10b981;
            --excel-hover: #059669;
            --info-blue: #0ea5e9;
            --info-hover: #0284c7;
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

        .panel-content { flex: 1; padding: 35px 40px; overflow-x: hidden; max-width: 1450px; margin: 0 auto; width: 100%; box-sizing: border-box; }

        /* =========================================================
           ENCABEZADO DE LA VISTA
           ========================================================= */
        .view-header {
            display: flex; flex-direction: column; margin-bottom: 25px;
            animation: fadeSlideUp 0.4s ease-out;
        }
        .view-header h1 { margin: 0 0 8px 0; font-size: 1.8rem; font-weight: 800; color: var(--text-dark); letter-spacing: -0.5px; }
        .view-header p { margin: 0; color: var(--text-muted); font-size: 0.95rem; font-weight: 500; }

        /* =========================================================
           TARJETA DE FILTROS PREMIUM
           ========================================================= */
        .advanced-filters {
            background: var(--surface-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-md);
            padding: 24px 30px; margin-bottom: 30px; border: 1px solid rgba(226, 232, 240, 0.8);
            display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px; align-items: flex-end;
            animation: fadeSlideUp 0.5s ease-out; animation-fill-mode: both; animation-delay: 0.1s;
        }

        .filters-left { display: flex; align-items: flex-end; gap: 15px; flex-wrap: wrap; }
        
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
        
        .btn-justificacion { background: var(--info-blue); color: white; box-shadow: 0 4px 6px rgba(14, 165, 233, 0.15); }
        .btn-justificacion:hover { background: var(--info-hover); transform: translateY(-2px); box-shadow: 0 6px 12px rgba(14, 165, 233, 0.25); }

        .btn-excel { background: var(--excel-green); color: white; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.15); }
        .btn-excel:hover { background: var(--excel-hover); transform: translateY(-2px); box-shadow: 0 6px 12px rgba(16, 185, 129, 0.25); }

        .button-group-right { display: flex; gap: 15px; }

        /* ESTADO VACÍO (Si no hay reporte) */
        .empty-state { text-align: center; padding: 60px 20px; background: var(--surface-white); border-radius: var(--radius-lg); border: 1px dashed var(--border-focus); animation: fadeSlideUp 0.5s ease-out; }
        .empty-state i { font-size: 3rem; color: #cbd5e1; margin-bottom: 15px; }
        .empty-state p { color: var(--text-muted); font-size: 1rem; font-weight: 600; margin: 0; }

        .report-container {
            animation: fadeSlideUp 0.5s ease-out; animation-fill-mode: both; animation-delay: 0.2s;
        }

        /* =========================================================
           MODAL PREMIUM DE JUSTIFICACIONES
           ========================================================= */
        .modal-overlay {
            display: none; position: fixed; top:0; left:0; width:100%; height:100%;
            background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px); z-index:9999;
            align-items:center; justify-content:center;
        }
        .modal-container {
            background:#fff; padding:35px; border-radius:16px; max-width:900px; width:95%;
            position:relative; max-height:85vh; display:flex; flex-direction:column;
            box-shadow: var(--shadow-floating); border: 1px solid rgba(226, 232, 240, 0.8);
            animation: fadeSlideUp 0.3s ease-out;
        }
        .modal-cerrar {
            position:absolute; top:20px; right:25px; font-size:28px; cursor:pointer; color:#94a3b8; transition: 0.2s;
        }
        .modal-cerrar:hover { color: var(--danger-color); }
        
        .modal-header-title { margin-top:0; font-family:'Inter'; color:var(--text-dark); font-weight: 800; display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
        
        .table-modal-container { overflow-y:auto; border: 1px solid var(--border-light); border-radius: 12px; flex:1; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); }
        #tablaJustificaciones { width: 100%; border-collapse: collapse; text-align: left; }
        #tablaJustificaciones th { background: #f8fafc; padding: 14px 16px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-light); position: sticky; top: 0; z-index: 5; }
        #tablaJustificaciones td { border-bottom: 1px solid #f1f5f9; padding: 12px 16px; font-size: 0.9rem; color: var(--text-regular); font-weight: 500; }
        
        #guardarJustificaciones { margin-top:20px; padding:12px 24px; background:var(--text-dark); color:white; border:none; border-radius:8px; font-weight:700; cursor:pointer; align-self:flex-end; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; box-shadow: 0 4px 6px rgba(15, 23, 42, 0.1); font-family: 'Inter'; font-size: 0.95rem; }
        #guardarJustificaciones:hover { background: #000; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(15, 23, 42, 0.15); }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .advanced-filters { flex-direction: column; align-items: stretch; }
            .filters-left { width: 100%; }
            .button-group-right { width: 100%; justify-content: flex-end; margin-top: 10px; }
        }
        @media (max-width: 768px) {
            .panel-sidebar { display: none; }
            .header-right .user-info-text, .header-right .fa-chevron-down { display: none; }
            .filter-group { width: 100%; }
            .filter-select { width: 100%; min-width: 100%; }
            .btn { width: 100%; }
            .button-group-right { flex-direction: column; }
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
            <a href="reporte_mensual.php" class="sidebar-link active">
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
            <h1>Reporte Analítico Mensual</h1>
            <p>Consulta, evalúa y justifica el cumplimiento de las metas operativas.</p>
        </div>

        <div class="advanced-filters">
            <form method="GET" action="reporte_mensual.php" class="filters-left" style="margin: 0;">
                
                <div class="filter-group">
                    <label for="anio" class="filter-label">Año</label>
                    <select name="anio" id="anio" class="filter-select" style="min-width: 100px;">
                        <?php for($y=2020; $y<=2030; $y++): ?>
                            <option value="<?= $y ?>" <?= ($y == $anio) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="mes" class="filter-label">Mes</label>
                    <select name="mes" id="mes" class="filter-select" style="min-width: 140px;">
                        <?php foreach ($nombres_meses as $num => $nombre): ?>
                            <option value="<?= $num ?>" <?= ($num == $mes) ? 'selected' : '' ?>><?= $nombre ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="area" class="filter-label">Campaña a Evaluar</label>
                    <select name="id_area" id="area-select" class="filter-select" onchange="this.form.submit()" style="min-width: 250px;">
                        <?php foreach ($areas as $area): ?>
                            <option value="<?= (int)$area['id_area'] ?>" <?= ((int)$area['id_area'] === (int)$id_area) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($area['nombre_area']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary" title="Buscar">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </form>

            <div class="button-group-right">
                <button type="button" id="abrirModal" class="btn btn-justificacion">
                    <i class="fas fa-comment-dots"></i> Justificar
                </button>
                <a href="../funciones/exportar_tablas.php?id_area=<?= $id_area ?>&anio=<?= $anio ?>&mes=<?= $mes ?>" class="btn btn-excel">
                    <i class="fas fa-file-excel"></i> Exportar
                </a>
            </div>
        </div>

        <div class="report-container">
            <?php
            if ($id_area) {
                switch ($id_area) {
                    case 1: include "reporte_mensual_TB.php"; break;
                    case 2: include "reporte_mensual_Brucela.php"; break;
                    default: echo "<div class='empty-state'><i class='fas fa-folder-open'></i><p>No existe formato para el área seleccionada.</p></div>"; break;
                }
            } else {
                echo "<div class='empty-state'><i class='fas fa-mouse-pointer'></i><p>Selecciona un área para comenzar.</p></div>";
            }
            ?>
        </div>
    </main>
</div>

<div id="modalJustificaciones" class="modal-overlay">
  <div class="modal-container">
    <span class="modal-cerrar" id="cerrarModal">&times;</span>
    <h2 class="modal-header-title"><i class="fas fa-clipboard-list" style="color:#0ea5e9;"></i> Justificaciones Mensuales</h2>
    
    <div class="table-modal-container">
        <table id="tablaJustificaciones">
          <thead>
            <tr>
                <th style="width: 35%;">Actividad</th>
                <th style="width: 15%;">Unidad</th>
                <th style="width: 12%;">Prog.</th>
                <th style="width: 12%;">Realiz.</th>
                <th style="width: 26%;">Justificación</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
    </div>
    
    <button id="guardarJustificaciones">
        <i class="fas fa-save"></i> Guardar Todas
    </button>
  </div>
</div>

<script src="../js/llenartabla.js?v=<?= rand() ?>"></script>
<script>
    // Control del Menú Usuario Desplegable
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

    // Lógica del Modal con animación Suave
    const modal = document.getElementById('modalJustificaciones');
    const abrir = document.getElementById('abrirModal');
    const cerrar = document.getElementById('cerrarModal');
    
    if(abrir) { 
        abrir.addEventListener('click', () => { 
            modal.style.display = 'flex'; 
        }); 
    }
    if(cerrar) { 
        cerrar.addEventListener('click', () => { 
            modal.style.display = 'none'; 
        }); 
    }
    window.addEventListener('click', (e) => { 
        if (e.target === modal) { 
            modal.style.display = 'none'; 
        } 
    });

    // EL JS DEL BUSCADOR ESTÁ AHORA EN LOS ARCHIVOS HIJOS (TB y Brucela)
</script>
</body>
</html>
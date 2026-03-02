<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar Moderno Compacto</title>
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* ESTILOS PARA EL CUERPO DE LA PÁGINA */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', 'Inter', sans-serif;
            background: #f8fafc;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
        }
        
        body.sidebar-collapsed {
            margin-left: 70px;
        }
        
        body:not(.sidebar-collapsed) {
            margin-left: 220px;
        }
        
        /* CONTENIDO PRINCIPAL */
        .main-content {
            padding: 25px 30px;
            min-height: 100vh;
        }
        
        /* TOGGLE BUTTON PARA MÓVIL */
        .mobile-toggle-btn {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 999;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .mobile-toggle-btn:hover {
            background: #2980b9;
            transform: scale(1.05);
        }
        
        /* OVERLAY PARA MÓVIL */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
            backdrop-filter: blur(2px);
        }
        
        /* RESPONSIVE */
        @media (max-width: 1024px) {
            body:not(.sidebar-collapsed) {
                margin-left: 200px;
            }
        }
        
        @media (max-width: 768px) {
            .mobile-toggle-btn {
                display: block;
            }
            
            body, body.sidebar-collapsed {
                margin-left: 0 !important;
            }
            
            .main-content {
                padding: 20px 15px;
            }
            
            .sidebar-overlay.active {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Botón toggle para móvil -->
    <button class="mobile-toggle-btn" id="mobileToggleBtn">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Overlay para móvil -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar Moderno Compacto -->
    <div class="sidebar-admin" id="sidebar">
        <!-- Botón para colapsar/expandir -->
        <button class="sidebar-toggle-btn" id="sidebarToggleBtn">
            <i class="fas fa-chevron-left"></i>
        </button>
        
        <!-- Header del Sidebar -->
        <div class="sidebar-header">
            <div class="logo-container">
                <img src="../imagenes/logoPng.png" alt="Logo Sistema" class="logo">
                <div class="user-info">
                    <h3 class="user-name">
                        <?php
                        $nombre = trim(($_SESSION['nombre_usuario'] ?? '') . ' ' . 
                                      ($_SESSION['ape_pat_usuario'] ?? '') . ' ' . 
                                      ($_SESSION['ape_mat_usuario'] ?? ''));
                        echo htmlspecialchars($nombre ?: 'Administrador');
                        ?>
                    </h3>
                    <span class="user-role">Administrador</span>
                </div>
            </div>
            <hr class="divider">
        </div>
        
        <!-- Navegación -->
        <ul class="nav" id="sidebarNav">
            <!-- Gestión de Actividades -->
            <li class="nav-group-title active" data-group="actividades">
                <span><i class="fas fa-tasks"></i> Actividades</span>
                <i class="fas fa-chevron-down"></i>
            </li>
            <ul class="nav-group" data-group="actividades">
                <li><a href="panel_control.php"><i class="fas fa-home"></i> <span>Inicio</span></a></li>
                <li><a href="../interfaces/registro_nueva_actividad.php"><i class="fas fa-plus-circle"></i> <span>Crear Actividades</span></a></li>
                <li><a href="../interfaces/nuevo_registro.php"><i class="fas fa-file-medical"></i> <span>Formulario Médico</span></a></li>
            </ul>
            
            <!-- Reportes -->
            <li class="nav-group-title" data-group="reportes">
                <span><i class="fas fa-chart-line"></i> Reportes</span>
                <i class="fas fa-chevron-down"></i>
            </li>
            <ul class="nav-group collapsed" data-group="reportes">
                <li><a href="../interfaces/estadisticas.php"><i class="fas fa-chart-bar"></i> <span>Estadísticas</span></a></li>
                <li><a href="../interfaces/reporte_mensual.php"><i class="fas fa-file-alt"></i> <span>Reporte General</span></a></li>
                <li><a href="../interfaces/carga_programacion.php"><i class="fas fa-upload"></i> <span>Carga Programación</span></a></li>
            </ul>
            
            <!-- Comprobaciones Físicas -->
            <li class="nav-group-title" data-group="comprobaciones">
                <span><i class="fas fa-clipboard-check"></i> Comprobaciones</span>
                <i class="fas fa-chevron-down"></i>
            </li>
            <ul class="nav-group collapsed" data-group="comprobaciones">
                <li><a href="../interfaces/comprobacion_fisica.php"><i class="fas fa-check-double"></i> <span>Comprobaciones Físicas</span></a></li>
            </ul>
            
            <!-- Usuarios -->
            <li class="nav-group-title" data-group="usuarios">
                <span><i class="fas fa-users"></i> Usuarios</span>
                <i class="fas fa-chevron-down"></i>
            </li>
            <ul class="nav-group collapsed" data-group="usuarios">
                <li><a href="#"><i class="fas fa-user-friends"></i> <span>Lista de Usuarios</span></a></li>
                <li><a href="#"><i class="fas fa-user-plus"></i> <span>Registrar Usuario</span></a></li>
            </ul>
        </ul>
        
        <!-- Footer del Sidebar (Opcional) -->
        <div class="sidebar-footer">
            <div class="system-status">Sistema Activo</div>
            <div class="system-version">v2.1.0</div>
        </div>
    </div>
    
    
    <script>
    // ===== VARIABLES GLOBALES =====
    const sidebar = document.getElementById('sidebar');
    const body = document.body;
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const mobileToggleBtn = document.getElementById('mobileToggleBtn');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mainContent = document.getElementById('mainContent');
    
    // ===== TOGGLE SIDEBAR (DESKTOP) =====
    sidebarToggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        body.classList.toggle('sidebar-collapsed');
        
        // Rotar ícono del botón
        const icon = this.querySelector('i');
        if (sidebar.classList.contains('collapsed')) {
            icon.style.transform = 'rotate(180deg)';
        } else {
            icon.style.transform = 'rotate(0deg)';
        }
        
        // Guardar preferencia en localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    });
    
    // ===== TOGGLE SIDEBAR (MÓVIL) =====
    mobileToggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('mobile-open');
        sidebarOverlay.classList.toggle('active');
        body.classList.toggle('sidebar-mobile-open');
    });
    
    // Cerrar sidebar al hacer clic en overlay (móvil)
    sidebarOverlay.addEventListener('click', function() {
        sidebar.classList.remove('mobile-open');
        this.classList.remove('active');
        body.classList.remove('sidebar-mobile-open');
    });
    
    // ===== ACORDEÓN DE GRUPOS =====
    document.querySelectorAll('.nav-group-title').forEach((title, index) => {
        title.addEventListener('click', function() {
            const group = this.getAttribute('data-group');
            const groupMenu = document.querySelector(`.nav-group[data-group="${group}"]`);
            
            // Si el grupo no está colapsado, colapsarlo
            if (!groupMenu.classList.contains('collapsed')) {
                groupMenu.classList.add('collapsed');
                this.classList.remove('active');
                return;
            }
            
            // Colapsar otros grupos abiertos
            document.querySelectorAll('.nav-group:not(.collapsed)').forEach(openGroup => {
                if (openGroup !== groupMenu) {
                    openGroup.classList.add('collapsed');
                    const openTitle = document.querySelector(`.nav-group-title[data-group="${openGroup.getAttribute('data-group')}"]`);
                    if (openTitle) openTitle.classList.remove('active');
                }
            });
            
            // Expandir grupo actual
            groupMenu.classList.remove('collapsed');
            this.classList.add('active');
            
            // Cerrar sidebar en móvil después de seleccionar
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('active');
                body.classList.remove('sidebar-mobile-open');
            }
        });
    });
    
    // ===== MARCAR PÁGINA ACTIVA =====
    document.querySelectorAll('.nav-group li a').forEach((link, index) => {
        // Asignar índice para animación escalonada
        link.style.setProperty('--item-index', index);
        
        // Marcar como activo si coincide con la URL actual
        if (link.href === window.location.href || 
            (link.getAttribute('href') && window.location.href.includes(link.getAttribute('href')))) {
            link.classList.add('active');
            
            // Expandir grupo padre
            const group = link.closest('.nav-group');
            if (group) {
                group.classList.remove('collapsed');
                const title = document.querySelector(`.nav-group-title[data-group="${group.getAttribute('data-group')}"]`);
                if (title) title.classList.add('active');
            }
        }
    });
    
    // ===== CARGAR PREFERENCIA GUARDADA =====
    document.addEventListener('DOMContentLoaded', function() {
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true') {
            sidebar.classList.add('collapsed');
            body.classList.add('sidebar-collapsed');
            sidebarToggleBtn.querySelector('i').style.transform = 'rotate(180deg)';
        }
        
        // Cerrar sidebar en móvil al cargar
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('mobile-open');
            sidebarOverlay.classList.remove('active');
        }
    });
    
    // ===== AJUSTAR AL TAMAÑO DE PANTALLA =====
    function handleResize() {
        if (window.innerWidth <= 768) {
            // En móvil, asegurar que sidebar esté cerrado
            sidebar.classList.remove('collapsed');
            body.classList.remove('sidebar-collapsed');
        }
    }
    
    window.addEventListener('resize', handleResize);
    handleResize(); // Ejecutar al cargar
    </script>
</body>
</html>
<?php
// interfaces/sidebar_left.php

// Verificar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header('Location: /login.php');
    exit;
}

$rol = $_SESSION['rol'] ?? 'medico';
$nombre = $_SESSION['nombre_usuario'] ?? 'Usuario';
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Contenedor principal - IZQUIERDA -->
<div class="left-nav-container">
    
    <!-- BOTÓN MENÚ (IZQUIERDA) -->
    <div class="menu-dropdown-container">
        <button class="menu-dropdown-btn" onclick="toggleMenuDropdown()">
            <i class="fas fa-bars"></i>
            <span>Menú</span>
        </button>
        
        <div class="menu-dropdown-content" id="menuDropdown">
            <div class="menu-header">
                <div class="menu-user-info">
                    <i class="fas fa-user-circle"></i>
                    <div>
                        <strong><?php echo htmlspecialchars($nombre); ?></strong>
                        <small><?php echo ucfirst($rol); ?></small>
                    </div>
                </div>
                <div class="system-status-menu">
                    <i class="fas fa-circle status-online"></i>
                    <span>Sistema Activo v2.1.0</span>
                </div>
            </div>
            
            <div class="menu-divider"></div>
            
            <a href="panel_control.php" class="menu-item <?= $current_page == 'panel_control.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Panel de Control</span>
            </a>
            <a href="reporte_mensual.php" class="menu-item <?= strpos($current_page, 'reporte_') !== false ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Reportes</span>
            </a>
            <a href="comprobaciones.php" class="menu-item">
                <i class="fas fa-clipboard-check"></i>
                <span>Comprobaciones Físicas</span>
            </a>
            <a href="usuarios.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Usuarios</span>
            </a>
            
            <?php if ($rol === 'admin'): ?>
            <div class="menu-divider"></div>
            <div class="menu-section-title">Administración</div>
            <a href="configuracion_sistema.php" class="menu-item">
                <i class="fas fa-sliders-h"></i>
                <span>Configuración Sistema</span>
            </a>
            <a href="backup.php" class="menu-item">
                <i class="fas fa-database"></i>
                <span>Backup</span>
            </a>
            <?php endif; ?>
            
            <div class="menu-divider"></div>
            
            <a href="../funciones/logout.php" class="menu-item logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </div>
    
    <!-- TÍTULO DE LA PÁGINA (junto al botón Menú) -->
    <h1 class="page-title-left">
        Panel de Control
    </h1>
</div>

<style>
/* ======================
   CONTENEDOR IZQUIERDO
   ====================== */
.left-nav-container {
    display: flex;
    align-items: center;
    gap: 15px;
}

/* ======================
   BOTÓN MENÚ (IZQUIERDA)
   ====================== */
.menu-dropdown-container {
    position: relative;
}

.menu-dropdown-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #882e2e; /* Rojo del sistema */
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    min-width: 80px;
    justify-content: center;
}

.menu-dropdown-btn:hover {
    background: #a05a5a;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.menu-dropdown-btn i {
    font-size: 16px;
}

/* Menú desplegable */
.menu-dropdown-content {
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    min-width: 250px;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    display: none;
    z-index: 1000;
    margin-top: 8px;
    border: 1px solid #ddd;
    overflow: hidden;
}

.menu-dropdown-content.show {
    display: block;
    animation: slideDown 0.2s;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Header del menú */
.menu-header {
    background: #882e2e;
    color: white;
    padding: 15px;
}

.menu-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
}

.menu-user-info i {
    font-size: 32px;
    color: white;
}

.menu-user-info div {
    display: flex;
    flex-direction: column;
}

.menu-user-info strong {
    font-size: 16px;
    font-weight: 600;
}

.menu-user-info small {
    font-size: 13px;
    opacity: 0.9;
}

.system-status-menu {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    opacity: 0.9;
}

.status-online {
    color: #4CAF50;
    font-size: 8px;
}

/* Items del menú */
.menu-divider {
    height: 1px;
    background: #eee;
    margin: 5px 0;
}

.menu-section-title {
    padding: 10px 16px 5px;
    font-size: 11px;
    text-transform: uppercase;
    color: #999;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: #333;
    text-decoration: none;
    transition: all 0.2s;
    border-left: 3px solid transparent;
}

.menu-item:hover {
    background: #f5f5f5;
    color: #882e2e;
    border-left-color: #882e2e;
}

.menu-item.active {
    background: rgba(136, 46, 46, 0.1);
    color: #882e2e;
    font-weight: 500;
    border-left-color: #882e2e;
}

.menu-item i {
    width: 20px;
    text-align: center;
    color: #666;
    font-size: 16px;
}

.menu-item:hover i,
.menu-item.active i {
    color: #882e2e;
}

.menu-item.logout {
    color: #d32f2f;
}

.menu-item.logout:hover {
    background: rgba(211, 47, 47, 0.1);
    color: #b71c1c;
    border-left-color: #d32f2f;
}

.menu-item.logout i {
    color: #d32f2f;
}

/* Título de la página */
.page-title-left {
    font-size: 24px;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    padding: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .left-nav-container {
        gap: 10px;
    }
    
    .menu-dropdown-btn span {
        display: none;
    }
    
    .menu-dropdown-btn {
        min-width: 50px;
        padding: 8px 12px;
    }
    
    .page-title-left {
        font-size: 20px;
    }
    
    .menu-dropdown-content {
        min-width: 220px;
    }
}
</style>

<script>
let menuDropdownOpen = false;

function toggleMenuDropdown() {
    const menu = document.getElementById('menuDropdown');
    const btn = document.querySelector('.menu-dropdown-btn');
    
    if (!menuDropdownOpen) {
        menu.classList.add('show');
        menuDropdownOpen = true;
        
        // Cerrar al hacer clic fuera
        setTimeout(() => {
            document.addEventListener('click', closeMenuOnClick);
        }, 10);
    } else {
        closeMenuDropdown();
    }
}

function closeMenuDropdown() {
    const menu = document.getElementById('menuDropdown');
    
    if (menu) menu.classList.remove('show');
    menuDropdownOpen = false;
    
    document.removeEventListener('click', closeMenuOnClick);
}

function closeMenuOnClick(event) {
    const menu = document.getElementById('menuDropdown');
    const btn = document.querySelector('.menu-dropdown-btn');
    
    if (menu && btn && 
        !menu.contains(event.target) && 
        !btn.contains(event.target)) {
        closeMenuDropdown();
    }
}

// Cerrar con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && menuDropdownOpen) {
        closeMenuDropdown();
    }
});
</script>
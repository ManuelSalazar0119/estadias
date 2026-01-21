<?php
// interfaces/sidebar_simple.php

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
$avatar = $_SESSION['avatar'] ?? '../imagenes/user-default.png';
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Contenedor principal -->
<div class="header-navigation">
    
    <!-- MENÚ DESPLEGABLE (AMARILLO) -->
    <div class="nav-dropdown-container">
        <button class="nav-dropdown-btn" onclick="toggleNavDropdown()">
            <i class="fas fa-bars"></i>
            <span>Menú</span>
            <i class="fas fa-chevron-down"></i>
        </button>
        
        <div class="nav-dropdown-menu" id="navDropdown">
            <a href="panel_control.php" class="nav-dropdown-item <?= $current_page == 'panel_control.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                Panel de Control
            </a>
            <a href="reporte_mensual.php" class="nav-dropdown-item <?= strpos($current_page, 'reporte_') !== false ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                Reportes
            </a>
            <a href="comprobaciones.php" class="nav-dropdown-item">
                <i class="fas fa-clipboard-check"></i>
                Comprobaciones Físicas
            </a>
            <a href="usuarios.php" class="nav-dropdown-item">
                <i class="fas fa-users"></i>
                Usuarios
            </a>
            
            <?php if ($rol === 'admin'): ?>
            <div class="dropdown-divider"></div>
            <div class="dropdown-section-title">Administración</div>
            <a href="configuracion_sistema.php" class="nav-dropdown-item">
                <i class="fas fa-sliders-h"></i>
                Configuración Sistema
            </a>
            <a href="backup.php" class="nav-dropdown-item">
                <i class="fas fa-database"></i>
                Backup
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- USUARIO + CERRAR SESIÓN (ROJO) -->
    <div class="user-simple-container">
        <div class="user-info-mini">
            <span class="user-name-mini"><?php echo htmlspecialchars($nombre); ?></span>
            <span class="user-role-mini"><?php echo ucfirst($rol); ?></span>
        </div>
        
        <a href="../funciones/logout.php" class="logout-btn-simple" title="Cerrar Sesión">
            <i class="fas fa-sign-out-alt"></i>
            <span class="logout-text">Cerrar Sesión</span>
        </a>
    </div>
</div>

<style>
/* Contenedor principal */
.header-navigation {
    display: flex;
    align-items: center;
    gap: 20px;
}

/* ======================
   MENÚ DESPLEGABLE (AMARILLO)
   ====================== */
.nav-dropdown-container {
    position: relative;
}

.nav-dropdown-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #ffc107; /* Amarillo */
    color: #333;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.nav-dropdown-btn:hover {
    background: #ffb300;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.nav-dropdown-btn i:first-child {
    font-size: 16px;
}

.nav-dropdown-btn i:last-child {
    font-size: 12px;
    transition: transform 0.2s;
}

.nav-dropdown-btn.active i:last-child {
    transform: rotate(180deg);
}

/* Menú desplegable */
.nav-dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    min-width: 220px;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    display: none;
    z-index: 1000;
    margin-top: 8px;
    border: 1px solid #ddd;
    padding: 8px 0;
}

.nav-dropdown-menu.show {
    display: block;
    animation: fadeIn 0.2s;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.nav-dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    color: #333;
    text-decoration: none;
    transition: all 0.2s;
    border-left: 3px solid transparent;
}

.nav-dropdown-item:hover {
    background: #f5f5f5;
    color: #882e2e;
    border-left-color: #ffc107;
}

.nav-dropdown-item.active {
    background: rgba(255, 193, 7, 0.1);
    color: #882e2e;
    font-weight: 500;
    border-left-color: #882e2e;
}

.nav-dropdown-item i {
    width: 20px;
    text-align: center;
    color: #666;
}

.nav-dropdown-item:hover i,
.nav-dropdown-item.active i {
    color: #882e2e;
}

.dropdown-divider {
    height: 1px;
    background: #eee;
    margin: 8px 0;
}

.dropdown-section-title {
    padding: 8px 16px 4px;
    font-size: 11px;
    text-transform: uppercase;
    color: #999;
    font-weight: 600;
    letter-spacing: 0.5px;
}

/* ======================
   USUARIO + CERRAR SESIÓN (ROJO)
   ====================== */
.user-simple-container {
    display: flex;
    align-items: center;
    gap: 15px;
    background: #882e2e; /* Rojo del sistema */
    padding: 6px 12px;
    border-radius: 6px;
    color: white;
}

.user-info-mini {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.user-name-mini {
    font-weight: 600;
    font-size: 14px;
}

.user-role-mini {
    font-size: 11px;
    opacity: 0.9;
    text-transform: capitalize;
}

/* Botón de cerrar sesión */
.logout-btn-simple {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 13px;
    transition: all 0.2s;
    cursor: pointer;
}

.logout-btn-simple:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-1px);
}

.logout-btn-simple i {
    font-size: 14px;
}

/* Responsive */
@media (max-width: 768px) {
    .header-navigation {
        gap: 10px;
    }
    
    .nav-dropdown-btn span {
        display: none;
    }
    
    .nav-dropdown-btn {
        padding: 8px 10px;
    }
    
    .logout-text {
        display: none;
    }
    
    .logout-btn-simple {
        padding: 6px 8px;
    }
    
    .user-name-mini {
        max-width: 100px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
}
</style>

<script>
let navDropdownOpen = false;

function toggleNavDropdown() {
    const menu = document.getElementById('navDropdown');
    const btn = document.querySelector('.nav-dropdown-btn');
    
    if (!navDropdownOpen) {
        menu.classList.add('show');
        btn.classList.add('active');
        navDropdownOpen = true;
        
        // Cerrar al hacer clic fuera
        setTimeout(() => {
            document.addEventListener('click', closeNavDropdownOnClick);
        }, 10);
    } else {
        closeNavDropdown();
    }
}

function closeNavDropdown() {
    const menu = document.getElementById('navDropdown');
    const btn = document.querySelector('.nav-dropdown-btn');
    
    if (menu) menu.classList.remove('show');
    if (btn) btn.classList.remove('active');
    navDropdownOpen = false;
    
    document.removeEventListener('click', closeNavDropdownOnClick);
}

function closeNavDropdownOnClick(event) {
    const menu = document.getElementById('navDropdown');
    const btn = document.querySelector('.nav-dropdown-btn');
    
    if (menu && btn && 
        !menu.contains(event.target) && 
        !btn.contains(event.target)) {
        closeNavDropdown();
    }
}

// Cerrar con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && navDropdownOpen) {
        closeNavDropdown();
    }
});
</script>
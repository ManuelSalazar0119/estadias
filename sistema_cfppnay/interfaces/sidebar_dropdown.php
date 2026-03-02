<?php
// interfaces/sidebar_dropdown.php

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
?>

<!-- Botón de usuario en esquina superior derecha -->
<div class="user-dropdown-container">
    <button class="user-dropdown-btn" onclick="toggleDropdown()">
        <div class="user-avatar-small">
            <img src="<?php echo htmlspecialchars($avatar); ?>" 
                 alt="<?php echo htmlspecialchars($nombre); ?>"
                 onerror="this.src='../imagenes/user-default.png'">
        </div>
        <span class="user-name"><?php echo htmlspecialchars($nombre); ?></span>
        <i class="fas fa-chevron-down"></i>
    </button>
    
    <!-- Menú desplegable -->
    <div class="dropdown-menu">
        <!-- Información del usuario -->
        <div class="dropdown-header">
            <div class="user-avatar-medium">
                <img src="<?php echo htmlspecialchars($avatar); ?>" 
                     alt="<?php echo htmlspecialchars($nombre); ?>"
                     onerror="this.src='../imagenes/user-default.png'">
            </div>
            <div class="user-info">
                <strong><?php echo htmlspecialchars($nombre); ?></strong>
                <small><?php echo ucfirst($rol); ?></small>
            </div>
        </div>
        
        <div class="dropdown-divider"></div>
        
        <!-- Opciones de navegación -->
        <a href="panel_control.php" class="dropdown-item">
            <i class="fas fa-tachometer-alt"></i> Panel de Control
        </a>
        <a href="reporte_mensual.php" class="dropdown-item">
            <i class="fas fa-chart-bar"></i> Reportes
        </a>
        <a href="comprobaciones.php" class="dropdown-item">
            <i class="fas fa-clipboard-check"></i> Comprobaciones
        </a>
        <a href="usuarios.php" class="dropdown-item">
            <i class="fas fa-users"></i> Usuarios
        </a>
        
        <?php if ($rol === 'admin'): ?>
        <div class="dropdown-divider"></div>
        <a href="configuracion_sistema.php" class="dropdown-item">
            <i class="fas fa-sliders-h"></i> Configuración
        </a>
        <?php endif; ?>
        
        <div class="dropdown-divider"></div>
        
        <!-- Sistema y cerrar sesión -->
        <div class="dropdown-system">
            <span class="system-status">
                <i class="fas fa-circle status-active"></i> Sistema Activo v2.1.0
            </span>
        </div>
        <a href="../funciones/logout.php" class="dropdown-item logout">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </a>
    </div>
</div>

<style>
/* Estilos básicos para el dropdown */
.user-dropdown-container {
    position: relative;
    display: inline-block;
}

.user-dropdown-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #882e2e;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 50px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s;
}

.user-dropdown-btn:hover {
    background: #a05a5a;
}

.user-avatar-small {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid white;
}

.user-avatar-small img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-name {
    font-weight: 500;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    min-width: 250px;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    display: none;
    z-index: 1000;
    margin-top: 10px;
    border: 1px solid #ddd;
}

.dropdown-menu.show {
    display: block;
    animation: fadeIn 0.2s;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.dropdown-header {
    padding: 15px;
    background: #882e2e;
    color: white;
    border-radius: 8px 8px 0 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar-medium {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid white;
}

.user-avatar-medium img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-info strong {
    font-size: 16px;
}

.user-info small {
    opacity: 0.9;
    font-size: 12px;
}

.dropdown-divider {
    height: 1px;
    background: #eee;
    margin: 5px 0;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    color: #333;
    text-decoration: none;
    transition: all 0.2s;
    border-left: 3px solid transparent;
}

.dropdown-item:hover {
    background: #f5f5f5;
    border-left-color: #882e2e;
    color: #882e2e;
}

.dropdown-item i {
    width: 20px;
    text-align: center;
    color: #666;
}

.dropdown-item:hover i {
    color: #882e2e;
}

.dropdown-item.logout {
    color: #d32f2f;
}

.dropdown-item.logout:hover {
    background: #ffebee;
    border-left-color: #d32f2f;
    color: #b71c1c;
}

.dropdown-system {
    padding: 10px 15px;
    font-size: 12px;
    color: #666;
}

.status-active {
    color: #4CAF50;
    font-size: 8px;
    margin-right: 5px;
}

/* Overlay para cerrar dropdown */
.dropdown-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: transparent;
    z-index: 999;
    display: none;
}

.dropdown-overlay.show {
    display: block;
}
</style>

<script>
let dropdownOpen = false;

function toggleDropdown() {
    const menu = document.querySelector('.dropdown-menu');
    const overlay = document.querySelector('.dropdown-overlay');
    
    if (!dropdownOpen) {
        menu.classList.add('show');
        if (!overlay) {
            createOverlay();
        } else {
            overlay.classList.add('show');
        }
        dropdownOpen = true;
    } else {
        closeDropdown();
    }
}

function createOverlay() {
    const overlay = document.createElement('div');
    overlay.className = 'dropdown-overlay';
    overlay.onclick = closeDropdown;
    document.body.appendChild(overlay);
    overlay.classList.add('show');
}

function closeDropdown() {
    const menu = document.querySelector('.dropdown-menu');
    const overlay = document.querySelector('.dropdown-overlay');
    
    if (menu) menu.classList.remove('show');
    if (overlay) overlay.classList.remove('show');
    dropdownOpen = false;
}

// Cerrar dropdown con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && dropdownOpen) {
        closeDropdown();
    }
});
</script>
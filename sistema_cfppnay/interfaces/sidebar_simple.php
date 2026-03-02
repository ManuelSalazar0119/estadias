<?php
// interfaces/sidebar_simple.php
$current = basename($_SERVER['PHP_SELF']);
$rol_usuario = $_SESSION['rol'] ?? ($_SESSION['rol_usuario'] ?? 'medico');
?>
<aside class="panel-sidebar">
    <div style="padding: 25px; text-align: center; border-bottom: 1px solid #f1f5f9;">
        <img src="../imagenes/logoPng.png" alt="Logo" style="height: 50px;">
    </div>
    <nav class="sidebar-nav">
        <span class="sidebar-section-title">Gestión Operativa</span>
        <a href="panel_control.php" class="sidebar-link <?= ($current == 'panel_control.php') ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        <a href="registro_nueva_actividad.php" class="sidebar-link <?= ($current == 'registro_nueva_actividad.php') ? 'active' : '' ?>">
            <i class="fas fa-plus-circle"></i> Nueva Actividad
        </a>
        <a href="nuevo_registro.php" class="sidebar-link <?= ($current == 'nuevo_registro.php') ? 'active' : '' ?>">
            <i class="fas fa-file-medical"></i> Registro de Campo
        </a>

        <span class="sidebar-section-title">Análisis de Datos</span>
        <a href="reporte_mensual.php" class="sidebar-link <?= ($current == 'reporte_mensual.php') ? 'active' : '' ?>">
            <i class="fas fa-file-invoice"></i> Reporte General
        </a>
        <a href="estadisticas.php" class="sidebar-link <?= ($current == 'estadisticas.php') ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i> Estadísticas
        </a>
        <a href="carga_programacion.php" class="sidebar-link <?= ($current == 'carga_programacion.php') ? 'active' : '' ?>">
            <i class="fas fa-calendar-check"></i> Programación
        </a>

        <span class="sidebar-section-title">Configuración</span>
        <a href="usuarios.php" class="sidebar-link <?= ($current == 'usuarios.php') ? 'active' : '' ?>">
            <i class="fas fa-users-cog"></i> Usuarios
        </a>
        
        <?php if ($rol_usuario === 'admin'): ?>
            <a href="backup.php" class="sidebar-link <?= ($current == 'backup.php') ? 'active' : '' ?>">
                <i class="fas fa-database"></i> Backup
            </a>
        <?php endif; ?>

        <div style="margin-top: auto; padding-top: 20px;">
            <a href="../funciones/logout.php" class="sidebar-link" style="color: #ef4444;">
                <i class="fas fa-sign-out-alt"></i> Salir del Sistema
            </a>
        </div>
    </nav>
</aside>
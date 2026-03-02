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
?>

<div class="left-nav-container">
    <div class="logo-wrapper">
        <img src="../imagenes/logoPng.png" alt="CEFPPNAY Logo" class="nav-logo">
    </div>
    <div class="title-wrapper">
        <h1 class="page-title-left">Panel de Control</h1>
        <span class="page-subtitle-left">Gestión Operativa</span>
    </div>
</div>

<style>
/* Estilos integrados para la sección izquierda superior */
.left-nav-container {
    display: flex;
    align-items: center;
    gap: 16px;
}

.logo-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    padding: 8px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid #f1f5f9;
}

.nav-logo {
    height: 48px;
    width: auto;
    object-fit: contain;
}

.title-wrapper {
    display: flex;
    flex-direction: column;
    justify-content: center;
    border-left: 2px solid #e2e8f0;
    padding-left: 16px;
}

.page-title-left {
    font-family: 'Inter', system-ui, sans-serif;
    font-size: 20px;
    font-weight: 800;
    color: #1e293b;
    margin: 0;
    line-height: 1.1;
    letter-spacing: -0.5px;
}

.page-subtitle-left {
    font-family: 'Inter', system-ui, sans-serif;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 2px;
}

@media (max-width: 768px) {
    .nav-logo { height: 36px; }
    .page-title-left { font-size: 16px; }
    .page-subtitle-left { display: none; }
}
</style>
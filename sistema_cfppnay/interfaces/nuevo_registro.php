<?php
// ESTE ARCHIVO ES DONDE LOS MÉDICOS VAN A METER LOS DATOS TRABAJADOS
// EN CAMPO O RELACIONADOS CON LA ACTIVIDAD
session_start();
include("../funciones/conexion.php");

// Verifica permisos
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit;
}

$nombre = $_SESSION['nombre_usuario'] ?? 'Usuario';
$rol = $_SESSION['rol_usuario'] ?? 'Invitado';

// Obtener lista de actividades activas
$sql_actividades = "SELECT id_actividad, nombre_actividad FROM actividades WHERE activo_actividad = 1";
$res_actividades = $conn->query($sql_actividades);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario Médico - CEFPPNAY</title>
    
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
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-dark);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
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

        .panel-sidebar { width: 260px; background: var(--surface-white); border-right: 1px solid var(--border-light); display: flex; flex-direction: column; z-index: 90; flex-shrink: 0;}
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
        .split-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: start; max-width: 1400px; margin: 0 auto; }
        .left-column { min-width: 0; }
        .right-column { min-width: 0; }

        /* ESTILOS DEL FORMULARIO (Columna Izquierda) */
        .form-card {
            background: var(--surface-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-md);
            border: 1px solid rgba(226, 232, 240, 0.8); padding: 3rem; width: 100%; box-sizing: border-box;
            animation: fadeSlideUp 0.4s ease-out;
        }

        .form-header {
            display: flex; gap: 1.5rem; align-items: flex-start; margin-bottom: 2.5rem;
            border-bottom: 1px solid var(--border-light); padding-bottom: 1.5rem;
        }

        .header-icon-box {
            width: 56px; height: 56px; background: linear-gradient(135deg, var(--surface-white), #f8fafc);
            color: var(--primary-color); border: 1px solid var(--border-light);
            border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; flex-shrink: 0; box-shadow: var(--shadow-sm);
        }

        .header-titles h1 { font-size: 1.8rem; font-weight: 800; color: var(--text-dark); margin: 0 0 0.25rem 0; letter-spacing: -0.5px; }
        .header-titles p { color: var(--text-muted); font-size: 0.95rem; margin: 0; font-weight: 500; }

        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
        .form-group { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1.5rem; }
        
        .form-label { font-size: 0.85rem; font-weight: 700; color: var(--text-regular); letter-spacing: 0.3px; display: flex; align-items: center; gap: 6px;}
        .form-label i { color: #94a3b8; }
        .required { color: var(--danger-color); }

        .form-control {
            width: 100%; padding: 0.85rem 1.2rem; border: 1px solid var(--border-focus); border-radius: 8px;
            font-family: 'Inter', sans-serif; font-size: 0.95rem; color: var(--text-dark); background: #f8fafc;
            transition: all 0.2s; box-sizing: border-box; outline: none;
        }
        .form-control:hover { border-color: #94a3b8; }
        .form-control:focus { border-color: var(--primary-color); background: #ffffff; box-shadow: 0 0 0 4px var(--primary-light); }
        textarea.form-control { min-height: 100px; resize: vertical; }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 12px center; padding-right: 40px;
        }

        .divider { height: 1px; background: var(--border-light); margin: 2rem 0; }
        .section-title { font-size: 1.2rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--text-dark); display: flex; align-items: center; gap: 10px; }
        .section-title i { color: var(--primary-color); }

        /* UPLOAD PDF PREMIUM */
        .file-upload-box {
            border: 2px dashed #cbd5e1; border-radius: var(--radius-lg);
            background: #f8fafc; padding: 2.5rem; text-align: center;
            cursor: pointer; position: relative; transition: all 0.3s ease;
        }
        .file-upload-box:hover { background: #f1f5f9; border-color: var(--primary-color); transform: translateY(-2px); box-shadow: var(--shadow-sm); }
        .file-upload-box input[type="file"] { position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer; }
        
        .upload-icon { font-size: 2.5rem; color: #94a3b8; margin-bottom: 0.8rem; transition: color 0.3s; }
        .file-upload-box:hover .upload-icon { color: var(--primary-color); transform: scale(1.1); }
        .upload-text { font-size: 1rem; color: var(--text-dark); font-weight: 700; margin: 0; }
        .upload-hint { font-size: 0.85rem; color: var(--text-muted); display: block; margin-top: 6px; font-weight: 500; }

        .preview-box { 
            margin-top: 1.5rem; border: 1px solid var(--border-light); border-radius: var(--radius-md); 
            padding: 1.5rem; background: #fff; display: none; box-shadow: var(--shadow-md); 
            animation: fadeSlideUp 0.3s ease-out;
        }

        .checkbox-wrapper { display: flex; align-items: center; gap: 12px; padding: 1rem 1.5rem; background: #f8fafc; border: 1px solid var(--border-light); border-radius: 8px; color: var(--text-regular); font-size: 0.95rem; font-weight: 600; transition: all 0.2s; margin-top: 1.5rem;}
        .checkbox-wrapper:hover { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .checkbox-wrapper input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--primary-color); cursor: pointer; }

        /* ESTADOS DE CARGA/VACÍOS */
        .loading-state, .empty-state { text-align: center; padding: 3rem; color: var(--text-muted); background: #f8fafc; border-radius: var(--radius-md); border: 2px dashed var(--border-focus); transition: all 0.3s; }
        .empty-state h4 { margin: 0 0 8px 0; color:var(--text-dark); font-size:1.1rem; font-weight:800; }

        /* BOTONES PREMIUM */
        .form-actions { margin-top: 3.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-light); display: flex; justify-content: flex-end; gap: 1rem; }
        
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 0.85rem 1.8rem; border-radius: 8px; font-weight: 700; font-size: 0.95rem; cursor: pointer;
            border: none; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); letter-spacing: 0.3px; height: 44px;
        }
        .btn-secondary { background: #f1f5f9; color: var(--text-regular); border: 1px solid var(--border-focus); }
        .btn-secondary:hover { background: #e2e8f0; color: var(--text-dark); transform: translateY(-2px); }
        
        .btn-primary { background: var(--text-dark); color: white; box-shadow: 0 4px 6px rgba(15, 23, 42, 0.1); }
        .btn-primary:hover { background: #000; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(15, 23, 42, 0.15); }
        .btn-primary:active { transform: translateY(0); }

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

        @media (max-width: 1024px) {
            .split-layout { grid-template-columns: 1fr; }
            .instructions-card { position: relative; top: 0; margin-top: 20px; }
        }
        @media (max-width: 768px) {
            .form-grid-2 { grid-template-columns: 1fr; }
            .form-card { padding: 1.5rem; }
            .panel-sidebar { display: none; }
            .header-right .user-info-text, .header-right .fa-chevron-down { display: none; }
        }
    </style>

    <script>
    function cargarCampos() {
        const actividadId = document.getElementById("actividad").value;
        const contenedor = document.getElementById("camposActividad");
        
        if (!actividadId) {
            contenedor.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-arrow-up" style="font-size: 2.5rem; margin-bottom:15px; color: #cbd5e1; display:block;"></i>
                    <h4>Selecciona una actividad</h4>
                    <p style="margin:0; font-size:0.95rem;">Elige una opción arriba para desplegar los campos técnicos.</p>
                </div>`;
            return;
        }

        contenedor.innerHTML = `
            <div class="loading-state">
                <i class="fas fa-circle-notch fa-spin" style="font-size: 2.5rem; color: var(--primary-color); margin-bottom:15px; display:block;"></i>
                <h4>Cargando parámetros...</h4>
            </div>`;

        fetch("../funciones/obtener_campos.php?id_actividad=" + actividadId)
            .then(res => res.text())
            .then(html => {
                contenedor.innerHTML = html;
                setTimeout(() => {
                    const inputs = contenedor.querySelectorAll('input, select, textarea');
                    inputs.forEach(el => {
                        el.classList.add('form-control');
                        if(el.tagName.toLowerCase() === 'select') {
                            el.style.appearance = 'none';
                            el.style.backgroundImage = "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E\")";
                            el.style.backgroundRepeat = 'no-repeat';
                            el.style.backgroundPosition = 'right 12px center';
                            el.style.paddingRight = '40px';
                        }
                    });
                }, 50);
            })
            .catch(err => {
                contenedor.innerHTML = '<div class="empty-state" style="color:var(--danger-color); border-color:var(--danger-color);"><i class="fas fa-exclamation-triangle" style="font-size: 2.5rem; margin-bottom:15px; display:block;"></i><h4>Error de carga</h4><p>Hubo un problema obteniendo los campos. Intente de nuevo.</p></div>';
            });
    }

    function mostrarPreview(input) {
        const preview = document.getElementById('preview_pdf');
        const iframe = document.getElementById('iframe_pdf');
        const label = document.getElementById('file-label');
        const icon = document.querySelector('.upload-icon');
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (file.type === "application/pdf") {
                iframe.src = URL.createObjectURL(file);
                preview.style.display = "block";
                label.textContent = file.name;
                label.style.color = "var(--primary-color)";
                icon.className = "fas fa-check-circle upload-icon";
                icon.style.color = "var(--primary-color)";
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Formato incorrecto',
                    text: 'Por favor sube únicamente archivos PDF.',
                    confirmButtonColor: '#1e293b'
                });
                input.value = "";
                preview.style.display = "none";
                label.textContent = "Haz clic para buscar o arrastra tu archivo aquí";
                icon.className = "fas fa-file-pdf upload-icon";
                icon.style.color = "#94a3b8";
            }
        }
    }
    </script>
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
            <a href="nuevo_registro.php" class="sidebar-link active">
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
        
        <div class="split-layout">
            
            <div class="left-column">
                <form action="../funciones/guardar_registro.php" method="POST" enctype="multipart/form-data">
                    <div class="form-card">
                        <div class="form-header">
                            <div class="header-icon-box"><i class="fas fa-stethoscope"></i></div>
                            <div class="header-titles">
                                <h1>Registro Médico de Campo</h1>
                                <p>Complete y valide la información de las actividades realizadas.</p>
                            </div>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-layer-group"></i> Tipo de Actividad <span class="required">*</span></label>
                                <select class="form-control" name="id_actividad" id="actividad" onchange="cargarCampos()" required>
                                    <option value="" disabled selected>-- Seleccione una opción --</option>
                                    <?php while ($row = $res_actividades->fetch_assoc()): ?>
                                    <option value="<?php echo $row['id_actividad']; ?>">
                                        <?php echo htmlspecialchars($row['nombre_actividad']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label"><i class="far fa-calendar-check"></i> Fecha de Realización <span class="required">*</span></label>
                                <input type="date" class="form-control" name="fecha_registro" required value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-map-marked-alt"></i> Zona Asignada <span class="required">*</span></label>
                                <select class="form-control" name="id_zona" required>
                                    <option value="" disabled selected>Seleccione la zona</option>
                                    <?php
                                    $res_zonas = $conn->query("SELECT id_zona, nombre_zona FROM zonas");
                                    while ($z = $res_zonas->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo $z['id_zona']; ?>"><?php echo htmlspecialchars($z['nombre_zona']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-map-pin"></i> Municipio <span class="required">*</span></label>
                                <select class="form-control" name="id_municipio" required>
                                    <option value="" disabled selected>Seleccione el municipio</option>
                                    <?php
                                    $res_mun = $conn->query("SELECT id_municipio, nombre FROM municipios");
                                    while ($m = $res_mun->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo $m['id_municipio']; ?>"><?php echo htmlspecialchars($m['nombre']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-comment-medical"></i> Observaciones Adicionales</label>
                            <textarea class="form-control" name="observaciones_registro" placeholder="Detalle cualquier nota clínica o técnica relevante del trabajo realizado..."></textarea>
                        </div>

                        <div class="divider"></div>

                        <h3 class="section-title"><i class="fas fa-list-ul"></i> Parámetros de la Actividad</h3>
                        <div id="camposActividad">
                            <div class="empty-state">
                                <i class="fas fa-arrow-up" style="font-size: 2.5rem; margin-bottom:15px; color: #cbd5e1; display:block;"></i>
                                <h4>Selecciona una actividad</h4>
                                <p style="margin:0; font-size:0.95rem;">Elige una opción arriba para desplegar los campos técnicos.</p>
                            </div>
                        </div>

                        <div class="divider"></div>

                        <h3 class="section-title"><i class="fas fa-file-upload"></i> Evidencia Documental</h3>
                        <div class="file-upload-box" onclick="document.getElementById('archivo_pdf').click()">
                            <i class="fas fa-file-pdf upload-icon"></i>
                            <p class="upload-text" id="file-label">Haz clic para buscar o arrastra tu archivo aquí</p>
                            <span class="upload-hint">Únicamente formato PDF (Máx. 5MB)</span>
                            <input type="file" name="archivo_pdf" id="archivo_pdf" accept="application/pdf" required onchange="mostrarPreview(this)">
                        </div>

                        <div class="preview-box" id="preview_pdf">
                            <p style="margin-top:0; margin-bottom:15px; font-size:13px; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing: 0.5px;">Vista Previa del Documento</p>
                            <iframe id="iframe_pdf" style="width:100%; height:350px; border:none; border-radius:10px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); background:#f8fafc;"></iframe>
                            
                            <div class="checkbox-wrapper">
                                <input type="checkbox" id="confirmar_datos" required>
                                <label for="confirmar_datos" style="cursor: pointer; width: 100%;">Declaro bajo protesta de decir verdad que la información y el documento adjunto son verídicos.</label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='panel_control.php'">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Registro
                            </button>
                        </div>
                    </div>
                </form>
            </div> <div class="right-column">
                <div class="instructions-card">
                    <h3><i class="fas fa-book-medical" style="color: #0ea5e9;"></i> Guía de Captura</h3>
                    
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>Selección de Actividad</h4>
                            <p>Elija la actividad que realizó en campo. Al hacerlo, el sistema desplegará automáticamente los parámetros técnicos que debe llenar.</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Datos de Ubicación</h4>
                            <p>Especifique la Zona y el Municipio exacto donde se llevó a cabo el trabajo operativo para un correcto mapeo.</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Evidencia Documental</h4>
                            <p>Es obligatorio adjuntar el formato de campo escaneado. Solo se admiten archivos <b>PDF legibles</b>.</p>
                        </div>
                    </div>

                    <div class="info-alert">
                        <i class="fas fa-shield-alt"></i>
                        <p><b>Validación Oficial:</b> Al marcar la casilla de confirmación final, usted valida legalmente que los datos ingresados son auténticos.</p>
                    </div>
                </div>
            </div> </div> </main>
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

    // Alerta de éxito elegante
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('ok')) {
        Swal.fire({
            title: '¡Registro Exitoso!',
            text: 'Los datos médicos se han guardado correctamente en el sistema.',
            icon: 'success',
            confirmButtonColor: '#1e293b',
            confirmButtonText: 'Aceptar',
            background: '#ffffff',
            customClass: {
                popup: 'form-card',
                title: 'header-titles',
                confirmButton: 'btn btn-primary'
            }
        });
    }
</script>

</body>
</html>
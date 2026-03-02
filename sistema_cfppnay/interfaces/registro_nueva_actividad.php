<?php
session_start();

// ESTE ARCHIVO ES PARA CREAR ACTIVIDADES PARA LOS PROGRAMAS
// LO USARÁ ÚNICAMENTE EL COORDINADOR Y EL ADMINISTRADOR

// Verifica si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: http://localhost/login.php');
    exit;
}
$rol = $_SESSION['rol'] ?? 'medico';
$nombre = $_SESSION['nombre_usuario'] ?? 'Usuario';

// Conexión a la BD
include("../funciones/conexion.php");

// Si envían el formulario de nueva actividad
$mensaje = '';
$tipo_mensaje = '';
$mostrar_alerta = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['nombre_actividad'])) {
    $nombre_actividad = $_POST['nombre_actividad'];
    $descripcion = $_POST['descripcion_actividad'];
    $id_area = intval($_POST['id_area']);

    // Insertar actividad
    $stmt = $conn->prepare("INSERT INTO actividades (nombre_actividad, descripcion_actividad, id_area) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $nombre_actividad, $descripcion, $id_area);
    $stmt->execute();
    $id_actividad = $conn->insert_id;
    $stmt->close();

    // Insertar campos
    if (!empty($_POST['campos'])) {
        $stmt_campo = $conn->prepare("INSERT INTO campos_actividad (id_actividad, nombre_campo_actividad, tipo_campo_actividad, obligatorio_actividad, opciones_lista_actividad) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($_POST['campos'] as $campo) {
            $nombre_campo = $campo['nombre'];
            $tipo_campo = $campo['tipo'];
            $obligatorio = isset($campo['obligatorio']) ? 1 : 0;
            $opciones = ($tipo_campo === 'lista' && !empty($campo['opciones'])) ? $campo['opciones'] : null;

            $stmt_campo->bind_param("issis", $id_actividad, $nombre_campo, $tipo_campo, $obligatorio, $opciones);
            $stmt_campo->execute();
        }
        $stmt_campo->close();
    }
    $mensaje = "La actividad y sus campos se han configurado exitosamente.";
    $tipo_mensaje = 'success';
    $mostrar_alerta = true;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configurar Actividad - CEFPPNAY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
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
            --danger-hover: #dc2626;
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

        .panel-sidebar { width: 260px; background: var(--surface-white); border-right: 1px solid var(--border-light); display: flex; flex-direction: column; z-index: 90; }
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
           GRID DIVIDIDO EN DOS COLUMNAS (FORMULARIO E INSTRUCCIONES)
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
            color: var(--text-dark); border: 1px solid var(--border-light);
            border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; flex-shrink: 0; box-shadow: var(--shadow-sm);
        }

        .header-titles h1 { font-size: 1.8rem; font-weight: 800; color: var(--text-dark); margin: 0 0 0.25rem 0; letter-spacing: -0.5px; }
        .header-titles p { color: var(--text-muted); font-size: 0.95rem; margin: 0; font-weight: 500; }

        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
        .form-group { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1.5rem; }
        
        .form-label { font-size: 0.85rem; font-weight: 700; color: var(--text-regular); letter-spacing: 0.3px; }
        .required { color: var(--danger-color); }

        .form-control {
            width: 100%; padding: 0.85rem 1.2rem; border: 1px solid var(--border-focus); border-radius: 8px;
            font-family: 'Inter', sans-serif; font-size: 0.95rem; color: var(--text-dark); background: #f8fafc;
            transition: all 0.2s; box-sizing: border-box; outline: none;
        }
        .form-control:hover { border-color: #94a3b8; }
        .form-control:focus { border-color: var(--primary-color); background: #ffffff; box-shadow: 0 0 0 4px var(--primary-light); }
        
        textarea.form-control { min-height: 100px; resize: vertical; }

        /* SECCIÓN DE CAMPOS DINÁMICOS */
        .campos-section { margin-top: 30px; padding-top: 2.5rem; border-top: 2px dashed var(--border-light); }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .section-title { font-size: 1.2rem; font-weight: 800; color: var(--text-dark); margin: 0; display: flex; align-items: center; gap: 10px; }
        .section-title i { color: var(--primary-color); }

        /* BOTONES PREMIUM */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 0.85rem 1.5rem; border-radius: 8px; font-weight: 700; font-size: 0.95rem; cursor: pointer;
            border: none; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); letter-spacing: 0.3px; height: 44px;
        }
        .btn-outline { background: transparent; border: 2px solid var(--primary-color); color: var(--primary-color); }
        .btn-outline:hover { background: var(--primary-light); transform: translateY(-2px); box-shadow: 0 4px 6px rgba(47, 133, 90, 0.1); }
        
        .btn-secondary { background: #f1f5f9; color: var(--text-regular); border: 1px solid var(--border-focus); }
        .btn-secondary:hover { background: #e2e8f0; color: var(--text-dark); transform: translateY(-2px); }
        
        .btn-primary { background: var(--text-dark); color: white; box-shadow: 0 4px 6px rgba(15, 23, 42, 0.1); }
        .btn-primary:hover { background: #000; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(15, 23, 42, 0.15); }
        .btn-primary:active { transform: translateY(0); }

        .btn-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 15px; border: none; cursor: pointer; transition: all 0.2s; }
        .btn-danger-light { background: var(--danger-light); color: var(--danger-color); border: 1px solid var(--danger-light); }
        .btn-danger-light:hover { background: var(--danger-color); color: white; transform: scale(1.05); box-shadow: 0 4px 6px rgba(239, 68, 68, 0.2); border-color: var(--danger-color); }

        .campo-card {
            background: #ffffff; border: 1px solid var(--border-light); border-radius: var(--radius-md);
            padding: 1.5rem; margin-bottom: 1.5rem; position: relative; transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }
        .campo-card:hover { border-color: var(--border-focus); box-shadow: var(--shadow-md); transform: translateY(-2px); }

        .empty-campos { text-align: center; padding: 3rem; color: var(--text-muted); background: #f8fafc; border-radius: var(--radius-md); border: 2px dashed var(--border-focus); transition: all 0.3s; }
        .empty-campos:hover { border-color: var(--primary-color); background: var(--primary-light); }

        .checkbox-wrapper { display: flex; align-items: center; gap: 12px; padding: 1rem 1.5rem; background: #f8fafc; border: 1px solid var(--border-light); border-radius: 8px; color: var(--text-regular); font-size: 0.95rem; font-weight: 600; transition: all 0.2s; }
        .checkbox-wrapper:hover { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .checkbox-wrapper input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--primary-color); cursor: pointer; }

        @keyframes fadeInScale { from { opacity: 0; transform: scale(0.98) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
        .campo-card { animation: fadeInScale 0.3s ease-out; }

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
    let campoCounter = 0;
    
    function agregarCampo() {
        const contenedor = document.getElementById("campos-container");
        const emptyMessage = document.getElementById("empty-campos-message");
        if (emptyMessage) emptyMessage.style.display = "none";
        
        campoCounter++;
        const index = campoCounter;
        
        const campoCard = document.createElement("div");
        campoCard.classList.add("campo-card");
        campoCard.id = `campo-${index}`;
        
        // HTML inyectado usando las clases premium actualizadas
        campoCard.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border-light); padding-bottom:12px; margin-bottom:16px;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <span style="background:var(--primary-light); color:var(--primary-color); width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:12px; box-shadow: var(--shadow-sm);">#${index}</span>
                    <span style="font-weight:700; color:var(--text-dark); font-size:14px; text-transform:uppercase; letter-spacing:0.5px;">Configuración de Variable</span>
                </div>
                <button type="button" class="btn-icon btn-danger-light" onclick="eliminarCampo(${index})" title="Eliminar campo">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
            
            <div class="form-grid-2" style="margin-bottom:0;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Nombre del Campo <span class="required">*</span></label>
                    <input type="text" class="form-control" name="campos[${index}][nombre]" placeholder="Ej: Temperatura, Peso, Estatus..." required>
                </div>
                
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Tipo de Dato <span class="required">*</span></label>
                    <select class="form-control" name="campos[${index}][tipo]" onchange="toggleOpciones(this, ${index})" required style="appearance: none; background-image: url('data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'16\\' height=\\'16\\' viewBox=\\'0 0 24 24\\' fill=\\'none\\' stroke=\\'%2364748b\\' stroke-width=\\'2\\' stroke-linecap=\\'round\\' stroke-linejoin=\\'round\\'%3E%3Cpolyline points=\\'6 9 12 15 18 9\\'%3E%3C/polyline%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 12px center; padding-right: 40px;">
                        <option value="" disabled selected>Seleccionar tipo...</option>
                        <option value="texto">Texto Corto</option>
                        <option value="numero">Número</option>
                        <option value="fecha">Fecha</option>
                        <option value="lista">Lista Desplegable</option>
                        <option value="textarea">Texto Largo</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group" id="opciones-${index}" style="display:none; margin-top: 1.5rem; margin-bottom:0;">
                <label class="form-label">Opciones de la lista <span style="color:var(--text-muted); font-weight:500; font-size:12px; margin-left:4px;">(Separadas por comas)</span></label>
                <input type="text" class="form-control" name="campos[${index}][opciones]" placeholder="Opción 1, Opción 2, Opción 3">
            </div>
            
            <div class="checkbox-wrapper" style="margin-top: 1.5rem;">
                <input type="checkbox" name="campos[${index}][obligatorio]" value="1" id="req-${index}">
                <label for="req-${index}" style="cursor:pointer; width:100%;">Requerir que el médico llene este campo de forma obligatoria</label>
            </div>
        `;
        
        contenedor.appendChild(campoCard);
        setTimeout(() => campoCard.scrollIntoView({ behavior: 'smooth', block: 'center' }), 100);
    }
    
    function eliminarCampo(id) {
        const campoCard = document.getElementById(`campo-${id}`);
        if (campoCard) {
            campoCard.style.opacity = '0';
            campoCard.style.transform = 'scale(0.95)';
            setTimeout(() => {
                campoCard.remove();
                const contenedor = document.getElementById("campos-container");
                if (contenedor.children.length === 1) { // 1 is the empty-message div
                    document.getElementById("empty-campos-message").style.display = "block";
                }
            }, 250);
        }
    }
    
    function toggleOpciones(select, index) {
        const opcionesDiv = document.getElementById(`opciones-${index}`);
        opcionesDiv.style.display = (select.value === "lista") ? "block" : "none";
        
        const inputOpciones = opcionesDiv.querySelector('input');
        if (select.value === "lista") {
            inputOpciones.setAttribute('required', 'required');
        } else {
            inputOpciones.removeAttribute('required');
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
            <a href="registro_nueva_actividad.php" class="sidebar-link active">
                <i class="fas fa-layer-group"></i> <span>Crear Actividades</span>
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
                <div class="form-card">
                    <div class="form-header">
                        <div class="header-icon-box"><i class="fas fa-tools"></i></div>
                        <div class="header-titles">
                            <h1>Constructor de Actividades</h1>
                            <p>Diseñe la estructura de datos que los médicos llenarán en campo.</p>
                        </div>
                    </div>

                    <form method="POST">
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label">Nombre de la Actividad <span class="required">*</span></label>
                                <input type="text" class="form-control" name="nombre_actividad" placeholder="Ej: Muestreo de Sangre, Control Reproductivo..." required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Área Asignada <span class="required">*</span></label>
                                <select class="form-control" name="id_area" required style="appearance: none; background-image: url('data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'16\\' height=\\'16\\' viewBox=\\'0 0 24 24\\' fill=\\'none\\' stroke=\\'%2364748b\\' stroke-width=\\'2\\' stroke-linecap=\\'round\\' stroke-linejoin=\\'round\\'%3E%3Cpolyline points=\\'6 9 12 15 18 9\\'%3E%3C/polyline%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 12px center; padding-right: 40px;">
                                    <option value="" disabled selected>Selecciona un área</option>
                                    <?php 
                                    $result_areas = $conn->query("SELECT id_area, nombre_area FROM areas");
                                    if ($result_areas) {
                                        while ($row = $result_areas->fetch_assoc()) {
                                            echo '<option value="'.$row['id_area'].'">'.htmlspecialchars($row['nombre_area']).'</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Descripción de la Actividad</label>
                            <textarea class="form-control" name="descripcion_actividad" placeholder="Instrucciones o detalles de esta actividad operativa para ayudar al médico..." rows="3"></textarea>
                        </div>

                        <div class="campos-section">
                            <div class="section-header">
                                <h3 class="section-title"><i class="fas fa-stream"></i> Variables a Capturar</h3>
                                <button type="button" class="btn btn-outline" onclick="agregarCampo()">
                                    <i class="fas fa-plus"></i> Agregar Nuevo Campo
                                </button>
                            </div>

                            <div id="campos-container">
                                <div class="empty-campos" id="empty-campos-message">
                                    <i class="fas fa-cubes" style="font-size: 2.5rem; color:#cbd5e1; margin-bottom:15px; display:block; transition: all 0.3s;"></i>
                                    <h4 style="margin: 0 0 8px 0; color:var(--text-dark); font-size:1.1rem; font-weight:800;">Tu formulario está vacío</h4>
                                    <p style="margin:0; font-size:0.95rem;">Haz clic en "Agregar Nuevo Campo" para comenzar a armar la estructura.</p>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 3.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-light); display: flex; justify-content: flex-end; gap: 1rem;">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='panel_control.php'">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check-circle"></i> Guardar Actividad
                            </button>
                        </div>
                    </form>
                </div>
            </div> <div class="right-column">
                <div class="instructions-card">
                    <h3><i class="fas fa-lightbulb" style="color: #0ea5e9;"></i> Pasos para Configurar</h3>
                    
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>Nombre de Actividad</h4>
                            <p>Escribe un nombre claro y descriptivo para la actividad que los médicos realizarán en campo.</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Seleccionar un Área</h4>
                            <p>Elige a qué campaña o área pertenece esta actividad (ej. Tuberculosis, Brucelosis).</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Descripción</h4>
                            <p>Proporciona instrucciones detalladas o notas adicionales que ayudarán al médico a entender cómo llenar el reporte.</p>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h4>Agregar Nuevo Campo</h4>
                            <p>Utiliza el botón para añadir las variables o preguntas que el médico deberá contestar (Texto, Número, Fecha, Lista).</p>
                        </div>
                    </div>

                    <div class="info-alert">
                        <i class="fas fa-check-circle"></i>
                        <p>Al guardar, esta estructura estará inmediatamente disponible para todos los médicos en el sistema.</p>
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

    // Alerta de éxito elegante controlada por PHP
    <?php if ($mostrar_alerta): ?>
        Swal.fire({
            title: '¡Actividad Creada!',
            text: '<?php echo $mensaje; ?>',
            icon: 'success',
            confirmButtonColor: '#1e293b',
            confirmButtonText: 'Entendido',
            background: '#ffffff',
            customClass: {
                popup: 'form-card',
                title: 'header-titles',
                confirmButton: 'btn btn-primary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirigir al panel principal después de guardar
                window.location.href = 'panel_control.php';
            }
        });
    <?php endif; ?>
</script>

</body>
</html>
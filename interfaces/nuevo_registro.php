<?php
session_start();
include("../funciones/conexion.php");

// Verifica que el usuario sea administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol_usuario'] != 'Administrador') {
    die("Acceso no autorizado.");
}

$nombre = $_SESSION['nombre_usuario'] ?? 'Usuario';

// Obtener lista de actividades activas
$sql_actividades = "SELECT id_actividad, nombre_actividad FROM actividades WHERE activo_actividad = 1";
$res_actividades = $conn->query($sql_actividades);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Actividad - Sistema CEFPPENAY</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
    /* === VARIABLES DE DISEÑO (Look Minimalista) === */
    :root {
        --font-main: 'Inter', sans-serif;
        --bg-body: #F8FAFC;       
        --bg-card: #FFFFFF;      
        --text-primary: #0F172A; 
        --text-secondary: #64748B;
        --border-color: #E2E8F0;
        --primary-color: #2563EB; 
        --radius: 12px;
        --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }

    /* === RESET Y FUERZA BRUTA PARA QUITAR VACAS Y ROJO === */
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: var(--font-main);
        /* El !important asegura que se quite la imagen de las vacas sí o sí */
        background-color: var(--bg-body) !important;
        background-image: none !important; 
        color: var(--text-primary);
        height: 100vh;
        overflow: hidden; 
    }

    /* ===== LAYOUT PRINCIPAL ALINEADO A LA IZQUIERDA ===== */
.app-container {
    display: grid;
    grid-template-columns: 2px 1fr; /* CAMBIÉ: solo 2px para el borde */
    grid-template-rows: 70px 1fr;
    grid-template-areas: 
        "sidebar header"
        "sidebar main";
    min-height: 100vh;
}

/* ===== SIDEBAR ===== */
.sidebar {
    grid-area: sidebar;
    background: transparent; /* CAMBIÉ: transparente */
    position: relative;
    width: 2px; /* CAMBIÉ: solo 2px */
    min-width: 2px;
    border-right: 2px solid var(--gray-300); /* CAMBIÉ: borde de 2px */
    box-shadow: none; /* CAMBIÉ: sin sombra */
}

    /* Contenido Principal */
    .main-content {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
        background: var(--bg-body) !important;
    }

    /* Header Superior */
    .top-header {
        height: 70px;
        background: var(--bg-body); 
        display: flex;
        justify-content: flex-end;
        align-items: center;
        padding: 0 2.5rem;
        flex-shrink: 0;
    }

    .user-badge {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--bg-card);
        padding: 6px 12px;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    .user-badge img { width: 28px; height: 28px; border-radius: 50%; }

    /* Área Scrollable del Formulario */
    .scroll-area {
        flex: 1;
        overflow-y: auto;
        padding: 0 2.5rem 2.5rem 2.5rem;
    }

    /* === TARJETA DEL FORMULARIO (Clean UI) === */
    .form-card {
        background: var(--bg-card);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 1px solid var(--border-color);
        max-width: 900px;
        margin: 0 auto;
        padding: 3rem;
    }

    /* Encabezado del Formulario */
    .form-header {
        display: flex;
        gap: 1.5rem;
        align-items: flex-start;
        margin-bottom: 2.5rem;
    }

    .header-icon-box {
        width: 52px;
        height: 52px;
        background-color: #DCFCE7;
        color: #166534;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .header-titles h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }
    .header-titles p {
        color: var(--text-secondary);
        font-size: 0.95rem;
    }

    /* === GRID DEL FORMULARIO === */
    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .required { color: #EF4444; }

    /* Inputs Modernos */
    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-family: var(--font-main);
        font-size: 0.95rem;
        color: var(--text-primary);
        background: #fff;
        transition: border 0.2s, box-shadow 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    select.form-control {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748B'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 16px;
        appearance: none;
        padding-right: 2.5rem;
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    /* === SECCIONES DINÁMICAS === */
    .divider {
        height: 1px;
        background: var(--border-color);
        margin: 2.5rem 0 1.5rem 0;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Area de Carga de Archivos */
    .file-upload-box {
        border: 2px dashed var(--border-color);
        border-radius: 12px;
        background: #F8FAFC;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        position: relative;
        transition: background 0.2s;
    }
    .file-upload-box:hover { background: #F1F5F9; border-color: #CBD5E1; }
    
    .file-upload-box input[type="file"] {
        position: absolute;
        width: 100%; height: 100%; top: 0; left: 0;
        opacity: 0; cursor: pointer;
    }

    .upload-icon { font-size: 2rem; color: #94A3B8; margin-bottom: 0.5rem; }
    .upload-text { font-size: 0.9rem; color: var(--text-secondary); font-weight: 500; }
    .upload-hint { font-size: 0.8rem; color: var(--text-placeholder); display: block; margin-top: 4px; }

    /* === BOTONES === */
    .form-actions {
        margin-top: 2.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        border: none;
        transition: all 0.2s;
    }

    .btn-secondary {
        background: #fff;
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
    }
    .btn-secondary:hover { background: #F8FAFC; color: var(--text-primary); }

    .btn-primary {
        background: var(--primary-color);
        color: white;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    .btn-primary:hover { background: #1d4ed8; }

    /* === PREVIEW PDF === */
    .preview-box {
        margin-top: 1.5rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1rem;
        background: #fff;
        display: none; 
    }
    .checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 1rem;
        padding: 1rem;
        background: #F0FDF4; 
        border-radius: 6px;
        color: #166534;
        font-size: 0.9rem;
    }

    /* === RESPONSIVE === */
    @media (max-width: 900px) {
        .app-container { grid-template-columns: 1fr; }
        .sidebar-wrapper { display: none; } 
        .form-grid-2 { grid-template-columns: 1fr; }
        .form-card { padding: 1.5rem; }
        .top-header { padding: 0 1.5rem; }
        .scroll-area { padding: 0 1.5rem 2rem 1.5rem; }
    }
    
    .loading-state, .empty-state {
        text-align: center; padding: 2rem; color: var(--text-secondary);
        background: #F8FAFC; border-radius: 8px; border: 1px dashed var(--border-color);
    }
    </style>

    <script>
    function cargarCampos() {
        const actividadId = document.getElementById("actividad").value;
        const contenedor = document.getElementById("camposActividad");
        
        if (!actividadId) {
            contenedor.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-arrow-up" style="margin-bottom:8px;"></i>
                    <p>Selecciona una actividad arriba para ver sus campos.</p>
                </div>`;
            return;
        }

        contenedor.innerHTML = `
            <div class="loading-state">
                <i class="fas fa-circle-notch fa-spin"></i> Cargando campos requeridos...
            </div>`;

        fetch("../funciones/obtener_campos.php?id_actividad=" + actividadId)
            .then(res => res.text())
            .then(html => {
                contenedor.innerHTML = html;
                setTimeout(() => {
                    const inputs = contenedor.querySelectorAll('input, select, textarea');
                    inputs.forEach(el => el.classList.add('form-control'));
                }, 50);
            })
            .catch(err => {
                contenedor.innerHTML = '<p style="color:red">Error al cargar campos.</p>';
            });
    }

    function mostrarPreview(input) {
        const preview = document.getElementById('preview_pdf');
        const iframe = document.getElementById('iframe_pdf');
        const label = document.getElementById('file-label');
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (file.type === "application/pdf") {
                iframe.src = URL.createObjectURL(file);
                preview.style.display = "block";
                label.textContent = "Archivo seleccionado: " + file.name;
                label.style.color = "var(--primary-color)";
            } else {
                alert("Por favor sube solo archivos PDF.");
                input.value = "";
            }
        }
    }

    function validarFormulario() {
        return true; 
    }
    </script>
</head>
<body>

<div class="app-container">
    
    <div class="sidebar-wrapper">
        <?php include_once("sidebar.php"); ?>
    </div>

    <main class="main-content">
        
        <header class="top-header">
            <div class="user-badge">
                <img src="../imagenes/user-default.png" alt="User">
                <span><?php echo htmlspecialchars($nombre); ?></span>
                <i class="fas fa-chevron-down" style="font-size: 10px; margin-left: 4px;"></i>
            </div>
        </header>

        <div class="scroll-area">
            
            <form action="../funciones/guardar_registro.php" method="POST" enctype="multipart/form-data" onsubmit="return validarFormulario()">
                
                <div class="form-card">
                    
                    <div class="form-header">
                        <div class="header-icon-box">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="header-titles">
                            <h1>Registrar Nueva Actividad</h1>
                            <p>Complete todos los campos para crear una nueva actividad en el sistema.</p>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="far fa-check-square"></i> Nombre de la Actividad <span class="required">*</span>
                            </label>
                            <select class="form-control" name="id_actividad" id="actividad" onchange="cargarCampos()" required>
                                <option value="" disabled selected>-- Selecciona una actividad --</option>
                                <?php 
                                $res_actividades->data_seek(0); 
                                while ($row = $res_actividades->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $row['id_actividad']; ?>">
                                    <?php echo htmlspecialchars($row['nombre_actividad']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="far fa-calendar-alt"></i> Fecha <span class="required">*</span>
                            </label>
                            <input type="date" class="form-control" name="fecha_registro" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Zona <span class="required">*</span>
                            </label>
                            <select class="form-control" name="id_zona" required>
                                <option value="" disabled selected>Selecciona una zona</option>
                                <?php
                                $res_zonas = $conn->query("SELECT id_zona, nombre_zona FROM zonas");
                                while ($z = $res_zonas->fetch_assoc()):
                                ?>
                                <option value="<?php echo $z['id_zona']; ?>"><?php echo htmlspecialchars($z['nombre_zona']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-city"></i> Municipio <span class="required">*</span>
                            </label>
                            <select class="form-control" name="id_municipio" required>
                                <option value="" disabled selected>Selecciona un municipio</option>
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
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" placeholder="Describa brevemente el propósito y características de esta actividad..."></textarea>
                    </div>

                    <div class="divider"></div>

                    <h3 class="section-title"><i class="fas fa-sliders-h"></i> Campos de la Actividad</h3>
                    <div id="camposActividad">
                        <div class="empty-state">
                            <p style="font-size:0.9rem; color: #94A3B8;">Seleccione un "Tipo de Actividad" arriba para desplegar los campos.</p>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <h3 class="section-title"><i class="fas fa-cloud-upload-alt"></i> Comprobante PDF</h3>
                    
                    <div class="file-upload-box" onclick="document.getElementById('archivo_pdf').click()">
                        <i class="fas fa-file-pdf upload-icon"></i>
                        <p class="upload-text" id="file-label">Haz clic para subir el comprobante</p>
                        <span class="upload-hint">Solo archivos PDF (Máx. 10MB)</span>
                        <input type="file" name="archivo_pdf" id="archivo_pdf" accept="application/pdf" required onchange="mostrarPreview(this)">
                    </div>

                    <div class="preview-box" id="preview_pdf">
                        <iframe id="iframe_pdf" style="width:100%; height:300px; border:none; border-radius:4px;"></iframe>
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="confirmar_datos" required>
                            <label for="confirmar_datos">Confirmo que los datos ingresados coinciden con la información del documento.</label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="history.back()">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Registro</button>
                    </div>

                </div>
            </form>
        </div>
    </main>
</div>

</body>
</html>
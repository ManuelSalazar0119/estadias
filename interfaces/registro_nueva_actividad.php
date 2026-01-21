<?php
session_start();
// Verifica si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /login.php');
    exit;
}
$rol = $_SESSION['rol'] ?? 'medico';
$nombre = $_SESSION['nombre_usuario'] ?? 'Usuario';

// Conexión a la BD
$conexion = new mysqli("localhost", "root", "", "cefppenay");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Si envían el formulario de nueva actividad
$mensaje = '';
$tipo_mensaje = '';
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['nombre_actividad'])) {
    $nombre_actividad = $_POST['nombre_actividad'];
    $descripcion = $_POST['descripcion_actividad'];
    $id_area = intval($_POST['id_area']);

    // Insertar actividad
    $conexion->query("INSERT INTO actividades (nombre_actividad, descripcion_actividad, id_area) 
                      VALUES ('$nombre_actividad', '$descripcion',$id_area)");
    $id_actividad = $conexion->insert_id;

    // Insertar campos
    if (!empty($_POST['campos'])) {
        foreach ($_POST['campos'] as $campo) {
            $nombre_campo = $campo['nombre'];
            $tipo_campo = $campo['tipo'];
            $obligatorio = isset($campo['obligatorio']) ? 1 : 0;
            $opciones = $tipo_campo === 'lista' ? $campo['opciones'] : null;

            $conexion->query("INSERT INTO campos_actividad 
                              (id_actividad, nombre_campo_actividad, tipo_campo_actividad, obligatorio_actividad, opciones_lista_actividad)
                              VALUES ($id_actividad, '$nombre_campo', '$tipo_campo', $obligatorio, " . ($opciones ? "'$opciones'" : "NULL") . ")");
        }
    }
    $mensaje = "Actividad registrada correctamente ✅";
    $tipo_mensaje = 'success';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Nueva Actividad</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/reporte.css?v=<?php echo(rand()); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    /* Variables CSS para consistencia */
    :root {
        --primary-color: #388e3c;
        --primary-dark: #2e7031;
        --primary-light: #e8f5e9;
        --secondary-color: #5c6bc0;
        --light-bg: #f8fafc;
        --border-color: #e2e8f0;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
        --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
        --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
        --radius-sm: 6px;
        --radius-md: 10px;
        --radius-lg: 16px;
    }
    
    /* Reset y estilos base */
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: var(--light-bg);
        color: var(--text-primary);
    }
    
    /* Contenedor principal del formulario */
    .form-container {
        padding: 24px;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        box-sizing: border-box;
    }
    
    /* Tarjeta del formulario */
    .form-card {
        background: #ffffff;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
        padding: 32px;
        max-width: 800px;
        width: 100%;
        margin: 0 auto;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        border: 1px solid var(--border-color);
    }
    
    /* Encabezado del formulario */
    .form-header {
        display: flex;
        align-items: center;
        margin-bottom: 28px;
        padding-bottom: 20px;
        border-bottom: 2px solid var(--primary-light);
    }
    
    .form-header i {
        background-color: var(--primary-light);
        color: var(--primary-color);
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin-right: 16px;
    }
    
    .form-header h1 {
        font-size: 1.8rem;
        margin: 0;
        color: var(--text-primary);
        font-weight: 700;
    }
    
    .form-header p {
        margin: 6px 0 0 0;
        color: var(--text-secondary);
        font-size: 0.95rem;
    }
    
    /* Mensajes de alerta */
    .alert {
        padding: 14px 18px;
        border-radius: var(--radius-md);
        margin-bottom: 24px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .alert-success {
        background-color: #d1fae5;
        color: #065f46;
        border-left: 4px solid var(--success-color);
    }
    
    /* Grupo de formulario */
    .form-group {
        margin-bottom: 24px;
        position: relative;
    }
    
    .form-row {
        display: flex;
        gap: 20px;
        margin-bottom: 24px;
    }
    
    .form-row .form-group {
        flex: 1;
        margin-bottom: 0;
    }
    
    /* Etiquetas */
    .form-label {
        display: block;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
        font-size: 0.95rem;
    }
    
    .form-label .required {
        color: var(--danger-color);
        margin-left: 3px;
    }
    
    /* Inputs y selects */
    .form-control {
        width: 100%;
        padding: 12px 14px;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 1rem;
        background: #ffffff;
        transition: all 0.2s ease;
        box-sizing: border-box;
        font-family: inherit;
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(56, 142, 60, 0.1);
    }
    
    textarea.form-control {
        min-height: 100px;
        resize: vertical;
        line-height: 1.5;
    }
    
    /* Estilos para los campos dinámicos */
    .campos-section {
        margin-top: 30px;
        padding-top: 24px;
        border-top: 2px solid var(--border-color);
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .section-title {
        font-size: 1.3rem;
        color: var(--text-primary);
        font-weight: 600;
        margin: 0;
    }
    
    .section-title i {
        color: var(--primary-color);
        margin-right: 10px;
    }
    
    /* Tarjeta de campo individual */
    .campo-card {
        background: var(--light-bg);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 20px;
        margin-bottom: 16px;
        position: relative;
        transition: all 0.2s ease;
    }
    
    .campo-card:hover {
        border-color: var(--primary-color);
        box-shadow: var(--shadow-sm);
    }
    
    .campo-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px dashed var(--border-color);
    }
    
    .campo-card-title {
        font-weight: 600;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .campo-card-title .campo-index {
        background: var(--primary-color);
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
    }
    
    .remove-campo {
        background: none;
        border: none;
        color: var(--danger-color);
        cursor: pointer;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s;
    }
    
    .remove-campo:hover {
        background-color: rgba(239, 68, 68, 0.1);
    }
    
    /* Checkbox personalizado */
    .checkbox-container {
        display: flex;
        align-items: center;
        cursor: pointer;
        margin-top: 8px;
        user-select: none;
    }
    
    .checkbox-container input {
        display: none;
    }
    
    .checkmark {
        width: 20px;
        height: 20px;
        border: 2px solid var(--border-color);
        border-radius: 4px;
        margin-right: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    
    .checkbox-container input:checked + .checkmark {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .checkbox-container input:checked + .checkmark::after {
        content: "✓";
        color: white;
        font-size: 14px;
        font-weight: bold;
    }
    
    /* Botones */
    .btn {
        padding: 12px 24px;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
        font-family: inherit;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        color: white;
    }
    
    .btn-primary:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    
    .btn-secondary {
        background-color: white;
        color: var(--primary-color);
        border: 2px solid var(--primary-color);
    }
    
    .btn-secondary:hover {
        background-color: var(--primary-light);
    }
    
    .btn-success {
        background-color: var(--success-color);
        color: white;
        padding: 14px 32px;
        font-size: 1.1rem;
        margin-top: 24px;
        align-self: flex-start;
    }
    
    .btn-success:hover {
        background-color: #0da271;
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    
    .btn-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        padding: 0;
        justify-content: center;
    }
    
    /* Contenedor de botones */
    .form-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 2px solid var(--border-color);
    }
    
    /* Mensaje cuando no hay campos */
    .empty-campos {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-secondary);
        background: var(--light-bg);
        border-radius: var(--radius-md);
        border: 2px dashed var(--border-color);
        margin-bottom: 20px;
    }
    
    .empty-campos i {
        font-size: 2.5rem;
        color: var(--border-color);
        margin-bottom: 15px;
        display: block;
    }
    
    /* Estilos responsivos */
    @media (max-width: 992px) {
        .form-card {
            padding: 24px;
            max-width: 90%;
        }
        
        .form-row {
            flex-direction: column;
            gap: 0;
        }
        
        .form-row .form-group {
            margin-bottom: 24px;
        }
    }
    
    @media (max-width: 768px) {
        .form-container {
            padding: 16px;
        }
        
        .form-card {
            padding: 20px;
            max-width: 100%;
        }
        
        .form-header {
            flex-direction: column;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .form-header i {
            margin-right: 0;
            margin-bottom: 16px;
        }
        
        .form-header h1 {
            font-size: 1.5rem;
        }
        
        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .form-actions {
            flex-direction: column;
            gap: 15px;
        }
        
        .btn-success {
            align-self: stretch;
            text-align: center;
        }
    }
    
    /* Animaciones */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .campo-card {
        animation: fadeIn 0.3s ease-out;
    }
    </style>
    <script>
    let campoCounter = 0;
    
    function agregarCampo() {
        const contenedor = document.getElementById("campos-container");
        const emptyMessage = document.getElementById("empty-campos-message");
        
        if (emptyMessage) {
            emptyMessage.style.display = "none";
        }
        
        campoCounter++;
        const index = campoCounter;
        
        const campoCard = document.createElement("div");
        campoCard.classList.add("campo-card");
        campoCard.id = `campo-${index}`;
        campoCard.innerHTML = `
            <div class="campo-card-header">
                <div class="campo-card-title">
                    <span class="campo-index">${index}</span>
                    Campo Personalizado
                </div>
                <button type="button" class="remove-campo" onclick="eliminarCampo(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nombre del Campo <span class="required">*</span></label>
                    <input type="text" class="form-control" name="campos[${index}][nombre]" 
                           placeholder="Ej: Nombre del Paciente, Cantidad, Observaciones..." required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tipo de Campo <span class="required">*</span></label>
                    <select class="form-control" name="campos[${index}][tipo]" 
                            onchange="toggleOpciones(this, ${index})" required>
                        <option value="">Seleccionar tipo</option>
                        <option value="texto">Texto</option>
                        <option value="numero">Número</option>
                        <option value="fecha">Fecha</option>
                        <option value="lista">Lista desplegable</option>
                        <option value="textarea">Texto largo</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <div id="opciones-${index}" style="display:none;">
                        <label class="form-label">Opciones de la lista</label>
                        <input type="text" class="form-control" name="campos[${index}][opciones]" 
                               placeholder="Separadas por comas (Ej: Opción 1, Opción 2, Opción 3)">
                        <small style="color: var(--text-secondary); font-size: 0.85rem; display: block; margin-top: 5px;">
                            Escribe las opciones separadas por comas
                        </small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Configuración</label>
                    <div class="checkbox-container">
                        <input type="checkbox" name="campos[${index}][obligatorio]" value="1" id="obligatorio-${index}">
                        <label for="obligatorio-${index}" class="checkmark"></label>
                        <span>Campo obligatorio</span>
                    </div>
                </div>
            </div>
        `;
        
        contenedor.appendChild(campoCard);
        
        // Desplazarse al nuevo campo agregado
        setTimeout(() => {
            campoCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 100);
    }
    
    function eliminarCampo(id) {
        const campoCard = document.getElementById(`campo-${id}`);
        if (campoCard) {
            campoCard.style.animation = 'fadeIn 0.3s ease-out reverse';
            setTimeout(() => {
                campoCard.remove();
                actualizarContadores();
                
                // Mostrar mensaje si no quedan campos
                const contenedor = document.getElementById("campos-container");
                if (contenedor.children.length === 0) {
                    const emptyMessage = document.getElementById("empty-campos-message");
                    if (emptyMessage) {
                        emptyMessage.style.display = "block";
                    }
                }
            }, 300);
        }
    }
    
    function actualizarContadores() {
        const campoCards = document.querySelectorAll('.campo-card');
        campoCards.forEach((card, index) => {
            const indexSpan = card.querySelector('.campo-index');
            if (indexSpan) {
                indexSpan.textContent = index + 1;
            }
        });
    }
    
    function toggleOpciones(select, index) {
        const opcionesDiv = document.getElementById(`opciones-${index}`);
        if (select.value === "lista") {
            opcionesDiv.style.display = "block";
        } else {
            opcionesDiv.style.display = "none";
        }
    }
    
    // Validación antes de enviar el formulario
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                // Validar que al menos haya un campo si es necesario
                const camposCount = document.querySelectorAll('.campo-card').length;
                if (camposCount === 0) {
                    if (!confirm('No has agregado ningún campo a la actividad. ¿Deseas continuar sin campos?')) {
                        e.preventDefault();
                        return false;
                    }
                }
                
                // Validar que los campos de lista tengan opciones si se seleccionó ese tipo
                const listaCampos = document.querySelectorAll('select[name^="campos["][name$="[tipo]"]');
                let valid = true;
                
                listaCampos.forEach(select => {
                    if (select.value === 'lista') {
                        const index = select.name.match(/\[(\d+)\]/)[1];
                        const opcionesInput = document.querySelector(`input[name="campos[${index}][opciones]"]`);
                        
                        if (!opcionesInput.value.trim()) {
                            alert(`El campo "${select.closest('.campo-card').querySelector('input[name^="campos["][name$="[nombre]"]').value}" es de tipo lista pero no tiene opciones definidas.`);
                            valid = false;
                        }
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                }
            });
        }
    });
    </script>
</head>
<body class="<?php echo 'rol-' . $rol; ?>">
<main>
    <div class="parent">
        <!-- Sidebar izquierdo (div1) -->
        <div class="div1">
            <?php include_once("sidebar.php"); ?>
        </div>

        <!-- Perfil de usuario (div2) -->
        <div class="div2" style="display:flex; align-items:center; justify-content:flex-end; padding:20px;">
            <div class="user-profile-dropdown" style="position:relative;">
                <button class="user-profile-btn" style="display:flex; align-items:center; gap:10px; background:none; border:none; cursor:pointer;">
                    <img src="../imagenes/user-default.png" alt="Foto de usuario" style="width:40px; height:40px; border-radius:50%;">
                    <span><?php echo htmlspecialchars($nombre); ?></span>
                    <span style="font-size:18px;">▼</span>
                </button>
                <div class="user-dropdown-menu" style="display:none; position:absolute; right:0; top:48px; background:#fff; border:1px solid #ccc; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.08); min-width:160px; z-index:10;">
                    <a href="#" class="dropdown-item" style="display:block; padding:10px 18px; color:#333; text-decoration:none;">Cambiar contraseña</a>
                    <a href="/logout.php" class="dropdown-item" style="display:block; padding:10px 18px; color:#c00; text-decoration:none;">Cerrar sesión</a>
                </div>
            </div>
        </div>

        <!-- Título de la sección y nav secundario (div5) -->
        <div class="div3">
            <div class="form-container">
                <?php if ($mensaje): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="form-card">
                    <div class="form-header">
                        <i class="fas fa-tasks"></i>
                        <div>
                            <h1>Registrar Nueva Actividad</h1>
                            <p>Complete todos los campos para crear una nueva actividad en el sistema</p>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nombre de la Actividad <span class="required">*</span></label>
                            <input type="text" class="form-control" name="nombre_actividad" 
                                   placeholder="Ej: Consulta médica, Toma de muestras, Sesión de terapia..." required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Área <span class="required">*</span></label>
                            <select class="form-control" name="id_area" required>
                                <option value="">Selecciona un área</option>
                                <?php 
                                $areas = [];
                                $result_areas = $conexion->query("SELECT id_area, nombre_area FROM areas");
                                while ($row = $result_areas->fetch_assoc()) {
                                    $areas[] = $row;
                                }
                                foreach ($areas as $area): ?>
                                    <option value="<?php echo $area['id_area']; ?>">
                                        <?php echo htmlspecialchars($area['nombre_area']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion_actividad" 
                                  placeholder="Describa brevemente el propósito y características de esta actividad..."></textarea>
                        <small style="color: var(--text-secondary); font-size: 0.85rem; display: block; margin-top: 5px;">
                            Opcional: Proporcione detalles adicionales sobre la actividad
                        </small>
                    </div>
                    
                    <div class="campos-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <i class="fas fa-list-alt"></i>
                                Campos de la Actividad
                            </h3>
                            <button type="button" class="btn btn-secondary" onclick="agregarCampo()">
                                <i class="fas fa-plus"></i> Agregar Campo
                            </button>
                        </div>
                        
                        <p style="color: var(--text-secondary); margin-bottom: 20px;">
                            Defina los campos de información que se solicitarán al completar esta actividad.
                        </p>
                        
                        <div id="campos-container">
                            <div class="empty-campos" id="empty-campos-message">
                                <i class="fas fa-inbox"></i>
                                <h4 style="margin: 10px 0 8px 0;">No hay campos agregados</h4>
                                <p>Haz clic en "Agregar Campo" para comenzar a definir los datos que necesitas recopilar.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="javascript:history.back()" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Actividad
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Formulario en el área principal (div4) -->
        <div class="div4">
        </div>
    </div>
</main>
<script>
// JS para mostrar el menú desplegable del usuario
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.querySelector('.user-profile-btn');
    const menu = document.querySelector('.user-dropdown-menu');
    if (btn && menu) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', function() {
            menu.style.display = 'none';
        });
    }
    
    // Agregar un campo inicial si se desea
    // agregarCampo();
});
</script>
</body>
</html>
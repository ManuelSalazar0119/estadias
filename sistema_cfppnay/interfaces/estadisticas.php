<?php
include_once("../funciones/conexion.php");
session_start();

// Verifica login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: http://localhost/login.php");
    exit;
}

$rol = $_SESSION['rol'] ?? 'medico';
$nombre = $_SESSION['nombre_usuario'] ?? 'Usuario';

// Traer actividades
$actividades = [];
if(isset($conn)){
    $res = $conn->query("SELECT id_actividad, nombre_actividad FROM actividades ORDER BY nombre_actividad");
    while ($row = $res->fetch_assoc()) {
        $actividades[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas - CEFPPNAY</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebar_unificado.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            --danger-light: #fef2f2;
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

        .panel-content { flex: 1; padding: 35px 40px; overflow-x: hidden; max-width: 1400px; margin: 0 auto; width: 100%; box-sizing: border-box; }

        /* =========================================================
           ENCABEZADO DE LA VISTA
           ========================================================= */
        .view-header {
            display: flex; flex-direction: column; margin-bottom: 25px;
            animation: fadeSlideUp 0.4s ease-out;
        }
        .view-header h1 { margin: 0 0 8px 0; font-size: 1.8rem; font-weight: 800; color: var(--text-dark); letter-spacing: -0.5px; }
        .view-header p { margin: 0; color: var(--text-muted); font-size: 0.95rem; font-weight: 500; }

        /* =========================================================
           TARJETA DE FILTROS PREMIUM
           ========================================================= */
        .advanced-filters {
            background: var(--surface-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-md);
            padding: 24px 30px; margin-bottom: 30px; border: 1px solid rgba(226, 232, 240, 0.8);
            animation: fadeSlideUp 0.5s ease-out; animation-fill-mode: both; animation-delay: 0.1s;
        }

        .filter-row { display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end; }
        
        .filter-group { display: flex; flex-direction: column; gap: 8px; position: relative; }
        .filter-label { font-size: 0.75rem; font-weight: 800; color: var(--text-regular); text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px; }
        .filter-label i { color: #94a3b8; }
        
        .form-control {
            padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border-focus);
            background: #fff; color: var(--text-dark); font-size: 0.95rem; font-weight: 600; font-family: 'Inter', sans-serif;
            min-width: 150px; outline: none; transition: all 0.2s; cursor: pointer;
        }
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 12px center; padding-right: 40px;
        }
        .form-control:hover { border-color: #94a3b8; }
        .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 4px var(--primary-light); }

        /* BOTONES PREMIUM */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 0 20px; border-radius: 8px; font-size: 0.9rem; font-weight: 700; border: none; cursor: pointer;
            height: 44px; text-decoration: none; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); letter-spacing: 0.3px;
        }
        .btn-primary { background: var(--text-dark); color: #fff; box-shadow: 0 4px 6px rgba(15, 23, 42, 0.1); }
        .btn-primary:hover { background: #000; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(15, 23, 42, 0.15); }
        .btn-primary:active { transform: translateY(0); }
        .btn-primary:disabled { background: #94a3b8; cursor: not-allowed; transform: none; box-shadow: none; }
        
        .btn-pdf { background: var(--danger-light); color: var(--danger-color); border: 1px solid var(--danger-light); }
        .btn-pdf:hover { background: var(--danger-color); color: white; border-color: var(--danger-color); transform: translateY(-2px); box-shadow: 0 6px 12px rgba(239, 68, 68, 0.2); }

        .button-group-right { display: flex; gap: 15px; margin-left: auto; flex-wrap: wrap; }

        /* =========================================================
           TARJETA DE GRÁFICA
           ========================================================= */
        .chart-card {
            background: var(--surface-white); border-radius: var(--radius-lg); box-shadow: var(--shadow-md);
            border: 1px solid rgba(226, 232, 240, 0.8); padding: 30px; height: 480px; position: relative;
            animation: fadeSlideUp 0.5s ease-out; animation-fill-mode: both; animation-delay: 0.2s;
        }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .filter-row { gap: 15px; }
            .button-group-right { margin-left: 0; width: 100%; margin-top: 5px; }
            .btn { flex: 1; }
        }
        @media (max-width: 768px) {
            .panel-sidebar { display: none; }
            .panel-content { padding: 20px; }
            .filter-group { width: 100%; }
            .form-control { width: 100%; }
            .button-group-right { flex-direction: column; gap: 10px; }
            .header-right .user-info-text, .header-right .fa-chevron-down { display: none; }
            .chart-card { height: 350px; padding: 16px; }
        }
    </style>
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
                <i class="fas fa-layer-group"></i> <span>Crear Actividades</span>
            </a>
            <a href="nuevo_registro.php" class="sidebar-link">
                <i class="fas fa-file-medical"></i> <span>Formulario Médico</span>
            </a>

            <div class="sidebar-section-title">REPORTES</div>
            <a href="estadisticas.php" class="sidebar-link active">
                <i class="fas fa-chart-pie"></i> <span>Estadísticas</span>
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
        
        <div class="view-header">
            <h1>Análisis de Rendimiento</h1>
            <p>Visualice e interprete las estadísticas de las actividades operativas por periodo.</p>
        </div>

        <div class="advanced-filters">
            <div class="filter-row">
                
                <div class="filter-group" style="flex: 2; min-width: 250px;">
                    <label for="actividad" class="filter-label"><i class="fas fa-layer-group"></i> Actividad Operativa</label>
                    <select id="actividad" class="form-control">
                        <?php foreach ($actividades as $act): ?>
                            <option value="<?= $act['id_actividad'] ?>"><?= htmlspecialchars($act['nombre_actividad']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group" style="flex: 1; min-width: 150px;">
                    <label for="mes" class="filter-label"><i class="fas fa-calendar-day"></i> Periodo (Mes)</label>
                    <select id="mes" class="form-control">
                        <option value="0">Todos los meses</option>
                        <?php
                        $meses = [
                            1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
                            5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
                            9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'
                        ];
                        foreach ($meses as $num=>$nombre_mes): ?>
                            <option value="<?= $num ?>"><?= $nombre_mes ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group" style="flex: 1; min-width: 120px;">
                    <label for="anio" class="filter-label"><i class="fas fa-calendar-alt"></i> Año</label>
                    <select id="anio" class="form-control">
                        <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?= $y ?>"><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="button-group-right">
                    <button id="btnVerEstadisticas" class="btn btn-primary">
                        <i class="fas fa-chart-line"></i> Generar Gráfica
                    </button>
                    
                    <button id="btnUnirPDFs" class="btn btn-pdf" title="Descargar todas las evidencias de esta gráfica en un solo archivo">
                        <i class="fas fa-file-pdf"></i> Unir PDFs
                    </button>
                </div>
                
            </div>
        </div>

        <div class="chart-card">
            <canvas id="graficaActividad"></canvas>
        </div>

    </main>
</div>

<script>
    // 1. Lógica del Dropdown de Usuario con Animación
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

    // 2. Inicialización Premium de Chart.js
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = "#64748b";
    
    let ctx = document.getElementById('graficaActividad').getContext('2d');
    
    // Gradiente para las barras
    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(47, 133, 90, 0.9)'); // Verde institucional oscuro
    gradient.addColorStop(1, 'rgba(47, 133, 90, 0.4)'); // Verde institucional claro

    let grafica = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Total de Registros Completados',
                data: [],
                backgroundColor: gradient,
                borderColor: '#2F855A',
                borderWidth: 1,
                borderRadius: 8, // Bordes redondeados modernos
                borderSkipped: false,
                barPercentage: 0.5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: { boxWidth: 12, usePointStyle: true, font: { weight: '600' } }
                },
                title: { 
                    display: true, 
                    text: 'Seleccione filtros para visualizar datos',
                    font: { size: 16, weight: '800' },
                    padding: { bottom: 25 },
                    color: '#0f172a'
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleFont: { size: 13, family: "'Inter', sans-serif" },
                    bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                    padding: 14,
                    cornerRadius: 10,
                    displayColors: false,
                    boxPadding: 6
                }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: { color: '#f1f5f9', drawBorder: false },
                    border: { display: false },
                    ticks: { font: { weight: '600' } }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    border: { display: false },
                    ticks: { font: { weight: '600' } }
                }
            },
            animation: {
                y: { duration: 1000, easing: 'easeOutQuart' }
            }
        }
    });

    // 3. Cargar Datos (AJAX)
    document.getElementById('btnVerEstadisticas').addEventListener('click', function() {
        const id_actividad = document.getElementById('actividad').value;
        const anio = document.getElementById('anio').value;
        const mes = document.getElementById('mes').value;
        const btn = this;
        
        // Efecto de carga en el botón
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Cargando...';
        btn.disabled = true;

        fetch(`../funciones/get_estadisticas.php?id_actividad=${id_actividad}&anio=${anio}&mes=${mes}`)
            .then(r => r.json())
            .then(data => {
                if(data.labels.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Sin datos',
                        text: 'No se encontraron registros para el periodo seleccionado.',
                        confirmButtonColor: '#1e293b',
                        customClass: { popup: 'form-card', title: 'header-titles', confirmButton: 'btn btn-primary' }
                    });
                }

                grafica.data.labels = data.labels;
                grafica.data.datasets[0].data = data.valores;
                
                // Actualizar título de la gráfica dinámicamente
                const nombreActividad = document.getElementById('actividad').options[document.getElementById('actividad').selectedIndex].text;
                grafica.options.plugins.title.text = `Rendimiento Analítico: ${nombreActividad}`;
                
                grafica.update();
            })
            .catch(err => {
                console.error("Error cargando gráfica:", err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'Hubo un problema al consultar la base de datos.',
                    confirmButtonColor: '#ef4444',
                    customClass: { popup: 'form-card', title: 'header-titles', confirmButton: 'btn btn-danger-light' }
                });
            })
            .finally(() => {
                btn.innerHTML = '<i class="fas fa-chart-line"></i> Generar Gráfica';
                btn.disabled = false;
            });
    });

    // 4. Unir PDFs
    document.getElementById('btnUnirPDFs').addEventListener('click', function() {
        const id_actividad = document.getElementById('actividad').value;
        const anio = document.getElementById('anio').value;
        const mes = document.getElementById('mes').value;
        
        if(mes == 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Selección Requerida',
                text: 'Por favor selecciona un mes específico para unir los documentos PDF de evidencia.',
                confirmButtonColor: '#2F855A',
                customClass: { popup: 'form-card', title: 'header-titles', confirmButton: 'btn btn-primary' }
            });
            return;
        }

        window.open(`../funciones/unir_pdfs.php?id_actividad=${id_actividad}&anio=${anio}&mes=${mes}`, '_blank');
    });
</script>

</body>
</html>
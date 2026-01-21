<?php
include_once("../funciones/conexion.php");
include_once("../funciones/funciones_reportes.php");
session_start();
// Verifica si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /login.php');
    exit;
}
$rol = $_SESSION['rol'] ?? 'medico';
$nombreU = $_SESSION['nombre_usuario'] ?? 'Usuario';

// Obtener áreas desde la base de datos
$areas = [];
$result_areas = $conn   ->query("SELECT id_area, nombre_area FROM areas");
while ($row = $result_areas->fetch_assoc()) {
    $areas[] = $row;
}
// Determina el área seleccionada (por GET o por defecto)
// Determinar año y mes por GET o por defecto (hoy)
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));
$mes  = isset($_GET['mes'])  ? intval($_GET['mes'])  : intval(date('m'));
$id_area = isset($_GET['id_area']) ? intval($_GET['id_area']) : ($areas[0]['id_area'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Mensual</title>
    <link rel="stylesheet" href="../css/reporte.css?v=<?php echo(rand()); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style type="text/css">
        .tg  {border-collapse:collapse;border-spacing:0;}
        .tg td{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:14px;
        overflow:hidden;padding:10px 5px;word-break:normal;}
        .tg th{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:14px;
        font-weight:normal;overflow:hidden;padding:10px 5px;word-break:normal;}
        .tg .tg-cly1{text-align:left;vertical-align:middle}
        .tg .tg-wa1i{font-weight:bold;text-align:center;vertical-align:middle}
        .tg .tg-qjdd{background-color:#D0E0E3;text-align:left;vertical-align:middle}
        .tg .tg-4bam{background-color:#FFF;text-align:center;vertical-align:bottom}
        .tg .tg-uzvj{border-color:inherit;font-weight:bold;text-align:center;vertical-align:middle}
        .tg .tg-yla0{font-weight:bold;text-align:left;vertical-align:middle}
        .tg .tg-nrix{text-align:center;vertical-align:middle}
        .tg .tg-g4z8{background-color:#D9EAD3;text-align:left;vertical-align:middle}
        .tg .tg-8d8j{text-align:center;vertical-align:bottom}
        .tg .tg-3yw8{background-color:#FF0;text-align:center;vertical-align:bottom}
    </style>
</head>
<body>
<main>
    
<div class="parent">
    <div class="div1">
        <?php include_once("sidebar.php"); ?>
    </div>
    <div class="div2">
        <div class="toolbar">

            <!-- ÚNICO FORMULARIO -->
            <form method="GET" action="reporte_mensual.php" class="filtro-form">
            <div class="form-group">
                <label for="anio">Año:</label>
                <input type="number" name="anio" id="anio"
                    value="<?= htmlspecialchars($anio ?? '') ?>" min="2000" max="2100">
            </div>

            <div class="form-group">
                <label for="mes">Mes:</label>
                <select name="mes" id="mes">
                <?php
                $meses = [
                    1=>"Enero",2=>"Febrero",3=>"Marzo",4=>"Abril",5=>"Mayo",6=>"Junio",
                    7=>"Julio",8=>"Agosto",9=>"Septiembre",10=>"Octubre",11=>"Noviembre",12=>"Diciembre"
                ];
                foreach ($meses as $num => $nombre) {
                    $selected = ((int)($mes ?? 0) === $num) ? "selected" : "";
                    echo "<option value='$num' $selected>$nombre</option>";
                }
                ?>
                </select>
            </div>

            <div class="form-group">
                <label for="area">Área:</label>
                <select id="area-select" name="id_area" onchange="this.form.submit()">
                <?php foreach ($areas as $area): ?>
                    <option value="<?= (int)$area['id_area'] ?>"
                    <?= ((int)$area['id_area'] === (int)($id_area ?? 0)) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($area['nombre_area']) ?>
                    </option>
                <?php endforeach; ?>
                </select>
            </div>

            <button type="submit">Generar</button>
            </form>
            <button id="abrirModal" class="btn-justificacion">
                Agregar/Editar Justificaciones
            </button>


            <!-- PERFIL DE USUARIO -->
            <div class="user-profile-dropdown" style="position:relative;">
                <button class="user-profile-btn" style="display:flex; align-items:center; gap:10px; background:none; border:none; cursor:pointer;">
                    <img src="../imagenes/user-default.png" alt="Foto de usuario" style="width:40px; height:40px; border-radius:50%;">
                    <span><?php echo htmlspecialchars($nombreU); ?></span>
                    <span style="font-size:18px;">▼</span>
                </button>
                <!-- Menú desplegable oculto por defecto, MOSTRAR CON JS!! -->
                <div class="user-dropdown-menu" style="display:none; position:absolute; right:0; top:48px; background:#fff; border:1px solid #ccc; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.08); min-width:160px; z-index:10;">
                    <a href="#" class="dropdown-item" style="display:block; padding:10px 18px; color:#333; text-decoration:none;">Cambiar contraseña</a>
                    <a href="../funciones/logout.php" class="dropdown-item" style="display:block; padding:10px 18px; color:#c00; text-decoration:none;">Cerrar sesión</a>
                </div>
            </div>

        </div>
    </div>
    <div class="div3">
    <a href="../funciones/exportar_tablas.php?id_area=<?= $id_area ?>&anio=<?= $anio ?>&mes=<?= $mes ?>" 
        class="btn btn-success">
        Exportar a Excel
    </a>
    <?php
    // =========================
    // Cargar plantilla del área seleccionada
    // =========================
    if ($id_area) {
        switch ($id_area) {
            case 1: // Brucelosis
                include "../interfaces/reporte_mensual_TB.php";
                break;
            case 2: // Tuberculosis
                include "../interfaces/reporte_mensual_Brucela.php";
                break;
            case 3: // Rabia
                include "reportes/reporte_rabia.php";
                break;
            default:
                echo "<p>No existe plantilla para el área seleccionada.</p>";
                break;
        }
    } else {
        echo "<p>Selecciona un área para mostrar el reporte mensual.</p>";
    }
    ?>
    </div>
    <div class="div4">4</div>
</div>
</main>

<script>
  // Toggle del menú de perfil y cierre al hacer click fuera
  (function () {
    const dropdown = document.querySelector('.user-profile-dropdown');
    if (!dropdown) return;
    const btn = dropdown.querySelector('.user-profile-btn');
    const menu = dropdown.querySelector('.user-dropdown-menu');

    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      dropdown.classList.toggle('open');
      const open = dropdown.classList.contains('open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      menu.setAttribute('aria-hidden', open ? 'false' : 'true');
    });

    document.addEventListener('click', () => {
      if (dropdown.classList.contains('open')) {
        dropdown.classList.remove('open');
        btn.setAttribute('aria-expanded', 'false');
        menu.setAttribute('aria-hidden', 'true');
      }
    });
  })();
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalJustificaciones');
    const abrir = document.getElementById('abrirModal');
    const cerrar = document.getElementById('cerrarModal');

    // Abrir modal
    abrir.addEventListener('click', () => {
        modal.style.display = 'flex';
    });

    // Cerrar modal con la X
    cerrar.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // Cerrar modal haciendo clic fuera del contenido
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>


<div id="modalJustificaciones" class="modal-overlay">
  <div class="modal-container">
    <span class="modal-cerrar" id="cerrarModal">&times;</span>
    <h2>Justificaciones Mensuales</h2>
    <table id="tablaJustificaciones">
      <thead>
        <tr>
          <th>Actividad</th>
          <th>Unidad</th>
          <th>Programado</th>
          <th>Realizado</th>
          <th>Justificación</th>
        </tr>
      </thead>
      <tbody>
        <!-- Aquí JS agregará las filas -->
      </tbody>
    </table>
    <button id="guardarJustificaciones">Guardar Todas</button>
  </div>
</div>
<script src="../js/llenartabla.js?v=<?= rand() ?>"></script>

</body>
</html>
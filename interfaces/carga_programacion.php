<?php
include_once("../funciones/conexion.php");
header('Content-Type: text/html; charset=utf-8');
session_start();

// Verifica si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /login.php');
    exit;
}

$rol = $_SESSION['rol'] ?? 'medico';
$nombreU = $_SESSION['nombre_usuario'] ?? 'Usuario';

// Tipo, año y mes
$tipo = $_GET['tipo'] ?? 'anual';
$anio = (int)($_GET['anio'] ?? date("Y"));
$mes  = isset($_GET['mes']) ? (int)$_GET['mes'] : null;

// 1️⃣ Traer actividades ya registradas
$programadas = [];
if ($tipo === 'anual') {
    $sql = "SELECT pa.id_actividad, pa.unidad, pa.programado, a.nombre_actividad
            FROM programacion_anual pa
            INNER JOIN actividades a ON pa.id_actividad = a.id_actividad
            WHERE pa.anio = ?
            ORDER BY a.nombre_actividad";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $anio);
    $stmt->execute();
    $res = $stmt->get_result();
    $programadas = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} elseif ($tipo === 'mensual' && $mes) {
    $sql = "SELECT pm.id_actividad, pm.unidad, pm.programado, a.nombre_actividad
            FROM programacion_mensual pm
            INNER JOIN actividades a ON pm.id_actividad = a.id_actividad
            WHERE pm.anio = ? AND pm.mes = ?
            ORDER BY a.nombre_actividad";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $anio, $mes);
    $stmt->execute();
    $res = $stmt->get_result();
    $programadas = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// 2️⃣ Traer actividades faltantes
$idsRegistradas = array_column($programadas, 'id_actividad');
$idsRegistradasStr = implode(",", $idsRegistradas ?: [0]); // evitar string vacío

$sql = "SELECT id_actividad, nombre_actividad 
        FROM actividades 
        WHERE id_actividad NOT IN ($idsRegistradasStr)
        ORDER BY nombre_actividad";
$result = $conn->query($sql);
$faltantes = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Carga de Programación</title>
<link rel="stylesheet" href="../css/carga.css?v=<?php echo(rand()); ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<main>
<div class="parent">

    <!-- Sidebar -->
    <div class="div1">
        <?php include_once("sidebar.php"); ?>
    </div>

    <!-- Header -->
    <div class="div2">
        <h2>Carga de Programación</h2>
        <div class="user-profile-dropdown" style="position:relative;">
            <button class="user-profile-btn">
                <img src="../imagenes/user-default.png" alt="Foto de usuario">
                <span><?php echo htmlspecialchars($nombreU); ?></span>
                <span>▼</span>
            </button>
            <div class="user-dropdown-menu">
                <a href="#">Cambiar contraseña</a>
                <a href="../funciones/logout.php">Cerrar sesión</a>
            </div>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="div3">
        <form id="formProgramacion" action="../funciones/guardar_programacion.php" method="post">
            
            <!-- Tipo anual/mensual -->
            <div class="form-row">
                <label><input type="radio" name="tipo" value="anual" <?= $tipo==='anual'?'checked':'' ?> onchange="toggleMes()"> Anual</label>
                <label><input type="radio" name="tipo" value="mensual" <?= $tipo==='mensual'?'checked':'' ?> onchange="toggleMes()"> Mensual</label>
            </div>

            <!-- Año y Mes -->
            <div class="form-row">
                <label>Año:
                    <select name="anio">
                        <?php for($y=date("Y")-1;$y<=date("Y")+1;$y++): ?>
                            <option value="<?= $y ?>" <?= $y==$anio?'selected':'' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </label>

                <label id="mesSelect" style="display:<?= $tipo==='mensual'?'inline':'none' ?>">
                    Mes:
                    <select name="mes">
                        <?php
                        $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
                                  5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
                                  9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
                        foreach($meses as $num=>$nombre): ?>
                            <option value="<?= $num ?>" <?= $mes==$num?'selected':'' ?>><?= $nombre ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <!-- Tabla -->
            <div class="form-row">
                <table>
                    <thead>
                        <tr>
                            <th>Actividad</th>
                            <th>Unidad (ej. UPP, Cabezas)</th>
                            <th>Programado</th>
                        </tr>
                    </thead>
                    <tbody id="tablaProgramacion">
                        <!-- AJAX llenará estas filas -->
                    </tbody>
                </table>
            </div>

            <!-- Botón Guardar -->
            <div class="form-row">
                <button type="submit">Guardar programación</button>
            </div>

        </form>
    </div>

    <!-- Div extra (opcional) -->
    <div class="div4"></div>

</div>
</main>

<script>
// AJAX para enviar el formulario
document.getElementById("formProgramacion").addEventListener("submit", function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    fetch(this.action, {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => alert(data))
    .catch(error => alert("❌ Error en la conexión: " + error));
});

function toggleMes() {
    const tipo = document.querySelector('input[name="tipo"]:checked').value;
    document.getElementById('mesSelect').style.display = (tipo==='mensual')?'inline':'none';
    actualizarTablaAJAX();
}

function actualizarTablaAJAX() {
    const tipo = document.querySelector('input[name="tipo"]:checked').value;
    const anio = document.querySelector('select[name="anio"]').value;
    const mes  = document.querySelector('select[name="mes"]').value;

    let url = '../funciones/tabla_programacion.php?tipo='+tipo+'&anio='+anio;
    if(tipo==='mensual') url += '&mes='+mes;

    fetch(url)
    .then(res => res.text())
    .then(html => document.getElementById("tablaProgramacion").innerHTML = html);
}

// Llamada inicial al cargar la página
document.addEventListener("DOMContentLoaded", actualizarTablaAJAX);

// Actualizar tabla al cambiar año o mes
document.querySelector('select[name="anio"]').addEventListener('change', actualizarTablaAJAX);
document.querySelector('select[name="mes"]').addEventListener('change', actualizarTablaAJAX);
document.querySelectorAll('input[name="tipo"]').forEach(radio => radio.addEventListener('change', toggleMes));
</script>
<script>
// Mas o menos así sería el JS para mostrar el menú desplegable del usuario
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
});
</script>

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
</body>
</html>

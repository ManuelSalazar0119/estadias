<?php
include_once("../funciones/conexion.php");

$id_actividad = intval($_GET['id_actividad'] ?? 0);

$sql = "SELECT * FROM campos_actividad WHERE id_actividad = $id_actividad ORDER BY id_camposA";
$res = $conn->query($sql);

$col = 0;
if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        if ($col % 2 == 0) echo '<div class="row">';
        echo '<div class="col-half">';
        echo "<div class='input-group'>";
        echo "<label for='campo_{$row['id_camposA']}'>" . htmlspecialchars($row['nombre_campo_actividad']) . ":</label>";
        switch ($row['tipo_campo_actividad']) {
            case 'texto':
                echo "<input type='text' id='campo_{$row['id_camposA']}' name='campo_{$row['id_camposA']}' " . ($row['obligatorio_actividad'] ? 'required' : '') . ">";
                break;
            case 'numero':
                echo "<input type='number' id='campo_{$row['id_camposA']}' name='campo_{$row['id_camposA']}' " . ($row['obligatorio_actividad'] ? 'required' : '') . ">";
                break;
            case 'fecha':
                echo "<input type='date' id='campo_{$row['id_camposA']}' name='campo_{$row['id_camposA']}' " . ($row['obligatorio_actividad'] ? 'required' : '') . ">";
                break;
            case 'lista':
                echo "<select id='campo_{$row['id_camposA']}' name='campo_{$row['id_camposA']}' " . ($row['obligatorio_actividad'] ? 'required' : '') . ">";
                $opciones = explode(",", $row['opciones_lista_actividad']);
                foreach ($opciones as $op) {
                    echo "<option value='" . htmlspecialchars(trim($op)) . "'>" . htmlspecialchars(trim($op)) . "</option>";
                }
                echo "</select>";
                break;
        }
        echo "</div>"; // .input-group
        echo '</div>'; // .col-half
        $col++;
        if ($col % 2 == 0) echo '</div>'; // .row
    }
    // Si hay un campo impar, cierra la fila
    if ($col % 2 != 0) echo '</div>';
} else {
    echo "<p>No hay campos configurados para esta actividad.</p>";
}
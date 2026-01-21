<?php
include('conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $monto = $_POST['monto'];
    $dia = $_POST['dia'];
    $aboprodfolio = $_POST['aboprodfolio'];

    // Validar los datos
    if (!empty($monto) && !empty($dia)){
        $sql = "UPDATE folios SET cantidad_abonos = ?, dia_abonos = ? WHERE id_folio = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $monto, $dia, $aboprodfolio);

        if ($stmt->execute()) {
            echo "Monto y día actualizados correctamente.";
        } else {
            echo "Error al actualizar.";
        }
        $stmt->close();
    } else {
        echo "Todos los campos son obligatorios.";
    }
} else {
    echo "Acceso no permitido.";
}

$conn->close();
?>

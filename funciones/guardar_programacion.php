<?php
include_once("../funciones/conexion.php");
header('Content-Type: text/html; charset=utf-8');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acceso no permitido.");
}

// Variables recibidas
$tipo        = $_POST['tipo'] ?? '';
$anio        = (int)($_POST['anio'] ?? 0);
$mes         = isset($_POST['mes']) ? (int)$_POST['mes'] : null;
$programados = $_POST['programado'] ?? [];
$unidades    = $_POST['unidad'] ?? [];

if (!$tipo || !$anio) {
    die("❌ Datos incompletos.");
}

// Verificar que al menos una actividad tenga datos
$hayDatos = false;
foreach ($programados as $id_actividad => $valores) {
    foreach ($valores as $i => $valor) {
        $unidad = trim($unidades[$id_actividad][$i] ?? "");
        $valor  = ($valor === "" ? null : (int)$valor);
        if ($unidad !== "" && $valor !== null) {
            $hayDatos = true;
            break 2; // salimos de ambos foreach
        }
    }
}
if (!$hayDatos) {
    die("❌ Debe completar al menos una actividad.");
}

// Iniciar transacción
$conn->begin_transaction();

try {
    if ($tipo === 'anual') {
        $sql = "INSERT INTO programacion_anual (id_actividad, anio, programado, unidad)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE programado = VALUES(programado),
                                        unidad = VALUES(unidad)";
        $stmt = $conn->prepare($sql);

        foreach ($programados as $id_actividad => $valores) {
            foreach ($valores as $i => $valor) {
                $unidad = trim($unidades[$id_actividad][$i] ?? "");
                $valor  = ($valor === "" ? null : (int)$valor);
                if ($unidad === "" || $valor === null) continue;

                $stmt->bind_param("iiis", $id_actividad, $anio, $valor, $unidad);
                $stmt->execute();
            }
        }
        $stmt->close();

    } elseif ($tipo === 'mensual') {
        if (!$mes) throw new Exception("Debe seleccionar un mes.");

        $sql = "INSERT INTO programacion_mensual (id_actividad, anio, mes, programado, unidad)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE programado = VALUES(programado),
                                        unidad = VALUES(unidad)";
        $stmt = $conn->prepare($sql);

        foreach ($programados as $id_actividad => $valores) {
            foreach ($valores as $i => $valor) {
                $unidad = trim($unidades[$id_actividad][$i] ?? "");
                $valor  = ($valor === "" ? null : (int)$valor);
                if ($unidad === "" || $valor === null) continue;

                $stmt->bind_param("iiiis", $id_actividad, $anio, $mes, $valor, $unidad);
                $stmt->execute();
            }
        }
        $stmt->close();

    } else {
        throw new Exception("Tipo de carga inválido.");
    }

    // Confirmar transacción
    $conn->commit();
    echo "✅ Programación guardada correctamente.";

} catch (Exception $e) {
    $conn->rollback();
    echo "❌ Error al guardar la programación: " . $e->getMessage();
}

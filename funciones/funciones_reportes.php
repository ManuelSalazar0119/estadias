<?php
function getCampoIdPorNombre(mysqli $conn, int $idActividad, string $nombreCampo): ?int {
    $sql = "SELECT id_camposA
            FROM campos_actividad
            WHERE id_actividad = ? AND LOWER(nombre_campo_actividad) = LOWER(?)
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $idActividad, $nombreCampo);
    $stmt->execute();
    $stmt->bind_result($idCampo);
    if ($stmt->fetch()) {
        $stmt->close();
        return (int)$idCampo;
    }
    $stmt->close();
    return null;
}

/**
 * Cuenta UPPs distintas (por “Clave”) en el mes.
 */
function obtenerUPPs(mysqli $conn, int $idActividad, int $anio, int $mes): int {
    $idCampoClave = getCampoIdPorNombre($conn, $idActividad, 'CLAVE');
    if (!$idCampoClave) return 0;

    $sql = "SELECT COUNT(DISTINCT v.valor) AS total
            FROM registros_actividad r
            JOIN valores_actividad v 
              ON v.id_registro = r.id_registro
             AND v.id_camposA = ?
            WHERE r.id_actividad = ?
              AND YEAR(r.fecha_registro) = ?
              AND MONTH(r.fecha_registro) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $idCampoClave, $idActividad, $anio, $mes);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($res['total'] ?? 0);
}

/**
 * Suma “Animales Probados” del mes.
 */
function obtenerCabezas(mysqli $conn, int $idActividad, int $anio, int $mes): int {
    $idCampoProbados = getCampoIdPorNombre($conn, $idActividad, 'PROBADOS');
    if (!$idCampoProbados) return 0;

    $sql = "SELECT COALESCE(
                SUM(CASE 
                        WHEN v.valor REGEXP '^[0-9]+$' THEN CAST(v.valor AS UNSIGNED)
                        ELSE 0
                    END), 0) AS total
            FROM registros_actividad r
            JOIN valores_actividad v 
              ON v.id_registro = r.id_registro
             AND v.id_camposA = ?
            WHERE r.id_actividad = ?
              AND YEAR(r.fecha_registro) = ?
              AND MONTH(r.fecha_registro) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $idCampoProbados, $idActividad, $anio, $mes);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($res['total'] ?? 0);
}

/**
 * Obtiene lo programado en el mes para una actividad.
 */
function obtenerProgramadoMensual(mysqli $conn, int $idActividad, string $unidadM, int $anio, int $mes): int {
    $sql = "SELECT programado 
            FROM programacion_mensual
            WHERE id_actividad = ? AND anio = ? AND mes = ? AND unidad = ?
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $idActividad, $anio, $mes, $unidadM);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($res['programado'] ?? 0);
}

/**
 * Obtiene lo programado en el año para una actividad.
 */
function obtenerProgramadoAnual(mysqli $conn, int $idActividad, int $anio): int {
    $sql = "SELECT programado 
            FROM programacion_anual
            WHERE id_actividad = ? AND anio = ?
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idActividad, $anio);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($res['programado'] ?? 0);
}

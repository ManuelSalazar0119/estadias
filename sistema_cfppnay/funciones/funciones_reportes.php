<?php
/**
 * Busca el ID de un campo dinámico por su nombre.
 * Optimizado con caché estática para no saturar la base de datos.
 */
function getCampoIdPorNombre(mysqli $conn, int $idActividad, string $nombreCampo): ?int {
    static $cache = [];
    $key = $idActividad . '_' . strtolower($nombreCampo);
    
    if (isset($cache[$key])) return $cache[$key];

    $sql = "SELECT id_camposA FROM campos_actividad 
            WHERE id_actividad = ? AND LOWER(nombre_campo_actividad) = LOWER(?) LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $idActividad, $nombreCampo);
    $stmt->execute();
    $stmt->bind_result($idCampo);
    
    if ($stmt->fetch()) {
        $stmt->close();
        $cache[$key] = (int)$idCampo;
        return $cache[$key];
    }
    $stmt->close();
    return null;
}

/**
 * Cuenta UPPs distintas (por el campo 'CLAVE') en un periodo específico.
 */
function obtenerUPPs(mysqli $conn, int $idActividad, int $anio, int $mes, bool $acumulado = false): int {
    $idCampoClave = getCampoIdPorNombre($conn, $idActividad, 'CLAVE');
    if (!$idCampoClave) return 0;

    // Si es acumulado, buscamos desde el mes 1 hasta el mes seleccionado
    $operadorMes = $acumulado ? "<=" : "=";

    $sql = "SELECT COUNT(DISTINCT v.valor) AS total
            FROM registros_actividad r
            JOIN valores_actividad v ON v.id_registro = r.id_registro AND v.id_camposA = ?
            WHERE r.id_actividad = ? AND YEAR(r.fecha_registro) = ? AND MONTH(r.fecha_registro) $operadorMes ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $idCampoClave, $idActividad, $anio, $mes);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($res['total'] ?? 0);
}

/**
 * Suma los animales probados (campo 'PROBADOS') en un periodo específico.
 */
function obtenerCabezas(mysqli $conn, int $idActividad, int $anio, int $mes, bool $acumulado = false): int {
    $idCampoProbados = getCampoIdPorNombre($conn, $idActividad, 'PROBADOS');
    if (!$idCampoProbados) return 0;

    $operadorMes = $acumulado ? "<=" : "=";

    $sql = "SELECT SUM(CASE WHEN v.valor REGEXP '^[0-9]+$' THEN CAST(v.valor AS UNSIGNED) ELSE 0 END) AS total
            FROM registros_actividad r
            JOIN valores_actividad v ON v.id_registro = r.id_registro AND v.id_camposA = ?
            WHERE r.id_actividad = ? AND YEAR(r.fecha_registro) = ? AND MONTH(r.fecha_registro) $operadorMes ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $idCampoProbados, $idActividad, $anio, $mes);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($res['total'] ?? 0);
}

/**
 * Obtiene lo programado (Mensual o Acumulado).
 */
function obtenerProgramadoMensual(mysqli $conn, int $idActividad, string $unidadM, int $anio, int $mes, bool $acumulado = false): int {
    $operadorMes = $acumulado ? "<=" : "=";
    
    $sql = "SELECT SUM(programado) as total 
            FROM programacion_mensual
            WHERE id_actividad = ? AND anio = ? AND mes $operadorMes ? AND unidad = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $idActividad, $anio, $mes, $unidadM);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($res['total'] ?? 0);
}

/**
 * Calcula porcentajes de avance de forma segura (evitando división por cero).
 */
function calcularPorcentaje($realizado, $programado): string {
    if ($programado <= 0) return "0.00%";
    $porcentaje = ($realizado / $programado) * 100;
    return number_format($porcentaje, 2) . "%";
}
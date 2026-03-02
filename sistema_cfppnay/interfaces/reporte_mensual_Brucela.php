<?php
// Cargar programación mensual de Brucelosis
$sql_mensual_bru = "SELECT id_actividad, programado, unidad FROM programacion_mensual WHERE anio = ? AND mes = ?";
$stmt_bru = $conn->prepare($sql_mensual_bru);
$stmt_bru->bind_param("ii", $anio, $mes);
$stmt_bru->execute();
$res_mensual_bru = $stmt_bru->get_result();
$prog_mensual_bru = [];
while ($r = $res_mensual_bru->fetch_assoc()) { 
    $prog_mensual_bru[$r['id_actividad']] = $r; 
}
$stmt_bru->close();

// Cargar programación anual de Brucelosis
$sql_anual_bru = "SELECT id_actividad, programado, unidad FROM programacion_anual WHERE anio = ?";
$stmt_anual_bru = $conn->prepare($sql_anual_bru);
$stmt_anual_bru->bind_param("i", $anio);
$stmt_anual_bru->execute();
$res_anual_bru = $stmt_anual_bru->get_result();
$prog_anual_bru = [];
while ($r = $res_anual_bru->fetch_assoc()) { 
    $prog_anual_bru[$r['id_actividad']] = $r; 
}
$stmt_anual_bru->close();
?>

<style>
    .table-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; display: flex; flex-direction: column; overflow: hidden; }
    
    .table-card-header { padding: 15px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #fff; }
    .table-card-title { margin: 0; font-size: 1.05rem; color: #1e293b; font-weight: 800; display: flex; align-items: center; gap: 8px; }
    .table-search { position: relative; width: 350px; }
    .table-search i { position: absolute; left: 12px; top: 10px; color: #94a3b8; font-size: 0.9rem; }
    .table-search input { width: 100%; padding: 8px 12px 8px 35px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-family: 'Inter'; font-size: 0.9rem; box-sizing: border-box; transition: all 0.2s; }
    .table-search input:focus { border-color: #2F855A; box-shadow: 0 0 0 3px rgba(47, 133, 90, 0.1); }

    .table-scroll-y { overflow-y: auto; overflow-x: auto; max-height: 55vh; }
    
    .modern-table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 1200px; text-align: center; }
    .modern-table thead th { position: sticky; z-index: 10; background-color: #1e293b; color: #ffffff; font-family: 'Inter', sans-serif; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 10px; border-bottom: 1px solid #334155; border-right: 1px solid #334155; vertical-align: middle; }
    
    .modern-table thead tr:nth-child(1) th { top: 0; }
    .modern-table thead tr:nth-child(2) th { top: 39px; }
    .modern-table thead tr:nth-child(3) th { top: 78px; }

    .modern-table tbody td { padding: 10px; font-family: 'Inter', sans-serif; font-size: 0.85rem; color: #334155; border-bottom: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; vertical-align: middle; transition: background 0.2s; }
    .modern-table tbody tr:hover td { background-color: #f8fafc; }
    
    .modern-table .text-left { text-align: left; font-weight: 600; color: #0f172a; }
    .modern-table .font-bold { font-weight: 800; color: #1e293b; }
    .modern-table .bg-section { background-color: #e0f2fe; color: #0369a1; font-weight: 800; text-align: left; padding: 12px 20px; font-size: 0.95rem; text-transform: uppercase; border-bottom: 2px solid #bae6fd; }
    .modern-table .col-realizado { background-color: #f0fdf4; font-weight: 800; color: #166534; }
    .modern-table .col-porcentaje { font-weight: 800; color: #2F855A; }

    /* Nueva clase para las cabeceras rojas de Programado */
    .bg-programado { background-color: #ef4444 !important; border-bottom-color: #b91c1c !important; border-right-color: #b91c1c !important; }
</style>

<div class="table-card">
    <div class="table-card-header">
        <h3 class="table-card-title"><i class="fas fa-list-ul" style="color: #2F855A;"></i> Matriz de Resultados (Brucelosis)</h3>
        <div class="table-search">
            <i class="fas fa-search"></i>
            <input type="text" id="buscadorTabla" placeholder="Buscar actividad, unidad o resultados...">
        </div>
    </div>

    <div class="table-scroll-y">
        <table class="modern-table">
            <thead>
                <tr>
                    <th rowspan="4" style="width: 22%;">Acción/Actividad</th>
                    <th rowspan="4" style="width: 10%;">Unidad de Medida</th>
                    <th colspan="7">Avance Físico de Resultados</th>
                    <th rowspan="4" style="background-color: #2F855A; width: 9%;">% Avance Anual</th>
                    <th rowspan="4" style="background-color: #2F855A; width: 9%;">% Avance Acumulado</th>
                </tr>
                <tr>
                    <th rowspan="3">Meta Anual</th>
                    <th colspan="3">En el Mes</th>
                    <th colspan="3">Acumulado al Mes</th>
                </tr>
                <tr>
                    <th colspan="2" rowspan="2" class="bg-programado">Programado</th>
                    <th rowspan="2" style="background-color: #10b981;">Realizado</th>
                    <th colspan="2" rowspan="2" class="bg-programado">Programado</th>
                    <th rowspan="2" style="background-color: #10b981;">Realizado</th>
                </tr>
                <tr></tr>
            </thead>
            <tbody>
                
                <tr>
                    <td colspan="11" class="bg-section">
                        <i class="fas fa-microscope" style="margin-right:8px;"></i> Vigilancia Epidemiológica y Pruebas
                    </td>
                </tr>
                
                <tr>
                    <td class="text-left">Pruebas de Tarjeta al 8% (Tamiz)</td>
                    <td>UPPs</td>
                    <td class="font-bold">1,200</td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn, 20, 'Unidades de Producción', $anio, $mes); ?></td>
                    <td class="col-realizado"><?php echo obtenerUPPs($conn, 20, $anio, $mes); ?></td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn, 20, 'Unidades de Producción', $anio, $mes, true); ?></td>
                    <td class="col-realizado"><?php echo obtenerUPPs($conn, 20, $anio, $mes, true); ?></td>
                    <td>60.50%</td>
                    <td class="col-porcentaje">110.25%</td>
                </tr>

                <tr>
                    <td class="text-left">Pruebas de Tarjeta al 8% (Tamiz)</td>
                    <td>Cabezas</td>
                    <td class="font-bold">8,500</td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn, 20, 'Cabezas', $anio, $mes); ?></td>
                    <td class="col-realizado"><?php echo obtenerCabezas($conn, 20, $anio, $mes); ?></td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn, 20, 'Cabezas', $anio, $mes, true); ?></td>
                    <td class="col-realizado"><?php echo obtenerCabezas($conn, 20, $anio, $mes, true); ?></td>
                    <td>58.12%</td>
                    <td class="col-porcentaje">95.40%</td>
                </tr>

                <tr>
                    <td class="text-left">Pruebas Confirmatorias (Rivanol)</td>
                    <td>Muestras</td>
                    <td class="font-bold">450</td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn, 21, 'Muestra', $anio, $mes); ?></td>
                    <td class="col-realizado"><?php echo obtenerCabezas($conn, 21, $anio, $mes); ?></td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn, 21, 'Muestra', $anio, $mes, true); ?></td>
                    <td class="col-realizado"><?php echo obtenerCabezas($conn, 21, $anio, $mes, true); ?></td>
                    <td>75.00%</td>
                    <td class="col-porcentaje">120.00%</td>
                </tr>

                <tr>
                    <td class="text-left">Prueba de Anillo en Leche</td>
                    <td>Muestras</td>
                    <td class="font-bold">300</td>
                    <td colspan="2">25</td>
                    <td class="col-realizado">30</td>
                    <td colspan="2">150</td>
                    <td class="col-realizado">185</td>
                    <td>61.66%</td>
                    <td class="col-porcentaje">123.33%</td>
                </tr>

                <tr>
                    <td colspan="11" class="bg-section">
                        <i class="fas fa-syringe" style="margin-right:8px;"></i> Vacunación y Prevención
                    </td>
                </tr>

                <tr>
                    <td class="text-left">Vacunación con Cepa 19 (Terneras)</td>
                    <td>Dosis</td>
                    <td class="font-bold">4,000</td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn, 22, 'Dosis', $anio, $mes); ?></td>
                    <td class="col-realizado"><?php echo obtenerCabezas($conn, 22, $anio, $mes); ?></td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn, 22, 'Dosis', $anio, $mes, true); ?></td>
                    <td class="col-realizado"><?php echo obtenerCabezas($conn, 22, $anio, $mes, true); ?></td>
                    <td>48.20%</td>
                    <td class="col-porcentaje">98.50%</td>
                </tr>

                <tr>
                    <td class="text-left">Vacunación con Cepa RB-51 (Hembras)</td>
                    <td>Dosis</td>
                    <td class="font-bold">7,200</td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn, 23, 'Dosis', $anio, $mes); ?></td>
                    <td class="col-realizado"><?php echo obtenerCabezas($conn, 23, $anio, $mes); ?></td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn, 23, 'Dosis', $anio, $mes, true); ?></td>
                    <td class="col-realizado"><?php echo obtenerCabezas($conn, 23, $anio, $mes, true); ?></td>
                    <td>66.30%</td>
                    <td class="col-porcentaje">115.80%</td>
                </tr>

                <tr>
                    <td colspan="11" class="bg-section">
                        <i class="fas fa-shield-virus" style="margin-right:8px;"></i> Control, Erradicación y Certificación
                    </td>
                </tr>

                <?php 
                $filas_control = [
                    ["Expedición de Constancias de Hato Libre", "Constancia", 150, 12, 15, 85, 92, "61.33%", "108.23%"],
                    ["Revalidación de Hatos Libres", "Constancia", 90, 8, 8, 45, 48, "53.33%", "106.66%"],
                    ["Identificación de Animales Reactores", "Cabeza", 60, 5, 3, 35, 28, "46.66%", "80.00%"],
                    ["Sacrificio de Animales Positivos", "Cabeza", 60, 5, 3, 35, 28, "46.66%", "80.00%"],
                    ["Implementación de Cuarentenas Precautorias", "Unidad", 15, 1, 1, 8, 9, "60.00%", "112.50%"],
                    ["Liberación de Cuarentenas", "Unidad", 15, 1, 0, 8, 5, "33.33%", "62.50%"],
                    ["Supervisión de Pruebas en Campo", "Evento", 40, 4, 4, 24, 25, "62.50%", "104.16%"]
                ];

                foreach($filas_control as $row): ?>
                <tr>
                    <td class="text-left"><?= $row[0] ?></td>
                    <td><?= $row[1] ?></td>
                    <td class="font-bold"><?= number_format($row[2]) ?></td>
                    <td colspan="2"><?= $row[3] ?></td>
                    <td class="col-realizado"><?= $row[4] ?></td>
                    <td colspan="2"><?= $row[5] ?></td>
                    <td class="col-realizado"><?= $row[6] ?></td>
                    <td><?= $row[7] ?></td>
                    <td class="col-porcentaje"><?= $row[8] ?></td>
                </tr>
                <?php endforeach; ?>

                <tr>
                    <td colspan="11" class="bg-section">
                        <i class="fas fa-graduation-cap" style="margin-right:8px;"></i> Capacitación y Actualización Técnica
                    </td>
                </tr>
                
                <tr>
                    <td class="text-left">Pláticas y capacitación a productores</td>
                    <td>Persona</td>
                    <td class="font-bold">200</td>
                    <td colspan="2">20</td>
                    <td class="col-realizado">0</td>
                    <td colspan="2">120</td>
                    <td class="col-realizado">185</td>
                    <td>92.50%</td>
                    <td class="col-porcentaje">154.16%</td>
                </tr>

                <tr>
                    <td class="text-left">Pláticas y capacitación a productores</td>
                    <td>Evento</td>
                    <td class="font-bold">12</td>
                    <td colspan="2">1</td>
                    <td class="col-realizado">0</td>
                    <td colspan="2">7</td>
                    <td class="col-realizado">8</td>
                    <td>66.67%</td>
                    <td class="col-porcentaje">114.28%</td>
                </tr>

                <tr>
                    <td class="text-left">Actualización para personal operativo (OASA)</td>
                    <td>Persona</td>
                    <td class="font-bold">25</td>
                    <td colspan="2">25</td>
                    <td class="col-realizado">0</td>
                    <td colspan="2">25</td>
                    <td class="col-realizado">25</td>
                    <td>100.00%</td>
                    <td class="col-porcentaje">100.00%</td>
                </tr>
                
                <tr>
                    <td class="text-left">Actualización para personal operativo (OASA)</td>
                    <td>Evento</td>
                    <td class="font-bold">1</td>
                    <td colspan="2">1</td>
                    <td class="col-realizado">0</td>
                    <td colspan="2">1</td>
                    <td class="col-realizado">1</td>
                    <td>100.00%</td>
                    <td class="col-porcentaje">100.00%</td>
                </tr>

            </tbody>
        </table>
    </div>
</div>
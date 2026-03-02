<?php
// Cargar programación mensual
$sql_mensual = "SELECT id_actividad, programado, unidad FROM programacion_mensual WHERE anio = ? AND mes = ?";
$stmt = $conn->prepare($sql_mensual);
$stmt->bind_param("ii", $anio, $mes);
$stmt->execute();
$res_mensual = $stmt->get_result();
$prog_mensual = [];
while ($row = $res_mensual->fetch_assoc()) {
    $prog_mensual[$row['id_actividad']] = $row;
}
$stmt->close();

// Cargar programación anual
$sql_anual = "SELECT id_actividad, programado, unidad FROM programacion_anual WHERE anio = ?";
$stmt = $conn->prepare($sql_anual);
$stmt->bind_param("i", $anio);
$stmt->execute();
$res_anual = $stmt->get_result();
$prog_anual = [];
while ($row = $res_anual->fetch_assoc()) {
    $prog_anual[$row['id_actividad']] = $row;
}
$stmt->close();
?>

<style>
    .table-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; display: flex; flex-direction: column; overflow: hidden; }
    
    /* CABECERA Y BUSCADOR INTEGRADOS */
    .table-card-header { padding: 15px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #fff; }
    .table-card-title { margin: 0; font-size: 1.05rem; color: #1e293b; font-weight: 800; display: flex; align-items: center; gap: 8px; }
    .table-search { position: relative; width: 350px; }
    .table-search i { position: absolute; left: 12px; top: 10px; color: #94a3b8; font-size: 0.9rem; }
    .table-search input { width: 100%; padding: 8px 12px 8px 35px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-family: 'Inter'; font-size: 0.9rem; box-sizing: border-box; transition: all 0.2s; }
    .table-search input:focus { border-color: #2F855A; box-shadow: 0 0 0 3px rgba(47, 133, 90, 0.1); }

    .table-scroll-y { overflow-y: auto; overflow-x: auto; max-height: 65vh; }
    
    .modern-table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 1200px; text-align: center; }
    .modern-table thead th { position: sticky; z-index: 10; background-color: #1e293b; color: #ffffff; font-family: 'Inter', sans-serif; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 10px; border-bottom: 1px solid #334155; border-right: 1px solid #334155; vertical-align: middle; }
    
    /* Configuración Sticky para thead con múltiples filas */
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
        <h3 class="table-card-title"><i class="fas fa-list-ul" style="color: #2F855A;"></i> Matriz de Resultados (TB)</h3>
        <div class="table-search">
            <i class="fas fa-search"></i>
            <input type="text" id="buscadorTabla" placeholder="Buscar actividad, unidad o resultados...">
        </div>
    </div>

    <div class="table-scroll-y">
        <table class="modern-table">
            <thead>
                <tr>
                    <th rowspan="4" style="width: 20%;">Acción/Actividad</th>
                    <th rowspan="4" style="width: 12%;">Unidad de Medida</th>
                    <th colspan="7">Avance Físico</th>
                    <th rowspan="4" style="width: 10%; background-color: #2F855A;">% de avance anual</th>
                    <th rowspan="4" style="width: 10%; background-color: #2F855A;">% de avance acumulado</th>
                </tr>
                <tr>
                    <th rowspan="3">Programado Anual</th>
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
                <tr><td colspan="11" class="bg-section"><i class="fas fa-microscope" style="margin-right:8px;"></i> Vigilancia</td></tr>
                
                <tr>
                    <td class="text-left">Realización de Pruebas Cervicales NO Comparativas</td>
                    <td>Unidades de Producción</td>
                    <td class="font-bold">1,441</td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn,8,'Unidades de Producción',$anio,$mes); ?></td>
                    <td class="col-realizado"><?php echo obtenerUPPs($conn,8, $anio, $mes); ?></td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn,8,'Unidades de Producción',$anio,$mes, true); ?></td>
                    <td class="col-realizado"><?php echo obtenerUPPs($conn,8, $anio, $mes, true); ?></td>
                    <td>63.71%</td>
                    <td class="col-porcentaje">105.52%</td>
                </tr>
                <tr>
                    <td class="text-left">Realización de Pruebas Cervicales Comparativas</td>
                    <td>Cabeza</td>
                    <td class="font-bold">2,408</td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn,8,'Cabezas',$anio,$mes); ?></td>
                    <td class="col-realizado"><?php echo obtenerCabezas($conn,8, $anio, $mes); ?></td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn,8,'Cabezas',$anio,$mes, true); ?></td>
                    <td class="col-realizado"><?php echo obtenerCabezas($conn,8, $anio, $mes, true); ?></td>
                    <td>66.94%</td>
                    <td class="col-porcentaje">132.78%</td>
                </tr>
                <tr>
                    <td class="text-left">Muestreo en Rastro</td>
                    <td>Muestra</td>
                    <td class="font-bold">80</td>
                    <td colspan="2">7</td>
                    <td class="col-realizado">10</td>
                    <td colspan="2">48</td>
                    <td class="col-realizado">88</td>
                    <td>110.00%</td>
                    <td class="col-porcentaje">183.33%</td>
                </tr>
                <tr>
                    <td class="text-left">Diagnóstico Histopatológico</td>
                    <td>Diagnóstico</td>
                    <td class="font-bold">80</td>
                    <td colspan="2">7</td>
                    <td class="col-realizado">10</td>
                    <td colspan="2">48</td>
                    <td class="col-realizado">88</td>
                    <td>110.00%</td>
                    <td class="col-porcentaje">183.33%</td>
                </tr>
                <tr>
                    <td class="text-left">Diagnóstico de Bacteriología</td>
                    <td>Diagnóstico</td>
                    <td class="font-bold">80</td>
                    <td colspan="2">7</td>
                    <td class="col-realizado">10</td>
                    <td colspan="2">48</td>
                    <td class="col-realizado">88</td>
                    <td>110.00%</td>
                    <td class="col-porcentaje">183.33%</td>
                </tr>
                <tr>
                    <td class="text-left">Tipificación</td>
                    <td>Diagnóstico</td>
                    <td class="font-bold">15</td>
                    <td colspan="2">2</td>
                    <td class="col-realizado">0</td>
                    <td colspan="2">11</td>
                    <td class="col-realizado">10</td>
                    <td>66.67%</td>
                    <td class="col-porcentaje">90.91%</td>
                </tr>
                <tr>
                    <td class="text-left">Diagnóstico PCR</td>
                    <td>Diagnóstico</td>
                    <td class="font-bold">15</td>
                    <td colspan="2">2</td>
                    <td class="col-realizado">0</td>
                    <td colspan="2">10</td>
                    <td class="col-realizado">1</td>
                    <td>6.67%</td>
                    <td class="col-porcentaje">10.00%</td>
                </tr>
                <tr>
                    <td class="text-left">Cabezas Sacrificadas</td>
                    <td>Cabeza</td>
                    <td class="font-bold">11,100</td>
                    <td colspan="2">935</td>
                    <td class="col-realizado">933</td>
                    <td colspan="2">6,117</td>
                    <td class="col-realizado">5,827</td>
                    <td>52.50%</td>
                    <td class="col-porcentaje">95.26%</td>
                </tr>
                <tr>
                    <td class="text-left">Cabezas Inspeccionadas</td>
                    <td>Cabeza</td>
                    <td class="font-bold">10,608</td>
                    <td colspan="2">888</td>
                    <td class="col-realizado">918</td>
                    <td colspan="2">5,876</td>
                    <td class="col-realizado">5,737</td>
                    <td>54.08%</td>
                    <td class="col-porcentaje">97.63%</td>
                </tr>
                <tr>
                    <td class="text-left">Inspección en Rastro</td>
                    <td>Porcentaje</td>
                    <td class="font-bold">100</td>
                    <td colspan="2">98</td>
                    <td class="col-realizado">98</td>
                    <td colspan="2">98</td>
                    <td class="col-realizado">98</td>
                    <td>98.00%</td>
                    <td class="col-porcentaje">100.00%</td>
                </tr>
                <tr>
                    <td class="text-left">Barrido</td>
                    <td>Unidades de Producción</td>
                    <td class="font-bold">289</td>
                    <td colspan="2">42</td>
                    <td class="col-realizado">9</td>
                    <td colspan="2">147</td>
                    <td class="col-realizado">182</td>
                    <td>62.98%</td>
                    <td class="col-porcentaje">123.81%</td>
                </tr>
                <tr>
                    <td class="text-left">Barrido</td>
                    <td>Cabezas</td>
                    <td class="font-bold">9,419</td>
                    <td colspan="2">1,225</td>
                    <td class="col-realizado">249</td>
                    <td colspan="2">4,720</td>
                    <td class="col-realizado">6,276</td>
                    <td>66.63%</td>
                    <td class="col-porcentaje">132.97%</td>
                </tr>
                <tr>
                    <td class="text-left">Realización Pruebas en Hatos Bovinos Cuarentena Definitiva</td>
                    <td>Unidades de Producción</td>
                    <td class="font-bold">34</td>
                    <td colspan="2">2</td>
                    <td class="col-realizado">1</td>
                    <td colspan="2">19</td>
                    <td class="col-realizado">13</td>
                    <td>38.24%</td>
                    <td class="col-porcentaje">68.42%</td>
                </tr>
                <tr>
                    <td class="text-left">Realización Pruebas en Hatos Bovinos Cuarentena Definitiva</td>
                    <td>Cabezas</td>
                    <td class="font-bold">3,634</td>
                    <td colspan="2">344</td>
                    <td class="col-realizado">141</td>
                    <td colspan="2">2,232</td>
                    <td class="col-realizado">1,746</td>
                    <td>48.05%</td>
                    <td class="col-porcentaje">78.23%</td>
                </tr>
                <tr>
                    <td class="text-left">Realización Pruebas en Hatos Bovinos Cuarentena Precautoria</td>
                    <td>Unidades de Producción</td>
                    <td class="font-bold">6</td>
                    <td colspan="2">0</td>
                    <td class="col-realizado">0</td>
                    <td colspan="2">5</td>
                    <td class="col-realizado">4</td>
                    <td>66.67%</td>
                    <td class="col-porcentaje">80.00%</td>
                </tr>
                <tr>
                    <td class="text-left">Realización Pruebas en Hatos Bovinos Cuarentena Precautoria</td>
                    <td>Cabezas</td>
                    <td class="font-bold">506</td>
                    <td colspan="2">0</td>
                    <td class="col-realizado">0</td>
                    <td colspan="2">451</td>
                    <td class="col-realizado">349</td>
                    <td>68.97%</td>
                    <td class="col-porcentaje">77.38%</td>
                </tr>
                <tr>
                    <td class="text-left">Realización de Pruebas en Hatos Bovinos Relacionados y Expuestos</td>
                    <td>Unidades de Producción</td>
                    <td class="font-bold">21</td>
                    <td colspan="2">2</td>
                    <td class="col-realizado">0</td>
                    <td colspan="2">13</td>
                    <td class="col-realizado">5</td>
                    <td>23.81%</td>
                    <td class="col-porcentaje">38.46%</td>
                </tr>
                <tr>
                    <td class="text-left">Realización de Pruebas en Hatos Bovinos Relacionados y Expuestos</td>
                    <td>Cabezas</td>
                    <td class="font-bold">1,104</td>
                    <td colspan="2">84</td>
                    <td class="col-realizado">0</td>
                    <td colspan="2">779</td>
                    <td class="col-realizado">207</td>
                    <td>18.75%</td>
                    <td class="col-porcentaje">26.57%</td>
                </tr>
                <tr>
                    <td class="text-left">Identificación de Hato más Probable de Origen Bovinos</td>
                    <td>Caso</td>
                    <td class="font-bold">4</td>
                    <td colspan="2">0</td>
                    <td class="col-realizado">1</td>
                    <td colspan="2">1</td>
                    <td class="col-realizado">3</td>
                    <td>75.00%</td>
                    <td class="col-porcentaje">300.00%</td>
                </tr>
                <tr>
                    <td class="text-left">Realización de Prueba Anual en Zona de Amortiguamiento (EPNI)</td>
                    <td>Unidades de Producción</td>
                    <td class="font-bold">148</td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn,9,'Unidades de Producción',$anio,$mes); ?></td>
                    <td class="col-realizado"><?php echo obtenerUPPs($conn,9, $anio, $mes); ?></td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn,9,'Unidades de Producción',$anio,$mes, true); ?></td>
                    <td class="col-realizado"><?php echo obtenerUPPs($conn,9, $anio, $mes, true); ?></td>
                    <td>27.03%</td>
                    <td class="col-porcentaje">57.14%</td>
                </tr>
                <tr>
                    <td class="text-left">Realización de Prueba Anual en Zona de Amortiguamiento (EPNI)</td>
                    <td>Cabezas</td>
                    <td class="font-bold">3,469</td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn,9,'Cabezas',$anio,$mes); ?></td>
                    <td class="col-realizado"><?php echo obtenerCabezas($conn,9, $anio, $mes); ?></td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn,9,'Cabezas',$anio,$mes, true); ?></td>
                    <td class="col-realizado"><?php echo obtenerCabezas($conn,9, $anio, $mes, true); ?></td>
                    <td>46.79%</td>
                    <td class="col-porcentaje">95.53%</td>
                </tr>
                <tr>
                    <td class="text-left">Realización de Prueba Anual en Zona de Amortiguamiento (Control)</td>
                    <td>Unidades de Producción</td>
                    <td class="font-bold">372</td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn,10,'Unidades de Producción',$anio,$mes); ?></td>
                    <td class="col-realizado"><?php echo obtenerUPPs($conn,10, $anio, $mes); ?></td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn,10,'Unidades de Producción',$anio,$mes, true); ?></td>
                    <td class="col-realizado"><?php echo obtenerUPPs($conn,10, $anio, $mes, true); ?></td>
                    <td>18.28%</td>
                    <td class="col-porcentaje">42.77%</td>
                </tr>
                <tr>
                    <td class="text-left">Realización de Prueba Anual en Zona de Amortiguamiento (Control)</td>
                    <td>Cabezas</td>
                    <td class="font-bold">9,465</td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn,10,'Cabezas',$anio,$mes); ?></td>
                    <td class="col-realizado"><?php echo obtenerCabezas($conn,10, $anio, $mes); ?></td>
                    <td colspan="2"><?php echo obtenerProgramadoMensual($conn,10,'Cabezas',$anio,$mes, true); ?></td>
                    <td class="col-realizado"><?php echo obtenerCabezas($conn,10, $anio, $mes, true); ?></td>
                    <td>17.45%</td>
                    <td class="col-porcentaje">36.19%</td>
                </tr>

                <tr><td colspan="11" class="bg-section"><i class="fas fa-shield-virus" style="margin-right:8px;"></i> Medidas Zoosanitarias</td></tr>
                
                <tr>
                    <td class="text-left">Desinfección de Instalaciones</td>
                    <td>Unidad de Producción</td>
                    <td class="font-bold">34</td>
                    <td colspan="2">2</td>
                    <td class="col-realizado">1</td>
                    <td colspan="2">19</td>
                    <td class="col-realizado">14</td>
                    <td>41.18%</td>
                    <td class="col-porcentaje">73.68%</td>
                </tr>
                <tr>
                    <td class="text-left">Supervisión a Rastro</td>
                    <td>Evento</td>
                    <td class="font-bold">240</td>
                    <td colspan="2">23</td>
                    <td class="col-realizado">6</td>
                    <td colspan="2">126</td>
                    <td class="col-realizado">88</td>
                    <td>36.67%</td>
                    <td class="col-porcentaje">69.84%</td>
                </tr>
                <tr>
                    <td class="text-left">Supervisión de Pruebas</td>
                    <td>Evento</td>
                    <td class="font-bold">10</td>
                    <td colspan="2">1</td>
                    <td class="col-realizado">0</td>
                    <td colspan="2">5</td>
                    <td class="col-realizado">3</td>
                    <td>30.00%</td>
                    <td class="col-porcentaje">60.00%</td>
                </tr>
                <tr>
                    <td class="text-left">Liberación de Cuarentena Definitiva</td>
                    <td>Porcentaje</td>
                    <td class="font-bold">100</td>
                    <td colspan="2">100</td>
                    <td class="col-realizado">100</td>
                    <td colspan="2">100</td>
                    <td class="col-realizado">100</td>
                    <td>100.00%</td>
                    <td class="col-porcentaje">100.00%</td>
                </tr>
                <tr>
                    <td class="text-left">Liberación de Cuarentena Precautoria</td>
                    <td>Porcentaje</td>
                    <td class="font-bold">100</td>
                    <td colspan="2">100</td>
                    <td class="col-realizado">100</td>
                    <td colspan="2">100</td>
                    <td class="col-realizado">100</td>
                    <td>100.00%</td>
                    <td class="col-porcentaje">100.00%</td>
                </tr>
                <tr>
                    <td class="text-left">Implementación de Cuarentenas Definitivas</td>
                    <td>Porcentaje</td>
                    <td class="font-bold">100</td>
                    <td colspan="2">100</td>
                    <td class="col-realizado">100</td>
                    <td colspan="2">100</td>
                    <td class="col-realizado">100</td>
                    <td>100.00%</td>
                    <td class="col-porcentaje">100.00%</td>
                </tr>
                <tr>
                    <td class="text-left">Implementación de Cuarentenas Precautorias</td>
                    <td>Porcentaje</td>
                    <td class="font-bold">100</td>
                    <td colspan="2">100</td>
                    <td class="col-realizado">100</td>
                    <td colspan="2">100</td>
                    <td class="col-realizado">100</td>
                    <td>100.00%</td>
                    <td class="col-porcentaje">100.00%</td>
                </tr>
                <tr>
                    <td class="text-left">Referenciación Geográfica de las Unidades de Producción Atendidas</td>
                    <td>Reporte (Archivo Shapefile)</td>
                    <td class="font-bold">12</td>
                    <td colspan="2">1</td>
                    <td class="col-realizado">1</td>
                    <td colspan="2">7</td>
                    <td class="col-realizado">7</td>
                    <td>58.33%</td>
                    <td class="col-porcentaje">100.00%</td>
                </tr>

                <tr><td colspan="11" class="bg-section"><i class="fas fa-graduation-cap" style="margin-right:8px;"></i> Actualización Técnica</td></tr>
                
                <tr>
                    <td class="text-left">Actualización para productores </td>
                    <td>Persona</td>
                    <td class="font-bold">150</td>
                    <td colspan="2">15</td>
                    <td class="col-realizado">0</td>
                    <td colspan="2">90</td>
                    <td class="col-realizado">233</td>
                    <td>155.33%</td>
                    <td class="col-porcentaje">258.89%</td>
                </tr>
                <tr>
                    <td class="text-left">Actualización para productores </td>
                    <td>Evento</td>
                    <td class="font-bold">10</td>
                    <td colspan="2">1</td>
                    <td class="col-realizado">0</td>
                    <td colspan="2">6</td>
                    <td class="col-realizado">7</td>
                    <td>70.00%</td>
                    <td class="col-porcentaje">116.67%</td>
                </tr>
                <tr>
                    <td class="text-left">Actualización para personal del OASA</td>
                    <td>Persona</td>
                    <td class="font-bold">20</td>
                    <td colspan="2">20</td>
                    <td class="col-realizado">0</td>
                    <td colspan="2">20</td>
                    <td class="col-realizado">0</td>
                    <td>0.00%</td>
                    <td class="col-porcentaje">0.00%</td>
                </tr>
                <tr>
                    <td class="text-left">Actualización para personal del OASA</td>
                    <td>Evento</td>
                    <td class="font-bold">1</td>
                    <td colspan="2">1</td>
                    <td class="col-realizado">0</td>
                    <td colspan="2">1</td>
                    <td class="col-realizado">0</td>
                    <td>0.00%</td>
                    <td class="col-porcentaje">0%</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
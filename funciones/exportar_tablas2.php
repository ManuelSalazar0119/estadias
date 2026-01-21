<?php
require '../funciones/vendor/autoload.php';
include_once("../funciones/conexion.php");
include_once("../funciones/funciones_reportes.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Parámetros
$id_area = intval($_GET['id_area'] ?? 0);
$anio    = intval($_GET['anio'] ?? date('Y'));
$mes     = intval($_GET['mes'] ?? date('m'));

// Creamos el Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Reporte Mensual");

// =====================
// ESTILOS
// =====================
$estiloCabecera = [
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];
$estiloNormal = [
    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];
$estiloCentro = [
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];

// =====================
// CABECERA CON MERGES
// =====================
$sheet->mergeCells('A1:A4');
$sheet->mergeCells('B1:B4');
$sheet->mergeCells('C1:I1'); // Avance Físico
$sheet->mergeCells('J1:J4');
$sheet->mergeCells('K1:K4');

$sheet->mergeCells('C2:C4'); // Programado Anual
$sheet->mergeCells('D2:F2'); // En el Mes
$sheet->mergeCells('G2:I2'); // Acumulado al Mes

$sheet->mergeCells('D3:E4'); // Programado
$sheet->mergeCells('F3:F4'); // Realizado
$sheet->mergeCells('G3:H4'); // Programado acumulado
$sheet->mergeCells('I3:I4'); // Realizado acumulado

// Asignar valores de cabecera
$sheet->setCellValue('A1', 'Acción/Actividad');
$sheet->setCellValue('B1', 'Unidad de Medida');
$sheet->setCellValue('C1', 'Avance Físico');
$sheet->setCellValue('C2', 'Programado Anual');
$sheet->setCellValue('D2', 'En el Mes');
$sheet->setCellValue('D3', 'Programado');
$sheet->setCellValue('F3', 'Realizado');
$sheet->setCellValue('G2', 'Acumulado al Mes');
$sheet->setCellValue('G3', 'Programado');
$sheet->setCellValue('I3', 'Realizado');
$sheet->setCellValue('J1', '% de avance anual');
$sheet->setCellValue('K1', '% de avance acumulado');

// Aplicar estilo cabecera
$sheet->getStyle('A1:K4')->applyFromArray($estiloCabecera);

// =====================
// FILAS DE DATOS
// =====================
// Ejemplo de datos (puedes reemplazar con tus funciones de BD)
$datos = [
    ['Vigilancia', '', '', '', '', '', '', '', '', '', ''],
    ['Realización de Pruebas Cervicales Comparativas','Unidades de Producción',1441,
        obtenerProgramadoMensual($conn,8,'Unidades de Producción',$anio,$mes),
        '',
        obtenerUPPs($conn,8,$anio,$mes),
        870,918,'63.71%','105.52%'
    ],
    ['Realización de Pruebas Cervicales Comparativas','Cabeza',2408,
        obtenerProgramadoMensual($conn,8,'Cabezas',$anio,$mes),'',
        obtenerCabezas($conn,8,$anio,$mes),
        1214,1612,'66.94%','132.78%'
    ],
    ['Muestreo en Rastro','Muestra',80,7,'',10,48,88,'110.00%','183.33%'],
    ['Diagnóstico Histopatológico','Diagnóstico',80,7,'',10,48,88,'110.00%','183.33%'],
];

// =====================
// ESCRIBIR DATOS RESPETANDO COLUMNAS
// =====================
$fila = 5;
foreach($datos as $row){
    $sheet->setCellValue("A$fila", $row[0]); // Acción/Actividad
    $sheet->setCellValue("B$fila", $row[1]); // Unidad de Medida
    $sheet->setCellValue("C$fila", $row[2]); // Programado Anual
    $sheet->setCellValue("D$fila", $row[3]); // En el Mes - Programado
    $sheet->setCellValue("F$fila", $row[5]); // En el Mes - Realizado
    $sheet->setCellValue("G$fila", $row[6]); // Acumulado - Programado
    $sheet->setCellValue("I$fila", $row[7]); // Acumulado - Realizado
    $sheet->setCellValue("J$fila", $row[8]); // % Anual
    $sheet->setCellValue("K$fila", $row[9]); // % Acumulado

    // Estilos
    $sheet->getStyle("A$fila")->applyFromArray($estiloNormal);
    foreach(['B','C','D','F','G','I','J','K'] as $col){
        $sheet->getStyle($col.$fila)->applyFromArray($estiloCentro);
    }

    $fila++;
}

// Ajustar ancho de columnas
foreach(range('A','K') as $col){
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// =====================
// EXPORTAR EXCEL
// =====================
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="reporte_mensual.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;

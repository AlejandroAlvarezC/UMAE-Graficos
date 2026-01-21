<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../modelos/Especialidad_Ocasion.php';

use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function normalizarFiltro($valor) {
    if (is_array($valor)) return $valor;
    if (is_string($valor)) return $valor === '' ? [] : explode(',', $valor);
    return [];
}

$anioFiltro = normalizarFiltro($_POST['anio'] ?? '');
$especialidadFiltro = normalizarFiltro($_POST['especialidad'] ?? '');
$descripcionFiltro = normalizarFiltro($_POST['descripcion'] ?? '');

$datos = Especialidad_Ocasion::listar();

$datosFiltrados = array_filter($datos, function($item) use ($anioFiltro, $especialidadFiltro, $descripcionFiltro) {
    if (count($anioFiltro) > 0 && !in_array($item['anio'], $anioFiltro)) return false;
    if (count($especialidadFiltro) > 0 && !in_array($item['especialidad'], $especialidadFiltro)) return false;
    if (count($descripcionFiltro) > 0 && !in_array($item['descripcion'], $descripcionFiltro)) return false;
    return true;
});

$spreadsheet = new Spreadsheet();

// Hoja para Primera Vez
$sheet1era = $spreadsheet->getActiveSheet();
$sheet1era->setTitle('Primera Vez');

// Hoja para Subsecuente
$sheetSub = $spreadsheet->createSheet();
$sheetSub->setTitle('Subsecuente');

$headers = ['Clave', 'Especialidad', 'División'];
$meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

// === Primera Vez ===
// Encabezados
$col = 'A';
foreach ($headers as $header) {
    $sheet1era->setCellValue($col . '1', $header);
    $col++;
}
foreach ($meses as $mes) {
    $sheet1era->setCellValue($col++ . '1', strtoupper($mes));
}
$sheet1era->setCellValue($col . '1', 'Año');

// Datos
$row = 2;
foreach ($datosFiltrados as $dato) {
    $col = 'A';
    $sheet1era->setCellValue($col++ . $row, $dato['clave']);
    $sheet1era->setCellValue($col++ . $row, $dato['especialidad']);
    $sheet1era->setCellValue($col++ . $row, $dato['descripcion']);
    foreach ($meses as $mes) {
        $sheet1era->setCellValue($col++ . $row, is_numeric($dato[$mes . '_1era']) ? (int)$dato[$mes . '_1era'] : 0);
    }
    $sheet1era->setCellValue($col . $row, $dato['anio']);
    $row++;
}
$ultimaFila1era = $row - 1;

// === Subsecuente ===
// Encabezados
$col = 'A';
foreach ($headers as $header) {
    $sheetSub->setCellValue($col . '1', $header);
    $col++;
}
foreach ($meses as $mes) {
    $sheetSub->setCellValue($col++ . '1', strtoupper($mes));
}
$sheetSub->setCellValue($col . '1', 'Año');

// Datos
$row = 2;
foreach ($datosFiltrados as $dato) {
    $col = 'A';
    $sheetSub->setCellValue($col++ . $row, $dato['clave']);
    $sheetSub->setCellValue($col++ . $row, $dato['especialidad']);
    $sheetSub->setCellValue($col++ . $row, $dato['descripcion']);
    foreach ($meses as $mes) {
        $sheetSub->setCellValue($col++ . $row, is_numeric($dato[$mes . '_sub']) ? (int)$dato[$mes . '_sub'] : 0);
    }
    $sheetSub->setCellValue($col . $row, $dato['anio']);
    $row++;
}
$ultimaFilaSub = $row - 1;

// === Crear gráficos ===

// Categorías (meses)
$categories = [
    new DataSeriesValues('String', "'Primera Vez'!\$D\$1:\$O\$1", null, 12),
];


$tituloAnios = count($anioFiltro) > 0 ? ' (' . implode(', ', $anioFiltro) . ')' : '';

// Primera Vez - series
$seriesLabels1era = [];
$seriesValues1era = [];
for ($fila = 2; $fila <= $ultimaFila1era; $fila++) {
    $especialidad = $sheet1era->getCell("B{$fila}")->getValue();
$anio = $sheet1era->getCell("P{$fila}")->getValue(); // Columna P contiene el año
$sheet1era->setCellValue("B{$fila}", "{$especialidad} ({$anio})");
$seriesLabels1era[] = new DataSeriesValues('String', "'Primera Vez'!\$B\${$fila}", null, 1);

    $seriesValues1era[] = new DataSeriesValues('Number', "'Primera Vez'!\$D\${$fila}:\$O\${$fila}", null, 12);
}
$series1era = new DataSeries(
    DataSeries::TYPE_LINECHART,
    DataSeries::GROUPING_STANDARD,
    range(0, count($seriesLabels1era) - 1),
    $seriesLabels1era,
    $categories,
    $seriesValues1era
);
$plotArea1era = new PlotArea(null, [$series1era]);
$legend = new Legend(Legend::POSITION_RIGHT, null, false);
$xAxisLabel = null;
$yAxisLabel = null;
$title1era = new Title('Gráfico - Mensual por Especialidad - Primera Vez' . $tituloAnios);
$chart1era = new Chart(
    'chart1era',
    $title1era,
    $legend,
    $plotArea1era,
    true,
    0,
    $xAxisLabel,
    $yAxisLabel
);
$chart1era->setTopLeftPosition('A' . ($ultimaFila1era + 3));
$chart1era->setBottomRightPosition('O' . ($ultimaFila1era + 20));
$sheet1era->addChart($chart1era);
$sheet1era->setCellValue("A" . ($ultimaFila1era + 21), '💡 Consejo: haz clic derecho en una línea del gráfico y selecciona "Agregar etiquetas de datos" para ver los valores.');


// Subsecuente - categorías
$categoriesSub = [
    new DataSeriesValues('String', "'Subsecuente'!\$D\$1:\$O\$1", null, 12),
];

// Subsecuente - series
$seriesLabelsSub = [];
$seriesValuesSub = [];
for ($fila = 2; $fila <= $ultimaFilaSub; $fila++) {
   $especialidad = $sheetSub->getCell("B{$fila}")->getValue();
$anio = $sheetSub->getCell("P{$fila}")->getValue(); // Columna P contiene el año
$sheetSub->setCellValue("B{$fila}", "{$especialidad} ({$anio})");
$seriesLabelsSub[] = new DataSeriesValues('String', "'Subsecuente'!\$B\${$fila}", null, 1);

    $seriesValuesSub[] = new DataSeriesValues('Number', "'Subsecuente'!\$D\${$fila}:\$O\${$fila}", null, 12);
}
$seriesSub = new DataSeries(
    DataSeries::TYPE_LINECHART,
    DataSeries::GROUPING_STANDARD,
    range(0, count($seriesLabelsSub) - 1),
    $seriesLabelsSub,
    $categoriesSub,
    $seriesValuesSub
);
$plotAreaSub = new PlotArea(null, [$seriesSub]);
$titleSub = new Title('Gráfico - Mensual por Especialidad - Subsecuente' . $tituloAnios);
$chartSub = new Chart(
    'chartSub',
    $titleSub,
    $legend,
    $plotAreaSub,
    true,
    0,
    $xAxisLabel,
    $yAxisLabel
);
$chartSub->setTopLeftPosition('A' . ($ultimaFilaSub + 3));
$chartSub->setBottomRightPosition('O' . ($ultimaFilaSub + 20));
$sheetSub->addChart($chartSub);
$sheetSub->setCellValue("A" . ($ultimaFilaSub + 21), '💡 Consejo: haz clic derecho en una línea del gráfico y selecciona "Agregar etiquetas de datos" para ver los valores.');

// Headers para descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="tabla_y_graficos_especialidad_ocasion.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->setIncludeCharts(true);
$writer->save('php://output');
exit;
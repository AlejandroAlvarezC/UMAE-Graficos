<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../modelos/paramedicos.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

function limpiarFiltro($valor) {
    if (empty($valor)) return [];
    return array_values(array_filter(array_map('trim', explode(',', $valor)), fn($v) => $v !== ''));
}

// === Filtros desde POST ===
$anioFiltro = limpiarFiltro($_POST['anio'] ?? '');
$especialidadFiltro = limpiarFiltro($_POST['especialidad'] ?? '');
$divisionFiltro = limpiarFiltro($_POST['division'] ?? '');


// === Obtener datos ===
$datos = Paramedicos::listar();

$datosFiltrados = array_filter($datos, function ($item) use ($anioFiltro, $especialidadFiltro, $divisionFiltro) {
    if (count($anioFiltro) > 0 && !in_array($item['anio'], $anioFiltro)) return false;
    if (count($especialidadFiltro) > 0 && !in_array(strtolower(trim($item['especialidad'])), array_map('strtolower', $especialidadFiltro))) return false;
    if (count($divisionFiltro) > 0 && !in_array(strtolower(trim($item['division'])), array_map('strtolower', $divisionFiltro))) return false;
    return true;
});


// === Crear hoja Excel ===
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Paramédicos');

$meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$mesesTitulos = array_map('ucfirst', $meses);

// Encabezados
$headers = ['Clave', 'Especialidad', 'División'];
$col = 'A';
foreach ($headers as $h) $sheet->setCellValue($col++ . '1', $h);
foreach ($mesesTitulos as $m) $sheet->setCellValue($col++ . '1', $m);
$sheet->setCellValue($col++ . '1', 'Total');
$sheet->setCellValue($col . '1', 'Año');

// Datos
$row = 2;
foreach ($datosFiltrados as $dato) {
    $sheet->setCellValue('A' . $row, $dato['clave']);
    $sheet->setCellValue('B' . $row, $dato['especialidad'] . ' (' . $dato['anio'] . ')');
    $sheet->setCellValue('C' . $row, $dato['division']);

    $colLetraMesInicio = 'D';
    $colLetraMesFin = 'O';
    $colActual = $colLetraMesInicio;
    foreach ($meses as $mes) {
        $valor = (int)$dato[$mes];
        $sheet->setCellValue($colActual . $row, $valor);
        $colActual++;
    }

    $sheet->setCellValue('P' . $row, "=SUM({$colLetraMesInicio}{$row}:{$colLetraMesFin}{$row})");
    $sheet->setCellValue('Q' . $row, $dato['anio']);
    $row++;
}

$ultimaFila = $row - 1;

///////////////////////////
// GRÁFICO 1: Total por mes
///////////////////////////
$seriesMeses = [];
$labelsMeses = [];

for ($i = 2; $i <= $ultimaFila; $i++) {
    $labelsMeses[] = new DataSeriesValues('String', "'Paramédicos'!\$B\$$i", null, 1);
    $seriesMeses[] = new DataSeriesValues('Number', "'Paramédicos'!\$D\$$i:\$O\$$i", null, 12);
}

$categoriasMeses = [new DataSeriesValues('String', "'Paramédicos'!\$D\$1:\$O\$1", null, 12)];

$graficoMeses = new DataSeries(
    DataSeries::TYPE_LINECHART,
    DataSeries::GROUPING_STANDARD,
    range(0, count($labelsMeses) - 1),
    $labelsMeses,
    $categoriasMeses,
    $seriesMeses
);

$plotAreaMeses = new PlotArea(null, [$graficoMeses]);
$legend = new Legend(Legend::POSITION_RIGHT, null, false);
$titleMeses = new Title('Gráfico - Mensual por Especialidad');

$chartMeses = new Chart(
    'chartMeses',
    $titleMeses,
    $legend,
    $plotAreaMeses,
    true,
    0,
    null,
    null
);

$chartMeses->setTopLeftPosition('A' . ($ultimaFila + 3));
$chartMeses->setBottomRightPosition('O' . ($ultimaFila + 20));
$sheet->addChart($chartMeses);

// === Crear fórmulas dinámicas tipo =D3+D5 para Totales mensuales por división ===

$mapaCeldasPorDivisionYMes = []; // clave: 'División (2024)', valor: ['enero' => ['D3', 'D5'], ...]

$filaDatosInicio = 2;
$filaDatosFin = $ultimaFila;
$filaIndex = $filaDatosInicio;

foreach ($datosFiltrados as $dato) {
    $claveDivision = $dato['division'] . " ({$dato['anio']})";
    $colLetra = 'D'; // empieza en columna "enero"
    foreach ($meses as $mes) {
        $celda = $colLetra . $filaIndex;
        $mapaCeldasPorDivisionYMes[$claveDivision][$mes][] = $celda;
        $colLetra++;
    }
    $filaIndex++;
}

$filaDivisionInicio = $ultimaFila + 22;

$sheet->setCellValue("A" . ($filaDivisionInicio - 1), 'Totales mensuales por división');

$col = 'B';
foreach ($mesesTitulos as $mesTitulo) {
    $sheet->setCellValue($col++ . $filaDivisionInicio, $mesTitulo);
}
$sheet->setCellValue($col . $filaDivisionInicio, 'Total');

$sheet->setCellValue("A{$filaDivisionInicio}", 'División (Año)');

$filaActual = $filaDivisionInicio + 1;
foreach ($mapaCeldasPorDivisionYMes as $division => $mesesCeldas) {
    $sheet->setCellValue("A{$filaActual}", $division);
    $colLetra = 'B';
    $formulaTotal = [];

    foreach ($meses as $mes) {
        $celdas = $mesesCeldas[$mes] ?? [];
        if (count($celdas) === 0) {
            $sheet->setCellValue($colLetra . $filaActual, 0);
        } elseif (count($celdas) === 1) {
            $sheet->setCellValue($colLetra . $filaActual, '=' . $celdas[0]);
        } else {
            $sheet->setCellValue($colLetra . $filaActual, '=' . implode('+', $celdas));
        }
        $formulaTotal[] = $colLetra . $filaActual;
        $colLetra++;
    }

    // Columna Total
    $sheet->setCellValue($colLetra . $filaActual, '=' . implode('+', $formulaTotal));
    $filaActual++;
}

$filaDivisionFin = $filaActual - 1;

$categoriesDiv = [new DataSeriesValues('String', "'Paramédicos'!\$B\${$filaDivisionInicio}:\$M\${$filaDivisionInicio}", null, 12)];

$seriesDiv = [];
$labelsDiv = [];

$fila = $filaDivisionInicio + 1;
while ($fila <= $filaDivisionFin) {
    $labelsDiv[] = new DataSeriesValues('String', "'Paramédicos'!\$A\$$fila", null, 1);
    $seriesDiv[] = new DataSeriesValues('Number', "'Paramédicos'!\$B\$$fila:\$M\$$fila", null, 12);
    $fila++;
}

$graficoDiv = new DataSeries(
    DataSeries::TYPE_LINECHART,
    DataSeries::GROUPING_STANDARD,
    range(0, count($labelsDiv) - 1),
    $labelsDiv,
    $categoriesDiv,
    $seriesDiv
);

$plotDiv = new PlotArea(null, [$graficoDiv]);
$chartDiv = new Chart('chartDiv', new Title('Gráfico - Mensual por División'), $legend, $plotDiv, true, 0, null, null);
$chartDiv->setTopLeftPosition("A" . ($filaDivisionFin + 2));
$chartDiv->setBottomRightPosition("O" . ($filaDivisionFin + 20));
$sheet->addChart($chartDiv);


///////////////////////////
// GRÁFICO 2: Totales por División (Año)
///////////////////////////
$mapaDivisionTotales = [];
$filaIndex = 2;

foreach ($datosFiltrados as $dato) {
    $clave = $dato['division'] . " ({$dato['anio']})";
    $celda = "P{$filaIndex}";
    if (!isset($mapaDivisionTotales[$clave])) {
        $mapaDivisionTotales[$clave] = [];
    }
    $mapaDivisionTotales[$clave][] = $celda;
    $filaIndex++;
}

$filaInicio = $filaDivisionFin + 22; // dejar 22 filas después del gráfico mensual por división
$sheet->setCellValue("A{$filaInicio}", 'División (Año)');
$sheet->setCellValue("B{$filaInicio}", 'Total');

$fila = $filaInicio + 1;
foreach ($mapaDivisionTotales as $division => $celdas) {
    $sheet->setCellValue("A$fila", $division);
    if (count($celdas) === 1) {
        $sheet->setCellValue("B$fila", "={$celdas[0]}");
    } else {
        $formula = '=' . implode('+', $celdas);
        $sheet->setCellValue("B$fila", $formula);
    }
    $fila++;
}

$filaFin = $fila - 1;

$categories2 = [new DataSeriesValues('String', "'Paramédicos'!\$A\$" . ($filaInicio + 1) . ":\$A\$$filaFin", null, count($mapaDivisionTotales))];
$values2 = [new DataSeriesValues('Number', "'Paramédicos'!\$B\$" . ($filaInicio + 1) . ":\$B\$$filaFin", null, count($mapaDivisionTotales))];
$labels2 = [new DataSeriesValues('String', "'Paramédicos'!\$B\$$filaInicio", null, 1)];

$series2 = new DataSeries(
    DataSeries::TYPE_BARCHART,
    DataSeries::GROUPING_CLUSTERED,
    [0],
    $labels2,
    $categories2,
    $values2
);

$plotArea2 = new PlotArea(null, [$series2]);
$chart2 = new Chart('chart2', new Title('Gráfico - Anual por División'), $legend, $plotArea2, true, 0, null, null);

$chart2->setTopLeftPosition("A" . ($filaFin + 2));
$chart2->setBottomRightPosition("H" . ($filaFin + 18));
$sheet->addChart($chart2);

$sheet->setCellValue("A" . ($filaFin + 20), '💡 Consejo: clic derecho en las barras → "Agregar etiquetas de datos"');

// === Descargar archivo ===
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="tabla_y_graficos_paramedicos.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->setIncludeCharts(true);
$writer->save('php://output');
exit;



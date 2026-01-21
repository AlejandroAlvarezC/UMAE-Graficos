<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../modelos/Urgencia.php'; // Cambiar al modelo correcto si es diferente

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
    // Explode, trim y quitar elementos vacíos
    $arr = array_filter(array_map('trim', explode(',', $valor)), fn($v) => $v !== '');
    return array_values($arr); // para reindexar si quieres
}

// === Filtros desde POST ===
$anioFiltro = limpiarFiltro($_POST['anio'] ?? '');
$especialidadFiltro = limpiarFiltro($_POST['especialidad'] ?? '');
$divisionFiltro = limpiarFiltro($_POST['division'] ?? '');

// === Obtener datos ===
$datos = Urgencia::listar();

$datosFiltrados = array_filter($datos, function ($item) use ($anioFiltro, $especialidadFiltro, $divisionFiltro) {
    if (count($anioFiltro) > 0 && !in_array($item['anio'], $anioFiltro)) return false;
    if (count($especialidadFiltro) > 0 && !in_array(strtolower(trim($item['especialidad'])), array_map('strtolower', $especialidadFiltro))) return false;
    if (count($divisionFiltro) > 0 && !in_array(strtolower(trim($item['division'])), array_map('strtolower', $divisionFiltro))) return false;
    return true;
});


// === Crear hoja Excel ===
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Urgencias');

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
    // Columnas A (clave), B (especialidad), C (división)
    $sheet->setCellValue('A' . $row, $dato['clave']);
    $sheet->setCellValue('B' . $row, "{$dato['especialidad']} ({$dato['anio']})");
    $sheet->setCellValue('C' . $row, $dato['division']);

    // Columnas D a O (meses)
    $colLetraMesInicio = 'D';
    $colLetraMesFin = 'O';
    $colActual = $colLetraMesInicio;
    foreach ($meses as $mes) {
        $valor = (int)$dato[$mes];
        $sheet->setCellValue($colActual . $row, $valor);
        $colActual++;
    }

    // Columna P (total)
    $sheet->setCellValue('P' . $row, "=SUM({$colLetraMesInicio}{$row}:{$colLetraMesFin}{$row})");

    // Columna Q (año)
    $sheet->setCellValue('Q' . $row, $dato['anio']);

    $row++;
}

$ultimaFila = $row - 1;

///////////////////////////
// GRÁFICO 1: Total por mes
///////////////////////////
$seriesMeses = [];
$labelsMeses = [];
$categoriasMeses = [new DataSeriesValues('String', "'Urgencias'!\$D\$1:\$O\$1", null, 12)];

for ($i = 2; $i <= $ultimaFila; $i++) {
    $labelsMeses[] = new DataSeriesValues('String', "'Urgencias'!\$B\$$i", null, 1); // División
    $seriesMeses[] = new DataSeriesValues('Number', "'Urgencias'!\$D\$$i:\$O\$$i", null, 12);
}

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
    null, // Sin título eje X
    null  // Sin título eje Y
);


$chartMeses->setTopLeftPosition('A' . ($ultimaFila + 3));
$chartMeses->setBottomRightPosition('O' . ($ultimaFila + 20));
$sheet->addChart($chartMeses);

// === Gráfico 3: Total mensual por división ===
$filaDivMesInicio = $ultimaFila + 25; // 👈 justo debajo del gráfico 1
$sheet->setCellValue("A{$filaDivMesInicio}", 'División (Año)');
$colLetra = 'B';
foreach ($mesesTitulos as $mesNombre) {
    $sheet->setCellValue("{$colLetra}{$filaDivMesInicio}", $mesNombre);
    $colLetra++;
}

// Agrupar datos por división (año) y mes
$divisionesAgrupadas = [];

$filaIndex = 2;
foreach ($datosFiltrados as $dato) {
    $clave = ($dato['division'] ?: $dato['especialidad']) . " ({$dato['anio']})";

    if (!isset($divisionesAgrupadas[$clave])) {
        $divisionesAgrupadas[$clave] = array_fill_keys($meses, []);
    }

    foreach ($meses as $i => $mes) {
        $colLetra = chr(ord('D') + $i); // D=enero, E=febrero...
        $celda = "{$colLetra}{$filaIndex}";
        $divisionesAgrupadas[$clave][$mes][] = $celda;
    }

    $filaIndex++;
}

// Escribir filas agrupadas
$fila = $filaDivMesInicio + 1;
foreach ($divisionesAgrupadas as $clave => $valoresPorMes) {
    $sheet->setCellValue("A{$fila}", $clave);
    $colLetra = 'B';
    foreach ($meses as $mes) {
        $celdas = $valoresPorMes[$mes];
        if (count($celdas) === 1) {
            $sheet->setCellValue("{$colLetra}{$fila}", "={$celdas[0]}");
        } else {
            $formula = '=' . implode('+', $celdas);
            $sheet->setCellValue("{$colLetra}{$fila}", $formula);
        }
        $colLetra++;
    }
        // Calcular total (suma de columnas B a M)
    $sheet->setCellValue("N{$fila}", "=SUM(B{$fila}:M{$fila})");
$sheet->setCellValue("N{$filaDivMesInicio}", 'Total');

    $fila++;
}
$filaDivMesFin = $fila - 1;
$filaFinDivMesTabla = $filaDivMesFin; // Guardamos para luego posicionar la tabla anual más abajo

$labels3 = [];
$categorias3 = [new DataSeriesValues('String', "'Urgencias'!\$B\$$filaDivMesInicio:\$M\$$filaDivMesInicio", null, 12)];
$series3 = [];

$filaTemp = $filaDivMesInicio + 1;
while ($filaTemp <= $filaDivMesFin) {
    $etiqueta = new DataSeriesValues('String', "'Urgencias'!\$A\$$filaTemp", null, 1);
    $serie = new DataSeriesValues('Number', "'Urgencias'!\$B\$$filaTemp:\$M\$$filaTemp", null, 12);
    $series3[] = $serie;
    $labels3[] = $etiqueta;
    $filaTemp++;
}

$grafico3 = new DataSeries(
    DataSeries::TYPE_LINECHART,
    DataSeries::GROUPING_STANDARD,
    range(0, count($series3) - 1),
    $labels3, // estas etiquetas ya fueron llenadas individualmente en el while
    $categorias3,
    $series3
);


$plotArea3 = new PlotArea(null, [$grafico3]);
$chart3 = new Chart(
    'chart3',
    new Title('Gráfico - Mensual por División'),
    $legend,
    $plotArea3,
    true,
    0,
    null,
    null
);

// Posicionar el gráfico debajo del primero, pero antes del gráfico anual
$chart3->setTopLeftPosition("A" . ($filaDivMesFin + 2));
$chart3->setBottomRightPosition("O" . ($filaDivMesFin + 18)); // 18 filas más abajo, no 20

$sheet->addChart($chart3);

///////////////////////////
// GRÁFICO 2: Totales por División (Año)
///////////////////////////
$agrupado = [];
$filaIndex = 2;
foreach ($datosFiltrados as $dato) {
    $clave = ($dato['division'] ?: $dato['especialidad']) . " ({$dato['anio']})";
    $celdaTotal = "P{$filaIndex}";

    // Agrupar las celdas por clave para sumar después si hay repetidos
    if (!isset($agrupado[$clave])) {
        $agrupado[$clave] = [];
    }
    $agrupado[$clave][] = $celdaTotal;

    $filaIndex++;
}

$filaInicio = $filaDivMesFin + 20;
$sheet->setCellValue("A{$filaInicio}", 'División (Año)');
$sheet->setCellValue("B{$filaInicio}", 'Total');

$fila = $filaInicio + 1;
foreach ($agrupado as $division => $celdas) {
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

$categories2 = [new DataSeriesValues('String', "'Urgencias'!\$A\$" . ($filaInicio + 1) . ":\$A\$$filaFin", null, count($agrupado))];
$values2 = [new DataSeriesValues('Number', "'Urgencias'!\$B\$" . ($filaInicio + 1) . ":\$B\$$filaFin", null, count($agrupado))];
$labels2 = [new DataSeriesValues('String', "'Urgencias'!\$B\$$filaInicio", null, 1)];

$series2 = new DataSeries(
    DataSeries::TYPE_BARCHART,
    DataSeries::GROUPING_CLUSTERED,
    [0],
    $labels2,
    $categories2,
    $values2
);

$plotArea2 = new PlotArea(null, [$series2]);
$chart2 = new Chart(
    'chart2',
    new Title('Gráfico - Anual por División'),
    $legend,
    $plotArea2,
    true,
    0,
    null,
    null
);

$posInicioChart2 = $filaFin + 4;
$chart2->setTopLeftPosition("A{$posInicioChart2}");
$chart2->setBottomRightPosition("H" . ($posInicioChart2 + 18));

$sheet->addChart($chart2);
$sheet->setCellValue("A" . ($filaFin + 20), '💡 Consejo: clic derecho en las barras → "Agregar etiquetas de datos"');


// === Descargar archivo ===
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="tabla_y_graficos_urgencias.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->setIncludeCharts(true);
$writer->save('php://output');
exit;

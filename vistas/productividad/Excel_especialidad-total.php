<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../modelos/Especialidad_Ocasion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

// === Filtros ===
$anioFiltro = isset($_POST['anio']) ? array_filter(explode(',', $_POST['anio'])) : [];
$especialidadFiltro = isset($_POST['especialidad']) ? array_filter(explode(',', $_POST['especialidad'])) : [];
$descripcionFiltro = isset($_POST['descripcion']) ? array_filter(explode(',', $_POST['descripcion'])) : [];

// === Datos ===
$datos = Especialidad_Ocasion::listar();

$datosFiltrados = array_filter($datos, function ($item) use ($anioFiltro, $especialidadFiltro, $descripcionFiltro) {
    if (count($anioFiltro) > 0 && !in_array($item['anio'], $anioFiltro)) return false;
    if (count($especialidadFiltro) > 0 && !in_array($item['especialidad'], $especialidadFiltro)) return false;
    if (count($descripcionFiltro) > 0 && !in_array($item['descripcion'], $descripcionFiltro)) return false;
    return true;
});

// === Crear documento ===
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Especialidades');

$meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
$mesesTitulos = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

// Encabezados
$headers = ['Clave', 'Especialidad', 'División'];
$col = 'A';
foreach ($headers as $h) $sheet->setCellValue($col++ . '1', $h);
foreach ($mesesTitulos as $m) $sheet->setCellValue($col++ . '1', $m);
$sheet->setCellValue($col++ . '1', 'Total General');
$sheet->setCellValue($col . '1', 'Año');

// Insertar datos con fórmula de suma
$row = 2;
$mapaDescripcionTotales = [];

foreach ($datosFiltrados as $dato) {
    $col = 'A';
    $sheet->setCellValue($col++ . $row, $dato['clave']);
    $especialidadConAnio = $dato['especialidad'] . ' (' . $dato['anio'] . ')';
$sheet->setCellValue($col++ . $row, $especialidadConAnio);
    $sheet->setCellValue($col++ . $row, $dato['descripcion']);

    $colMesInicio = $col;
    foreach ($meses as $mes) {
        $suma = (int)$dato[$mes . '_1era'] + (int)$dato[$mes . '_sub'];
        $sheet->setCellValue($col++ . $row, $suma);
    }

    $colMesFin = chr(ord($colMesInicio) + count($meses) - 1);
    $formula = "=SUM($colMesInicio$row:$colMesFin$row)";
    $columnaTotal = $col; // Ej. P
    $sheet->setCellValue($columnaTotal . $row, $formula);
    $sheet->setCellValue(++$columnaTotal . $row, $dato['anio']);

    $key = $dato['descripcion'] . ' (' . $dato['anio'] . ')';
    if (!isset($mapaDescripcionTotales[$key])) {
        $mapaDescripcionTotales[$key] = [];
    }
    $mapaDescripcionTotales[$key][] = $columnaTotal = chr(ord($columnaTotal) - 1) . $row;

    $row++;
}
$ultimaFila = $row - 1;

$tituloAnios = count($anioFiltro) > 0 ? ' (' . implode(', ', $anioFiltro) . ')' : '';
///////////////////////////
// GRÁFICO 1: Total por mes por especialidad
///////////////////////////
$seriesMeses = [];
$labelsMeses = [];
$categoriasMeses = [
    new DataSeriesValues('String', "'Especialidades'!\$D\$1:\$O\$1", null, 12)
];

for ($i = 2; $i <= $ultimaFila; $i++) {
    $labelsMeses[] = new DataSeriesValues('String', "'Especialidades'!\$B\$$i", null, 1); // Columna B: Especialidad
    $seriesMeses[] = new DataSeriesValues('Number', "'Especialidades'!\$D\$$i:\$O\$$i", null, 12); // Valores mensuales
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
$titleMeses = new Title('Gráfico - Mensual por Especialidad' . $tituloAnios);

$chartMeses = new Chart(
    'chartMeses',
    $titleMeses,
    $legend,
    $plotAreaMeses,
    true,
    0,
    null, // <-- Eje X sin título
    null  // <-- Eje Y sin título
);


$chartMeses->setTopLeftPosition('A' . ($ultimaFila + 3));
$chartMeses->setBottomRightPosition('O' . ($ultimaFila + 20));
$sheet->addChart($chartMeses);

//////////////////////////////////////////////
// GRÁFICO 2: Mensual por División (LÍNEAS)
//////////////////////////////////////////////

// Agrupar los datos: SUMAR por mes agrupado por descripción (división) + año
$mapaDivisionAnio = [];

$row = 2;
foreach ($datosFiltrados as $dato) {
    $key = $dato['descripcion'] . ' (' . $dato['anio'] . ')';

    if (!isset($mapaDivisionAnio[$key])) {
        $mapaDivisionAnio[$key] = [];
    }

    foreach ($meses as $i => $mes) {
        $colLetra = chr(ord('D') + $i); // D hasta O
        $celda = $colLetra . $row;

        if (!isset($mapaDivisionAnio[$key][$mes])) {
            $mapaDivisionAnio[$key][$mes] = [];
        }

        $mapaDivisionAnio[$key][$mes][] = $celda;
    }

    $row++;
}

// === Escribir tabla auxiliar ===
$filaInicioDivMes = $ultimaFila + 22;
$col = 'A';
$sheet->setCellValue($col++ . $filaInicioDivMes, 'División (Año)');
foreach ($mesesTitulos as $m) {
    $sheet->setCellValue($col++ . $filaInicioDivMes, $m);
}
$sheet->setCellValue($col . $filaInicioDivMes, 'Total'); // Solo para tabla, no se grafica

// Insertar datos en fila
$fila = $filaInicioDivMes + 1;

foreach ($mapaDivisionAnio as $divAnio => $valoresMes) {
    $col = 'A';
    $sheet->setCellValue($col++ . $fila, $divAnio);

    $celdasParaTotal = [];

    foreach ($meses as $mes) {
        $celdas = $valoresMes[$mes];
        if (count($celdas) === 1) {
            $formula = "={$celdas[0]}";
        } else {
            $formula = '=' . implode('+', $celdas);
        }

        $sheet->setCellValue($col++ . $fila, $formula);
        $celdasParaTotal = array_merge($celdasParaTotal, $celdas);
    }

    // Total general por fila
    if (count($celdasParaTotal) === 1) {
        $sheet->setCellValue($col . $fila, "={$celdasParaTotal[0]}");
    } else {
        $sheet->setCellValue($col . $fila, '=' . implode('+', $celdasParaTotal));
    }

    $fila++;
}

$filaFinDivMes = $fila - 1;

// === Crear gráfico ===
// Categorías: Encabezados de los meses
$categoriasDivMes = [new DataSeriesValues(
    'String',
    "'Especialidades'!\$B\$" . ($filaInicioDivMes) . ":\$M\$" . ($filaInicioDivMes),
    null,
    12
)];

// Series: Una línea por división (sin incluir la columna Total)
$seriesDivMes = [];
$labelsDivMes = [];

$filaSerie = $filaInicioDivMes + 1;
foreach ($mapaDivisionAnio as $divAnio => $valoresMes) {
    $labelsDivMes[] = new DataSeriesValues('String', "'Especialidades'!\$A\$$filaSerie", null, 1);
    $seriesDivMes[] = new DataSeriesValues('Number', "'Especialidades'!\$B\$$filaSerie:\$M\$$filaSerie", null, 12);
    $filaSerie++;
}

// Crear gráfico
$dataSeriesDivMes = new DataSeries(
    DataSeries::TYPE_LINECHART,
    DataSeries::GROUPING_STANDARD,
    range(0, count($labelsDivMes) - 1),
    $labelsDivMes,
    $categoriasDivMes,
    $seriesDivMes
);

$plotAreaDivMes = new PlotArea(null, [$dataSeriesDivMes]);
$titleDivMes = new Title('Gráfico - Mensual por División' . $tituloAnios);

$chartDivMes = new Chart(
    'chartDivMes',
    $titleDivMes,
    $legend,
    $plotAreaDivMes,
    true,
    0,
    null,
    null
);

// Posición visual
$chartDivMes->setTopLeftPosition("A" . ($filaFinDivMes + 2));
$chartDivMes->setBottomRightPosition("O" . ($filaFinDivMes + 18));
$sheet->addChart($chartDivMes);


///////////////////////////
// GRÁFICO 3: Anual por división
///////////////////////////
$filaInicio = $filaFinDivMes + 20;
$col = 'A';
$sheet->setCellValue($col++ . $filaInicio, 'División (Año)');
$sheet->setCellValue($col . $filaInicio, 'Total');

$fila = $filaInicio + 1;
foreach ($mapaDescripcionTotales as $desc => $celdasTotales) {
    $sheet->setCellValue("A$fila", $desc);
    if (count($celdasTotales) === 1) {
        $sheet->setCellValue("B$fila", "={$celdasTotales[0]}");
    } else {
        $sumaFormulas = '=' . implode('+', $celdasTotales);
        $sheet->setCellValue("B$fila", $sumaFormulas);
    }
    $fila++;
}
$filaFin = $fila - 1;

// Crear gráfico total general
$categories2 = [new DataSeriesValues('String', "'Especialidades'!\$A\$" . ($filaInicio + 1) . ":\$A\$$filaFin", null, count($mapaDescripcionTotales))];
$values2 = [new DataSeriesValues('Number', "'Especialidades'!\$B\$" . ($filaInicio + 1) . ":\$B\$$filaFin", null, count($mapaDescripcionTotales))];
$labels2 = [new DataSeriesValues('String', "'Especialidades'!\$B\$$filaInicio", null, 1)];

$series2 = new DataSeries(
    DataSeries::TYPE_BARCHART,
    DataSeries::GROUPING_CLUSTERED,
    [0],
    $labels2,
    $categories2,
    $values2
);

$sheet->setCellValue("A" . ($filaFin + 20), '💡 Consejo: clic derecho en las barras → "Agregar etiquetas de datos"');

$plotArea2 = new PlotArea(null, [$series2]);
$title2 = new Title('Gráfico - Anual por División' . $tituloAnios);

$chart2 = new Chart('chart2', $title2, $legend, $plotArea2, true, 0, null, null);


$chart2->setTopLeftPosition("A" . ($filaFin + 2));
$chart2->setBottomRightPosition("H" . ($filaFin + 18));
$sheet->addChart($chart2);

// === Enviar archivo ===
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="tabla_y_graficos_especialidades.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->setIncludeCharts(true);
$writer->save('php://output');
exit;

<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../modelos/egreso.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// === Filtros desde POST ===
$anioFiltro = $_POST['anio'] ?? '';
$especialidadFiltro = $_POST['especialidad'] ?? '';
$divisionFiltro = $_POST['division'] ?? '';

// === Obtener datos ===
$datos = Egreso::listar();

$anioArray = $anioFiltro !== '' ? explode(',', $anioFiltro) : [];
$especialidadArray = $especialidadFiltro !== '' ? array_map('strtolower', array_map('trim', explode(',', $especialidadFiltro))) : [];
$divisionArray = $divisionFiltro !== '' ? array_map('strtolower', array_map('trim', explode(',', $divisionFiltro))) : [];

$datosFiltrados = array_filter($datos, function ($item) use ($anioArray, $especialidadArray, $divisionArray) {
    if (!empty($anioArray) && !in_array($item['anio'], $anioArray)) return false;
    if (!empty($especialidadArray) && !in_array(strtolower(trim($item['especialidad'])), $especialidadArray)) return false;
    if (!empty($divisionArray) && !in_array(strtolower(trim($item['division'])), $divisionArray)) return false;
    return true;
});

// === Crear hoja Excel ===
$spreadsheet = new Spreadsheet();
$sheetDatos = $spreadsheet->getActiveSheet();
$sheetDatos->setTitle('Egreso');

$sheetEspecialidades = $spreadsheet->createSheet();
$sheetEspecialidades->setTitle('Gráficos Especialidad');

$sheetDivisiones = $spreadsheet->createSheet();
$sheetDivisiones->setTitle('Gráficos División');

$sheetResumen = $spreadsheet->createSheet();
$sheetResumen->setTitle('Resumen Total');

$meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$mesesTitulos = array_map('ucfirst', $meses);

// Encabezados hoja datos
$headers = ['Clave', 'Especialidad', 'División'];
$col = 'A';
foreach ($headers as $h) $sheetDatos->setCellValue($col++ . '1', $h);
foreach ($mesesTitulos as $m) $sheetDatos->setCellValue($col++ . '1', $m);
$sheetDatos->setCellValue($col++ . '1', 'Total');
$sheetDatos->setCellValue($col . '1', 'Año');

// Datos hoja datos
$row = 2;
foreach ($datosFiltrados as $dato) {
    $sheetDatos->setCellValue('A' . $row, $dato['clave']);
    $sheetDatos->setCellValue('B' . $row, $dato['especialidad']);
    $sheetDatos->setCellValue('C' . $row, $dato['division']);

    $colActual = 'D';
    foreach ($meses as $mes) {
        $valor = (int)$dato[$mes];
        $sheetDatos->setCellValue($colActual . $row, $valor);
        $colActual++;
    }

    $sheetDatos->setCellValue('P' . $row, "=SUM(D{$row}:O{$row})");
    $sheetDatos->setCellValue('Q' . $row, $dato['anio']);
    $row++;
}
$ultimaFila = $row - 1;

// Leyenda y categorías comunes
$legend = new Legend(Legend::POSITION_RIGHT, null, false);
$categoriasMeses = [new DataSeriesValues('String', "'Egreso'!\$D\$1:\$O\$1", null, 12)];

// Agrupar filas por año
$filasPorAnio = [];
for ($i = 2; $i <= $ultimaFila; $i++) {
    $anio = $sheetDatos->getCell("Q$i")->getValue();
    $filasPorAnio[$anio][] = $i;
}

// === GRÁFICO 1: Gráficos Especialidades con tabla y título ===
$filaGraficoEspecialidad = 1;
$espacioEntreBloques = 25;

foreach ($filasPorAnio as $anio => $filas) {
    $agrupadoEspecialidad = [];

    foreach ($filas as $filaIndex) {
        $especialidad = $sheetDatos->getCell("B$filaIndex")->getValue();
        $agrupadoEspecialidad[$especialidad][] = $filaIndex;
    }

    // Título
    $sheetEspecialidades->mergeCells("A{$filaGraficoEspecialidad}:M{$filaGraficoEspecialidad}");
    $sheetEspecialidades->setCellValue("A{$filaGraficoEspecialidad}", "Totales mensuales por especialidad – Año $anio");
    $sheetEspecialidades->getStyle("A{$filaGraficoEspecialidad}")->applyFromArray([
        'font' => ['bold' => true, 'size' => 14],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => [
            'outline' => ['borderStyle' => Border::BORDER_THIN],
            'inside' => ['borderStyle' => Border::BORDER_THIN],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'DDEBF7']
        ]
    ]);

    $filaTablaInicio = $filaGraficoEspecialidad + 1;

    // Encabezado tabla
    $sheetEspecialidades->setCellValue("A{$filaTablaInicio}", "Especialidad");
    $colIndex = 2;
    foreach ($mesesTitulos as $mesNombre) {
        $colLetra = Coordinate::stringFromColumnIndex($colIndex++);
        $sheetEspecialidades->setCellValue("{$colLetra}{$filaTablaInicio}", $mesNombre);
    }
    $rangoEncabezado = "A{$filaTablaInicio}:" . Coordinate::stringFromColumnIndex(13) . $filaTablaInicio;
    $sheetEspecialidades->getStyle($rangoEncabezado)->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'DDEBF7']
        ],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ]);

    // Llenar tabla
    $labels = [];
    $series = [];
    $filaActual = $filaTablaInicio + 1;

    foreach ($agrupadoEspecialidad as $especialidad => $filasEspecialidad) {
        $sheetEspecialidades->setCellValue("A{$filaActual}", $especialidad);

        for ($i = 0; $i < 12; $i++) {
            $colMes = Coordinate::stringFromColumnIndex($i + 4); // D = enero
            $celulasMes = [];

            foreach ($filasEspecialidad as $filaIndex) {
                $celulasMes[] = "Egreso!{$colMes}{$filaIndex}";
            }

            $formula = count($celulasMes) === 1 ? "={$celulasMes[0]}" : '=' . implode('+', $celulasMes);
            $colTabla = Coordinate::stringFromColumnIndex($i + 2); // B en adelante
            $sheetEspecialidades->setCellValue("{$colTabla}{$filaActual}", $formula);
        }

        // Bordes fila
        $rangoFila = "A{$filaActual}:" . Coordinate::stringFromColumnIndex(13) . $filaActual;
        $sheetEspecialidades->getStyle($rangoFila)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);

        // Para gráfico
        $labels[] = new DataSeriesValues('String', null, null, 1, [$especialidad]);
        $rangoGrafico = "'Gráficos Especialidad'!\$B\${$filaActual}:\$M\${$filaActual}";
        $series[] = new DataSeriesValues('Number', $rangoGrafico, null, 12);

        $filaActual++;
    }

    // Autoajustar columnas
    for ($i = 1; $i <= 13; $i++) {
        $sheetEspecialidades->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
    }

    // Insertar gráfico debajo tabla
    $grafico = new DataSeries(
        DataSeries::TYPE_LINECHART,
        DataSeries::GROUPING_STANDARD,
        range(0, count($labels) - 1),
        $labels,
        $categoriasMeses,
        $series
    );

    $plotArea = new PlotArea(null, [$grafico]);
    $chart = new Chart(
        "grafico_especialidad_$anio",
        new Title("Especialidades - Año $anio"),
        $legend,
        $plotArea,
        true,
        0,
        new Title('Meses'),
        new Title('Días Egreso')
    );

    $filaGraficoTop = $filaActual + 1;
    $chart->setTopLeftPosition("B{$filaGraficoTop}");
    $chart->setBottomRightPosition("P" . ($filaGraficoTop + 17));
    $sheetEspecialidades->addChart($chart);

    // Espacio para siguiente bloque
    $filaGraficoEspecialidad = $filaGraficoTop + $espacioEntreBloques;
}

// === GRÁFICO 2: Gráficos División con tabla y título con borde ===
$filaGraficoDivision = 1;

foreach ($filasPorAnio as $anio => $filasAnio) {
    $agrupadoDivision = [];

    foreach ($filasAnio as $filaIndex) {
        $division = $sheetDatos->getCell("C$filaIndex")->getValue();
        $agrupadoDivision[$division][] = $filaIndex;
    }

    // Título bloque con formato
    $sheetDivisiones->mergeCells("A{$filaGraficoDivision}:M{$filaGraficoDivision}");
    $sheetDivisiones->setCellValue("A{$filaGraficoDivision}", "Totales mensuales por división – Año $anio");
    $sheetDivisiones->getStyle("A{$filaGraficoDivision}")->applyFromArray([
        'font' => ['bold' => true, 'size' => 14],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => [
            'outline' => ['borderStyle' => Border::BORDER_THIN],
            'inside' => ['borderStyle' => Border::BORDER_THIN],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'DDEBF7']
        ]
    ]);

    $filaTablaInicio = $filaGraficoDivision + 1;

    // Encabezado tabla
    $sheetDivisiones->setCellValue("A{$filaTablaInicio}", "División");
    $colIndex = 2;
    foreach ($mesesTitulos as $mesNombre) {
        $colLetra = Coordinate::stringFromColumnIndex($colIndex++);
        $sheetDivisiones->setCellValue("{$colLetra}{$filaTablaInicio}", $mesNombre);
    }

    $rangoEncabezado = "A{$filaTablaInicio}:" . Coordinate::stringFromColumnIndex(13) . $filaTablaInicio;
    $sheetDivisiones->getStyle($rangoEncabezado)->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'DDEBF7']
        ],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ]);

    // Llenar tabla
    $labels = [];
    $series = [];
    $filaActual = $filaTablaInicio + 1;

    foreach ($agrupadoDivision as $division => $filas) {
        $sheetDivisiones->setCellValue("A{$filaActual}", $division);

        for ($i = 0; $i < 12; $i++) {
            $colMes = Coordinate::stringFromColumnIndex($i + 4); // D = enero
            $celulasMes = [];

            foreach ($filas as $filaIndex) {
                $celulasMes[] = "Egreso!{$colMes}{$filaIndex}";
            }

            $formula = count($celulasMes) === 1 ? "={$celulasMes[0]}" : '=' . implode('+', $celulasMes);
            $colTabla = Coordinate::stringFromColumnIndex($i + 2); // B en adelante
            $sheetDivisiones->setCellValue("{$colTabla}{$filaActual}", $formula);
        }

        // Bordes fila
        $rangoFila = "A{$filaActual}:" . Coordinate::stringFromColumnIndex(13) . $filaActual;
        $sheetDivisiones->getStyle($rangoFila)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);

        // Para gráfico
        $labels[] = new DataSeriesValues('String', null, null, 1, [$division]);
        $rangoGrafico = "'Gráficos División'!\$B\${$filaActual}:\$M\${$filaActual}";
        $series[] = new DataSeriesValues('Number', $rangoGrafico, null, 12);

        $filaActual++;
    }

    // Autoajustar columnas
    for ($i = 1; $i <= 13; $i++) {
        $sheetDivisiones->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
    }

    // Insertar gráfico debajo tabla
    $grafico = new DataSeries(
        DataSeries::TYPE_LINECHART,
        DataSeries::GROUPING_STANDARD,
        range(0, count($labels) - 1),
        $labels,
        $categoriasMeses,
        $series
    );

    $plotArea = new PlotArea(null, [$grafico]);
    $chart = new Chart(
        "grafico_division_$anio",
        new Title("Divisiones - Año $anio"),
        $legend,
        $plotArea,
        true,
        0,
        new Title('Meses'),
        new Title('Días Egreso')
    );

    $filaGraficoTop = $filaActual + 1;
    $chart->setTopLeftPosition("B{$filaGraficoTop}");
    $chart->setBottomRightPosition("P" . ($filaGraficoTop + 17));
    $sheetDivisiones->addChart($chart);

    // Espacio para siguiente bloque
    $filaGraficoDivision = $filaGraficoTop + $espacioEntreBloques;
}

// === GRÁFICO 3: Resumen Total por División ===
$filaInicio = 1;
$sheetResumen->setCellValue("A{$filaInicio}", 'División (Año)');
$sheetResumen->setCellValue("B{$filaInicio}", 'Total');

$agrupado = [];
$filaIndex = 2;

foreach ($datosFiltrados as $dato) {
    $clave = ($dato['division'] ?: $dato['especialidad']) . " ({$dato['anio']})";
    $celdaTotal = "Egreso!P{$filaIndex}";

    if (!isset($agrupado[$clave])) {
        $agrupado[$clave] = [];
    }
    $agrupado[$clave][] = $celdaTotal;

    $filaIndex++;
}

$fila = $filaInicio + 1;
foreach ($agrupado as $division => $celdas) {
    $sheetResumen->setCellValue("A$fila", $division);
    $formula = count($celdas) === 1 ? "={$celdas[0]}" : '=' . implode('+', $celdas);
    $sheetResumen->setCellValue("B$fila", $formula);
    $fila++;
}
$filaFin = $fila - 1;

$categories2 = [new DataSeriesValues('String', "'Resumen Total'!\$A\$" . ($filaInicio + 1) . ":\$A\$$filaFin", null, count($agrupado))];
$values2 = [new DataSeriesValues('Number', "'Resumen Total'!\$B\$" . ($filaInicio + 1) . ":\$B\$$filaFin", null, count($agrupado))];
$labels2 = [new DataSeriesValues('String', "'Resumen Total'!\$B\$$filaInicio", null, 1)];

$series2 = new DataSeries(
    DataSeries::TYPE_BARCHART,
    DataSeries::GROUPING_CLUSTERED,
    [0],
    $labels2,
    $categories2,
    $values2
);

$plotArea2 = new PlotArea(null, [$series2]);
$chart2 = new Chart('chart2', new Title('Días Egreso anual por división'), $legend, $plotArea2, true, 0, new Title('División'), new Title('Total'));

$chart2->setTopLeftPosition("D" . ($filaFin + 2));
$chart2->setBottomRightPosition("K" . ($filaFin + 18));
$sheetResumen->addChart($chart2);

// === Descargar archivo ===
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="tabla_y_graficos-Egresos.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->setIncludeCharts(true);
$writer->save('php://output');
exit;

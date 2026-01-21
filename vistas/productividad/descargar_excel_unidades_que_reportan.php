<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../modelos/Urgencia.php';
require_once '../../modelos/Paramedicos.php';
require_once '../../modelos/Especialidad_Ocasion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// Evitar cualquier salida antes de headers
ob_start();

$anioFiltro = $_POST['anio'] ?? '';
$divisionFiltro = $_POST['division'] ?? '';

$anios = $anioFiltro !== '' ? explode(',', $anioFiltro) : [];
$divisiones = $divisionFiltro !== '' ? explode(',', $divisionFiltro) : [];

$meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$mesesTitulos = array_map('ucfirst', $meses);

function procesarDatos($datos, $divisionName, $meses) {
    $resultado = [];
    foreach ($datos as $fila) {
        $anio = $fila[0];
        $registro = ['unidad' => "{$divisionName} ({$anio})", 'anio' => $anio];
        foreach ($meses as $index => $mes) {
            $registro[$mes] = isset($fila[$index + 1]) ? (int)$fila[$index + 1] : 0;
        }
        $resultado[] = $registro;
    }
    return $resultado;
}

$datosUrgencias = procesarDatos(Urgencia::obtenerTotalesPorMes(), 'Urgencias', $meses);
$datosParamedicos = procesarDatos(Paramedicos::obtenerTotalesPorMes(), 'Paramédicos', $meses);
$datosEspecialidades = procesarDatos(Especialidad_Ocasion::obtenerTotalesPorMes(), 'Especialidades', $meses);

$datosCombinados = array_merge($datosUrgencias, $datosParamedicos, $datosEspecialidades);

if (count($anios) > 0) {
    $datosCombinados = array_filter($datosCombinados, fn($d) => in_array($d['anio'], $anios));
}
if (count($divisiones) > 0) {
    $datosCombinados = array_filter($datosCombinados, fn($d) => array_filter($divisiones, fn($div) => str_contains($d['unidad'], $div)));
}


$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Productividad Total');

// Encabezados
$encabezados = array_merge(['Unidad'], $mesesTitulos, ['Total Anual', 'Año']);
$sheet->fromArray($encabezados, NULL, 'A1');

// Datos
$fila = 2;
foreach ($datosCombinados as $dato) {
    $filaDatos = [$dato['unidad']];
    foreach ($meses as $mes) {
        $filaDatos[] = $dato[$mes] ?? 0;
    }
    // Fórmula para total anual (de columnas B a M)
    $colTotal = '=SUM(B'.$fila.':M'.$fila.')';
    $filaDatos[] = $colTotal;
    $filaDatos[] = $dato['anio'];
    $sheet->fromArray($filaDatos, NULL, "A{$fila}");
    $fila++;
}
$ultimaFila = $fila - 1;

// --- Gráfico de líneas mensual ---
$labelsSeries = [];
$dataSeriesValues = [];
for ($i = 2; $i <= $ultimaFila; $i++) {
    $labelsSeries[] = new DataSeriesValues('String', "'Productividad Total'!\$A\${$i}", null, 1);
    $dataSeriesValues[] = new DataSeriesValues('Number', "'Productividad Total'!\$B\${$i}:\$M\${$i}", null, 12);
}
$categories = [new DataSeriesValues('String', "'Productividad Total'!\$B\$1:\$M\$1", null, 12)];

$seriesMensual = new DataSeries(
    DataSeries::TYPE_LINECHART,
    DataSeries::GROUPING_STANDARD,
    range(0, count($dataSeriesValues) - 1),
    $labelsSeries,
    $categories,
    $dataSeriesValues
);
$plotAreaMensual = new PlotArea(null, [$seriesMensual]);
$legendMensual = new Legend(Legend::POSITION_RIGHT, null, false);
$titleMensual = new Title('Gráfico - Total Mensual');
$chartMensual = new Chart('chart_mensual', $titleMensual, $legendMensual, $plotAreaMensual);
$chartMensual->setTopLeftPosition('A' . ($ultimaFila + 3));
$chartMensual->setBottomRightPosition('O' . ($ultimaFila + 20));
$sheet->addChart($chartMensual);

// --- Gráfico de barras Total Anual ---
$labelsAnual = [new DataSeriesValues('String', "'Productividad Total'!\$A\$2:\$A\${$ultimaFila}", null, $ultimaFila - 1)];
$dataAnual = [new DataSeriesValues('Number', "'Productividad Total'!\$N\$2:\$N\${$ultimaFila}", null, $ultimaFila - 1)];
$seriesTitle = [new DataSeriesValues('String', '"Total"', null, 1)];

$seriesAnual = new DataSeries(
    DataSeries::TYPE_BARCHART,
    DataSeries::GROUPING_STANDARD,
    [0],
    $seriesTitle,   // <- este es el título que se muestra en la leyenda del gráfico
    $labelsAnual,
    $dataAnual
);


$plotAreaAnual = new PlotArea(null, [$seriesAnual]);
$legendAnual = new Legend(Legend::POSITION_RIGHT, null, false);
$titleAnual = new Title('Gráfico - Total Anual');
$chartAnual = new Chart('chart_anual', $titleAnual, $legendAnual, $plotAreaAnual);
$chartAnual->setTopLeftPosition('A' . ($ultimaFila + 21));
$chartAnual->setBottomRightPosition('O' . ($ultimaFila + 35));
$sheet->addChart($chartAnual);

// --- Enviar archivo ---
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="productividad_total.xlsx"');
header('Cache-Control: max-age=0');
header('Expires: 0');
header('Pragma: public');

$writer = new Xlsx($spreadsheet);
$writer->setIncludeCharts(true);
ob_end_clean(); // Limpiar buffer antes de enviar el archivo
$writer->save('php://output');
exit;


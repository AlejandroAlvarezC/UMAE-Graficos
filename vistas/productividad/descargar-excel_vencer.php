<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../../modelos/Vencer.php'; // Ajusta la ruta si es diferente

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;


function limpiarFiltro($valor) {
    if (empty($valor)) return [];
    return array_filter(array_map('trim', explode(',', $valor)));
}

// === Filtros recibidos ===
$campos = [
    'folio', 'evento', 'ini_paciente', 'seguridad_social', 'edad', 'sexo', 'diagnostico',
    'fecha_evento', 'fecha_noti', 'turno', 'servicio', 'categoria',
    'proceso', 'definicion', 'descripcion', 'estatus', 'anio'
];

$filtros = [];
foreach ($campos as $campo) {
    $filtros[$campo] = limpiarFiltro($_POST[$campo] ?? '');
}

// === Obtener los datos completos ===
$datos = Vencer::listar(); // Asegúrate de tener este método

// === Filtrar datos ===
$datosFiltrados = array_filter($datos, function ($fila) use ($filtros) {
    foreach ($filtros as $campo => $valores) {
        if (empty($valores)) continue;
        $valorFila = strtolower(trim((string)($fila[$campo] ?? '')));
        if (!in_array('NULO', $valores) && !in_array($valorFila, array_map('strtolower', $valores))) {
            return false;
        }
    }
    return true;
});

// === Determinar columnas activas (excluir las con "NULO") ===
$columnasActivas = [];
foreach ($filtros as $campo => $valores) {
    if (!in_array('NULO', $valores)) {
        $columnasActivas[] = $campo;
    }
}

// === Crear Excel ===
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Eventos VENCER');

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

function getColumnLetter($index) {
    return Coordinate::stringFromColumnIndex($index);
}

// Encabezados
$colIndex = 1;
foreach ($columnasActivas as $campo) {
    $colLetra = getColumnLetter($colIndex);
    $sheet->getCell($colLetra . '1')->setValue(ucfirst($campo));
    $colIndex++;
}

// Filas
$row = 2;
foreach ($datosFiltrados as $fila) {
    $colIndex = 1;
    foreach ($columnasActivas as $campo) {
        $colLetra = getColumnLetter($colIndex);
        $celda = $colLetra . $row;
        $valor = $fila[$campo] ?? '';
     if ($campo === 'seguridad_social') {
    $valorEntero = is_numeric($valor) ? (int)$valor : 0;
    $sheet->getCell($celda)->setValueExplicit($valorEntero, DataType::TYPE_NUMERIC);
    $sheet->getStyle($celda)->getNumberFormat()->setFormatCode('0'); // Sin comas ni decimales
} else {
    $sheet->getCell($celda)->setValue($valor);
}


        $colIndex++;
    }
    $row++;
}


// Descargar Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="vencer_reporte.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;




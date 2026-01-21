<?php
require_once '../modelos/Especialidad_Ocasion.php';
header('Content-Type: application/json');

$clave = $_GET['clave'] ?? null;
$anio = $_GET['anio'] ?? null;

if (!$clave || !$anio) {
    echo json_encode(['existe' => false, 'error' => 'Clave o año vacíos']);
    exit;
}

$conexion = new Conexion();
$claveEscapada = $conexion->getConexion()->real_escape_string($clave);
$anioEntero = intval($anio);

$sql = "SELECT * FROM especialidades WHERE clave = '$claveEscapada' AND anio = $anioEntero";
$resultado = $conexion->consultar($sql);
$conexion->cerrar();

if (!$resultado || count($resultado) === 0) {
    echo json_encode(['existe' => false, 'error' => 'No encontrado']);
    exit;
}

$esp = $resultado[0];

$response = [
    'existe' => true,
    'especialidad' => $esp[2],
    'descripcion' => $esp[3],
    'anio' => $esp[28],
];

$meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
$i = 4;
foreach ($meses as $mes) {
    $response[$mes . '_1era'] = $esp[$i++];
    $response[$mes . '_sub'] = $esp[$i++];
}

echo json_encode($response);

?>





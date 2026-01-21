<?php
require_once '../modelos/Urgencia.php';
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

$sql = "SELECT * FROM urgencias WHERE clave = '$claveEscapada' AND anio = $anioEntero";
$resultado = $conexion->consultar($sql);
$conexion->cerrar();

if (!$resultado || count($resultado) === 0) {
    echo json_encode(['existe' => false, 'error' => 'No encontrado']);
    exit;
}

$urg = $resultado[0];

$response = [
    'existe' => true,
    'especialidad' => $urg[2],
    'division' => $urg[3],
    'anio' => $urg[16],
];

$meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
for ($i = 0; $i < count($meses); $i++) {
    $response[$meses[$i]] = $urg[4 + $i]; // mes desde columna 4 en adelante
}

echo json_encode($response);


?>
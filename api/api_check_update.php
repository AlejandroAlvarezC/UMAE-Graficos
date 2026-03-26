<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// Buscamos un archivo de texto donde guardaremos la fecha del último CSV
$archivo = 'ultima_actualizacion.txt';

// Si el archivo existe, leemos la fecha. Si no, devolvemos 0.
$fecha = file_exists($archivo) ? file_get_contents($archivo) : "0";

echo json_encode(['ultima_actualizacion' => $fecha]);
?>
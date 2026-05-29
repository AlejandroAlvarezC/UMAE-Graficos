<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Revisar si React nos mandó el año y el indicador que quiere buscar
if (!isset($_GET['anio']) || !isset($_GET['indicador'])) {
    echo json_encode(["success" => false, "message" => "Faltan parámetros obligatorios (año o indicador)"]);
    exit;
}

$anio = (int) $_GET['anio'];
$indicador = trim((string) $_GET['indicador']); 

$host = 'sql112.infinityfree.com';
$dbname = 'if0_41125231_vencer'; 
$username = 'if0_41125231';
$password = 'DEtK59bqZzA';

try {
    $conn = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Filtrar estrictamente por año Y por el indicador activo
    $stmt = $conn->prepare("SELECT mes, numerador, denominador, porcentaje FROM indicadores_hosp WHERE anio = :anio AND indicador = :indicador ORDER BY id ASC");
    $stmt->bindParam(':anio', $anio);
    $stmt->bindParam(':indicador', $indicador);
    $stmt->execute();

    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($resultados) > 0) {
        $datosFormateados = array_map(function($fila) {
            return [
                "mes" => $fila['mes'],
                "numerador" => (int)$fila['numerador'],
                "denominador" => (int)$fila['denominador'],
                "porcentaje" => (float)$fila['porcentaje']
            ];
        }, $resultados);

        echo json_encode(["success" => true, "datos" => $datosFormateados]);
        exit;
    } else {
        echo json_encode(["success" => false, "datos" => []]);
        exit;
    }

} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    exit;
}
?>
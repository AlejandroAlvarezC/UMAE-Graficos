<?php
// Permitir peticiones (CORS) y responder en JSON
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Recibir el paquete JSON que nos mandó React
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true);

// Validar que nos hayan mandado el año y el array de "datosBatch"
if (!isset($data['anio']) || !isset($data['datosBatch']) || empty($data['datosBatch'])) {
    echo json_encode(["success" => false, "message" => "Faltan datos o el lote está vacío."]);
    exit;
}

$anio = (int) $data['anio'];
$datosBatch = $data['datosBatch'];

// ==========================================
// CONFIGURACIÓN BD
// ==========================================
$host = 'sql112.infinityfree.com';
$dbname = 'if0_41125231_vencer'; 
$username = 'if0_41125231';
$password = 'DEtK59bqZzA';

try {
    $conn = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn->beginTransaction();

    // 1. Extraer cuáles indicadores (HOSP_01, HOSP_02, etc.) vienen en este Excel
    $indicadoresEnviados = [];
    foreach ($datosBatch as $fila) {
        $ind = $fila['indicador'];
        if (!in_array($ind, $indicadoresEnviados)) {
            $indicadoresEnviados[] = $ind;
        }
    }

    // 2. Borrar de la BD *solo* los indicadores que acabamos de extraer para ese año.
    // Así evitamos datos duplicados si resuben el mismo Excel.
    foreach ($indicadoresEnviados as $ind) {
        $stmtDelete = $conn->prepare("DELETE FROM indicadores_hosp WHERE anio = :anio AND indicador = :indicador");
        $stmtDelete->execute([
            ':anio' => $anio,
            ':indicador' => $ind
        ]);
    }

    // 3. Insertar el lote masivo completo
    $query = "INSERT INTO indicadores_hosp (anio, indicador, mes, numerador, denominador, porcentaje) 
              VALUES (:anio, :indicador, :mes, :numerador, :denominador, :porcentaje)";
    $stmtInsert = $conn->prepare($query);

    foreach ($datosBatch as $fila) {
        $stmtInsert->execute([
            ':anio' => $anio,
            ':indicador' => $fila['indicador'],
            ':mes' => $fila['mes'],
            ':numerador' => $fila['numerador'],
            ':denominador' => $fila['denominador'],
            ':porcentaje' => $fila['porcentaje']
        ]);
    }

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Lote de datos guardado correctamente"]);
    exit;

} catch(PDOException $e) {
    if($conn) {
        $conn->rollBack();
    }
    echo json_encode(["success" => false, "message" => "Error en la BD: " . $e->getMessage()]);
    exit;
}
?>
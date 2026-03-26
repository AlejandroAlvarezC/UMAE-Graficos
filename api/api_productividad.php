<?php
// 1. ACTIVAR COMPRESIÓN GZIP (Reduce el tamaño del archivo un 90%)
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
    ob_start("ob_gzhandler");
} else {
    ob_start();
}

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

ini_set('memory_limit', '256M');
set_time_limit(300); 

$host = 'sql112.infinityfree.com';
$dbname = 'if0_41125231_vencer'; 
$username = 'if0_41125231';
$password = 'DEtK59bqZzA'; // Pon tu contraseña real aquí

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. OPTIMIZACIÓN DE CONSULTA: Solo pedimos lo que React usa.
    // Ignoramos columnas basura o de control para que el JSON pese mucho menos.
    $sql = "SELECT 
                division, 
                especialidad, 
                matricula_medico, 
                consultorio, 
                fecha_atencion, 
                mes, 
                anio, 
                turno, 
                citado, 
                primera_vez, 
                diagnostico_principal 
            FROM productividad_externa";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    echo '['; 
    $primeraFila = true;
    
    // FETCH_ASSOC con Streaming
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!$primeraFila) {
            echo ','; 
        }
        echo json_encode($row); 
        $primeraFila = false;
    }
    
    echo ']'; 

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de BD: ' . $e->getMessage()]);
}

// Enviar el buffer comprimido al navegador
ob_end_flush();
?>
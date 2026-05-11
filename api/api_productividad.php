<?php
// 1. ACTIVAR COMPRESIÓN GZIP 
if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
    ob_start("ob_gzhandler");
} else {
    ob_start();
}

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// Le damos pulmones más grandes a PHP por si la BD crece mucho
ini_set('memory_limit', '512M');
set_time_limit(300); 

$host = 'sql112.infinityfree.com';
$dbname = 'if0_41125231_vencer'; 
$username = 'if0_41125231';
$password = 'DEtK59bqZzA';

try {
    // CAMBIO CLAVE 1: utf8mb4 para igualar cómo se guardaron y no romper los acentos
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4"); // Refuerzo de codificación

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
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!$primeraFila) {
            echo ','; 
        }
        
        // CAMBIO CLAVE 2: JSON_INVALID_UTF8_SUBSTITUTE
        // Si hay una letra corrupta de Excel, no destruye el registro, solo la limpia
        $jsonStr = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        
        // Prevención en caso de un error extremo en la fila
        if ($jsonStr === false) {
            $jsonStr = json_encode(['error' => 'fila_corrupta']);
        }
        
        echo $jsonStr; 
        $primeraFila = false;
    }
    
    echo ']'; 

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de BD: ' . $e->getMessage()]);
}

ob_end_flush();
?>
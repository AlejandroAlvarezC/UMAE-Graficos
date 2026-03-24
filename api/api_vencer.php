<?php
// 1. ERRORES (Solo para desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. ENCABEZADOS DE SEGURIDAD (El "Pase de Invitado" / CORS)
header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// 3. MANEJAR LA PRE-GUNTA DEL NAVEGADOR (Preflight OPTIONS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 4. INICIAR SESIÓN
session_start();


// 5. INTENTAR OBTENER LOS DATOS
try {
    // Asegúrate de que esta ruta sea correcta. 
    // Nota: en tu comentario decías '../../' pero en tu código pusiste '../'
    $rutaModelo = '../modelos/Vencer.php'; 
    
    if (!file_exists($rutaModelo)) {
        throw new Exception("No encuentro el archivo del modelo en: " . $rutaModelo);
    }

    require_once $rutaModelo; 

    // Verificar si la clase y el método existen
    if (!class_exists('Vencer') || !method_exists('Vencer', 'listar')) {
        throw new Exception("La clase Vencer o el método listar no existen.");
    }

    // Obtenemos los registros directamente
    $registros = Vencer::listar();

    if ($registros) {
        // Como la base de datos ya es UTF-8, los enviamos directo
        echo json_encode($registros, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([]); 
    }

} catch (Exception $e) {
    // Si algo falla, devolver JSON de error
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
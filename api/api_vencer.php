<?php
// 1. DESACTIVAR ERRORES VISUALES (Para que no rompan el JSON)
error_reporting(0); 
ini_set('display_errors', 0);

// 2. ENCABEZADOS DE SEGURIDAD (El "Pase de Invitado")
header("Access-Control-Allow-Origin: *"); // Permite entrar a cualquiera
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

// OJO: Para pruebas locales, comentamos la seguridad temporalmente
// Si quieres probar sin iniciar sesión en el sistema principal, mantén estas líneas comentadas:
/*
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["error" => "No autorizado (Sesion cerrada)"]);
    exit();
}
*/

// 5. INTENTAR OBTENER LOS DATOS
try {
    // Asegúrate de que esta ruta sea correcta. 
    // Si la carpeta 'api' está dentro de 'paginaPrueba', subir 2 niveles (../../) debería llevar a 'modelos'
    $rutaModelo = '../modelos/vencer.php';
    
    if (!file_exists($rutaModelo)) {
        throw new Exception("No encuentro el archivo del modelo en: " . $rutaModelo);
    }

    require_once $rutaModelo; 

    // Verificar si la clase y el método existen
    if (!class_exists('Vencer') || !method_exists('Vencer', 'listar')) {
        throw new Exception("La clase Vencer o el método listar no existen.");
    }

    $registros = Vencer::listar();

    if ($registros) {
        echo json_encode($registros, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([]); 
    }

} catch (Exception $e) {
    // Si algo falla, devolver JSON de error, no HTML
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
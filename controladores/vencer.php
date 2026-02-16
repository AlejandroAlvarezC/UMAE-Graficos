<?php
// ¡IMPORTANTE! LA LÍNEA DE ARRIBA (<?php) DEBE SER LA PRIMERA. NO DEJES ESPACIOS ANTES.
ob_start(); // Iniciamos la limpieza

// Configuraciones
ini_set('display_errors', 0);
error_reporting(E_ALL);

$respuesta = [];

try {
    // 1. CARGA DEL MODELO (Buscamos Vencer.php)
    $rutas = [
        '../modelos/Vencer.php', // Ruta estándar
        '../modelos/vencer.php'  // Ruta minúscula
    ];
    
    $rutaModelo = null;
    foreach($rutas as $r) { if(file_exists($r)) $rutaModelo = $r; }

    if (!$rutaModelo) throw new Exception("CRÍTICO: No encuentro modelos/Vencer.php");

    require_once $rutaModelo;

    // 2. INSTANCIA
    if (class_exists('Vencer')) $v = new Vencer();
    elseif (class_exists('vencer')) $v = new vencer();
    else throw new Exception("El archivo carga pero no tiene la Clase Vencer.");

    // 3. PROCESO
    $accion = $_REQUEST['a'] ?? '';

    if ($accion === 'CargarCSV') {
        if (empty($_FILES['csv']['tmp_name'])) throw new Exception("Falta archivo.");
        
        $contenido = file_get_contents($_FILES['csv']['tmp_name']);
        
        // Limpieza de caracteres raros (BOM) al inicio del archivo Excel
        $contenido = preg_replace('/^\xEF\xBB\xBF/', '', $contenido); 

        $encoding = mb_detect_encoding($contenido, ['ISO-8859-1', 'Windows-1252', 'UTF-8'], true);
        if ($encoding !== 'UTF-8') $contenido = mb_convert_encoding($contenido, 'UTF-8', $encoding);

        $lineas = explode("\n", $contenido);
        if(count($lineas)>0) array_shift($lineas);
        
        $exitos = 0; $errores = 0;
        foreach ($lineas as $linea) {
            if (trim($linea) === '') continue;
            $datos = str_getcsv($linea);
            if (count($datos) < 5) $datos = str_getcsv($linea, ";");
            
            if (count($datos) >= 5) {
                $v->cargarDesdeCSV($datos);
                $res = $v->ingresar2();
                ($res == 'insertado' || $res == 'actualizado' || $res == 'colision_sin_cambios') ? $exitos++ : $errores++;
            }
        }
        $respuesta = ['status' => 'success', 'message' => "✅ PROCESADO.<br>Correctos: $exitos<br>Errores/Omitidos: $errores"];

    } else {
        $respuesta = ['status' => 'error', 'message' => "Acción desconocida"];
    }

} catch (Throwable $e) {
    $respuesta = ['status' => 'error', 'message' => '🚨 ERROR: ' . $e->getMessage()];
}

// --- LIMPIEZA FINAL AGRESIVA ---
// Si había un "<" perdido, esto intenta borrarlo.
if (ob_get_length()) ob_clean(); 

header('Content-Type: application/json; charset=utf-8');
echo json_encode($respuesta);
exit;
?>
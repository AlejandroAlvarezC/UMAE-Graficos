<?php
// CONTROLADOR: controladores/vencer.php
// INICIAMOS BUFFER (Atrapa cualquier error invisible o espacio en blanco)
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
ob_start();

// Configuración de errores
ini_set('display_errors', 0); // NO mostrar errores en el HTML (rompe el JSON)
error_reporting(E_ALL);

$respuesta = [];

try {
    // 1. CARGA DEL MODELO (Buscamos Vencer.php)
    $rutas = [
        '../modelos/Vencer.php', 
    ];
    
    $rutaModelo = null;
    foreach($rutas as $r) { if(file_exists($r)) $rutaModelo = $r; }

    if (!$rutaModelo) throw new Exception("CRÍTICO: No encuentro modelos/Vencer.php");

    require_once $rutaModelo;

    // 2. INSTANCIA
    if (class_exists('Vencer')) $v = new Vencer();
    elseif (class_exists('vencer')) $v = new vencer();
    else throw new Exception("El archivo carga pero no tiene la Clase Vencer.");

    // 3. PROCESAR PETICIÓN
    $accion = $_REQUEST['a'] ?? '';

    switch ($accion) {
        
        // --- CASO 1: CARGA DE EXCEL (Corregido para acentos) ---
        case 'CargarCSV':
            if (empty($_FILES['csv']['tmp_name'])) throw new Exception("Falta archivo.");
            
            $contenido = file_get_contents($_FILES['csv']['tmp_name']);
            // Limpieza BOM
            $contenido = preg_replace('/^\xEF\xBB\xBF/', '', $contenido); 

            $lineas = explode("\n", $contenido);
            if(count($lineas)>0) array_shift($lineas); // Quita encabezados
            
            $exitos = 0; $errores = 0;
            foreach ($lineas as $linea) {
                if (trim($linea) === '') continue;
                $datos = str_getcsv($linea);
                if (count($datos) < 5) $datos = str_getcsv($linea, ";");
                
                if (count($datos) >= 5) {
                    
                    // 🔥 EL TRADUCTOR ANTI-EXCEL DEFINITIVO 🔥
                    $datosLimpios = array();
                    foreach ($datos as $celda) {
                        // Si la celda viene en formato Windows/Excel antiguo, la reparamos a UTF-8
                        if (!mb_check_encoding($celda, 'UTF-8')) {
                            $datosLimpios[] = mb_convert_encoding($celda, 'UTF-8', 'Windows-1252');
                        } else {
                            // Si ya viene bien, la dejamos igual
                            $datosLimpios[] = $celda;
                        }
                    }

                    // Le mandamos los datos LIMPIOS al modelo en vez de los crudos
                    $v->cargarDesdeCSV($datosLimpios);
                    $res = $v->ingresar2();
                    ($res == 'insertado' || $res == 'actualizado' || $res == 'colision_sin_cambios') ? $exitos++ : $errores++;
                }
            }
            $respuesta = ['status' => 'success', 'message' => "✅ PROCESADO.<br>Correctos: $exitos<br>Errores/Omitidos: $errores"];
            break;

        // --- CASO 2: ELIMINAR (Para los botones de la tabla) ---
        case 'Eliminar':
            if (!empty($_GET['id'])) {
                $idDecoded = base64_decode($_GET['id']);
                // Validación simple para evitar errores
                if (is_numeric($idDecoded)) {
                    $v->id = $idDecoded;
                    $v->eliminar();
                    // Redirección normal HTML (porque el link es un <a href>)
                    header('Location: ../vistas/productividad/vencer.php?msg=Registro eliminado correctamente');
                    exit;
                }
            }
            header('Location: ../vistas/productividad/vencer.php?error=No se pudo eliminar');
            exit;
            break;

       // --- CASO 3: OBTENER DATOS JSON (VERSIÓN TURBO ⚡) ---
        case 'ObtenerDatosJSON':
            ini_set('memory_limit', '256M');
            
            // 1. Obtener datos (Rápido gracias al nuevo modelo)
            $data = Vencer::listar();

            // 2. Enviar JSON directo
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json; charset=utf-8');
            
            // JSON_INVALID_UTF8_SUBSTITUTE evita que el JSON se rompa si hay basura
            echo json_encode($data, JSON_NUMERIC_CHECK | JSON_INVALID_UTF8_SUBSTITUTE);
            exit;
            break;
    }

} catch (Throwable $e) {
    // Si algo falla, devolvemos error JSON (excepto en Eliminar que ya redirigió)
    $respuesta = ['status' => 'error', 'message' => 'ERROR SERVIDOR: ' . $e->getMessage()];
}

// --- RESPUESTA FINAL JSON (Solo para CargarCSV o errores) ---
if (ob_get_length()) ob_clean(); // Borra cualquier basura antes del JSON

header('Content-Type: application/json; charset=utf-8');
echo json_encode($respuesta);
exit;
?>
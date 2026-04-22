<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Content-Type: application/json; charset=utf-8');

// Manejo de peticiones OPTIONS (Preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// --- CONFIGURACIÓN DE BASE DE DATOS ---
$host = "sql112.infinityfree.com";
$dbname = "if0_41125231_vencer";
$user = "if0_41125231";
$pass = "DEtK59bqZzA";

try {
    // Forzamos charset utf8mb4 para que la BD acepte acentos y Ñ
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
    // Esto obliga a la conexión a hablar en UTF-8 puro
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
];
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Error de conexión: " . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {

    case 'GET':
        try {
            // Obtenemos los médicos para el diccionario de React
            $stmt = $pdo->query("SELECT matricula, nombre FROM medicos ORDER BY nombre ASC");
            $medicos = $stmt->fetchAll();
            
            // Retornamos con el prefijo "Dr. " para la interfaz
            $resultado = array_map(function($m) {
                return [
                    'matricula' => $m['matricula'],
                    'nombre' => 'Dr. ' . $m['nombre']
                ];
            }, $medicos);
            
            echo json_encode($resultado);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
        break;

    case 'POST':
        // --- CASO A: CARGA MASIVA DESDE CSV ---
        if (isset($_FILES['archivo_medicos'])) {
            $file = $_FILES['archivo_medicos']['tmp_name'];
            $handle = fopen($file, "r");
            
            // Detectar si usa coma o punto y coma
            $linea1 = fgets($handle);
            $delimitador = (strpos($linea1, ';') !== false) ? ';' : ',';
            rewind($handle);

            // Saltar cabecera
            fgetcsv($handle, 1000, $delimitador); 

            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO medicos (matricula, nombre) VALUES (?, ?) 
                                       ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)");
                
                $procesados = 0;
                while (($data = fgetcsv($handle, 1000, $delimitador)) !== FALSE) {
                    if (count($data) < 2) continue;

                    $nombreRaw = $data[0]; // Columna A: Nombre
                    $matRaw = $data[1];    // Columna B: Matricula

                    // 1. Convertir encoding de Windows (ANSI) a UTF-8 si es necesario
                    if (!mb_check_encoding($nombreRaw, 'UTF-8')) {
                        $nombreRaw = mb_convert_encoding($nombreRaw, 'UTF-8', 'Windows-1252');
                    }

                    // 2. Limpieza de Matrícula
                    $matricula = preg_replace('/[^a-zA-Z0-9]/', '', $matRaw);

                    // 3. Transformación ETL (Barras por espacios y Formato Título con soporte de acentos)
                    $nombreConEspacios = str_replace('/', ' ', $nombreRaw);
                    $nombreLimpio = mb_convert_case(mb_strtolower($nombreConEspacios, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

                    if ($matricula != '' && $nombreLimpio != '') {
                        $stmt->execute([$matricula, $nombreLimpio]);
                        $procesados++;
                    }
                }
                $pdo->commit();
                echo json_encode(["success" => true, "message" => "$procesados médicos importados correctamente"]);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(["success" => false, "error" => $e->getMessage()]);
            }
            fclose($handle);
        } 
        // --- CASO B: AGREGAR MÉDICO MANUAL (JSON) ---
        else {
            $input = json_decode(file_get_contents("php://input"), true);
            if (isset($input['matricula'], $input['nombre'])) {
                try {
                    $nombreLimpio = mb_convert_case(mb_strtolower(str_replace('/', ' ', $input['nombre']), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                    $stmt = $pdo->prepare("INSERT INTO medicos (matricula, nombre) VALUES (?, ?)");
                    $stmt->execute([$input['matricula'], $nombreLimpio]);
                    echo json_encode(["success" => true]);
                } catch (Exception $e) {
                    echo json_encode(["success" => false, "error" => $e->getMessage()]);
                }
            }
        }
        break;

    case 'PUT':
        // Editar nombre de médico existente
        $input = json_decode(file_get_contents("php://input"), true);
        if (isset($input['matricula'], $input['nombre'])) {
            try {
                $nombreLimpio = mb_convert_case(mb_strtolower(str_replace('/', ' ', $input['nombre']), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                $stmt = $pdo->prepare("UPDATE medicos SET nombre = ? WHERE matricula = ?");
                $stmt->execute([$nombreLimpio, $input['matricula']]);
                echo json_encode(["success" => true]);
            } catch (Exception $e) {
                echo json_encode(["success" => false, "error" => $e->getMessage()]);
            }
        }
        break;

    case 'DELETE':
        // Eliminar médico por matrícula
        if (isset($_GET['matricula'])) {
            try {
                $stmt = $pdo->prepare("DELETE FROM medicos WHERE matricula = ?");
                $stmt->execute([$_GET['matricula']]);
                echo json_encode(["success" => true]);
            } catch (Exception $e) {
                echo json_encode(["success" => false, "error" => $e->getMessage()]);
            }
        }
        break;
}
?>
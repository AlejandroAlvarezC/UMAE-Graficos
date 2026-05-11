<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// 1. Configura aquí tu conexión a MySQL
$host = "localhost";
$db = "tu_base_de_datos"; // Cambia por el nombre de tu BD
$user = "tu_usuario";
$pass = "tu_password";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["success" => false, "message" => "Error de conexión: " . $e->getMessage()]));
}

// 2. Verificamos que se haya enviado un archivo
if (!isset($_FILES['archivo_cie']) || $_FILES['archivo_cie']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["success" => false, "message" => "No se recibió ningún archivo o hubo un error en la subida."]);
    exit;
}

$archivoTmp = $_FILES['archivo_cie']['tmp_name'];
$registrosProcesados = 0;

try {
    // 3. Preparamos el Query (Asegúrate de que tu tabla se llame cat_cie10 o similar)
    // El ON DUPLICATE KEY actualiza el registro si el código ya existe
    $query = "INSERT INTO cat_cie (codigo, descripcion) VALUES (?, ?) 
              ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion)";
    $stmt = $pdo->prepare($query);

    // 4. Abrimos y leemos el CSV
    if (($handle = fopen($archivoTmp, "r")) !== FALSE) {
        
        // Opcional: Si tu CSV tiene encabezados (Código, Descripción), descomenta la siguiente línea para saltar la primera fila
        // fgetcsv($handle, 1000, ","); 

        while (($datos = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Limpiamos los datos
            $codigo = trim(strtoupper($datos[0]));
            $descripcion = trim($datos[1]);

            if (!empty($codigo) && !empty($descripcion)) {
                $stmt->execute([$codigo, $descripcion]);
                $registrosProcesados++;
            }
        }
        fclose($handle);
    }

    echo json_encode([
        "success" => true, 
        "message" => "Catálogo actualizado correctamente.",
        "registros" => $registrosProcesados
    ]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error al procesar la base de datos: " . $e->getMessage()]);
}
?>
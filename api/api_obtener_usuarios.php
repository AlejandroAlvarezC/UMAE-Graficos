<?php
// 1. Configuramos el archivo para que responda en formato JSON puro
header('Content-Type: application/json');

// 2. Incluimos tu modelo de base de datos
require_once '../../modelos/UsuariosAdmin.php'; // Asegúrate de que esta ruta sea correcta desde donde guardes este archivo

try {
    // 3. Obtenemos los usuarios usando tu método existente
    $usuarios_db = Usuarios::listar();
    
    $usuarios_formateados = [];
    
    // 4. Transformamos los datos al formato exacto que React necesita
    foreach ($usuarios_db as $fila) {
        $usuarios_formateados[] = [
            'id' => $fila[0],
            'nombre' => $fila[1],
            'correo' => $fila[2],
            // En tu BD actual parece que no tienes 'rol' o 'estado', 
            // así que por ahora les ponemos un valor por defecto o los ajustas si sí los tienes.
            'rol' => 'viewer', 
            'estado' => 'activo'
        ];
    }
    
    // 5. Enviamos los datos a React
    echo json_encode($usuarios_formateados);
    
} catch (Exception $e) {
    // Si hay error, le avisamos a React
    echo json_encode(['error' => 'Error al obtener usuarios: ' . $e->getMessage()]);
}
?>
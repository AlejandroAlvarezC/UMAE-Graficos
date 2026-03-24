<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// Nombre del archivo CSV donde guardas a los médicos
$archivoMedicos = "medicos.csv"; 
$diccionario = [];

if (file_exists($archivoMedicos) && ($gestor = fopen($archivoMedicos, "r")) !== FALSE) {
    // Detectar delimitador
    $linea1 = fgets($gestor);
    $delimitador = strpos($linea1, ';') !== false ? ';' : ',';
    rewind($gestor); 
    
    // Saltar los encabezados (Nombre, Matricula, Categoría)
    fgetcsv($gestor, 1000, $delimitador); 
    
    // Leer línea por línea
    while (($row = fgetcsv($gestor, 1000, $delimitador)) !== FALSE) {
        
        // Validamos que traiga al menos las primeras 2 columnas
        if(count($row) >= 2) {
            
            // 1. Extraer los datos crudos según tu orden
            $nombreCrudo = trim($row[0]); // Ej: "HERNANDEZ/RUIZ/MARIA"
            $matriculaCruda = trim($row[1]); // Ej: " 1234567 "
            // La categoría está en $row[2], pero para el diccionario principal no la requerimos ahora
            
            // 2. Limpieza de Matrícula (quitar espacios o saltos de línea basura)
            $matricula = preg_replace('/[^a-zA-Z0-9]/', '', $matriculaCruda);
            
            // 3. Limpieza de Nombre (La Transformación ETL)
            if($matricula !== '' && $nombreCrudo !== '') {
                // A) Reemplazar las barras (/) por espacios
                $nombreConEspacio = str_replace('/', ' ', $nombreCrudo);
                
                // B) Convertir de MAYÚSCULAS a Formato Título (Ej. Hernandez Ruiz Maria)
                // Se usa strtolower primero, y luego ucwords para capitalizar cada palabra
                $nombreLimpio = ucwords(strtolower($nombreConEspacio));
                
                // 4. Agregar al diccionario
                $diccionario[] = [
                    'matricula' => $matricula,
                    // Le agregamos el prefijo para que en React se lea increíble
                    'nombre' => 'Dr. ' . $nombreLimpio 
                ];
            }
        }
    }
    fclose($gestor);
    
    // Retornamos el JSON con los datos pulidos
    echo json_encode($diccionario);
} else {
    // Si no hay archivo, retornamos un arreglo vacío para que React no falle
    echo json_encode([]);
}
?>
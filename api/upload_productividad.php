<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// Fuerza a leer correctamente los saltos de línea de cualquier Excel
ini_set('auto_detect_line_endings', TRUE);

$host = 'sql112.infinityfree.com';
$dbname = 'if0_41125231_vencer'; 
$username = 'if0_41125231';
$password = 'DEtK59bqZzA';

// ==========================================
// FUNCIÓN LIBRE: NO RECHAZA NINGUNA FECHA
// ==========================================
function arreglarFecha($fechaStr) {
    $fechaStr = trim($fechaStr);
    
    // Si la celda está vacía (fila fantasma), es lo único que devolverá false
    if(empty($fechaStr)) return false; 

    // --- SALVAVIDAS 1: Número de serie de Excel (ej. 45397) ---
    if (is_numeric($fechaStr) && $fechaStr > 30000) {
        $unix_date = ($fechaStr - 25569) * 86400;
        return gmdate("Y-m-d", $unix_date);
    }

    // --- SALVAVIDAS 2: Meses en texto español (ej. 15-abr-2026) ---
    $meses = [
        'ene'=>'01', 'feb'=>'02', 'mar'=>'03', 'abr'=>'04', 'may'=>'05', 'jun'=>'06', 
        'jul'=>'07', 'ago'=>'08', 'sep'=>'09', 'oct'=>'10', 'nov'=>'11', 'dic'=>'12',
        'enero'=>'01', 'febrero'=>'02', 'marzo'=>'03', 'abril'=>'04', 'mayo'=>'05', 'junio'=>'06', 
        'julio'=>'07', 'agosto'=>'08', 'septiembre'=>'09', 'octubre'=>'10', 'noviembre'=>'11', 'diciembre'=>'12'
    ];
    $fechaStr = str_ireplace(array_keys($meses), array_values($meses), strtolower($fechaStr));

    // --- EL ESCÁNER LÁSER ---
    if (preg_match('/(\d{1,4})[\/\.\-](\d{1,2})[\/\.\-](\d{1,4})/', $fechaStr, $matches)) {
        $p1 = (int)$matches[1];
        $p2 = (int)$matches[2];
        $p3 = (int)$matches[3];

        if ($p1 > 1000) { $anio = $p1; $mes = $p2; $dia = $p3; } 
        elseif ($p3 > 1000) { 
            $anio = $p3;
            if ($p2 > 12) { $mes = $p1; $dia = $p2; } else { $dia = $p1; $mes = $p2; }
        } else { 
            $anio = $p3 + 2000;
            if ($p2 > 12) { $mes = $p1; $dia = $p2; } else { $dia = $p1; $mes = $p2; }
        }

        // ==========================================
        // CERO RECHAZOS: Solo evitamos que MySQL colapse
        // ==========================================
        if ($mes > 12) $mes = 12;
        if ($mes == 0) $mes = 1;
        if ($dia > 31) $dia = 31;
        if ($dia == 0) $dia = 1;
        // Si el año viene en 0, le ponemos 2026 para que pase
        if ($anio == 0) $anio = 2026; 

        return sprintf("%04d-%02d-%02d", $anio, $mes, $dia);
    }

    // Si la celda trae pura basura irrecuperable, forzamos esta fecha para que EL REGISTRO ENTRE.
    return '1999-01-01'; 
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");

    $diccionario = [];
    if (file_exists("divisionesEsp.csv") && ($cat = fopen("divisionesEsp.csv", "r")) !== FALSE) {
        $linea1 = fgets($cat);
        $delim = strpos($linea1, ';') !== false ? ';' : ',';
        rewind($cat); fgetcsv($cat, 1000, $delim); 
        while (($row = fgetcsv($cat, 1000, $delim)) !== FALSE) {
            if(count($row) >= 3) {
                $clave = preg_replace('/[^0-9]/', '', $row[1]); 
                if($clave !== '') $diccionario[$clave] = ['especialidad' => trim($row[2]), 'division' => trim($row[0])];
            }
        }
        fclose($cat);
    }

    $mapTurno = ['1' => 'Matutino', '2' => 'Vespertino', '3' => 'Nocturno', '4' => 'Jornada Acumulada'];
    $mapCitado = ['1' => 'Citado', '0' => 'Espontáneo / Unifila'];
    $mapPrimeraVez = ['1' => 'Primera Vez', '0' => 'Subsecuente'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_csv'])) {
        if (($handle = fopen($_FILES['archivo_csv']['tmp_name'], "r")) !== FALSE) {
            $linea1 = fgets($handle);
            $delimitador = strpos($linea1, ';') !== false ? ';' : ',';
            rewind($handle); 

            $headers = fgetcsv($handle, 10000, $delimitador);
            $headers = array_map(function($h) { return trim(preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/', '', $h)); }, $headers);
            $colMap = array_flip($headers);

            // INSERCIÓN DIRECTA: Sin IGNORE 
            $stmt = $pdo->prepare("INSERT INTO productividad_externa 
                (division, especialidad, matricula_medico, consultorio, fecha_atencion, dia, mes, anio, turno, citado, primera_vez, diagnostico_principal, clave_presupuestal) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $insertados = 0;
            $errores_fecha = 0;

            while (($data = fgetcsv($handle, 10000, $delimitador)) !== FALSE) {
                if(count($data) < 5) continue; 

                // Convierte la fecha a Año-Mes-Día usando el "escáner láser"
                $fechaMysql = arreglarFecha($data[$colMap['FECHA_ATENCION']] ?? '');
                if (!$fechaMysql) {
                    $errores_fecha++;
                    continue; 
                }
                
                // Extraemos día, mes y año calendario
                $partesF = explode('-', $fechaMysql);
                $anio_calendario = (int)$partesF[0]; 
                $mes_calendario = (int)$partesF[1]; 
                $dia = (int)$partesF[2];

                // ==========================================
                // LÓGICA DE CORTE HOSPITALARIO (Del 26 al 25)
                // ==========================================
                $mes_corte = $mes_calendario;
                $anio_corte = $anio_calendario;

                if ($dia >= 26) {
                    $mes_corte++; // Lo mandamos al mes siguiente
                    
                    // Si brinca de diciembre, pasa a enero del próximo año
                    if ($mes_corte > 12) {
                        $mes_corte = 1;
                        $anio_corte++;
                    }
                }

                $diagRaw = html_entity_decode(trim($data[$colMap['DIAG_PRINCIPAL']] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (!mb_check_encoding($diagRaw, 'UTF-8')) $diagRaw = mb_convert_encoding($diagRaw, 'UTF-8', 'Windows-1252');

                // ==========================================
                // REPARACIÓN: RESCATE DE ESPECIALIDADES EN TEXTO
                // ==========================================
                $espOriginal = trim($data[$colMap['ESPECIALIDAD']] ?? '');
                $cveEsp = preg_replace('/[^0-9]/', '', $espOriginal);
                
                $especialidadFinal = $diccionario[$cveEsp]['especialidad'] ?? $espOriginal;
                if ($especialidadFinal === '') $especialidadFinal = 'Sin Especialidad';
                
                $divisionFinal = $diccionario[$cveEsp]['division'] ?? "Sin Asignar";
                
                $stmt->execute([
                    $divisionFinal, 
                    $especialidadFinal, 
                    trim($data[$colMap['MATRIC_MEDICO']] ?? ''), 
                    trim($data[$colMap['CONSULTORIO']] ?? ''), 
                    $fechaMysql, 
                    $dia, 
                    $mes_corte,   
                    $anio_corte,  
                    $mapTurno[trim($data[$colMap['TURNO']] ?? '')] ?? "Otro", 
                    $mapCitado[trim($data[$colMap['CITADO']] ?? '')] ?? "Dato Raro", 
                    $mapPrimeraVez[trim($data[$colMap['PRIMERA_VEZ']] ?? '')] ?? "Dato Raro", 
                    $diagRaw, 
                    trim($data[$colMap['CVE_PRESUP_ADSCR']] ?? '')
                ]);

                $insertados++;
            }
            fclose($handle);
            file_put_contents('ultima_actualizacion.txt', time());
            
            echo json_encode(['success' => true, 'message' => "¡Completado! Registros guardados: $insertados. (Errores de fecha omitidos: $errores_fecha)"]);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error BD: ' . $e->getMessage()]);
}
?>
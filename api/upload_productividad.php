<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// 1. Configuración de Base de Datos
$host = 'sql112.infinityfree.com';
$dbname = 'if0_41125231_vencer'; 
$username = 'if0_41125231';
$password = 'DEtK59bqZzA';

try {
    // Usamos utf8mb4 y forzamos el set names para Ñ y acentos
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");

    // 2. Cargar el Diccionario de Especialidades
    $diccionario = [];
    $nombreArchivoDiccionario = "divisionesEsp.csv"; 

    if (file_exists($nombreArchivoDiccionario) && ($cat = fopen($nombreArchivoDiccionario, "r")) !== FALSE) {
        $linea1_dic = fgets($cat);
        $delim_dic = strpos($linea1_dic, ';') !== false ? ';' : ',';
        rewind($cat); 
        fgetcsv($cat, 1000, $delim_dic); 
        
        while (($row = fgetcsv($cat, 1000, $delim_dic)) !== FALSE) {
            if(count($row) >= 3) {
                $clave = preg_replace('/[^0-9]/', '', $row[1]); 
                if($clave !== '') {
                    $diccionario[$clave] = [
                        'especialidad' => trim($row[2]), 
                        'division' => trim($row[0])
                    ];
                }
            }
        }
        fclose($cat);
    }

    $mapTurno = ['1' => 'Matutino', '2' => 'Vespertino', '3' => 'Nocturno', '4' => 'Jornada Acumulada'];
    $mapCitado = ['1' => 'Citado', '0' => 'Espontáneo / Unifila'];
    $mapPrimeraVez = ['1' => 'Primera Vez', '0' => 'Subsecuente'];

    // 4. Procesamiento de Archivo
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_csv'])) {
        $fileTmpPath = $_FILES['archivo_csv']['tmp_name'];
        
        if (($handle = fopen($fileTmpPath, "r")) !== FALSE) {
            // Detección automática de delimitador
            $linea1 = fgets($handle);
            $delimitador = strpos($linea1, ';') !== false ? ';' : ',';
            rewind($handle); 

            $headers = fgetcsv($handle, 10000, $delimitador);
            $headers = array_map(function($h) { 
                $h = preg_replace('/\xEF\xBB\xBF/', '', $h); 
                return trim(preg_replace('/[\x00-\x1F\x7F]/', '', $h)); 
            }, $headers);
            $colMap = array_flip($headers);

            // Verificación de columnas (Asegúrate de que 'anio' y 'mes' existan en el CSV o se extraigan de la fecha)
            $columnasRequeridas = ['FECHA_ATENCION', 'ESPECIALIDAD', 'MATRIC_MEDICO', 'CONSULTORIO', 'CITADO', 'PRIMERA_VEZ', 'DIAG_PRINCIPAL', 'CVE_PRESUP_ADSCR', 'TURNO'];
            foreach ($columnasRequeridas as $req) {
                if (!isset($colMap[$req])) {
                    echo json_encode(['success' => false, 'message' => "Falta la columna: $req"]);
                    exit;
                }
            }

            // INSERT IGNORE es clave para el índice UNIQUE
            $stmt = $pdo->prepare("INSERT IGNORE INTO productividad_externa 
                (division, especialidad, matricula_medico, consultorio, fecha_atencion, dia, mes, anio, turno, citado, primera_vez, diagnostico_principal, clave_presupuestal) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $registrosInsertados = 0;
            $duplicadosSaltados = 0;

            while (($data = fgetcsv($handle, 10000, $delimitador)) !== FALSE) {
                if(count($data) < 5 || empty(trim($data[0]))) continue; 

                // --- PROCESAMIENTO ROBUSTO DE FECHA ---
                $fechaBruta = trim($data[$colMap['FECHA_ATENCION']]);
                $fechaSola = explode(' ', $fechaBruta)[0]; 
                
                // Intentar crear fecha desde formato d/m/Y (15/01/2026)
                $f = date_create_from_format('d/m/Y', $fechaSola);
                
                if ($f) {
                    $fechaMysql = $f->format('Y-m-d');
                    $dia = (int)$f->format('d');
                    $mes = (int)$f->format('m');
                    $anio = (int)$f->format('Y');
                } else {
                    // Si falla, intentar formato Y-m-d
                    $f = date_create_from_format('Y-m-d', $fechaSola);
                    if ($f) {
                        $fechaMysql = $f->format('Y-m-d');
                        $dia = (int)$f->format('d');
                        $mes = (int)$f->format('m');
                        $anio = (int)$f->format('Y');
                    } else {
                        // Fecha inválida: saltar registro o usar default
                        continue;
                    }
                }

                // --- LIMPIEZA DE CARACTERES ESPECIALES ---
                // Convertimos entidades HTML (&Ntilde;) a caracteres reales (Ñ)
                $diagnosticoRaw = html_entity_decode(trim($data[$colMap['DIAG_PRINCIPAL']]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                
                // Si el archivo viene de Excel (Windows-1252), forzamos a UTF-8
                if (!mb_check_encoding($diagnosticoRaw, 'UTF-8')) {
                    $diagnosticoRaw = mb_convert_encoding($diagnosticoRaw, 'UTF-8', 'Windows-1252');
                }

                $claveEspecialidad = preg_replace('/[^0-9]/', '', $data[$colMap['ESPECIALIDAD']]);
                $division = $diccionario[$claveEspecialidad]['division'] ?? "Sin Asignar";
                $especialidad = $diccionario[$claveEspecialidad]['especialidad'] ?? $claveEspecialidad;
                
                $turnoFinal = $mapTurno[trim($data[$colMap['TURNO']])] ?? "Otro";
                $citadoFinal = $mapCitado[trim($data[$colMap['CITADO']])] ?? "Dato Raro";
                $primVezFinal = $mapPrimeraVez[trim($data[$colMap['PRIMERA_VEZ']])] ?? "Dato Raro";

                // Ejecución con los datos extraídos de la fecha para garantizar consistencia en el INDEX UNIQUE
                $stmt->execute([
                    $division, 
                    $especialidad, 
                    trim($data[$colMap['MATRIC_MEDICO']]), 
                    trim($data[$colMap['CONSULTORIO']]), 
                    $fechaMysql, 
                    $dia, 
                    $mes, 
                    $anio, 
                    $turnoFinal, 
                    $citadoFinal, 
                    $primVezFinal, 
                    $diagnosticoRaw, 
                    trim($data[$colMap['CVE_PRESUP_ADSCR']])
                ]);

                if ($stmt->rowCount() > 0) {
                    $registrosInsertados++;
                } else {
                    $duplicadosSaltados++;
                }
            }
            fclose($handle);
            
            file_put_contents('ultima_actualizacion.txt', time());
            echo json_encode([
                'success' => true, 
                'message' => "¡Carga Exitosa! Nuevos: $registrosInsertados, Duplicados omitidos: $duplicadosSaltados."
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => "Error al abrir el archivo temporal."]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No se recibió el archivo "archivo_csv".']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error Crítico BD: ' . $e->getMessage()]);
}
?>
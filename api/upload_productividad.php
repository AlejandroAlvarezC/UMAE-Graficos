<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// 1. Configuración de Base de Datos
$host = 'sql112.infinityfree.com';
$dbname = 'if0_41125231_vencer'; 
$username = 'if0_41125231';
$password = 'DEtK59bqZzA'; // <-- ¡Ponla aquí!

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Cargar el Diccionario de Especialidades (CON EL ORDEN CORREGIDO)
    $diccionario = [];
    $nombreArchivoDiccionario = "divisionesEsp.csv"; 

    if (file_exists($nombreArchivoDiccionario) && ($cat = fopen($nombreArchivoDiccionario, "r")) !== FALSE) {
        $linea1_dic = fgets($cat);
        $delim_dic = strpos($linea1_dic, ';') !== false ? ';' : ',';
        rewind($cat); 
        fgetcsv($cat, 1000, $delim_dic); // Saltar encabezados
        
        while (($row = fgetcsv($cat, 1000, $delim_dic)) !== FALSE) {
            if(count($row) >= 3) {
                // [0] Division, [1] Cve Especialidad, [2] Descripcion
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

    // 4. Procesamiento
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_csv'])) {
        $fileTmpPath = $_FILES['archivo_csv']['tmp_name'];
        
        if (($handle = fopen($fileTmpPath, "r")) !== FALSE) {
            $linea1 = fgets($handle);
            $delimitador = strpos($linea1, ';') !== false ? ';' : ',';
            rewind($handle); 

            $headers = fgetcsv($handle, 10000, $delimitador);
            $headers = array_map(function($h) { 
                $h = preg_replace('/\xEF\xBB\xBF/', '', $h); 
                return trim(preg_replace('/[\x00-\x1F\x7F]/', '', $h)); 
            }, $headers);
            $colMap = array_flip($headers);

            $columnasRequeridas = ['FECHA_ATENCION', 'ESPECIALIDAD', 'MATRIC_MEDICO', 'CONSULTORIO', 'CITADO', 'PRIMERA_VEZ', 'DIAG_PRINCIPAL', 'CVE_PRESUP_ADSCR', 'TURNO', 'anio', 'mes'];
            foreach ($columnasRequeridas as $req) {
                if (!isset($colMap[$req])) {
                    echo json_encode(['success' => false, 'message' => "Falta la columna: $req"]);
                    exit;
                }
            }

            $registrosInsertados = 0;
            $stmt = $pdo->prepare("INSERT INTO productividad_externa 
                (division, especialidad, matricula_medico, consultorio, fecha_atencion, mes, anio, turno, citado, primera_vez, diagnostico_principal, clave_presupuestal) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            while (($data = fgetcsv($handle, 10000, $delimitador)) !== FALSE) {
                if(count($data) < 5) continue; 

                $fechaBruta = $data[$colMap['FECHA_ATENCION']];
                $claveEspecialidad = preg_replace('/[^0-9]/', '', $data[$colMap['ESPECIALIDAD']]);
                $matricula = trim($data[$colMap['MATRIC_MEDICO']]);
                
                $fechaSola = explode(' ', trim($fechaBruta))[0]; 
                $fechaMysql = null;
                if (strpos($fechaSola, '-') !== false) {
                    $fechaMysql = $fechaSola; 
                } else {
                    $f = date_create_from_format('d/m/Y', $fechaSola);
                    if ($f) $fechaMysql = $f->format('Y-m-d');
                }
                if (!$fechaMysql) $fechaMysql = '2000-01-01';

                $division = isset($diccionario[$claveEspecialidad]) ? $diccionario[$claveEspecialidad]['division'] : "Sin Asignar";
                $especialidad = isset($diccionario[$claveEspecialidad]) ? $diccionario[$claveEspecialidad]['especialidad'] : "Cod: $claveEspecialidad";
                
                $turnoFinal = isset($mapTurno[trim($data[$colMap['TURNO']])]) ? $mapTurno[trim($data[$colMap['TURNO']])] : "Otro";
                $citadoFinal = isset($mapCitado[trim($data[$colMap['CITADO']])]) ? $mapCitado[trim($data[$colMap['CITADO']])] : "Dato Raro";
                $primVezFinal = isset($mapPrimeraVez[trim($data[$colMap['PRIMERA_VEZ']])]) ? $mapPrimeraVez[trim($data[$colMap['PRIMERA_VEZ']])] : "Dato Raro";

                $stmt->execute([$division, $especialidad, $matricula, trim($data[$colMap['CONSULTORIO']]), $fechaMysql, (int)$data[$colMap['mes']], (int)$data[$colMap['anio']], $turnoFinal, $citadoFinal, $primVezFinal, trim($data[$colMap['DIAG_PRINCIPAL']]), trim($data[$colMap['CVE_PRESUP_ADSCR']])]);
                $registrosInsertados++;
            }
            fclose($handle);
            
            $conteoDic = count($diccionario);
            // Registramos la fecha y hora exacta (timestamp) de esta subida
            file_put_contents('ultima_actualizacion.txt', time());
            echo json_encode(['success' => true, 'message' => "Se insertaron $registrosInsertados registros. (Catálogo cargado: $conteoDic especialidades)."]);
        }
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error BD: ' . $e->getMessage()]);
}
?>
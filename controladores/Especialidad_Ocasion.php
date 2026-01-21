<?php
require_once '../modelos/Especialidad_Ocasion.php';

$accion = $_POST['a'] ?? $_GET['a'] ?? '';

$esp = new Especialidad_Ocasion();

switch ($accion) {
    case 'Ingresar':
    if (!empty($_POST['clave']) && !empty($_POST['especialidad']) && !empty($_POST['descripcion']) && !empty($_POST['anio'])) {
        $esp->cargarDesdeFormulario($_POST);

        // Leer modo
        $modo = $_POST['modo'] ?? 'insertar';

        // Solo validar duplicados cuando se quiere insertar
        if ($modo === 'insertar' && $esp->claveAnioExiste($esp->clave, $esp->anio)) {
            header('Location: ../vistas/productividad/ingresar_EspecialidadOcasion.php?error=Ya existe esa clave para ese año');
            exit;
        }

        $esp->ingresar();
        header('Location: ../vistas/productividad/Especialidad_Ocasion.php?msg=Operación exitosa');
        exit;
    } else {
        header('Location: ../vistas/productividad/Especialidad_Ocasion.php?error=Faltan datos');
        exit;
    }
    break;


    case 'Editar':
        if (!empty($_POST['id'])) {
            $esp->id = base64_decode($_POST['id']);
            $esp->cargarDesdeFormulario2($_POST);
            $esp->editar();

            header('Location: ../vistas/productividad/Especialidad_Ocasion.php?msg=Editado');
            exit;
        }
        break;

    case 'Eliminar':
        if (!empty($_GET['id'])) {
            $esp->id = base64_decode($_GET['id']);
            $esp->eliminar();

            header('Location: ../vistas/productividad/Especialidad_Ocasion.php?msg=Eliminado');
            exit;
        }
        break;

    case 'CargarCSV':
        header('Content-Type: application/json');

        if (!empty($_FILES['csv']['tmp_name'])) {
            $contenido = file_get_contents($_FILES['csv']['tmp_name']);
            $encoding = mb_detect_encoding($contenido, ['ISO-8859-1', 'Windows-1252', 'UTF-8'], true);
            if ($encoding !== 'UTF-8') {
                $contenido = mb_convert_encoding($contenido, 'UTF-8', $encoding);
            }

            $tmpFile = tmpfile();
            fwrite($tmpFile, $contenido);
            rewind($tmpFile);
            $archivo = $tmpFile;

            $encabezado = fgetcsv($archivo);
            $numColumnasEsperadas = 28;

            $filaActual = 1;

            // Contadores para cada tipo de mensaje
            $totales = [
                'success' => 0,
                'info' => 0,
                'error' => 0,
            ];
            $mensajesError = [];

            while ($datos = fgetcsv($archivo)) {
                $filaActual++;

                if (count($datos) !== $numColumnasEsperadas) {
                    fclose($archivo);
                    echo json_encode([
                        'status' => 'error',
                        'message' => "Formato inválido en la fila $filaActual. Se esperaban $numColumnasEsperadas columnas, pero se encontraron " . count($datos) . ". Verifica tu archivo CSV."
                    ]);
                    exit;
                }

                $par = new Especialidad_Ocasion();
                $par->cargarDesdeCSV($datos);

                $resultado = $par->ingresar2();

                if (isset($resultado['status']) && isset($totales[$resultado['status']])) {
                    $totales[$resultado['status']]++;
                    if ($resultado['status'] === 'error') {
                        $mensajesError[] = "Fila $filaActual: " . $resultado['message'];
                    }
                } else {
                    // Si no se reconoce el estado, contar como error
                    $totales['error']++;
                    $mensajesError[] = "Fila $filaActual: Estado desconocido en la respuesta.";
                }
            }

            fclose($archivo);

            // Crear mensaje resumen
            $mensajesResumen = [];

            if ($totales['success'] > 0) {
                $mensajesResumen[] = "✅ Registros insertados o actualizados correctamente: {$totales['success']}";
            }
            if ($totales['info'] > 0) {
                $mensajesResumen[] = "ℹ️ Registros sin cambios necesarios: {$totales['info']}";
            }
            if ($totales['error'] > 0) {
                $mensajesResumen[] = "❌ Errores en {$totales['error']} registros:";
                $mensajesResumen[] = implode('<br>', $mensajesError);
            }

            // Determinar el status global (error > info > success)
            if ($totales['error'] > 0) {
                $statusGlobal = 'error';
            } elseif ($totales['info'] > 0 && $totales['success'] === 0) {
                $statusGlobal = 'info';
            } else {
                $statusGlobal = 'success';
            }

            echo json_encode([
                'status' => $statusGlobal,
                'message' => implode('<br>', $mensajesResumen)
            ]);
            exit;
        }

        echo json_encode([
            'status' => 'error',
            'message' => 'No se seleccionó ningún archivo.'
        ]);
        exit;
        break;

case 'ReemplazarCSVFiltrado':
    header('Content-Type: application/json');

    $expectedColumns = 28;
    $filtro_clave = $_POST['filtro_clave'] ?? "";
    $filtro_anio = $_POST['filtro_anio'] ?? "";
    $filtro_especialidad = $_POST['filtro_especialidad'] ?? "";
    $filtro_descripcion = $_POST['filtro_descripcion'] ?? "";

    if (empty($_FILES['csv']['tmp_name']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'No se ha subido un archivo válido.']);
        exit;
    }

    $archivo = fopen($_FILES['csv']['tmp_name'], 'r');
    if (!$archivo) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'No se pudo abrir el archivo.']);
        exit;
    }

    $header = fgetcsv($archivo);
    if (!$header || count($header) != $expectedColumns) {
        fclose($archivo);
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "El archivo CSV debe tener $expectedColumns columnas."]);
        exit;
    }

    $conexion = new Conexion();
    $totalActualizados = 0;
    $sinCoincidencias = 0;

    while ($datos = fgetcsv($archivo)) {
        if (count($datos) != $expectedColumns) {
            continue; // Ignora filas con columnas incorrectas
        }

        // Asegurar codificación UTF-8 para los primeros campos
        for ($i = 0; $i <= 2; $i++) {
            $encoding = mb_detect_encoding($datos[$i], ['ISO-8859-1', 'Windows-1252', 'UTF-8'], true);
            if ($encoding !== 'UTF-8') {
                $datos[$i] = mb_convert_encoding($datos[$i], 'UTF-8', $encoding);
            }
        }

        $parTemp = new Especialidad_Ocasion();
        $parTemp->cargarDesdeCSV($datos);

        $condiciones = [];

        if ($filtro_clave !== "" && $filtro_clave !== "ninguno") {
            $condiciones[] = "clave = '" . $conexion->getConexion()->real_escape_string($filtro_clave) . "'";
        }
        if ($filtro_anio !== "" && $filtro_anio !== "ninguno") {
            $condiciones[] = "anio = '" . $conexion->getConexion()->real_escape_string($filtro_anio) . "'";
        }
        if ($filtro_especialidad !== "" && $filtro_especialidad !== "ninguno") {
            $condiciones[] = "especialidad = '" . $conexion->getConexion()->real_escape_string($filtro_especialidad) . "'";
        }
        if ($filtro_descripcion !== "" && $filtro_descripcion !== "ninguno") {
            $condiciones[] = "descripcion = '" . $conexion->getConexion()->real_escape_string($filtro_descripcion) . "'";
        }

        if (!empty($condiciones)) {
            $whereSQL = implode(" AND ", $condiciones);

            // Buscamos por clave Y año específicos del CSV
            $queryVerificar = "
                SELECT id FROM especialidades 
                WHERE $whereSQL 
                AND clave = '" . $conexion->getConexion()->real_escape_string($parTemp->clave) . "' 
                AND anio = '" . intval($parTemp->anio) . "'
            ";
            $coincidencias = $conexion->consultar($queryVerificar);

            if (!empty($coincidencias)) {
                foreach ($coincidencias as $fila) {
                    $parTemp->id = $fila[0]; // ✅ ¡Clave para que funcione editar!
                    $resultado = $parTemp->editar();
                    if ($resultado) {
                        $totalActualizados++;
                    }
                }
            } else {
                $sinCoincidencias++;
            }
        }
    }

    fclose($archivo);
    $conexion->cerrar();

    $mensaje = "✅ Registros actualizados: $totalActualizados";
    if ($sinCoincidencias > 0) {
        $mensaje .= "<br>⚠️ Registros sin coincidencia: $sinCoincidencias";
    }

    echo json_encode([
        'status' => $totalActualizados > 0 ? 'success' : 'warning',
        'message' => $mensaje
    ]);
    exit;
    break;



    default:
        header('Location: ../vistas/productividad/Especialidad_Ocasion.php');
        exit;
}

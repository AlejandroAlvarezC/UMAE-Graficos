<?php
require_once '../modelos/cirugia.php';

$accion = $_POST['a'] ?? $_GET['a'] ?? '';

$urg = new Cirugia();

switch ($accion) {
    case 'Ingresar':
        if (
            !empty($_POST['clave']) &&
            !empty($_POST['especialidad']) &&
            !empty($_POST['division']) &&
            !empty($_POST['anio'])
        ) {
            $urg->cargarDesdeFormulario($_POST);

            // Leer modo
            $modo = $_POST['modo'] ?? 'insertar';

            // Solo validar duplicados cuando se quiere insertar
            if ($modo === 'insertar' && $urg->claveAnioExiste($urg->clave, $urg->anio)) {
                header('Location: ../vistas/cirugia/ingresar-cirugia.php?error=Ya existe esa clave para ese año');
                exit;
            }

            // Insertar nuevo si no existe, o actualizar campos vacíos si ya existe
            if ($urg->ingresar()) {
                header('Location: ../vistas/cirugia/cirugia_inicio.php?msg=Operación exitosa');
            } else {
                header('Location: ../vistas/cirugia/cirugia_inicio.php?error=No se realizó ningún cambio');
            }
            exit;
        } else {
            header('Location: ../vistas/cirugia/cirugia_inicio.php?error=Faltan datos');
            exit;
        }
        break;


    case 'Editar':
        if (!empty($_POST['id'])) {
            $urg->id = base64_decode($_POST['id']);
            $urg->cargarDesdeFormulario2($_POST);
            $urg->editar();

            header('Location: ../vistas/cirugia/cirugia_inicio.php?msg=Editado');
            exit;
        }
        break;

    case 'Eliminar':
        if (!empty($_GET['id'])) {
            $urg->id = base64_decode($_GET['id']);
            $urg->eliminar();

            header('Location: ../vistas/cirugia/cirugia_inicio.php?msg=Eliminado');
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
            $numColumnasEsperadas = 16; // clave, especialidad, división, 12 meses, año

            $filaActual = 1;

            $totales = [
                'success' => 0,      // Nuevos registros insertados
                'actualizado' => 0,  // Registros existentes que se actualizaron
                'colision' => 0,     // Registros existentes sin cambios necesarios
                'error' => 0,        // Errores de inserción/actualización
            ];
            $mensajesError = [];

            while ($datos = fgetcsv($archivo)) {
                $filaActual++;

                if (count($datos) !== $numColumnasEsperadas) {
                    fclose($archivo);
                    echo json_encode([
                        'status' => 'error',
                        'message' => " Formato inválido en la fila $filaActual. Se esperaban $numColumnasEsperadas columnas, pero se encontraron " . count($datos) . ". Verifica tu archivo CSV."
                    ]);
                    exit;
                }

                $urgencia = new Cirugia();
                $urgencia->cargarDesdeCSV($datos);
                $resultado = $urgencia->ingresar();

                switch ($resultado) {
                    case 'insertado':
                        $totales['success']++;
                        break;
                    case 'actualizado':
                        $totales['actualizado']++;
                        break;
                    case 'colision_sin_cambios':
                        $totales['colision']++;
                        break;
                    default:
                        $totales['error']++;
                        $mensajesError[] = "❌ Error en la fila $filaActual: no se pudo insertar ni actualizar.";
                        break;
                }
            }

            fclose($archivo);

            $mensajesResumen = [];

            if ($totales['success'] > 0) {
                $mensajesResumen[] = " Nuevos registros insertados: {$totales['success']}";
            }
            if ($totales['actualizado'] > 0) {
                $mensajesResumen[] = " Registros actualizados: {$totales['actualizado']}";
            }
            if ($totales['colision'] > 0) {
                $mensajesResumen[] = " Registros ya existentes sin necesidad de actualización: {$totales['colision']}";
            }
            if ($totales['error'] > 0) {
                $mensajesResumen[] = " Errores en el procesamiento: {$totales['error']}";
                $mensajesResumen = array_merge($mensajesResumen, $mensajesError);
            }

            echo json_encode([
                'status' => 'success',
                'message' => implode('<br>', $mensajesResumen)
            ]);
            exit;
        }

        echo json_encode([
            'status' => 'error',
            'message' => ' No se seleccionó ningún archivo.'
        ]);
        exit;
        break;



    case 'ReemplazarCSVFiltrado':
        header('Content-Type: application/json');

        $expectedColumns = 16; // acorde a tu tabla

        $filtro_clave = $_POST['filtro_clave'] ?? "";
        $filtro_anio = $_POST['filtro_anio'] ?? "";
        $filtro_especialidad = $_POST['filtro_especialidad'] ?? "";
        $filtro_division = $_POST['filtro_division'] ?? "";

        // Validación: año obligatorio
        if (empty($filtro_anio) || $filtro_anio === "ninguno") {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'El año es obligatorio. Por favor selecciona un año.']);
            exit;
        }

        // Validación: archivo válido
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
            echo json_encode(['status' => 'error', 'message' => "El archivo CSV no tiene el formato correcto. Se esperan $expectedColumns columnas."]);
            exit;
        }

        $conexion = new Conexion();
        $actualizados = 0;
        $fila = 1;

        while (($datos = fgetcsv($archivo)) !== false) {
            $fila++;
            if (count($datos) != $expectedColumns) {
                continue; // Saltar fila malformada
            }

            for ($i = 0; $i <= 2; $i++) {
                $encoding = mb_detect_encoding($datos[$i], ['ISO-8859-1', 'Windows-1252', 'UTF-8'], true);
                if ($encoding !== 'UTF-8') {
                    $datos[$i] = mb_convert_encoding($datos[$i], 'UTF-8', $encoding);
                }
            }

            $parTemp = new Cirugia();
            $parTemp->cargarDesdeCSV($datos);

            // Validar que coincidan con los filtros
            if (
                ($filtro_clave !== "" && $filtro_clave !== "ninguno" && $parTemp->clave !== $filtro_clave) ||
                ($filtro_especialidad !== "" && $filtro_especialidad !== "ninguno" && $parTemp->especialidad !== $filtro_especialidad) ||
                ($filtro_division !== "" && $filtro_division !== "ninguno" && $parTemp->division !== $filtro_division) ||
                ($parTemp->anio != $filtro_anio)
            ) {
                continue; // Saltar si no cumple los filtros
            }

            // Buscar coincidencia exacta
            $claveEsc = mysqli_real_escape_string($conexion->getConexion(), $parTemp->clave);
            $espEsc = mysqli_real_escape_string($conexion->getConexion(), $parTemp->especialidad);
            $divEsc = mysqli_real_escape_string($conexion->getConexion(), $parTemp->division);

            $whereSQL = "clave = '$claveEsc' AND anio = $parTemp->anio AND especialidad = '$espEsc' AND division = '$divEsc'";
            $queryVerificar = "SELECT * FROM paciente WHERE $whereSQL";
            $coincidencias = $conexion->consultar($queryVerificar);

            if (!empty($coincidencias)) {
                $parTemp->editarSinID();
                $actualizados++;
            }
        }

        fclose($archivo);
        $conexion->cerrar();

        echo json_encode([
            'status' => 'success',
            'message' => "Actualización completada. Registros actualizados: $actualizados"
        ]);
        exit;
        break;


    default:
        header('Location: ../vistas/cirugia/cirugia_inicio.php');
        exit;
}

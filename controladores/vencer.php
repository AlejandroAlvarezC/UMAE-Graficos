<?php
require_once '../modelos/Vencer.php';

$accion = $_POST['a'] ?? $_GET['a'] ?? '';
$v = new Vencer();

switch ($accion) {
    case 'Ingresar':
        if (!empty($_POST['folio']) && !empty($_POST['evento']) && !empty($_POST['anio'])) {
            $v->cargarDesdeFormulario($_POST);

            $modo = $_POST['modo'] ?? 'insertar';

            if ($modo === 'insertar' && $v->folioEventoAnioExiste($v->folio, $v->evento, $v->anio)) {
                header('Location: ../vistas/productividad/ingresar-vencer.php?error=Ya existe esa clave para ese año y ese evento');
                exit;
            }

            $resultado = $v->ingresar();

            if ($resultado === 'insertado' || $resultado === 'actualizado') {
                header('Location: ../vistas/productividad/vencer.php?msg=Operación exitosa');
            } else {
                header('Location: ../vistas/productividad/vencer.php?error=No se realizó ningún cambio');
            }
            exit;
        } else {
            header('Location: ../vistas/productividad/vencer.php?error=Faltan datos');
            exit;
        }
        break;

    case 'Eliminar':
        if (!empty($_GET['id'])) {
            $v->id = base64_decode($_GET['id']);
            $v->eliminar();
            header('Location: ../vistas/productividad/vencer.php?msg=Eliminado');
            exit;
        }
        break;

  case 'Editar':
    if (!empty($_POST['id'])) {
        $v->id = base64_decode($_POST['id']);
        $v->cargarDesdeFormulario2($_POST);
        $v->editar();

        header('Location: ../vistas/productividad/vencer.php?msg=Editado');
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

        $encabezado = fgetcsv($archivo); // Saltamos encabezado
        $numColumnasEsperadas = 17;

        $filaActual = 1;
        $totales = ['success' => 0, 'actualizado' => 0, 'colision' => 0, 'error' => 0];
        $mensajesError = [];

        while ($datos = fgetcsv($archivo)) {
    $filaActual++;

    // Saltar fila si está completamente vacía
    if (count(array_filter($datos)) === 0) {
        continue;
    }

    if (count($datos) !== $numColumnasEsperadas) {
        fclose($archivo);
        echo json_encode([
            'status' => 'error',
            'message' => "Error en fila $filaActual: Se esperaban $numColumnasEsperadas columnas, se encontraron " . count($datos)
        ]);
        exit;
    }

    $vencer = new Vencer();
    $vencer->cargarDesdeCSV($datos);
    $resultado = $vencer->ingresar2();

    switch ($resultado) {
        case 'insertado': $totales['success']++; break;
        case 'actualizado': $totales['actualizado']++; break;
        case 'colision_sin_cambios': $totales['colision']++; break;
        default:
            $totales['error']++;
            $mensajesError[] = "❌ Error en fila $filaActual: no se pudo insertar ni actualizar.";
    }
}


        fclose($archivo);

        $mensajesResumen = [];
        if ($totales['success']) $mensajesResumen[] = "Registros insertados: {$totales['success']}";
        if ($totales['actualizado']) $mensajesResumen[] = "Registros actualizados: {$totales['actualizado']}";
        if ($totales['colision']) $mensajesResumen[] = "Registros ya existentes sin cambios: {$totales['colision']}";
        if ($totales['error']) {
            $mensajesResumen[] = "Errores: {$totales['error']}";
            $mensajesResumen = array_merge($mensajesResumen, $mensajesError);
        }

        echo json_encode([
            'status' => 'success',
            'message' => implode('<br>', $mensajesResumen)
        ]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'No se seleccionó archivo.']);
    exit;
    break;

  case 'ReemplazarCSVFiltrado':
    header('Content-Type: application/json');
    require_once '../modelos/Vencer.php';

    $expectedColumns = 17;

    $filtro_anio = $_POST['filtro_anio'] ?? '';
    $filtro_folio = $_POST['filtro_folio'] ?? '';
    $filtro_evento = $_POST['filtro_evento'] ?? '';

    // Validar filtro obligatorio
    if ($filtro_anio === "" || $filtro_anio === "ninguno") {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'El filtro Año es obligatorio.']);
        exit;
    }

    // Validar archivo
    if (empty($_FILES['csv']['tmp_name']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Archivo CSV no válido.']);
        exit;
    }

    // Abrir archivo CSV
    $archivo = fopen($_FILES['csv']['tmp_name'], 'r');
    if (!$archivo) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al abrir el archivo CSV.']);
        exit;
    }

    // Leer encabezado y validar columnas
    $header = fgetcsv($archivo, 0, ',');
    if (!$header || count($header) != $expectedColumns) {
        fclose($archivo);
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => "El archivo debe tener exactamente $expectedColumns columnas. Se detectaron " . count($header)
        ]);
        exit;
    }

    // Inicializar contadores
    $conexion = new Conexion();
    $actualizados = 0;
    $sinCoincidencia = 0;
    $malFormateadas = 0;

    // Procesar cada fila
    while (($datos = fgetcsv($archivo, 0, ',')) !== false) {
        if (count($datos) != $expectedColumns) {
            $malFormateadas++;
            continue;
        }

        // Ignorar filas completamente vacías
if (count(array_filter($datos)) === 0) {
    continue;
}


        // Convertir campos clave a UTF-8 para evitar errores con acentos
        foreach ($datos as $i => $valor) {
            $encoding = mb_detect_encoding($valor, ['ISO-8859-1', 'Windows-1252', 'UTF-8'], true);
            if ($encoding !== 'UTF-8') {
                $datos[$i] = mb_convert_encoding($valor, 'UTF-8', $encoding);
            }
        }

        $v = new Vencer();
        $v->cargarDesdeCSV($datos);

        // Comparar con filtros
        if (
            $v->anio != $filtro_anio ||
            ($filtro_folio !== '' && $filtro_folio !== 'ninguno' && $v->folio !== $filtro_folio) ||
            ($filtro_evento !== '' && $filtro_evento !== 'ninguno' && $v->evento !== $filtro_evento)
        ) {
            $sinCoincidencia++;
            continue;
        }

        // Actualizar si coincide
        if ($v->editarSinID()) {
            $actualizados++;
        }
    }

    fclose($archivo);
    $conexion->cerrar();

    echo json_encode([
        'status' => 'success',
        'message' => "Actualización completada.",
        'resumen' => [
            'actualizados' => $actualizados,
            'sinCoincidencia' => $sinCoincidencia,
            'malFormateadas' => $malFormateadas
        ]
    ]);
    exit;
    break;

  default:
        header('Location: ../vistas/productividad/vencer.php');
        exit;

}


?>
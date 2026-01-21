<?php
require_once '../modelos/Normatividad.php';

$accion = $_POST['a'] ?? $_GET['a'] ?? '';

if ($accion === 'Subir') {
    $manual = new Manual();
    $manual->normatividad = $_POST['normatividad'];
    $manual->nombre = $_POST['nombre'];
    $manual->anio = $_POST['anio'];
    $manual->entidad = $_POST['entidad'];
    $manual->servicio = $_POST['servicio'];
    $manual->fecha = $_POST['fecha'];
    $manual->direccion = $_POST['direccion'];

    $directorio = '../archivos/manuales/';
    $nombreFinal = time() . '_' . basename($_FILES['archivo']['name']);
    $rutaDestino = $directorio . $nombreFinal;

    if (move_uploaded_file($_FILES['archivo']['tmp_name'], $rutaDestino)) {
        $manual->archivo = $nombreFinal;
        $manual->ingresar();
    }
} elseif ($accion === 'Editar') {
    $manual = new Manual();
    $manual->id = base64_decode($_POST['id']);
    $manual->normatividad = $_POST['normatividad'];
    $manual->nombre = $_POST['nombre'];
    $manual->anio = $_POST['anio'];
    $manual->entidad = $_POST['entidad'];
    $manual->servicio = $_POST['servicio'];
    $manual->fecha = $_POST['fecha'];
    $manual->direccion = $_POST['direccion'];

    $directorio = '../archivos/manuales/';

    if (!empty($_FILES['archivo']['name'])) {
        // Eliminar archivo viejo si existe
        $archivoViejo = $directorio . $_POST['archivo_actual'];
        if (!empty($_POST['archivo_actual']) && file_exists($archivoViejo)) {
            unlink($archivoViejo);
        }

        $nombreFinal = time() . '_' . basename($_FILES['archivo']['name']);
        $rutaDestino = $directorio . $nombreFinal;

        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $rutaDestino)) {
            $manual->archivo = $nombreFinal;
        } else {
            // Si no se pudo subir, conservar el archivo viejo
            $manual->archivo = $_POST['archivo_actual'];
        }
    } else {
        // No se subió archivo nuevo, conservar el archivo viejo
        $manual->archivo = $_POST['archivo_actual'];
    }

    $manual->editar();
} elseif ($accion === 'elim') {
    $manual = new Manual();
    $manual->id = base64_decode($_GET['id']);

    // Obtener datos del manual para saber qué archivo eliminar
    $manualDatos = Manual::obtenerPorId($manual->id);

    $directorio = '../archivos/manuales/';
    $archivo = $directorio . $manualDatos[7]; // índice 7 es el campo archivo

    if (!empty($manualDatos[7]) && file_exists($archivo)) {
        unlink($archivo);
    }

    $manual->eliminar();
}

header("Location: ../vistas/normatividad/normatividad_inicio.php");
exit;

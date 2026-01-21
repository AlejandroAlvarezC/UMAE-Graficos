<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['login_error'] = "Debes iniciar sesión como administrador para acceder a esa sección.";
    header('Location: ../admin/login.php');  // O la ruta correcta de tu login
    exit();
}

require_once '../../modelos/Urgencia.php';
require_once '../../modelos/Paramedicos.php';
require_once '../../modelos/Especialidad_Ocasion.php';

$meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

// Obtener datos desde cada clase, agrupados por año
$urgencias = Urgencia::obtenerTotalesPorMes();
$paramedicos = Paramedicos::obtenerTotalesPorMes();
$especialidades = Especialidad_Ocasion::obtenerTotalesPorMes();

// Etiquetar con su respectiva división y convertir índices a claves
function procesarDatos($datos, $division, $meses)
{
    $resultado = [];
    foreach ($datos as $fila) {
        $registro = [];
        $registro['anio'] = $fila[0];
        $registro['division'] = $division;
        // Los meses empiezan en índice 1
        foreach ($meses as $i => $mes) {
            $registro[$mes] = isset($fila[$i + 1]) ? (int)$fila[$i + 1] : 0;
        }
        $resultado[] = $registro;
    }
    return $resultado;
}

// Procesar cada conjunto de datos
$datosUrgencias = procesarDatos($urgencias, 'Urgencias', $meses);
$datosEspecialidades = procesarDatos($especialidades, 'Especialidades', $meses);
$datosParamedicos = procesarDatos($paramedicos, 'Paramédicos', $meses);

// Unificar todos los datos
$datosUnificados = array_merge($datosUrgencias, $datosEspecialidades, $datosParamedicos);

$anios = array_unique(array_column($datosUnificados, 'anio'));
sort($anios);

$unidades = array_unique(array_column($datosUnificados, 'division'));
sort($unidades); // opcional, para ordenarlas alfabéticamente


// Para mostrar filas en la tabla HTML
function mostrarFilaUnidad($datos, $meses)
{
    foreach ($datos as $fila) {
        echo "<tr>";
        echo "<td><strong>{$fila['division']}</strong></td>";
        foreach ($meses as $mes) {
            $valor = (int)($fila[$mes] ?? 0);
            echo "<td>$valor</td>";
        }
        $total = array_sum(array_intersect_key($fila, array_flip($meses)));
        echo "<td><strong>$total</strong></td>";
        echo "<td>{$fila['anio']}</td>";
        echo "</tr>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap y estilos -->
    <link rel="stylesheet" href="../../css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="icon" type="image/png" href="../../logo-imss.png">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- CSS de Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- JS de Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <title>Productividad total | Administrador</title>
    <style>
        /* Estructura del layout */
        html,
        body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        /* Contenedor principal que ocupa el espacio disponible */
        .content {
            flex: 1;
        }


        /* Tabla Bootstrap estilizada */
        /* === Tabla elegante y profesional === */
        table.table {
            font-size: 0.92rem;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
        }

        table.table thead th {
            background: linear-gradient(135deg, #297190ff, #1f416dff);
            color: #ffffff;
            font-weight: 600;
            padding: 12px 10px;
            border-bottom: 2px solid #e0e0e0;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }

        table.table tbody td {
            padding: 10px 12px;
            text-align: center;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
            background-color: #fff;
            transition: background-color 0.3s ease;
        }

        table.table tbody tr:hover td {
            background-color: #f9f9fb;
        }

        table.table tfoot td {
            font-weight: bold;
            background-color: #eaf4f3;
            border-top: 2px solid #ccc;
        }

        /* Estilo para celda de totales */
        td.total-columna {
            background-color: #f2edf3 !important;
            font-weight: bold;
            color: #4a2e59;
            box-shadow: inset 0 0 5px rgba(18, 92, 122, 0.15);
        }

        td.total-fila {
            background-color: #f8e6ed !important;
            font-weight: bold;
            color: #7a123a;
        }

        /* Bordes redondeados solo en extremos */
        table.table thead th:first-child {
            border-top-left-radius: 12px;
        }

        table.table thead th:last-child {
            border-top-right-radius: 12px;
        }

        table.table tbody tr:last-child td:first-child {
            border-bottom-left-radius: 12px;
        }

        table.table tbody tr:last-child td:last-child {
            border-bottom-right-radius: 12px;
        }

        /* Contenedor principal de Select2 */
        .select2-container--default .select2-selection--multiple {
            width: 100%;
            border-radius: 14px;
            padding: 8px 12px;
            font-size: 1rem;
            font-weight: 500;
            color: #1a1a1a;
            background-color: #ffffff;
            border: 1.5px solid #ccd3ddff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
            cursor: pointer;
            min-height: 48px;
        }

        /* -----------------------
Filtros
-------------------------*/

        /* Etiquetas seleccionadas */
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #2b3d7aff;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: 500;
            padding: 4px 10;
            margin-top: 4px;
        }

        /* Botón de cerrar en las etiquetas */
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #ffffff;
            margin-right: 6px;
        }

        /* Flechita del dropdown */
        .select2-container--default .select2-selection--multiple .select2-selection__arrow {
            top: 50%;
            transform: translateY(-50%);
        }

        /* Al enfocar el select */
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #2b667aff;
            box-shadow: 0 0 0 2px rgba(43, 122, 120, 0.1);
        }


        /* Selector de año */
        #filtroAniosGraficos {
            min-height: 120px;
            font-size: 14px;
            padding: 5px;
        }


        #selectorAnioContainer {
            max-width: 200px;
        }

        .filter-container-inline {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            justify-content: flex-start;
            padding: 10px 15px;
            background-color: #f7f9fb;
            border: 1.5px solid #cddbe2;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 1rem;
        }

        .filter-container-inline label {
            font-weight: 600;
            color: #2b2b2b;
            margin-right: 6px;
            font-size: 0.95rem;
        }

        .select-personalizado {
            border-radius: 12px;
            border: 1.5px solidrgb(122, 26, 71);
            /* mismo azul sobrio que el buscador */
            padding: 7px 12px;
            font-weight: 500;
            color: #0a1618;
            background-color: #fff;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .select-personalizado:focus,
        .select-personalizado:hover {
            border-color: #7a123a;
            box-shadow: 0 0 6px rgba(214, 26, 136, 0.25);
            outline: none;
        }



        /* Contenedor gráfico */
        #graficoCard {
            border: 1.5px solid #7a123a;
            border-radius: 10px;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 12px rgb(122 18 58 / 0.15);
            margin-bottom: 30px;
        }

        #graficoCard h4 {
            color: #7a123a;
            font-weight: 700;
            margin-bottom: 15px;
        }

        /* Contenedor relativo para posicionar el icono */
        .dataTable-input {
            padding-left: 35px;
            position: relative;
            background-image: url("../../img/Iconos/buscar.png");
            background-repeat: no-repeat;
            background-size: 18px;
            background-position: 10px center;
            transition: box-shadow 0.3s ease, transform 0.2s ease;
        }

        /* Animación al enfocar */
        .dataTable-input:focus {
            outline: none;
            box-shadow: 0 0 10px rgba(18, 77, 122, 0.5);
            transform: scale(1.02);
        }

        /* Estilo para el selector de cantidad de registros */
        .dataTable-selector {
            border-radius: 10px;
            padding: 7px 15px;
            border: 1.5px solid #7a123a;
            color: #7a123a;
            font-weight: 500;
            box-shadow: 0 0 5px rgb(122 18 58 / 0.2);
        }

        /* Ajustes al texto "registros por página" */
        .dataTable-dropdown label {
            font-weight: 600;
            color: #333;
        }

        /* Contenedor general para filtros */
        .filter-container-inline {
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            align-items: flex-end;
            gap: 1rem;
            /* Espacio entre filtros */
            margin-bottom: 1rem;
            flex-wrap: wrap;
            /* Para que no se desborde en pantallas pequeñas */
        }

        #selectorAnioContainer,
        #selectorEspecialidadContainer,
        #selectorDescripcionContainer {
            min-width: 180px;
            flex: 1 1 auto;
        }


        /* Ocultar con clase hidden */
        .hidden {
            display: none;
        }

        /* Etiqueta */
        .filter-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #3a3a3a;
            font-size: 1rem;
        }

        /* Select personalizado */
        .form-select {
            width: 100%;
            padding: 10px 14px;
            font-size: 1rem;
            border: 1.8px solid #ccd6f6;
            border-radius: 12px;
            background-color: #fff;
            color: #333;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .form-select:hover {
            border-color: #7a92ff;
            box-shadow: 0 0 8px #7a92ff88;
        }

        .form-select:focus {
            outline: none;
            border-color: #4a69ff;
            box-shadow: 0 0 8px #4a69ffcc;
        }

        /* Margen inferior para separar cuando hay varios filtros */
        .mb-3 {
            margin-bottom: 1rem !important;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .filter-container {
                max-width: 90%;
                padding: 12px 15px;
            }

            .filter-container label {
                font-size: 0.9rem;
            }

            .form-select {
                font-size: 0.95rem;
            }
        }

        .dataTable-search input {
            border-radius: 12px;
            padding: 8px 14px;
            border: 1.5px solid #112f5fff;
            background-color: #f9f9f9;
            color: #1a1a1a;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .dataTable-search input:focus {
            border-color: #1ab1d6;
            outline: none;
            box-shadow: 0 0 6px rgba(26, 120, 214, 0.25);
        }

        .dataTable-search {
            padding: 5px;
        }

        /* Selector de cantidad de registros */
        .dataTable-selector {
            border-radius: 12px;
            padding: 6px 12px;
            border: 1.5px solid #114b5f;
            background-color: #fff;
            font-weight: 500;
            color: #11345fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Estilos del footer */
        footer {
            background-color: #7a123a;
            width: 100%;
            text-align: center;
            color: white;
            font-weight: bold;
            padding-top: 10px;
        }

        /* hover nav */
        .nav-link {
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: rgb(204, 167, 17) !important;

        }

        .navbar-brand {
            transition: color 0.3s;
        }

        .navbar-brand:hover {
            color: #9A7D0A !important;
        }

        .dropdown-submenu {
            display: none;
            margin-left: 1rem;
        }

        .dropdown-submenu-toggle.active+.dropdown-submenu {
            display: block;
        }

        .dropdown-submenu-toggle {
            cursor: pointer;
        }

        /* Cambiar el color de fondo cuando el submenú está activo */
        .dropdown-item.dropdown-submenu-toggle.active,
        .dropdown-item.dropdown-submenu-toggle:hover {
            background-color: rgb(202, 155, 26) !important;
            color: white;
        }

        .section-header {
            background-color: #dee2e6;
            /* Fondo gris claro */
            border-left: 5px solid #296590ff;
            /* MANTENEMOS tu color rojo */
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .section-header h1 {
            color: #343a40;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .section-header small {
            color: #6c757d;
            font-weight: 500;
        }

        /* === Botones modernos y elegantes === */

        .btn-success {
            background: linear-gradient(135deg, #294390ff, #1f6d67);
            border: none;
            border-radius: 12px;
            padding: 12px 30px;
            font-weight: 700;
            font-size: 1.1rem;
            color: white !important;
            box-shadow: 0 4px 12px rgba(41, 115, 144, 0.3);
            cursor: pointer;
            transition: background 0.4s ease, box-shadow 0.3s ease, transform 0.15s ease;
            user-select: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-success:hover,
        .btn-success:focus {
            background: linear-gradient(135deg, #0f2836ff, #2f8898ff);
            box-shadow: 0 6px 16px rgba(54, 178, 194, 0.5);
            transform: scale(1.05);
            outline: none;
        }

        .btn-success:active {
            transform: scale(0.98);
            box-shadow: 0 4px 12px rgba(41, 160, 104, 0.6);
        }

        .btn-success svg,
        .btn-success img {
            width: 20px;
            height: 20px;
        }


        /* ----- Armoniza estilos de DataTables con tu diseño ----- */

        #tfootTotales th {
            text-align: center;
            vertical-align: middle;
            font-weight: 600;
        }

        table.dataTable {
            width: 100% !important;
            font-size: 0.9rem;
            border-collapse: collapse !important;
            border-spacing: 0;
            background-color: white;
            border: none;
        }

        table.dataTable th,
        table.dataTable td {
            padding: 0.3rem !important;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }

        table.dataTable thead {
            background-color: rgba(34, 109, 117, 1);
            color: white;
            font-weight: 600;
        }

        table.dataTable thead th {
            border-color: rgb(170, 213, 211);
        }

        table.dataTable tbody tr:hover {
            box-shadow: 0 4px 12px rgba(18, 122, 65, 0.5);
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
            font-size: 0.95rem;
            color: #333;
        }

        .dataTables_wrapper .dataTables_filter input {
            border-radius: 14px;
            padding: 8px 12px;
            border: 1.5px solid #ccd6dd;
            background-color: #fff;
            transition: box-shadow 0.3s ease;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 14px;
            padding: 6px 12px;
            border: 1.5px solid #ccd6dd;
            background-color: #fff;
            font-weight: 500;
            color: #114b5f;
        }

        .dataTables_wrapper .dataTables_info {
            font-size: 0.9rem;
            color: #333;
            margin-top: 10px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 3px;
            padding: 6px 12px;
            margin: 0 2px;
            border: none;
            background: rgb(255, 255, 255);
            color: black !important;
            transition: background 0.3s ease;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: rgba(255, 255, 255, 0.68);
            color: black !important;
        }
    </style>
</head>

<body style="background-color: aliceblue;">


    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #7a123a;">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link text-white" href="../admin/admin.php">INICIO</a></li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#"
                            data-bs-toggle="dropdown">Productividad</a>
                        <ul class="dropdown-menu" data-bs-auto-close="outside">

                            <!-- Consulta externa -->
                            <li>
                                <a class="dropdown-item dropdown-submenu-toggle">Consulta externa</a>
                                <ul class="dropdown-submenu">
                                    <li><a class="dropdown-item" href="./unidades_que_reportan.php">Productividad total</a></li>
                                    <li><a class="dropdown-item" href="./paramedicos.php">Paramédicos</a>
                                    </li>

                                    <!-- Especialidades -->
                                    <li>
                                        <a class="dropdown-item dropdown-submenu-toggle">Especialidades</a>
                                        <ul class="dropdown-submenu">
                                            <li><a class="dropdown-item"
                                                    href="./especialidades_inicio.php">Especialidades</a>
                                            </li>
                                            <li><a class="dropdown-item" href="./Especialidad_Ocasion.php">Especialidad
                                                    de
                                                    ocasión</a></li>
                                        </ul>
                                    </li>

                                </ul>
                            </li>

                            <!-- Hospitalización -->
                            <li>
                                <a class="dropdown-item dropdown-submenu-toggle">Hospitalización</a>
                                <ul class="dropdown-submenu">
                                    <li><a class="dropdown-item" href="../hospitalizacion/ingresos_inicio.php">Ingresos</a></li>
                                    <li><a class="dropdown-item" href="../hospitalizacion/egresos_inicio.php">Egresos</a></li>
                                    <li><a class="dropdown-item" href="../hospitalizacion/pacientes_inicio.php">Días Paciente</a></li>
                                    <li><a class="dropdown-item" href="#">Días Cama</a></li>
                                </ul>
                            </li>

                            <!-- Cirugía -->
                            <li><a class="dropdown-item" href="../cirugia/cirugia_inicio.php">Cirugía</a></li>

                            <!-- Urgencias -->
                            <li><a class="dropdown-item" href="../productividad/urgencias_inicio.php">Urgencias</a></li>

                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="../admin/Personal/personal.php">Organigrama</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="../productividad/vencer.php">Vencer</a>
                    </li>

                    <!-- Normatividad -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../normatividad/normatividad_inicio.php">Normatividad</a>
                    </li>

                    <li class="nav-item"><a class="nav-link text-white" href="../admin/usuariosAdmin.php">Usuario</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <a class="btn btn-outline-light" onclick="anterior()" href="#" title="Atrás"><i class="bi bi-arrow-left"></i></a>
                    <a class="btn btn-outline-light" href="../usuario/unidadesreportan_user.php">Vista de usuario</a>

                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../usuario/unidadesreportan_user.php"><i class="fas fa-eye me-2"></i>Ver
                                    como Usuario</a></li>
                            <li><a class="dropdown-item" href="../admin/usuariosAdmin.php"><i
                                        class="fas fa-id-badge me-2"></i>Perfil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="../admin/logout.php"><i
                                        class="fas fa-sign-out-alt me-2"></i>Cerrar sesión</a></li>
                        </ul>
                    </div>
                </div>
            </div>
    </nav>



    <div class="content">
        <div class="container my-4">
            <div class="section-header">
                <h1 class="h2 mb-0">Productividad Total</h1>
                <small class="text-muted">Listado de registros.</small>
            </div>
            <div>
                <hr>
                <div class="d-flex gap-2 align-items-center justify-content-center mb-3">
                    <form id="formDescargarExcel" action="./descargar_excel_unidades_que_reportan.php" method="post" class="m-0">
                        <input type="hidden" name="anio" id="inputAnio">
                        <input type="hidden" name="especialidad" id="inputEspecialidad">
                        <input type="hidden" name="division" id="inputDivision">
                        <button class="btn btn-success" type="submit">🟩 Descargar Excel con Gráfico</button>
                    </form>

                    <a id="btnDescargarGraficoPDF2" class="btn btn-success">📈 Descargar PDF</a>
                    <a id="btnDescargarImagenesCanvas" class="btn btn-success">🖼️ Descargar Imagen</a>
                    <a id="btnGrafico" class="btn btn-success" onclick="toggleGraficoUnidades()">📊 Ver Gráfico</a>
                </div>
                <hr>


                <!-- Contenedor de los gráficos -->
                <div id="graficoCard" style="display:none;">

                    <div class="row mb-3" id="filtroGraficoContainer" style="flex-wrap: wrap;">
                        <div class="col-md-6 col-sm-12 d-flex align-items-center gap-2">
                            <label for="filtroAniosGraficos" class="form-label mb-0 fw-semibold text-dark">Año:</label>
                            <select id="filtroAniosGraficos" class="form-select form-select-sm select-multiple" multiple>
                                <?php foreach ($anios as $anio): ?>
                                    <option value="<?= $anio ?>"><?= $anio ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 col-sm-12 d-flex align-items-center gap-2">
                            <label for="filtroUnidadesGraficos" class="form-label mb-0 fw-semibold text-dark">Unidad:</label>
                            <select id="filtroUnidadesGraficos" class="form-select form-select-sm select-multiple" multiple>
                                <?php foreach ($unidades as $unidad): ?>
                                    <option value="<?= $unidad ?>"><?= $unidad ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-header">
                                    <h5 class="mb-0">Gráfico - Total Mensual</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="graficoParamedicos" width="400" height="180"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-header">
                                    <h5 class="mb-0">Gráfico - Total Anual</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="graficoTotales" width="400" height="180"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mt-5">
                    <div class="card-header">
                        <h5 class="mb-0">Tabla de Productividad Total</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">

                            <div class="row mb-3" style="flex-wrap: wrap;">
                                <div class="col-md-6 col-sm-12 d-flex align-items-center gap-2">
                                    <label for="filtroAnioTablaUnidades" class="form-label mb-0 fw-semibold text-dark">Año:</label>
                                    <select id="filtroAnioTablaUnidades" class="form-select form-select-sm select-multiple" multiple>
                                        <?php foreach ($anios as $anio): ?>
                                            <option value="<?= $anio ?>"><?= $anio ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Donde están tus filtros para la tabla (como el de año) -->
                                <div class="col-md-6 col-sm-12 d-flex align-items-center gap-2">
                                    <label for="filtroUnidadesTabla" class="form-label mb-0 fw-semibold text-dark">Unidad:</label>
                                    <select id="filtroUnidadesTabla" class="form-select form-select-sm select-multiple" multiple>
                                        <?php foreach ($unidades as $unidad): ?>
                                            <option value="<?= $unidad ?>"><?= $unidad ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                            </div>




                            <table class="table text-center align-middle" id="tablaUnidades">
                                <thead class="table-light">
                                    <tr>
                                        <th>Unidad</th>
                                        <?php foreach ($meses as $mes): ?>
                                            <th><?= ucfirst($mes) ?></th>
                                        <?php endforeach; ?>
                                        <th>Total Anual</th>
                                        <th>Año</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($datosUnificados as $dato): ?>
                                        <tr>
                                            <td><strong><?= $dato['division'] ?></strong></td>
                                            <?php
                                            $total = 0;
                                            foreach ($meses as $mes):
                                                $valor = (int)($dato[$mes] ?? 0);
                                                $total += $valor;
                                            ?>
                                                <td><?= $valor ?></td>
                                            <?php endforeach; ?>
                                            <td style="background-color: #f1f2fbff;"><strong><?= $total ?></strong></td>
                                            <td><?= $dato['anio'] ?? 'N/D' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>

                                <tfoot id="tfootTotales" class="table-light text-center align-middle">
                                    <tr>
                                        <th style="background-color: #f1f2fbff;">Total General</th>
                                        <?php foreach ($meses as $mes): ?>
                                            <th style="background-color: #f1f2fbff;" class="total-<?= $mes ?> fw-semibold"></th>
                                        <?php endforeach; ?>
                                        <th style="background-color: #8899eeff; font-weight: 1000; color: #0a123aff; font-size: medium" class="total-anual fw-semibold "></th>
                                        <th></th>
                                    </tr>
                                </tfoot>


                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        <footer>
            <p>Derechos reservados &copy; IMSS 2025</p>
        </footer>

        <script>
            const datosUnidades = <?= json_encode($datosUnificados) ?>;
        </script>


        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>


        <script>
            const coloresFijos = [
                '#1f77b4', '#ff7f0e', '#2ca02c', '#d62728',
                '#9467bd', '#8c564b', '#e377c2', '#7f7f7f',
                '#bcbd22', '#17becf'
            ];

            let graficoMensual, graficoAnual;

            function toggleGraficoUnidades() {
                const contenedor = document.getElementById('graficoCard');
                const boton = document.getElementById('btnGrafico');
                const filtroGrafico = document.getElementById('filtroGraficoContainer');
                const visible = contenedor.style.display === 'block';

                contenedor.style.display = visible ? 'none' : 'block';
                filtroGrafico.style.display = visible ? 'none' : 'flex';

                boton.textContent = visible ? '📊 Ver Gráfico' : '❌ Ocultar Gráfico';

                if (!visible) {
                    const filtros = getFiltrosSeleccionados();
                    sincronizarSelects(filtros); // 🔄 sincronizar selects primero
                    actualizarGraficoUnidades(filtros.anios, filtros.unidades); // ✅ aplicar filtros activos
                }
            }


            function actualizarGraficoUnidades(aniosFiltro = [], unidadesFiltro = []) {
                const meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio",
                    "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
                ];

                let filtrados = datosUnidades;

                if (aniosFiltro.length > 0) {
                    filtrados = filtrados.filter(d => aniosFiltro.includes(String(d.anio)));
                }
                if (unidadesFiltro.length > 0) {
                    filtrados = filtrados.filter(d =>
                        unidadesFiltro.includes(d.division.trim())
                    );

                }

                const datasetsMensual = filtrados.map((unidad, i) => ({
                    label: unidad.division + " (" + unidad.anio + ")",
                    data: meses.map(m => parseInt(unidad[m] || 0)),
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4,
                    borderColor: coloresFijos[i % coloresFijos.length]
                }));

                // Total mensual desde la tabla (tfoot)
                const totalMensual = meses.map(mes => {
                    const celda = document.querySelector(`.total-${mes}`);
                    if (!celda) return 0;
                    return parseInt(celda.innerText.replace(/,/g, '')) || 0;
                });

                // Línea punteada negra
                datasetsMensual.push({
                    label: "Total General",
                    data: totalMensual,
                    borderWidth: 3,
                    fill: false,
                    tension: 0.3,
                    borderDash: [5, 5],
                    borderColor: '#000',
                    pointBackgroundColor: '#000'
                });

                // Gráfico mensual
                if (graficoMensual) graficoMensual.destroy();
                const ctxMensual = document.getElementById('graficoParamedicos').getContext('2d');
                graficoMensual = new Chart(ctxMensual, {
                    type: 'line',
                    data: {
                        labels: meses.map(m => m.charAt(0).toUpperCase() + m.slice(1)),
                        datasets: datasetsMensual
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        }
                    }
                });

                // Gráfico anual
                const resumenAnual = filtrados.map(u => ({
                    division: u.division + " (" + u.anio + ")",
                    total: meses.reduce((sum, mes) => sum + parseInt(u[mes] || 0), 0)
                }));

                const totalAnualGeneralCelda = document.querySelector('.total-anual');
                const totalAnual = totalAnualGeneralCelda ?
                    parseInt(totalAnualGeneralCelda.innerText.replace(/,/g, '')) || 0 :
                    0;

                if (totalAnual > 0) {
                    resumenAnual.push({
                        division: "Total General Anual",
                        total: totalAnual
                    });
                }

                const bgColors = resumenAnual.map(r =>
                    r.division === "Total General Anual" ? '#000' : coloresFijos[resumenAnual.indexOf(r) % coloresFijos.length]
                );
                const borderColors = resumenAnual.map(r =>
                    r.division === "Total General Anual" ? '#111' : coloresFijos[resumenAnual.indexOf(r) % coloresFijos.length]
                );

                if (graficoAnual) graficoAnual.destroy();
                const ctxTotales = document.getElementById('graficoTotales').getContext('2d');
                graficoAnual = new Chart(ctxTotales, {
                    type: 'bar',
                    data: {
                        labels: resumenAnual.map(r => r.division),
                        datasets: [{
                            label: 'Total anual',
                            data: resumenAnual.map(r => r.total),
                            backgroundColor: bgColors,
                            borderColor: borderColors,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true
                            },
                            datalabels: {
                                anchor: 'end',
                                align: 'start',
                                color: '#555',
                                font: {
                                    weight: 'bold',
                                    size: 14
                                },
                                formatter: value => value.toLocaleString('es-MX')
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            }
        </script>


        <script>
            function getAniosSeleccionados() {
                return $('#filtroAnioTablaUnidades').val() || [];
            }


            function sincronizarSelects(valores) {
                $('#filtroAnioTablaUnidades').val(valores.anios);
                $('#filtroAniosGraficos').val(valores.anios);

                $('#filtroUnidadesTabla').val(valores.unidades);
                $('#filtroUnidadesGraficos').val(valores.unidades);

                $('.select-multiple').trigger('change.select2');
            }


            function getFiltrosSeleccionados() {
                return {
                    anios: $('#filtroAnioTablaUnidades').val() || [],
                    unidades: $('#filtroUnidadesTabla').val() || []
                };
            }

            function getFiltrosGraficosSeleccionados() {
                return {
                    anios: $('#filtroAniosGraficos').val() || [],
                    unidades: $('#filtroUnidadesGraficos').val() || []
                };
            }


            function actualizarDesdeTabla() {
                const filtros = getFiltrosSeleccionados();
                sincronizarSelects(filtros);
                aplicarFiltros(filtros);
            }

            function actualizarDesdeGraficos() {
                const filtros = getFiltrosGraficosSeleccionados();

                // sincronizar con los selects de la tabla
                sincronizarSelects(filtros);

                // aplicar filtros tanto a tabla como a gráficos
                aplicarFiltros(filtros);
            }

            function aplicarFiltros({
                anios,
                unidades
            }) {
                const tabla = $('#tablaUnidades').DataTable();

                // Asegurar que todos los valores son cadenas
                const regexAnios = (anios || []).map(String).join('|');
                const regexUnidades = (unidades || []).map(String).join('|');

                const indexAnio = <?= count($meses) + 2 ?>;
                const indexUnidad = 0;

                tabla.column(indexAnio).search(regexAnios, true, false);
                tabla.column(indexUnidad).search(regexUnidades, true, false);
                tabla.draw();

                actualizarGraficoUnidades(anios, unidades);
            }


            function sincronizarSelects(valores) {
                $('#filtroAnioTablaUnidades').val(valores.anios).trigger('change.select2');
                $('#filtroAniosGraficos').val(valores.anios).trigger('change.select2');

                $('#filtroUnidadesTabla').val(valores.unidades).trigger('change.select2');
                $('#filtroUnidadesGraficos').val(valores.unidades).trigger('change.select2');
            }

            function actualizarTodo() {
                const {
                    anios,
                    unidades
                } = getFiltrosSeleccionados();

                sincronizarSelects({
                    anios,
                    unidades
                });

                const tabla = $('#tablaUnidades').DataTable();

                const regexAnios = anios.length === 0 ? '' : anios.join('|');
                const regexUnidades = unidades.length === 0 ? '' : unidades.join('|');

                const indexAnio = <?= count($meses) + 2 ?>;
                const indexUnidad = 0;

                tabla.column(indexAnio).search(regexAnios, true, false);
                tabla.column(indexUnidad).search(regexUnidades, true, false);
                tabla.draw();

                actualizarGraficoUnidades(anios, unidades);
            }



            // Escuchar cambios en ambos filtros
            document.getElementById('filtroAnioTablaUnidades').addEventListener('change', actualizarTodo);
            document.getElementById('filtroAniosGraficos').addEventListener('change', actualizarTodo);

            // Inicializar al cargar
            $(document).ready(() => {
                const tabla = $('#tablaUnidades').DataTable({
                    pageLength: 5,
                    lengthMenu: [5, 10, 20, 30, 50],
                    language: {
                        search: "Buscar:",
                        lengthMenu: "Mostrar _MENU_ registros por página",
                        zeroRecords: "No se encontraron resultados",
                        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                        paginate: {
                            previous: "Anterior",
                            next: "Siguiente"
                        }
                    }
                });

                tabla.on('draw', function() {
                    actualizarTotalesTabla();
                });

                // Inicializar
                actualizarTotalesTabla();
                actualizarTodo();

                // Listeners para filtros
                $('#filtroUnidadesTabla').on('change', actualizarTodo);
            });

            $(document).ready(function() {
                $('.select-multiple').select2({
                    placeholder: "-- Todos --",
                    width: '100%',
                    allowClear: true,
                    closeOnSelect: false
                });

                // Eventos por contexto
                $('#filtroAnioTablaUnidades').on('change', actualizarDesdeTabla);
                $('#filtroUnidadesTabla').on('change', actualizarDesdeTabla);

                $('#filtroAniosGraficos').on('change', actualizarDesdeGraficos);
                $('#filtroUnidadesGraficos').on('change', actualizarDesdeGraficos);

                // Cargar inicial
                actualizarDesdeTabla();
                actualizarTotalesTabla();

                $('.select-multiple').on('select2:clear', function() {
                    actualizarTodo(); // o actualizarDesdeTabla() dependiendo del contexto
                });

                // Detectar "tachita" en filtros de TABLA
                $('#filtroAnioTablaUnidades, #filtroUnidadesTabla').on('select2:clear', function() {
                    setTimeout(() => {
                        actualizarDesdeTabla();
                    }, 0);
                });

                // Detectar "tachita" en filtros de GRÁFICOS
                $('#filtroAniosGraficos, #filtroUnidadesGraficos').on('select2:clear', function() {
                    setTimeout(() => {
                        actualizarDesdeGraficos();
                    }, 0);
                });

            });


            function actualizarTotalesTabla() {
                const api = $('#tablaUnidades').DataTable();
                const columnas = <?= json_encode($meses) ?>;
                const totales = {};
                let totalAnual = 0;

                columnas.forEach((mes, i) => {
                    const colIndex = i + 1; // porque columna 0 es "Unidad"
                    let suma = 0;
                    api.column(colIndex, {
                        search: 'applied'
                    }).nodes().each(function(cell) {
                        const val = parseInt($(cell).text()) || 0;
                        suma += val;
                    });
                    totales[mes] = suma;
                    $(`.total-${mes}`).text(suma.toLocaleString('es-MX'));
                    totalAnual += suma;
                });

                $('.total-anual').text(totalAnual.toLocaleString('es-MX'));
            }
        </script>

        <script>
            $('#formDescargarExcel').submit(function() {
                const anios = $('#filtroAniosGraficos').val();
                const unidades = $('#filtroUnidadesGraficos').val();

                $('#inputAnio').val(anios.join(','));
                $('#inputDivision').val(unidades.join(','));
            });
        </script>



        <script>
            document.getElementById('btnDescargarGraficoPDF2').addEventListener('click', async () => {
                const {
                    jsPDF
                } = window.jspdf;

                const graficoCard = document.getElementById("graficoCard");
                const estabaOculto = graficoCard && graficoCard.style.display === "none";
                if (estabaOculto) graficoCard.style.display = "block";

                await new Promise(resolve => setTimeout(resolve, 300));

                const canvasPrimera = document.getElementById("graficoParamedicos");
                const canvasSub = document.getElementById("graficoTotales");

                const pdf = new jsPDF();

                // Encabezado
                pdf.setFontSize(12);
                pdf.setFont(undefined, "bold");
                pdf.text("UNIDAD MÉDICA DE ALTA ESPECIALIDAD", 105, 14, {
                    align: "center"
                });
                pdf.setFont(undefined, "normal");
                pdf.text("HOSPITAL DE GINECO - PEDIATRÍA No. 48", 105, 21, {
                    align: "center"
                });
                pdf.text("PRODUCTIVIDAD TOTAL", 105, 28, {
                    align: "center"
                });

                // Fecha en la esquina superior derecha
                const fecha = new Date().toLocaleDateString();
                pdf.setFontSize(10);
                pdf.setFont(undefined, "italic");
                pdf.text(`Fecha: ${fecha}`, 105, 288, {
                    align: "center"
                });

                // Línea bajo el encabezado
                pdf.setLineWidth(0.5);
                pdf.line(10, 32, 200, 32);

                let yOffset = 42;

                const addCanvasToPDF = (canvas, titulo, offsetY) => {
                    const imgData = canvas.toDataURL("image/png");
                    pdf.setFontSize(11);
                    pdf.setFont(undefined, "normal");
                    pdf.text(titulo, 10, offsetY);
                    pdf.addImage(imgData, 'PNG', 10, offsetY + 5, 190, 95);
                };

                if (canvasPrimera) {
                    addCanvasToPDF(canvasPrimera, "Gráfico - Acumulado Mensual", yOffset);
                    yOffset += 110;
                }

                if (canvasSub) {
                    addCanvasToPDF(canvasSub, "Gráfico - Total Anual", yOffset);
                    yOffset += 110;
                }

                // Pie
                const pageHeight = pdf.internal.pageSize.height;
                pdf.setFontSize(10);
                pdf.setFont(undefined, "italic");
                pdf.text("Instituto Mexicano del Seguro Social", 105, pageHeight - 20, {
                    align: "center"
                });
                pdf.text("UMAE HGP 48", 105, pageHeight - 14, {
                    align: "center"
                });

                pdf.save("grafico-tabla-ProductividadTotal.pdf");

                if (estabaOculto) graficoCard.style.display = "none";
            });
        </script>

        <script>
            document.getElementById('btnDescargarImagenesCanvas').addEventListener('click', function() {
                exportarCanvasConFondo('graficoParamedicos', 'grafico_total_mensual.png');
                exportarCanvasConFondo('graficoTotales', 'grafico_total_anual.png');
            });

            function exportarCanvasConFondo(canvasId, nombreArchivo) {
                const canvasOriginal = document.getElementById(canvasId);
                if (!canvasOriginal) return alert("No se encontró el canvas: " + canvasId);

                // Crear un nuevo canvas en memoria
                const canvasTemp = document.createElement('canvas');
                canvasTemp.width = canvasOriginal.width;
                canvasTemp.height = canvasOriginal.height;
                const ctx = canvasTemp.getContext('2d');

                // Pintar fondo blanco
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvasTemp.width, canvasTemp.height);

                // Dibujar el canvas original encima (incluye grid, ejes, etc.)
                ctx.drawImage(canvasOriginal, 0, 0);

                // Descargar
                const enlace = document.createElement('a');
                enlace.href = canvasTemp.toDataURL('image/png');
                enlace.download = nombreArchivo;
                enlace.click();
            }
        </script>


        <script>
            document.querySelectorAll('.card-submenu-toggle').forEach(function(el) {
                el.addEventListener('click', function(e) {
                    e.preventDefault();

                    document.querySelectorAll('.card-submenu-toggle').forEach(function(toggle) {
                        if (toggle !== el) {
                            toggle.classList.remove('active');
                            const submenu = toggle.nextElementSibling;
                            if (submenu && submenu.classList.contains('card-submenu')) {
                                submenu.style.display = 'none';
                            }
                        }
                    });

                    el.classList.toggle('active');
                    const submenu = el.nextElementSibling;
                    if (submenu && submenu.classList.contains('card-submenu')) {
                        submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
                    }
                });
            });

            document.querySelectorAll('.dropdown-submenu-toggle').forEach(function(el) {
                el.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Evita que Bootstrap cierre el menú

                    // Cierra submenús hermanos
                    const parentMenu = el.closest('ul');
                    parentMenu.querySelectorAll('.dropdown-submenu-toggle').forEach(function(toggle) {
                        if (toggle !== el) toggle.classList.remove('active');
                    });

                    // Alterna el submenú actual
                    el.classList.toggle('active');
                });
            });
        </script>


        <!-- Links de librerías para pdf del gráfico -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>

        <script src="../../js/scripts.js" defer></script>
        <script src="../../js/bootstrap.bundle.min.js" defer></script>



</body>

</html>
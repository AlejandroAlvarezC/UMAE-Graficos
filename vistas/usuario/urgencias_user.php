<?php
require_once '../../modelos/urgencia.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../css/bootstrap.min.css" defer>
    <link rel="stylesheet" href="../../css/styles.css" defer>
    <link rel="icon" type="image/png" href="../../logo-imss.png">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- CSS de Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- JS de Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <title>UMAE-48 | Urgencias</title>

    <style>
        /* -----------------------------------
   RESET Y ESTRUCTURA BASE
----------------------------------- */
        html,
        body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        .content {
            flex: 1;
        }

        /* -----------------------------------
   TABLA DE DATOS
----------------------------------- */
        table.table {
            font-size: 0.9rem;
            border-collapse: collapse;
        }

        table.table thead th {
            background-color: rgb(155, 29, 29);
            color: white;
            border: 1px solid rgb(255, 0, 0);
            text-align: center;
            padding: 0.7px;
            vertical-align: middle;
        }

        table.table tbody tr {
            background-color: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.2s ease-in-out;
        }

        table.table tbody tr:hover {
            box-shadow: 0 4px 12px rgba(122, 18, 58, 0.5);
        }

        table.table td {
            padding: 0.2px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }

        /* -----------------------------------
   FILTROS
----------------------------------- */
        .filter-container-inline {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: flex-end;
            justify-content: flex-start;
            padding: 18px 22px;
            background: linear-gradient(135deg, #f9fafc, #e9f1f7);
            border: 1px solid #c4d3df;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(34, 61, 71, 0.08);
            margin-bottom: 2rem;
        }

        #selectorAnioContainer,
        #selectorEspecialidadContainer,
        #selectorDivisionContainer,
        #selectorDescripcionContainer,
        #selectorAnioContainer2,
        #selectorEspecialidadContainer2,
        #selectorDescripcionContainer2 {
            min-width: 180px;
            flex: 1 1 auto;
        }


        .filter-container-inline label {
            font-weight: 600;
            color: #333e4c;
            font-size: 1rem;
            margin-bottom: 4px;
        }

        .form-select,
        .select-personalizado {
            width: 100%;
            border-radius: 14px;
            padding: 10px 16px;
            font-size: 1rem;
            font-weight: 500;
            color: #1a1a1a;
            background-color: #ffffff;
            border: 1.5px solid #ccd6dd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .form-select:hover,
        .select-personalizado:hover,
        .form-select:focus,
        .select-personalizado:focus {
            border-color: rgb(167, 26, 26);
            box-shadow: 0 0 8px rgba(69, 131, 33, 0.25);
            outline: none;
        }

        /* Responsive filtros */
        @media (max-width: 576px) {
            .filter-container-inline {
                flex-direction: column;
                align-items: stretch;
                padding: 14px 16px;
            }

            .filter-container-inline label {
                font-size: 0.95rem;
            }

            .form-select {
                font-size: 0.95rem;
            }
        }

        /* -----------------------------------
   GRÁFICOS (Tarjetas)
----------------------------------- */
        #graficosContainer,
        #graficoCard2 {
            background: linear-gradient(135deg, #ffffff, #f9f9fb);
            border: 1.5px solid #d4a0b0;
            border-radius: 16px;
            padding: 28px 32px;
            box-shadow: 0 8px 20px rgba(122, 18, 58, 0.12);
            margin-bottom: 40px;
            transition: box-shadow 0.3s ease, transform 0.2s ease;
        }

        #graficosContainer:hover {
            box-shadow: 0 12px 32px rgba(122, 18, 58, 0.18);
            transform: scale(1.01);
        }

        #graficosContainer h4 {
            color: rgb(122, 18, 18);
            font-weight: 800;
            font-size: 1.4rem;
            border-bottom: 2px solid rgba(122, 18, 58, 0.3);
            padding-bottom: 10px;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }

        /* -----------------------------------
   FORMULARIO DE BÚSQUEDA DATATABLE
----------------------------------- */
        .dataTable-input {
            padding-left: 35px;
            background-repeat: no-repeat;
            background-size: 18px;
            background-position: 10px center;
            transition: box-shadow 0.3s ease, transform 0.2s ease;
        }

        .dataTable-input:focus {
            outline: none;
            box-shadow: 0 0 10px rgba(122, 18, 18, 0.5);
            transform: scale(1.02);
        }

        .dataTable-search input {
            border-radius: 12px;
            padding: 8px 14px;
            border: 1.5px solid rgb(46, 95, 17);
            background-color: #f9f9f9;
            color: #1a1a1a;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .dataTable-search input:focus {
            border-color: rgb(214, 26, 26);
            outline: none;
            box-shadow: 0 0 6px rgba(214, 26, 26, 0.25);
        }

        /* Selector cantidad de registros */
        .dataTable-selector {
            border-radius: 12px;
            padding: 6px 12px;
            border: 1.5px solid rgb(42, 95, 17);
            background-color: #fff;
            font-weight: 500;
            color: rgb(95, 17, 17);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .dataTable-dropdown label {
            font-weight: 600;
            color: #333;
        }

        /* -----------------------------------
   BOTONES

   ----------------------------------- */

        .btn-light {
            border-color: rgb(131, 40, 40) !important;
            background-color: rgb(255, 255, 255);
            color: black;
        }

        /* Botón del gráfico */
        #btnGrafico {
            background-color: rgb(125, 108, 108);
            border: none;
            border-radius: 12px;
            padding: 10px 25px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        #btnGrafico:hover {
            background-color: rgb(87, 73, 73);
            cursor: pointer;
            color: white !important;
        }

        .btn-urgencia {
            background-color: #b30000;
            /* rojo fuerte y serio */
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-urgencia:hover {
            background-color: #dc0000ff;
            /* más claro al pasar */
            box-shadow: 0 6px 16px rgba(179, 0, 0, 0.4);
            transform: translateY(-2px);
            text-decoration: none;
            color: white;
        }

        .btn-urgencia:active {
            transform: translateY(0);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-urgencia:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(204, 0, 0, 0.3);
        }



        .btn-success {
            background: linear-gradient(135deg, rgb(110, 30, 30), rgb(172, 29, 29));
            border: none;
            border-radius: 12px;
            padding: 12px 30px;
            font-weight: 700;
            font-size: 1.1rem;
            color: white !important;
            box-shadow: 0 6px 15px rgba(230, 28, 28, 0.61);
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
            background: linear-gradient(135deg, rgb(255, 0, 0), rgb(214, 75, 20));
            box-shadow: 0 8px 25px rgba(185, 31, 31, 0.89);
            transform: scale(1.05);
            outline: none;
        }

        .btn-success:active {
            transform: scale(0.98);
            box-shadow: 0 4px 12px rgba(39, 104, 23, 0.6);
        }

        .btn-success svg,
        .btn-success img {
            width: 20px;
            height: 20px;
        }

        /* ----------------------------------------------
         Dropdown submenu y navegación
      ------------------------------------------------*/

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

        .dropdown-item.dropdown-submenu-toggle.active,
        .dropdown-item.dropdown-submenu-toggle:hover {
            background-color: #00664d !important;
            color: white;
        }

        .nav-link {
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: #9A7D0A !important;
        }

        .navbar-brand {
            transition: color 0.3s;
        }

        .navbar-brand:hover {
            color: #9A7D0A !important;
        }



        /* ----------------------------------------------
           Footer
        ------------------------------------------------*/

        footer {
            background-color: #00664d;
            width: 100%;
            text-align: center;
            color: white;
            font-weight: bold;
            padding: 10px 0;
        }

        /* -----------------------------------
   OTROS
----------------------------------- */
        .hidden {
            display: none;
        }

        .mb-3 {
            margin-bottom: 1rem !important;
        }

        /* === Sección header === */

        .section-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .section-header h1 {
            color: rgb(155, 29, 29);
            font-weight: 700;
            font-size: 2.5rem;
            /* Tamaño aumentado */
            margin-bottom: 0.5rem;
        }

        .section-header small {
            color: #6c757d;
            font-weight: 500;
            font-size: 1.2rem;
            display: block;
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
            background-color: rgb(34, 117, 74);
            color: white;
            font-weight: 600;
        }

        table.dataTable thead th {
            border-color: rgb(170, 213, 211);
        }

        table.dataTable tbody tr:hover {
            box-shadow: 0 4px 12px rgba(18, 122, 22, 0.5);
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

        /* Opciones del dropdown */
        .select2-container--default .select2-results__option {
            font-size: 1rem;
            padding: 10px 14px;
            font-weight: 500;
            color: #212529;
            transition: background-color 0.2s ease;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #e9e9efff;
            color: #000;
        }

        /* Contenedor visible del campo múltiple */
        .select2-container--default .select2-selection--multiple {
            border-radius: 16px;
            padding: 6px 12px;
            border: 1.5px solid #ccd6dd;
            background-color: #ffffff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            min-height: 42px;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: text;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        /* Cuando tiene foco */
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        /* Tags seleccionados */
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #ead2d2ff;
            border: none;
            border-radius: 3px;
            padding: 4px 10px;
            font-size: 0.9rem;
            color: #212529;
            font-weight: 500;
            margin-top: 6px;
        }

        /* Botón de eliminar etiqueta */
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #2d352aff;
            margin-right: 6px;
            font-weight: bold;
            transition: color 0.2s ease;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #000000ff;
        }

        /* Texto de placeholder */
        .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
            font-size: 1rem;
            color: #495057;
            padding: 4px 6px;
        }

        /* Ajustar el campo de búsqueda interno para alinear el texto centrado */
        .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
            height: 30px;
            padding: 4px 6px !important;
            margin-top: 0px;
            margin-bottom: 0px;
            line-height: 1.5;
            font-size: 1rem;
            font-weight: 500;
        }
    </style>
</head>

<body style="background-color: aliceblue;">

    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #00664d;">

        <div class="container-fluid">

            <!-- Logo para pantallas grandes (antes del enlace "Inicio") -->
            <a href="../roles/index.php" class="me-2 d-none d-lg-block">
                <img src="../../img/umae-48.jpg" alt="Logo UMAE" height="80" class="rounded-circle">
            </a>


            <a class="navbar-brand" href="../roles/index.php">Inicio</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    <!-- PRODUCTIVIDAD -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#"
                            data-bs-toggle="dropdown">Productividad</a>
                        <ul class="dropdown-menu" data-bs-auto-close="outside">

                            <!-- Consulta externa -->
                            <li>
                                <a class="dropdown-item dropdown-submenu-toggle">Consulta externa</a>
                                <ul class="dropdown-submenu">
                                    <li><a class="dropdown-item" href="./unidadesreportan_user.php">Productividad Total</a></li>
                                    <li><a class="dropdown-item" href="./paramedicos_user.php">Paramédicos</a>
                                    </li>

                                    <!-- Especialidades -->
                                    <li>
                                        <a class="dropdown-item dropdown-submenu-toggle">Especialidades</a>
                                        <ul class="dropdown-submenu">
                                            <li><a class="dropdown-item"
                                                    href="./especialidades_user.php">Especialidades</a>
                                            </li>
                                            <li><a class="dropdown-item"
                                                    href="./EspecilidadOcas_user.php">Especialidad de
                                                    ocasión</a></li>
                                        </ul>
                                    </li>

                                </ul>
                            </li>

                            <!-- Hospitalización -->
                            <li>
                                <a class="dropdown-item dropdown-submenu-toggle">Hospitalización</a>
                                <ul class="dropdown-submenu">
                                    <li><a class="dropdown-item" href="./ingresos_user.php">Ingresos</a></li>
                                    <li><a class="dropdown-item" href="./egresos_user.php">Egresos</a></li>
                                    <li><a class="dropdown-item" href="./paciente_user.php">Días Paciente</a></li>
                                    <li><a class="dropdown-item" href="#">Días Cama</a></li>
                                </ul>
                            </li>

                            <!-- Cirugía -->
                            <li><a class="dropdown-item" href="./cirugia_user.php">Cirugía</a></li>

                            <!-- Urgencias -->
                            <li><a class="dropdown-item" href="./urgencias_user.php">Urgencias</a></li>

                        </ul>
                    </li>

                    <!-- Organigrama -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./organigrama.php">Organigrama</a>
                    </li>

                    <!-- Normatividad -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../usuario/normatividad_user.php">Normatividad</a>
                    </li>

                    <!-- Vencer -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./vencer_user.php">Vencer</a>
                    </li>

                    <!-- Sitios de interés -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="./sitios_interes-user.php">Sitios de interés</a>
                    </li>
                </ul>



                <!-- Logo centrado SOLO para móviles -->
                <div class="d-block d-lg-none w-100 text-center my-2">
                    <a href="../roles/index.php">
                        <img src="../../img/umae-48.jpg" alt="Logo UMAE" height="60" class="rounded-circle">
                    </a>
                </div>



                <!-- Sesión -->
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <?php if (isset($_SESSION['admin_name'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="../admin/admin.php"
                                data-bs-toggle="dropdown">
                                Ir a Panel de <?php echo $_SESSION['admin_name']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="../admin/admin.php">
                                        <i class="bi bi-speedometer2 me-1"></i>Volver al panel</a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="../admin/logout.php">
                                        <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión</a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <div class="d-flex align-items-center gap-2">
                            <a class="btn btn-outline-light" onclick="anterior()" href="#" title="Atrás"><i class="bi bi-arrow-left"></i></a>
                            <a class="btn btn-outline-light" href="../admin/login.php">Iniciar Sesión</a>
                        </div>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>




    <div class="content">
        <div class="container my-4">
            <h1 style="text-align: center; color: #495057; font-weight:bold">Urgencias</h1><br>

            <!-- Botones -->
            <div class="d-flex flex-wrap justify-content-center align-items-center my-3 gap-2">
                <div class="d-flex justify-content-center align-items-center gap-2">
                    <form id="formDescargarExcel" action="../productividad/excel_urgencias.php" method="post"
                        class="m-0">
                        <input type="hidden" name="anio" id="inputAnio" value="">
                        <input type="hidden" name="especialidad" id="inputEspecialidad" value="">
                        <input type="hidden" name="division" id="inputDivision" value="">
                        <button class="btn btn-success" type="submit">🟩 Descargar Excel con gráfico</button>
                    </form>
                    <a id="btnDescargarGraficoPDF" class="btn btn-success">📈 Descargar PDF</a>
                    <a id="btnDescargarImagenesCanvasUrgencias" class="btn btn-success">🖼️ Descargar Imagen</a>
                    <button id="btnGrafico" class="btn btn-success" onclick="toggleGraficos()">📊 Ver Gráficos</button>
                </div>
            </div>

            <!-- Filtros -->
            <div id="filtrosContainer" class="filter-container-inline my-3" style="display: none;">
                <div id="selectorAnioContainer" class="me-3" style="display: none;">
                    <label for="selectAnio" class="form-label">Año:</label>
                    <select id="selectAnio" class="form-select select-personalizado" multiple></select>
                </div>


                <div id="selectorDivisionContainer" class="me-3" style="display: none;">
                    <label for="selectDivision" class="form-label">División:</label>
                    <select id="selectDivision" class="form-select select-personalizado" multiple>
                    </select>
                </div>

                <div id="selectorEspecialidadContainer" class="me-3" style="display: none;">
                    <label for="selectEspecialidad" class="form-label">Especialidad:</label>
                    <select id="selectEspecialidad" class="form-select select-personalizado" multiple>
                    </select>
                </div>
            </div>

            <!-- Gráficos -->
            <div id="graficosContainer" style="display: none;">
                <div class="info-card mb-4">
                    <h4>Gráfico - Mensual por Especialidad</h4>
                    <canvas id="graficoUrgencias" width="400" height="180"></canvas>
                    <p id="mensajeSinDatos" style="display: none; color: red;"><strong>No hay datos ingresados.</strong>
                    </p>
                </div>

                <div class="info-card mb-4">
                    <h4>Gráfico - Mensual por División</h4>
                    <canvas id="graficoMensualDivision" width="400" height="180"></canvas>
                    <p id="mensajeSinDatosMensualDivision" style="display: none; color: red;"><strong>No hay datos
                            ingresados.</strong></p>
                </div>


                <div class="info-card mb-4">
                    <h4>Gráfico - Anual por División</h4>
                    <canvas id="graficoTotales" width="400" height="180"></canvas>
                    <p id="mensajeSinDatosTotales" style="display: none; color: red;"><strong>No hay datos
                            ingresados.</strong></p>
                </div>
            </div>

            <!-- Tabla -->
            <?php
            $urgencias = Urgencia::listar();
            if (count($urgencias) > 0) { ?>
                <div class="card shadow-sm mt-5">
                    <div class="card-header">
                        <h5 class="mb-0">Tabla de Urgencias</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">

                            <div class="row mb-3 justify-content-end">
                                <div class="col-md-3 col-sm-6 d-flex align-items-center gap-2">
                                    <label for="filtroAnioTabla" class="form-label mb-0 fw-semibold text-dark">Año:</label>
                                    <select id="filtroAnioTabla"
                                        class="form-select form-select-sm shadow-sm border border-secondary-subtle"
                                        multiple>
                                        <?php
                                        $anios = array_unique(array_column($urgencias, 'anio'));
                                        sort($anios);
                                        foreach ($anios as $anio) {
                                            echo "<option value=\"$anio\">$anio</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>



                            <table class="table table-bordered align-middle text-center" id="tabla-urgencias">
                                <thead>
                                    <tr>
                                        <th>Clave</th>
                                        <th>Especialidad</th>
                                        <th>División</th>
                                        <?php
                                        $meses = [
                                            'enero',
                                            'febrero',
                                            'marzo',
                                            'abril',
                                            'mayo',
                                            'junio',
                                            'julio',
                                            'agosto',
                                            'septiembre',
                                            'octubre',
                                            'noviembre',
                                            'diciembre'
                                        ];
                                        foreach ($meses as $mes) {
                                            echo "<th>" . ucfirst($mes) . "</th>";
                                        }
                                        ?>
                                        <th>Total</th>
                                        <th>Año</th>
                                        <th style="display: none;">Editar</th>
                                        <th style="display: none;">Eliminar</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    $totalesPorMes = array_fill_keys($meses, 0);
                                    $granTotal = 0;

                                    foreach ($urgencias as $u) { ?>
                                        <tr>
                                            <td><?= htmlspecialchars($u['clave']) ?></td>
                                            <td><?= htmlspecialchars($u['especialidad']) ?></td>
                                            <td><?= htmlspecialchars($u['division']) ?></td>
                                            <?php
                                            $total = 0;
                                            foreach ($meses as $mes) {
                                                $valor = (int)$u[$mes];
                                                $totalesPorMes[$mes] += $valor;
                                                echo "<td>" . htmlspecialchars($valor) . "</td>";
                                                $total += $valor;
                                            }
                                            $granTotal += $total;
                                            ?>
                                            <td style="background-color: #ffefefff;"><strong><?= $total ?></strong></td>
                                            <td><?= htmlspecialchars($u['anio']) ?></td>
                                            <td style="display: none;">
                                                <a class="btn btn-light btn-sm"
                                                    href="./editar-urgencias.php?id=<?= base64_encode($u['id']) ?>">Editar</a>
                                            </td>
                                            <td style="display: none;">
                                                <a class="btn btn-light btn-sm"
                                                    href="../../controladores/urgencias.php?a=Eliminar&id=<?= base64_encode($u['id']) ?>"
                                                    onclick="return confirm('¿Desea eliminar este registro?')">Eliminar</a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>

                                <tfoot id="tfootTotales" class="table-light">
                                    <tr style="background-color: #f4f9f4; font-weight: bold;">
                                        <td colspan="3" class="text-center align-middle"
                                            style="background-color: #ffefefff;">Total General</td>
                                        <?php foreach ($meses as $mes): ?>
                                            <td id="total-<?= $mes ?>" class="text-center align-middle"
                                                style="background-color: #ffefefff;">0</td>
                                        <?php endforeach; ?>
                                        <td style="background-color: #ed8585ff; color: #7d0000ff; font-weight: 1000; font-size:medium"
                                            id="total-general" class="text-center align-middle">0</td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>


                            </table>

                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <p><strong>No hay datos ingresados.</strong></p>
            <?php } ?>

        </div>
    </div>


    <footer>
        <p>Derechos reservados &copy; IMSS 2025</p>
    </footer>



    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
        let grafico = null;
        let graficoTotales = null;
        let graficoMensualDivision = null;

        let datosUrgencias = <?php
                                $datosPorAnio = [];
                                $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

                                foreach ($urgencias as $u) {
                                    $anio = $u['anio'];
                                    if (!isset($datosPorAnio[$anio])) {
                                        $datosPorAnio[$anio] = [];
                                    }

                                    $totales = [];
                                    $sumaTotal = 0;
                                    foreach ($meses as $mes) {
                                        $valor = (int)$u[$mes];
                                        $totales[] = $valor;
                                        $sumaTotal += $valor;
                                    }

                                    // ✅ Agregamos el campo 'anio' a cada registro
                                    $datosPorAnio[$anio][] = [
                                        'anio' => $anio, // <-- este campo permitirá identificar el año en JS
                                        'especialidad' => $u['especialidad'],
                                        'division' => $u['division'] ?? '',
                                        'totales' => $totales,
                                        'total' => $sumaTotal
                                    ];
                                }

                                echo json_encode($datosPorAnio);
                                ?>;

        const etiquetasMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre',
            'Octubre', 'Noviembre', 'Diciembre'
        ];
        const colores = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#17a2b8', '#fd7e14'];

        function construirFiltros() {
            const anios = Object.keys(datosUrgencias).sort();
            const selectAnio = document.getElementById('selectAnio');
            selectAnio.innerHTML = '<option value="">-- Todos los años --</option>';
            anios.forEach(anio => {
                selectAnio.innerHTML += `<option value="${anio}">${anio}</option>`;
            });

            actualizarFiltrosDependientes();
        }

        function actualizarFiltrosDependientes() {
            const aniosSeleccionados = $('#selectAnio').val() || [];
            const especialidadesSeleccionadas = $('#selectEspecialidad').val() || [];
            const divisionesSeleccionadas = $('#selectDivision').val() || [];

            let todosLosDatos = [];
            Object.entries(datosUrgencias).forEach(([anio, registros]) => {
                registros.forEach(reg => {
                    todosLosDatos.push({
                        ...reg,
                        anio
                    });
                });
            });

            let datosFiltrados = [...todosLosDatos];

            if (aniosSeleccionados.length) {
                datosFiltrados = datosFiltrados.filter(d => aniosSeleccionados.includes(d.anio));
            }
            if (especialidadesSeleccionadas.length) {
                datosFiltrados = datosFiltrados.filter(d =>
                    especialidadesSeleccionadas.includes(d.especialidad.trim().toLowerCase())
                );
            }
            if (divisionesSeleccionadas.length) {
                datosFiltrados = datosFiltrados.filter(d =>
                    divisionesSeleccionadas.includes(d.division.trim().toLowerCase())
                );
            }

            // Actualizar selects dependientes
            const selAnio = $('#selectAnio');
            const selEsp = $('#selectEspecialidad');
            const selDiv = $('#selectDivision');

            const aniosDisponibles = [...new Set(
                todosLosDatos
                .filter(d =>
                    (!especialidadesSeleccionadas.length || especialidadesSeleccionadas.includes(d.especialidad
                        .trim().toLowerCase())) &&
                    (!divisionesSeleccionadas.length || divisionesSeleccionadas.includes(d.division.trim()
                        .toLowerCase()))
                )
                .map(d => d.anio)
            )];

            selAnio.empty();
            selAnio.append(new Option('-- Todos los años --', '')); // ✅ placeholder

            aniosDisponibles.sort().forEach(anio => {
                const selected = aniosSeleccionados.includes(anio);
                selAnio.append(new Option(anio, anio, false, selected));
            });

            selAnio.trigger('change.select2');



            const especialidadesDisponibles = [...new Set(
                todosLosDatos
                .filter(d =>
                    (!aniosSeleccionados.length || aniosSeleccionados.includes(d.anio)) &&
                    (!divisionesSeleccionadas.length || divisionesSeleccionadas.includes(d.division.trim()
                        .toLowerCase()))
                )
                .map(d => d.especialidad.trim().toLowerCase())
            )];

            selEsp.empty();
            especialidadesDisponibles.sort().forEach(e => {
                selEsp.append(new Option(e.charAt(0).toUpperCase() + e.slice(1), e, false,
                    especialidadesSeleccionadas.includes(e)));
            });

            const divisionesDisponibles = [...new Set(
                todosLosDatos
                .filter(d =>
                    (!aniosSeleccionados.length || aniosSeleccionados.includes(d.anio)) &&
                    (!especialidadesSeleccionadas.length || especialidadesSeleccionadas.includes(d.especialidad
                        .trim().toLowerCase()))
                )
                .map(d => d.division.trim().toLowerCase())
            )];

            selDiv.empty();
            divisionesDisponibles.sort().forEach(d => {
                selDiv.append(new Option(d.charAt(0).toUpperCase() + d.slice(1), d, false, divisionesSeleccionadas
                    .includes(d)));
            });

            // ⬇️ Actualizar gráficos
            actualizarGrafico();
            actualizarGraficoMensualPorDivision();
            actualizarGraficoTotales();
        }



        function filtrarDatos() {
            const anios = $('#selectAnio').val() || [];
            const especialidades = $('#selectEspecialidad').val() || [];
            const divisiones = $('#selectDivision').val() || [];

            let datos = [];
            Object.values(datosUrgencias).forEach(arr => datos.push(...arr));

            if (anios.length) {
                datos = datos.filter(d => anios.includes(d.anio));
            }

            if (especialidades.length) {
                datos = datos.filter(d => especialidades.includes(d.especialidad.trim().toLowerCase()));
            }

            if (divisiones.length) {
                datos = datos.filter(d => divisiones.includes(d.division.trim().toLowerCase()));
            }

            return datos;
        }



        function actualizarGrafico() {
            const datos = filtrarDatos();
            const mensaje = document.getElementById('mensajeSinDatos');
            const canvas = document.getElementById("graficoUrgencias");
            const ctx = canvas.getContext("2d");

            if (grafico) grafico.destroy();

            if (datos.length === 0) {
                mensaje.style.display = 'block';
                canvas.style.display = 'none';
                return;
            }

            mensaje.style.display = 'none';
            canvas.style.display = 'block';

            // Calcular total general mensual basado en los datos filtrados
            const totalGeneral = Array(12).fill(0);
            datos.forEach(d => {
                d.totales.forEach((val, idx) => {
                    totalGeneral[idx] += val;
                });
            });


            const datasets = datos.map((d, i) => ({
                label: `${d.especialidad} (${d.anio})`,
                data: d.totales,
                borderColor: colores[i % colores.length],
                tension: 0.2,
                fill: false
            }));

            // Agregar la línea punteada si NO hay filtros seleccionado de especialidad
            const especialidadesSeleccionadas = $('#selectEspecialidad').val() || [];
            if (especialidadesSeleccionadas.length === 0) {

                datasets.push({
                    label: 'Total General',
                    data: totalGeneral,
                    borderColor: 'black',
                    borderDash: [5, 5],
                    fill: false,
                    tension: 0.1
                });
            }

            grafico = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: etiquetasMeses,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                        }
                    }
                }
            });
        }

        function actualizarGraficoMensualPorDivision() {
            const datos = filtrarDatos();
            const mensaje = document.getElementById('mensajeSinDatosMensualDivision');
            const canvas = document.getElementById("graficoMensualDivision");
            const ctx = canvas.getContext("2d");

            if (graficoMensualDivision) graficoMensualDivision.destroy();

            if (datos.length === 0) {
                mensaje.style.display = 'block';
                canvas.style.display = 'none';
                return;
            }

            mensaje.style.display = 'none';
            canvas.style.display = 'block';

            // 🔁 Agrupar por combinación año + división
            const grupos = {};

            datos.forEach(d => {
                const clave = `${d.division} (${d.anio})`;
                if (!grupos[clave]) {
                    grupos[clave] = Array(12).fill(0);
                }
                d.totales.forEach((v, idx) => {
                    grupos[clave][idx] += v;
                });
            });

            const datasets = Object.entries(grupos).map(([clave, data], i) => ({
                label: clave,
                data,
                borderColor: colores[i % colores.length],
                tension: 0.3,
                fill: false
            }));

            graficoMensualDivision = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: etiquetasMeses,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                        }
                    }
                }
            });
        }

        function actualizarGraficoTotales() {
            const aniosSeleccionados = $('#selectAnio').val() || [];
            const especialidadesSeleccionadas = $('#selectEspecialidad').val() || [];
            const divisionesSeleccionadas = $('#selectDivision').val() || [];

            const mensaje = document.getElementById('mensajeSinDatosTotales');
            const canvas = document.getElementById("graficoTotales");
            const ctx = canvas.getContext("2d");

            if (graficoTotales) graficoTotales.destroy();

            let etiquetas = [];
            let valores = [];

            let datos = [];
            Object.entries(datosUrgencias).forEach(([anio, registros]) => {
                registros.forEach(d => datos.push({
                    ...d,
                    anio
                }));
            });

            if (aniosSeleccionados.length) {
                datos = datos.filter(d => aniosSeleccionados.includes(d.anio));
            }

            if (especialidadesSeleccionadas.length) {
                datos = datos.filter(d => especialidadesSeleccionadas.includes(d.especialidad.trim().toLowerCase()));
            }

            if (divisionesSeleccionadas.length) {
                datos = datos.filter(d => divisionesSeleccionadas.includes(d.division.trim().toLowerCase()));
            }

            if (datos.length === 0) {
                mensaje.style.display = 'block';
                canvas.style.display = 'none';
                return;
            }

            mensaje.style.display = 'none';
            canvas.style.display = 'block';

            const agrupado = {};

            datos.forEach(d => {
                const etiqueta = `${d.division || d.especialidad} (${d.anio})`;
                agrupado[etiqueta] = (agrupado[etiqueta] || 0) + d.total;
            });

            etiquetas = Object.keys(agrupado);
            valores = Object.values(agrupado);

            // Calcular total general
            const totalGeneral = valores.reduce((acc, val) => acc + val, 0);

            // Solo agregar total si no se filtró por especialidad
            if (!especialidadesSeleccionadas.length) {
                etiquetas.push("Total General");
                valores.push(totalGeneral);
            }

            graficoTotales = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: etiquetas,
                    datasets: [{
                        label: 'Totales Anuales',
                        data: valores,
                        backgroundColor: etiquetas.map((et, i) => et === 'Total General' ? 'black' :
                            colores[i % colores.length])
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
                                size: 16
                            },
                            formatter: function(value) {
                                return value.toLocaleString('es-MX');
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }



        let graficosVisible = false;

        function toggleGraficos() {
            const container = document.getElementById("graficosContainer");
            const filtros = document.getElementById("filtrosContainer");
            const btn = document.getElementById("btnGrafico");

            // 🔁 Antes de mostrar u ocultar, sincronizamos filtros
            const aniosSeleccionados = $('#filtroAnioTabla').val() || [];
            $('#selectAnio').val(aniosSeleccionados).trigger('change.select2');


            actualizarFiltrosDependientes(); // Esto reconstruye todos los selects relacionados
            actualizarGrafico();
            actualizarGraficoMensualPorDivision();
            actualizarGraficoTotales();

            if (graficosVisible) {
                container.style.display = "none";
                filtros.style.display = "none";

                document.getElementById('selectorAnioContainer').style.display = "none";
                document.getElementById('selectorEspecialidadContainer').style.display = "none";
                document.getElementById('selectorDivisionContainer').style.display = "none";

                btn.textContent = "📊 Ver Gráficos";
            } else {
                container.style.display = "block";
                filtros.style.display = "flex";

                document.getElementById('selectorAnioContainer').style.display = "block";
                document.getElementById('selectorEspecialidadContainer').style.display = "block";
                document.getElementById('selectorDivisionContainer').style.display = "block";

                btn.textContent = "✖ Ocultar Gráficos";
            }

            graficosVisible = !graficosVisible;
        }

        document.addEventListener('DOMContentLoaded', () => {
            construirFiltros();

            $('#selectEspecialidad').on('change', function() {
                aplicarFiltrosDataTableUrgencias();
                actualizarFiltrosDependientes();
                actualizarGrafico();
                actualizarGraficoMensualPorDivision();
                actualizarGraficoTotales();
                tablaUrgencias.draw();
                actualizarTotalesUrgencias();
            });

            $('#selectDivision').on('change', function() {
                aplicarFiltrosDataTableUrgencias();
                actualizarFiltrosDependientes();
                actualizarGrafico();
                actualizarGraficoMensualPorDivision();
                actualizarGraficoTotales();
                tablaUrgencias.draw();
                actualizarTotalesUrgencias();
            });


        });

        document.getElementById('selectAnio').addEventListener('change', () => {
            const val = document.getElementById('selectAnio').value;
            document.getElementById('filtroAnioTabla').value = val;
            const anios = $('#selectAnio').val() || [];
            tablaUrgencias
                .column(16)
                .search(anios.length ? anios.join('|') : '', true, false)
                .draw();

            actualizarFiltrosDependientes();
            actualizarGrafico();
            actualizarGraficoMensualPorDivision();
            actualizarGraficoTotales();
        });

        document.getElementById('filtroAnioTabla').addEventListener('change', () => {
            const val = document.getElementById('filtroAnioTabla').value;
            document.getElementById('selectAnio').value = val;
            actualizarFiltrosDependientes();
            actualizarGrafico();
            actualizarGraficoMensualPorDivision();
            actualizarGraficoTotales();
        });

        function actualizarTotalesUrgencias() {
            const datosVisibles = tablaUrgencias.rows({
                search: 'applied'
            }).data();
            const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre',
                'octubre', 'noviembre', 'diciembre'
            ];

            const totalesPorMes = {};
            meses.forEach(m => totalesPorMes[m] = 0);
            let granTotal = 0;

            datosVisibles.each(function(row) {
                meses.forEach((mes, i) => {
                    const valor = parseInt(row[i + 3]) || 0;
                    totalesPorMes[mes] += valor;
                    granTotal += valor;
                });
            });

            meses.forEach((mes, i) => {
                $(`#total-${mes}`).text(totalesPorMes[mes].toLocaleString('es-MX'));
            });

            $('#total-general').text(granTotal.toLocaleString('es-MX'));
        }
    </script>


    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('#selectAnio, #selectDivision, #selectEspecialidad, #filtroAnioTabla').select2({
                width: '100%',
                placeholder: '-- Todos --',
            });

            window.tablaUrgencias = $('#tabla-urgencias').DataTable({
                pageLength: 5,
                lengthMenu: [5, 10, 20, 30, 50, 75, 100],
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

            tablaUrgencias.on('draw', actualizarTotalesUrgencias);
            actualizarTotalesUrgencias(); // al cargar

        });

        $('#selectAnio').on('change', function() {
            const val = $(this).val() || [];
            $('#filtroAnioTabla').val(val).trigger('change.select2');
            aplicarFiltrosDataTableUrgencias();
            actualizarFiltrosDependientes();
            actualizarGrafico();
            actualizarGraficoMensualPorDivision();
            actualizarGraficoTotales();
            tablaUrgencias.draw();
            actualizarTotalesUrgencias();
        });

        $('#filtroAnioTabla').on('change', function() {
            const val = $(this).val() || [];
            $('#selectAnio').val(val).trigger('change.select2');
            aplicarFiltrosDataTableUrgencias();
            actualizarFiltrosDependientes();
            actualizarGrafico();
            actualizarGraficoMensualPorDivision();
            actualizarGraficoTotales();
            tablaUrgencias.draw();
            actualizarTotalesUrgencias();
        });

        function aplicarFiltrosDataTableUrgencias() {
            const anios = $('#selectAnio').val() || [];
            const especialidades = $('#selectEspecialidad').val() || [];
            const divisiones = $('#selectDivision').val() || [];

            const indexAnio = 16;
            const indexEspecialidad = 1;
            const indexDivision = 2;

            // Aplicar filtros múltiples usando expresiones regulares (OR)
            tablaUrgencias.column(indexAnio)
                .search(anios.length ? anios.join('|') : '', true, false);
            tablaUrgencias.column(indexEspecialidad)
                .search(especialidades.length ? especialidades.join('|') : '', true, false);
            tablaUrgencias.column(indexDivision)
                .search(divisiones.length ? divisiones.join('|') : '', true, false);

            tablaUrgencias.draw();
        }
    </script>

    <script>
        $('#filtroAnioTabla').on('change', function() {
            const val = $(this).val();

            // Sincronizar con filtro de gráficos
            $('#selectAnio').val(val);

            // Aplicar a la tabla
            const anios = $('#selectAnio').val() || [];
            tablaUrgencias
                .column(16)
                .search(anios.length ? anios.join('|') : '', true, false)
                .draw();


            // Aunque el gráfico no esté visible, actualizamos los datos
            actualizarFiltrosDependientes();
            actualizarGrafico();
            actualizarGraficoMensualPorDivision();
            actualizarGraficoTotales();
        });
    </script>

    <script>
        document.querySelector('#formDescargarExcel').addEventListener('submit', function(e) {
            function valoresSeleccionados(idSelect) {
                const select = document.getElementById(idSelect);
                return Array.from(select.selectedOptions).map(o => o.value).join(',');
            }

            document.getElementById('inputAnio').value = valoresSeleccionados('selectAnio');
            document.getElementById('inputEspecialidad').value = valoresSeleccionados('selectEspecialidad');
            document.getElementById('inputDivision').value = valoresSeleccionados('selectDivision');
        });
    </script>

    <script>
        document.getElementById('btnDescargarGraficoPDF').addEventListener('click', async () => {
            const {
                jsPDF
            } = window.jspdf;

            const graficosContainer = document.getElementById("graficosContainer");
            const estabaOculto = graficosContainer && graficosContainer.style.display === "none";
            if (estabaOculto) graficosContainer.style.display = "block";

            // Esperar para asegurar render completo
            await new Promise(resolve => setTimeout(resolve, 300));

            const canvasUrgencias = document.getElementById("graficoUrgencias");
            const canvasMensualDivision = document.getElementById("graficoMensualDivision");
            const canvasTotales = document.getElementById("graficoTotales");

            // Crear PDF tamaño legal (216 x 356 mm)
            const pdf = new jsPDF({
                orientation: "portrait",
                unit: "mm",
                format: "legal"
            });

            // Encabezado
            pdf.setFontSize(12);
            pdf.setFont(undefined, "bold");
            pdf.text("UNIDAD MÉDICA DE ALTA ESPECIALIDAD", 105, 12, {
                align: "center"
            });
            pdf.setFont(undefined, "normal");
            pdf.text("HOSPITAL DE GINECO - PEDIATRÍA No. 48", 105, 18, {
                align: "center"
            });
            pdf.text("URGENCIAS", 105, 24, {
                align: "center"
            });

            // Línea horizontal
            pdf.setLineWidth(0.5);
            pdf.line(10, 28, 200, 28);

            // Función para agregar gráficos
            let yOffset = 39;
            const addCanvasToPDF = (canvas, titulo, offsetY) => {
                if (!canvas) return;
                const imgData = canvas.toDataURL("image/png");
                pdf.setFontSize(11);
                pdf.setFont(undefined, "bold");
                pdf.text(titulo, 10, offsetY);
                pdf.addImage(imgData, 'PNG', 10, offsetY + 5, 190, 85);
            };

            if (canvasUrgencias) {
                addCanvasToPDF(canvasUrgencias, "Gráfico - Mensual por Especialidad", yOffset);
                yOffset += 100;
            }

            if (canvasMensualDivision) {
                addCanvasToPDF(canvasMensualDivision, "Gráfico - Mensual por División", yOffset);
                yOffset += 100;
            }

            if (canvasTotales) {
                addCanvasToPDF(canvasTotales, "Gráfico - Anual por División", yOffset);
                yOffset += 100;
            }

            // Pie de página
            const pageHeight = pdf.internal.pageSize.getHeight();
            const fecha = new Date().toLocaleDateString();
            pdf.setFontSize(9);
            pdf.setFont(undefined, "italic");
            pdf.text(`Fecha: ${fecha}`, 105, pageHeight - 16, {
                align: "center"
            });
            pdf.text("Instituto Mexicano del Seguro Social", 105, pageHeight - 11, {
                align: "center"
            });
            pdf.text("UMAE HGP 48", 105, pageHeight - 6, {
                align: "center"
            });

            pdf.save("grafico-tablas-Urgencias.pdf");

            if (estabaOculto) graficosContainer.style.display = "none";
        });
    </script>

    <script>
        document.getElementById('btnDescargarImagenesCanvasUrgencias').addEventListener('click', function() {
            exportarCanvasConFondo('graficoUrgencias', 'grafico_mensual_especialidad_urgencias.png');
            exportarCanvasConFondo('graficoMensualDivision', 'grafico_mensual_division_urgencias.png');
            exportarCanvasConFondo('graficoTotales', 'grafico_anual_division_urgencias.png');
        });

        function exportarCanvasConFondo(canvasId, nombreArchivo) {
            const canvasOriginal = document.getElementById(canvasId);
            if (!canvasOriginal) return alert("No se encontró el canvas: " + canvasId);

            const canvasTemp = document.createElement('canvas');
            canvasTemp.width = canvasOriginal.width;
            canvasTemp.height = canvasOriginal.height;

            const ctx = canvasTemp.getContext('2d');

            // Pintar fondo blanco
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvasTemp.width, canvasTemp.height);

            // Dibujar el canvas original encima
            ctx.drawImage(canvasOriginal, 0, 0);

            // Crear enlace para descargar
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


    <!-- Links de librerías para crear gráfico en pdf -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>

    <script src="../../js/scripts.js" defer></script>
    <script src="../../js/bootstrap.bundle.min.js" defer></script>



</body>

</html>
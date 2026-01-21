<?php
require_once '../../modelos/paramedicos.php';
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
    <title>Paramédicos | Usuario</title>

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
            background-color: rgb(9, 104, 14);
            color: white;
            border: 1px solid rgb(26, 139, 32);
            vertical-align: middle;
            text-align: center;
            padding: 0.3rem;
        }

        table.table tbody tr {
            background-color: white;
            box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
            transition: box-shadow 0.2s ease-in-out;
        }

        table.table tbody tr:hover {
            box-shadow: 0 4px 12px rgb(122 18 58 / 0.5);
        }

        table.table td {
            vertical-align: middle;
            padding: 0.3rem;
            text-align: center;
            border: 1px solid #dee2e6;
            background-color: white;
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
            box-shadow: 0 4px 12px rgba(65, 71, 34, 0.08);
            margin-bottom: 2rem;
        }

        #selectorAnioContainer,
        #selectorEspecialidadContainer,
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
            border-radius: 16px;
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
            border-color: rgb(26, 167, 26);
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
        #graficoCard,
        #graficoCard2 {
            background: linear-gradient(135deg, #ffffff, #f9f9fb);
            border: 1.5px solid #a0d4a8ff;
            border-radius: 16px;
            padding: 28px 32px;
            box-shadow: 0 8px 20px rgba(32, 122, 18, 0.12);
            margin-bottom: 40px;
            transition: box-shadow 0.3s ease, transform 0.2s ease;
        }

        #graficoCard:hover {
            box-shadow: 0 12px 32px rgba(30, 122, 18, 0.18);
            transform: scale(1.01);
        }

        #graficoCard h4 {
            color: rgb(22, 122, 18);
            font-weight: 800;
            font-size: 1.4rem;
            border-bottom: 2px solid rgba(18, 122, 35, 0.3);
            padding-bottom: 10px;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }

        /* -----------------------------------
   FORMULARIO DE BÚSQUEDA DATATABLE
----------------------------------- */
        .dataTable-input {
            border-radius: 12px;
            padding-left: 35px;
            background-repeat: no-repeat;
            background-size: 18px;
            background-position: 10px center;
            transition: box-shadow 0.3s ease, transform 0.2s ease;
        }

        .dataTable-input:focus {
            outline: none;
            box-shadow: 0 0 10px rgba(39, 122, 18, 0.5);
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
            border-color: rgb(57, 214, 26);
            outline: none;
            box-shadow: 0 0 6px rgba(26, 214, 26, 0.25);
        }

        /* Selector cantidad de registros */
        .dataTable-selector {
            border-radius: 12px;
            padding: 6px 12px;
            border: 1.5px solid rgb(42, 95, 17);
            background-color: #fff;
            font-weight: 500;
            color: rgb(20, 95, 17);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .dataTable-dropdown label {
            font-weight: 600;
            color: #333;
        }

        /* -----------------------------------
   BOTONES
----------------------------------- */
        .btn-success {
            background: linear-gradient(135deg, rgb(37, 110, 30), rgb(86, 172, 29));
            border: none;
            border-radius: 12px;
            padding: 12px 30px;
            font-weight: 700;
            font-size: 1.1rem;
            color: white !important;
            box-shadow: 0 6px 15px rgba(85, 230, 28, 0.61);
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
            background: linear-gradient(135deg, rgba(81, 231, 67, 1), rgba(62, 123, 21, 1));
            box-shadow: 0 8px 25px rgba(115, 255, 60, 0.61);
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

        /* === Botones personalizados === */
        .btn-light {
            border-color: rgb(47, 98, 44) !important;
            background-color: rgb(255, 255, 255);
            color: black;
        }

        /* Botón del gráfico */
        #btnGrafico {
            background-color: #6c757d;
            border: none;
            border-radius: 12px;
            padding: 10px 25px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        #btnGrafico:hover {
            background-color: #495057;
            cursor: pointer;
            color: white !important;
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

        /* Botones .bg-vino (vino oscuro, llamativos) */
        .btn-urgencia {
            background-color: #dfc819ff;
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
            box-shadow: 0 4px 10px rgba(210, 230, 28, 0.61);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-urgencia:hover {
            background-color: #c5c813ff;
            /* más claro al pasar */
            box-shadow: 0 6px 16px rgba(213, 255, 43, 0.58);
            transform: translateY(-2px);
            text-decoration: none;
            color: white;
        }

        .btn-urgencia:active {
            transform: translateY(0);
            box-shadow: 0 3px 8px rgba(228, 231, 228, 0.2);
        }

        .btn-urgencia:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(214, 218, 215, 0.3);
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
            color: rgb(9, 104, 14);
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
            background-color: #e9ecef;
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
            box-shadow: 0 0 0 0.2rem rgba(201, 253, 13, 0.25);
        }

        /* Tags seleccionados */
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #d2ead5ff;
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
            color: #dc3545;
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
            <h1 style="text-align: center; color: #495057; font-weight:bold">Paramédicos</h1><br>


            <div class="d-flex flex-wrap justify-content-center align-items-center my-3 gap-2">
                <div class="d-flex justify-content-center align-items-center gap-2">
                    <form id="formDescargarExcel" action="../productividad/descargar_excel_paramedicos.php" method="post" class="m-0">
                        <input type="hidden" name="anio" id="inputAnio" value="">
                        <input type="hidden" name="especialidad" id="inputEspecialidad" value="">
                        <input type="hidden" name="division" id="inputDivision" value="">
                        <button class="btn btn-success" type="submit">🟩 Descargar Excel con gráfico</button>
                    </form>

                    <a id="btnDescargarGraficoPDF" class="btn btn-success">📈 Descargar PDF</a>
                    <a id="btnDescargarImagenesCanvasParamedicos" class="btn btn-success">🖼️ Descargar Imagen</a>
                    <button id="btnGrafico" class="btn btn-success" onclick="toggleGraficos()">📊 Ver Gráfico</button>
                </div>
            </div>

            <!-- Filtros -->
            <div id="filtrosContainer" class="filter-container-inline" style="display: none;">
                <div id="selectorAnioContainer" style="display:none;">
                    <label for="selectAnio" class="form-label">Año:</label>
                    <select id="selectAnio" class="form-select select-personalizado"
                        onchange="actualizarGrafico()" multiple></select>
                </div>

                <div id="selectorDescripcionContainer" style="display:none;">
                    <label for="selectDivision" class="form-label">División:</label>
                    <select id="selectDivision" class="form-select select-personalizado"
                        onchange="actualizarGrafico()" multiple>
                    </select>
                </div>

                <div id="selectorEspecialidadContainer" style="display:none;">
                    <label for="selectEspecialidad" class="form-label">Especialidad:</label>
                    <select id="selectEspecialidad" class="form-select select-personalizado"
                        onchange="actualizarGrafico()" multiple>
                    </select>
                </div>
            </div>

            <!-- Gráficos -->
            <div id="graficoCard" class="info-card" style="display: none;">
                <h4>Gráfico - Mensual por Especialidad</h4>
                <canvas id="graficoParamedicos" width="400" height="180"></canvas><br>
                <br>
                <h4>Gráfico - Mensual por División</h4>
                <canvas id="graficoOjivaDivision" width="400" height="180"></canvas>
                <br>

                <h4>Gráfico - Anual por División</h4>
                <canvas id="graficoTotales" width="400" height="180"></canvas>
                <p id="mensajeSinDatos" style="display: none; color: red;"><strong>No hay datos ingresados.</strong></p>
                <p id="mensajeSinDatosTotales" style="display: none; color: red;"><strong>No hay datos ingresados.</strong></p>
            </div>
            <div>

                <!-- Tabla -->
                <?php
                $paramedicos = Paramedicos::listar();
                if (count($paramedicos) > 0) { ?>
                    <div class="card shadow-sm mt-5">
                        <div class="card-header">
                            <h5 class="mb-0">Tabla de Paramédicos</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">

                                <div class="row mb-3 justify-content-end">
                                    <div class="col-md-3 col-sm-6 d-flex align-items-center gap-2">
                                        <label for="filtroAnioTablaParamedicos" class="form-label mb-0 fw-semibold text-dark">Año:</label>
                                        <select id="filtroAnioTablaParamedicos" class="form-select form-select-sm shadow-sm border border-secondary-subtle" multiple>
                                            <?php
                                            $aniosUnicos = array_unique(array_column($paramedicos, 'anio'));
                                            sort($aniosUnicos);
                                            foreach ($aniosUnicos as $anio):
                                            ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <table class="table table-bordered align-middle text-center" id="tabla-paramedicos">
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

                                        foreach ($paramedicos as $u) { ?>
                                            <tr>
                                                <td><?= htmlspecialchars($u['clave']) ?></td>
                                                <td><?= htmlspecialchars($u['especialidad']) ?></td>
                                                <td><?= htmlspecialchars($u['division']) ?></td>
                                                <?php
                                                $total = 0;
                                                foreach ($meses as $mes) {
                                                    $valor = (int)$u[$mes];
                                                    $totalesPorMes[$mes] += $valor; // acumulamos el valor por mes
                                                    echo "<td>" . htmlspecialchars($valor) . "</td>";
                                                    $total += $valor;
                                                }
                                                $granTotal += $total;
                                                ?>
                                                <td style="background-color: #f9fff8ff;#f8e6ed;"><strong><?= $total ?></strong></td>
                                                <td><?= htmlspecialchars($u['anio']) ?></td>
                                                <td style="display: none;">
                                                    <a class="btn btn-light btn-sm" href="./editarParamedico.php?id=<?= base64_encode($u['id']) ?>">Editar</a>
                                                </td>
                                                <td style="display: none;">
                                                    <a class="btn btn-light btn-sm" href="../../controladores/paramedicos.php?a=Eliminar&id=<?= base64_encode($u['id']) ?>" onclick="return confirm('¿Desea eliminar este registro?')">Eliminar</a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <tfoot id="tfootTotales" class="table-light">
                                        <tr id="filaTotales" style="background-color: #d1e7dd; font-weight: bold; border-top: 2px solid #0f5132; color: #0f5132;">
                                            <td colspan="3" style="background-color: #f9fff8ff;" class="text-center align-middle">Total General</td>

                                            <?php foreach ($meses as $mes): ?>
                                                <td class="total-col text-center align-middle" style="background-color: #f9fff8ff;" data-mes="<?= $mes ?>">0</td>
                                            <?php endforeach; ?>

                                            <td class="total-general text-center align-middle" style="background-color: #cfffd5ff; font-size: 1.1rem; font-weight: 800; color: #056505ff;">0</td>

                                            <td colspan="3" class="text-center align-middle"></td>
                                        </tr>
                                    </tfoot>




                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <p><strong>No hay datos ingresados.</strong></p>
                <?php } ?>


            </div>
        </div>
    </div>
    </div><br><br>


    <footer>
        <p>Derechos reservados &copy; IMSS 2025</p>
    </footer>



    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <script>
        const paramedicosData = <?= json_encode($paramedicos) ?>;
        const coloresFijos = [
            '#1f77b4', '#ff7f0e', '#2ca02c', '#d62728',
            '#9467bd', '#8c564b', '#e377c2', '#7f7f7f',
            '#bcbd22', '#17becf'
        ];

        let graficoMensual, graficoAnual, graficoOjivaDivision;

        const valoresPosibles = {
            anios: [...new Set(paramedicosData.map(p => p.anio))].sort(),
            especialidades: [...new Set(paramedicosData.map(p => p.especialidad))].sort(),
            divisiones: [...new Set(paramedicosData.map(p => p.division))].sort()
        };


        function toggleGraficos() {
            const container = document.getElementById('graficoCard');
            const filtros = document.getElementById('filtrosContainer');
            const boton = document.getElementById('btnGrafico');

            const visible = container.style.display === 'block';

            container.style.display = visible ? 'none' : 'block';
            filtros.style.display = visible ? 'none' : 'flex';
            boton.textContent = visible ? '📊 Ver Gráfico' : '❌ Ocultar Gráfico';

            const selectorAnio = document.getElementById('selectorAnioContainer');
            const selectorEspecialidad = document.getElementById('selectorEspecialidadContainer');
            const selectorDescripcion = document.getElementById('selectorDescripcionContainer');

            if (!visible) {
                selectorAnio.style.display = 'block';
                selectorEspecialidad.style.display = 'block';
                selectorDescripcion.style.display = 'block';

                llenarFiltros();
                actualizarGrafico();
            } else {
                selectorAnio.style.display = 'none';
                selectorEspecialidad.style.display = 'none';
                selectorDescripcion.style.display = 'none';
            }
        }

        function llenarFiltros() {
            const seleccionadosAnios = $('#selectAnio').val() || [];
            const seleccionadosEspecialidad = $('#selectEspecialidad').val() || [];
            const seleccionadosDivision = $('#selectDivision').val() || [];

            llenarSelect('selectAnio', valoresPosibles.anios, seleccionadosAnios);
            llenarSelect('selectEspecialidad', valoresPosibles.especialidades, seleccionadosEspecialidad);
            llenarSelect('selectDivision', valoresPosibles.divisiones, seleccionadosDivision);

            actualizarGrafico();
        }


        function actualizarFiltrosYGraficos() {
            const seleccionadosAnios = $('#selectAnio').val() || [];
            const seleccionadosEspecialidad = $('#selectEspecialidad').val() || [];
            const seleccionadosDivision = $('#selectDivision').val() || [];

            const datosFiltrados = paramedicosData.filter(p =>
                (seleccionadosAnios.length === 0 || seleccionadosAnios.includes(p.anio)) &&
                (seleccionadosEspecialidad.length === 0 || seleccionadosEspecialidad.includes(p.especialidad)) &&
                (seleccionadosDivision.length === 0 || seleccionadosDivision.includes(p.division))
            );

            // 👇 Aquí está el cambio importante: filtrar cada uno *excluyendo* su propio filtro
            const nuevosAnios = [...new Set(
                paramedicosData
                .filter(p =>
                    (seleccionadosEspecialidad.length === 0 || seleccionadosEspecialidad.includes(p.especialidad)) &&
                    (seleccionadosDivision.length === 0 || seleccionadosDivision.includes(p.division))
                )
                .map(p => p.anio)
            )].sort();

            const nuevasEspecialidades = [...new Set(
                paramedicosData
                .filter(p =>
                    (seleccionadosAnios.length === 0 || seleccionadosAnios.includes(p.anio)) &&
                    (seleccionadosDivision.length === 0 || seleccionadosDivision.includes(p.division))
                )
                .map(p => p.especialidad)
            )].sort();

            const nuevasDivisiones = [...new Set(
                paramedicosData
                .filter(p =>
                    (seleccionadosAnios.length === 0 || seleccionadosAnios.includes(p.anio)) &&
                    (seleccionadosEspecialidad.length === 0 || seleccionadosEspecialidad.includes(p.especialidad))
                )
                .map(p => p.division)
            )].sort();

            llenarSelect('selectAnio', nuevosAnios, seleccionadosAnios);
            llenarSelect('selectEspecialidad', nuevasEspecialidades, seleccionadosEspecialidad);
            llenarSelect('selectDivision', nuevasDivisiones, seleccionadosDivision);

            actualizarGrafico();
            $('#filtroAnioTablaParamedicos').val(seleccionadosAnios).trigger('change.select2');
        }



        function llenarSelect(id, valores, seleccionadosPrevios = []) {
            const select = document.getElementById(id);
            const esMultiple = true; // Asegura que siempre sea múltiple
            select.innerHTML = '';

            // Reaplica atributo multiple
            select.multiple = esMultiple;

            valores.forEach(valor => {
                const option = document.createElement('option');
                option.value = valor;
                option.textContent = valor;
                if (seleccionadosPrevios.includes(valor)) {
                    option.selected = true;
                }
                select.appendChild(option);
            });

            // Vuelve a aplicar select2 (destruye y vuelve a activar)
            $(`#${id}`).select2({
                width: '100%',
                placeholder: '-- Todos--',
            });
        }


        function actualizarGrafico() {
            const anios = $('#selectAnio').val() || [];
            const divisiones = $('#selectDivision').val() || [];
            const especialidades = $('#selectEspecialidad').val() || [];

            const datosFiltrados = paramedicosData.filter(p =>
                (anios.length === 0 || anios.includes(p.anio)) &&
                (divisiones.length === 0 || divisiones.includes(p.division)) &&
                (especialidades.length === 0 || especialidades.includes(p.especialidad))
            );

            const meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio",
                "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
            ];

            const grupos = [...new Set(datosFiltrados.map(d => `${d.especialidad} (${d.anio})`))];

            const datasetsMensual = grupos.map((grupo, index) => {
                const [especialidad, anio] = grupo.match(/^(.+?) \((\d{4})\)$/).slice(1);
                const registros = datosFiltrados.filter(d =>
                    d.anio === anio && d.especialidad === especialidad
                );
                const data = meses.map(mes =>
                    registros.reduce((sum, d) => sum + parseInt(d[mes] || 0), 0)
                );
                return {
                    label: `${especialidad} (${anio})`,
                    data,
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4,
                    borderColor: coloresFijos[index % coloresFijos.length]
                };
            });

            // === NUEVO GRÁFICO: Ojiva mensual por división ===
            const gruposOjiva = [...new Set(datosFiltrados.map(d => `${d.division} (${d.anio})`))];

            const datasetsOjiva = gruposOjiva.map((grupo, index) => {
                const [division, anio] = grupo.match(/^(.+?) \((\d{4})\)$/).slice(1);
                const registros = datosFiltrados.filter(d =>
                    d.anio === anio && d.division === division
                );

                const data = meses.map(mes =>
                    registros.reduce((sum, d) => sum + parseInt(d[mes] || 0), 0)
                );

                return {
                    label: `${division} (${anio})`,
                    data,
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3,
                    borderColor: coloresFijos[index % coloresFijos.length]
                };
            });


            const ctxOjiva = document.getElementById('graficoOjivaDivision').getContext('2d');
            if (graficoOjivaDivision) graficoOjivaDivision.destroy();
            graficoOjivaDivision = new Chart(ctxOjiva, {
                type: 'line',
                data: {
                    labels: meses.map(m => m.charAt(0).toUpperCase() + m.slice(1)),
                    datasets: datasetsOjiva
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



            if (especialidades.length === 0) {
                const totalesMensuales = meses.map(mes =>
                    datosFiltrados.reduce((sum, d) => sum + parseInt(d[mes] || 0), 0)
                );

                datasetsMensual.push({
                    label: "Total General",
                    data: totalesMensuales,
                    borderWidth: 3,
                    fill: false,
                    tension: 0.3,
                    borderDash: [5, 5],
                    borderColor: '#000',
                    pointBackgroundColor: '#000'
                });
            }

            const ctxMensual = document.getElementById('graficoParamedicos').getContext('2d');
            if (graficoMensual) graficoMensual.destroy();
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

            const resumenAnual = {};
            for (let d of datosFiltrados) {
                const clave = `${d.division} (${d.anio})`;
                const total = meses.reduce((sum, mes) => sum + parseInt(d[mes] || 0), 0);
                resumenAnual[clave] = (resumenAnual[clave] || 0) + total;
            }

            const etiquetas = Object.keys(resumenAnual);
            const valores = Object.values(resumenAnual);

            if (especialidades.length === 0) {
                const totalAnualGeneral = valores.reduce((a, b) => a + b, 0);
                etiquetas.push("Total General Anual");
                valores.push(totalAnualGeneral);
            }

            const ctxTotales = document.getElementById('graficoTotales').getContext('2d');
            if (graficoAnual) graficoAnual.destroy();
            graficoAnual = new Chart(ctxTotales, {
                type: 'bar',
                data: {
                    labels: etiquetas,
                    datasets: [{
                        label: 'Total anual',
                        data: valores,
                        backgroundColor: etiquetas.map((etiqueta, i) =>
                            etiqueta === "Total General Anual" ? '#000' : coloresFijos[i % coloresFijos.length]
                        ),
                        borderColor: etiquetas.map((etiqueta, i) =>
                            etiqueta === "Total General Anual" ? '#111' : coloresFijos[i % coloresFijos.length]
                        ),
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'start',
                            color: '#555',
                            font: {
                                weight: 'bold',
                                size: 16
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
        let tablaParamedicos;

        function actualizarTotales() {
            const datosVisibles = tablaParamedicos.rows({
                search: 'applied'
            }).data();
            const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

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

            meses.forEach(mes => {
                $(`.total-col[data-mes="${mes}"]`).text(totalesPorMes[mes]);
            });
            $('.total-general').text(granTotal);
        }

        $(document).ready(function() {
            // Inicializar Select2
            $('#selectAnio, #selectDivision, #selectEspecialidad, #filtroAnioTablaParamedicos').select2({
                width: '100%',
                placeholder: '-- Todos --'
            });

            // Llenar ambos selects al cargar
            llenarSelect('selectAnio', valoresPosibles.anios);
            llenarSelect('filtroAnioTablaParamedicos', valoresPosibles.anios);

            // Inicializar DataTable
            tablaParamedicos = $('#tabla-paramedicos').DataTable({
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

            tablaParamedicos.on('draw', actualizarTotales);
            actualizarTotales();

            // Aplicar filtros de inicio
            aplicarFiltrosDataTable();
            $('#filtroAnioTablaParamedicos').trigger('change');

            // Sincronización entre selects
            $('#filtroAnioTablaParamedicos').on('change', function() {
                const aniosSeleccionados = $(this).val() || [];
                const aniosActualesGrafico = $('#selectAnio').val() || [];

                if (JSON.stringify(aniosSeleccionados.sort()) !== JSON.stringify(aniosActualesGrafico.sort())) {
                    $('#selectAnio').val(aniosSeleccionados).trigger('change.select2');
                }

                aplicarFiltrosDataTable();
                if ($('#graficoCard').is(':visible')) {
                    actualizarGrafico();
                }
            });

            $('#selectAnio').on('change', function() {
                const seleccionados = $(this).val() || [];
                const actuales = $('#filtroAnioTablaParamedicos').val() || [];

                if (JSON.stringify(seleccionados.sort()) !== JSON.stringify(actuales.sort())) {
                    $('#filtroAnioTablaParamedicos').val(seleccionados).trigger('change.select2');
                }

                aplicarFiltrosDataTable();
                if ($('#graficoCard').is(':visible')) {
                    actualizarGrafico();
                }
            });

            $('#selectDivision, #selectEspecialidad').on('change', function() {
                aplicarFiltrosDataTable();
                if ($('#graficoCard').is(':visible')) {
                    actualizarGrafico();
                }
            });

            $('#selectAnio, #selectDivision, #selectEspecialidad').on('change', function() {
                actualizarFiltrosYGraficos(); // 🔄 Sincroniza selects
                aplicarFiltrosDataTable(); // 🔍 Filtro en tabla
            });

        });



        function aplicarFiltrosDataTable() {
            const anios = $('#selectAnio').val() || $('#filtroAnioTablaParamedicos').val() || [];
            const divisiones = $('#selectDivision').val() || [];
            const especialidades = $('#selectEspecialidad').val() || [];

            const indexAnio = <?php echo count($meses) + 4; ?>;

            tablaParamedicos
                .column(indexAnio).search(anios.length ? anios.join('|') : '', true, false)
                .column(2).search(divisiones.length ? divisiones.join('|') : '', true, false)
                .column(1).search(especialidades.length ? especialidades.join('|') : '', true, false)
                .draw();
        }


        $('#filtroAnioTablaParamedicos').on('change', function() {
            const aniosSeleccionados = $(this).val() || [];

            // Actualizar selectAnio solo si es diferente para evitar loops
            const aniosActualesGrafico = $('#selectAnio').val() || [];
            const sonIguales = JSON.stringify(aniosSeleccionados.sort()) === JSON.stringify(aniosActualesGrafico.sort());

            if (!sonIguales) {
                $('#selectAnio').val(aniosSeleccionados).trigger('change.select2');
            }

            aplicarFiltrosDataTable();
            if ($('#graficoCard').is(':visible')) {
                actualizarGrafico();
            }
        });

        $('#selectAnio').on('change', function() {
            const seleccionados = $(this).val() || [];
            const actuales = $('#filtroAnioTablaParamedicos').val() || [];

            const sonIguales = JSON.stringify(seleccionados.sort()) === JSON.stringify(actuales.sort());

            if (!sonIguales) {
                $('#filtroAnioTablaParamedicos').val(seleccionados).trigger('change.select2');
            }
        });
    </script>

    <script>
        document.getElementById('btnDescargarImagenesCanvasParamedicos').addEventListener('click', function() {
            descargarCanvasConFondo('graficoParamedicos', 'grafico_mensual_especialidad_paramedicos.png');
            descargarCanvasConFondo('graficoOjivaDivision', 'grafico_mensual_division_paramedicos.png');
            descargarCanvasConFondo('graficoTotales', 'grafico_anual_division_paramedicos.png');
        });

        function descargarCanvasConFondo(canvasId, nombreArchivo) {
            const canvasOriginal = document.getElementById(canvasId);
            if (!canvasOriginal) {
                alert("No se encontró el canvas: " + canvasId);
                return;
            }

            // Crear un canvas temporal para poner fondo blanco
            const canvasTemp = document.createElement('canvas');
            canvasTemp.width = canvasOriginal.width;
            canvasTemp.height = canvasOriginal.height;
            const ctx = canvasTemp.getContext('2d');

            // Fondo blanco
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvasTemp.width, canvasTemp.height);

            // Dibuja el canvas original encima
            ctx.drawImage(canvasOriginal, 0, 0);

            // Descargar la imagen
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

    <script>
        document.querySelector('#formDescargarExcel button[type="submit"]').addEventListener('click', function(e) {
            const getValoresMultiples = (idSelect) => {
                const opciones = document.getElementById(idSelect).selectedOptions;
                return Array.from(opciones).map(opt => opt.value).join(',');
            };

            document.getElementById('inputAnio').value = getValoresMultiples('selectAnio');
            document.getElementById('inputEspecialidad').value = getValoresMultiples('selectEspecialidad');
            document.getElementById('inputDivision').value = getValoresMultiples('selectDivision');
        });
    </script>

    <script>
        document.getElementById('btnDescargarGraficoPDF').addEventListener('click', async () => {
            const {
                jsPDF
            } = window.jspdf;

            const graficoCard = document.getElementById("graficoCard");
            const estabaOculto = graficoCard && graficoCard.style.display === "none";
            if (estabaOculto) graficoCard.style.display = "block";

            // Esperar para asegurar render completo
            await new Promise(resolve => setTimeout(resolve, 300));

            const canvasParamedicos = document.getElementById("graficoParamedicos");
            const canvasOjivaDivision = document.getElementById("graficoOjivaDivision");
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
            pdf.text("PARAMÉDICOS TOTAL", 105, 24, {
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

            if (canvasParamedicos) {
                addCanvasToPDF(canvasParamedicos, "Gráfico - Mensual por Especialidad", yOffset);
                yOffset += 100;
            }

            if (canvasOjivaDivision) {
                addCanvasToPDF(canvasOjivaDivision, "Gráfico - Mensual por División", yOffset);
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

            pdf.save("grafico-tablas-Paramedicos.pdf");

            if (estabaOculto) graficoCard.style.display = "none";
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
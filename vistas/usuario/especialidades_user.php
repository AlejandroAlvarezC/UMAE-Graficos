<?php require_once '../../modelos/Especialidad_Ocasion.php';
session_start();
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

    <!-- CSS de Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- JS de Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <title>UMAE-48 | Especialidades</title>
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

        /*------------------------------------ 
        Tabla de datos 
        --------------------------------------*/
        table.table {
            font-size: 0.9rem;
            border-collapse: collapse;
        }

        table.table thead th {
            background-color: rgb(34, 117, 112);
            color: white;
            border: 1px solidrgb(185, 32, 96);
            vertical-align: middle;
            text-align: center;
            padding: 0.75rem 0.5rem;
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
            padding: 0.5rem 0.75rem;
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
            box-shadow: 0 4px 12px rgba(34, 61, 71, 0.08);
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
            border-color: #218380;
            box-shadow: 0 0 8px rgba(33, 131, 128, 0.25);
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
            border: 1.5px solid #a0d4c8ff;
            border-radius: 16px;
            padding: 28px 32px;
            box-shadow: 0 8px 20px rgba(18, 122, 46, 0.12);
            margin-bottom: 40px;
            transition: box-shadow 0.3s ease, transform 0.2s ease;
        }

        #graficoCard:hover {
            box-shadow: 0 12px 32px rgba(18, 122, 99, 0.18);
            transform: scale(1.01);
        }

        #graficoCard h4 {
            color: #127a73ff;
            font-weight: 800;
            font-size: 1.4rem;
            border-bottom: 2px solid rgba(18, 122, 84, 0.3);
            padding-bottom: 10px;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }

        /* -----------------------------------
   FORMULARIO DE BÚSQUEDA DATATABLE
----------------------------------- */
        .dataTable-input {
            padding-left: 35px;
            background-image: url("../../img/Iconos/buscar.png");
            background-repeat: no-repeat;
            background-size: 18px;
            background-position: 10px center;
            transition: box-shadow 0.3s ease, transform 0.2s ease;
        }

        .dataTable-input:focus {
            outline: none;
            box-shadow: 0 0 10px rgba(18, 122, 103, 0.5);
            transform: scale(1.02);
        }

        .dataTable-search input {
            border-radius: 12px;
            padding: 8px 14px;
            border: 1.5px solid #114b5f;
            background-color: #f9f9f9;
            color: #1a1a1a;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .dataTable-search input:focus {
            border-color: rgba(26, 214, 51, 0.73);
            outline: none;
            box-shadow: 0 0 6px rgba(57, 214, 26, 0.25);
        }

        /* Selector cantidad de registros */
        .dataTable-selector {
            border-radius: 12px;
            padding: 6px 12px;
            border: 1.5px solid #114b5f;
            background-color: #fff;
            font-weight: 500;
            color: #114b5f;
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
            background: linear-gradient(135deg, #299089, rgba(14, 70, 66, 1));
            border: none;
            border-radius: 12px;
            padding: 9px 16px;
            font-weight: 700;
            font-size: 1.1rem;
            color: white !important;
            box-shadow: 0 6px 15px rgba(39, 187, 150, 0.5);
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
            background: linear-gradient(135deg, rgba(25, 105, 81, 1), rgba(8, 109, 84, 1));
            box-shadow: 0 8px 25px rgba(24, 128, 66, 0.8);
            transform: scale(1.05);
            outline: none;
        }

        .btn-success:active {
            transform: scale(0.98);
            box-shadow: 0 4px 12px rgba(23, 104, 84, 0.6);
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
            color: rgb(34, 117, 112);
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
            background-color: rgb(34, 117, 112);
            color: white;
            font-weight: 600;
        }

        table.dataTable thead th {
            border-color: rgb(170, 213, 211);
        }

        table.dataTable tbody tr:hover {
            box-shadow: 0 4px 12px rgba(18, 122, 105, 0.5);
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
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        /* Tags seleccionados */
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #90d5c8ff;
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
            <h1 style="text-align: center; color: #495057; font-weight:bold">Especialidades Total</h1><br>

            <?php $especialidades = Especialidad_Ocasion::listar(); ?>

            <div class="d-flex gap-2 align-items-center justify-content-center mb-3">
                <a class="btn btn-success" href="./EspecilidadOcas_user.php">📝 Ir a Esp. de Ocasión</a>
                <form id="formDescargarExcel" action="../Productividad/Excel_especialidad-total.php" method="post"
                    class="m-0">
                    <input type="hidden" name="anio" id="inputAnio" value="">
                    <input type="hidden" name="especialidad" id="inputEspecialidad" value="">
                    <input type="hidden" name="descripcion" id="inputDescripcion" value="">
                    <button class="btn btn-success" type="submit">🟩 Descargar Excel con Gráfico</button>
                </form>
                <a id="btnDescargarGraficoPDF2" class="btn btn-success">📈 Descargar PDF</a>
                <a id="btnDescargarImagenesCanvas2" class="btn btn-success">🖼️ Descargar Imagen</a>
                <a id="btnGrafico2" class="btn btn-success" onclick="toggleGrafico2()">📊 Ver Gráfico</a>
            </div>

            <div id="contenedorGrafico2" class="info-card" style="display:none;">
                <div id="filtrosContainer2" class="filter-container-inline">
                    <div id="selectorAnioContainer2" style="display:block;">
                        <label for="filtroAnio2" class="form-label">Año:</label>
                        <select id="filtroAnio2" class="select2" multiple>
                        </select>
                    </div>

                    <div id="selectorDescripcionContainer2" style="display:block;">
                        <label for="filtroDescripcion2" class="form-label">División:</label>
                        <select id="filtroDescripcion2" class="select2" multiple>
                        </select>
                    </div>

                    <div id="selectorEspecialidadContainer2" style="display:block;">
                        <label for="filtroEspecialidad2" class="form-label">Especialidad:</label>
                        <select id="filtroEspecialidad2" class="select2" multiple>
                        </select>
                    </div>
                </div>

                <div id="graficoCard" class="info-card">
                    <h4 class="mt-4">Gráfico - Mensual por Especialidad</h4>
                    <canvas id="graficoMeses2" width="400" height="180"></canvas>
                    <br>
                    <h4>Gráfico - Mensual por División</h4>
                    <canvas id="graficoMensualDivision2" width="400" height="180"></canvas>
                    <p id="mensajeSinDatosMensualDivision2" style="display:none; color:red;"><strong>No hay datos
                            disponibles.</strong></p>

                    <br>

                    <h4>Gráfico - Anual por División</h4>
                    <canvas id="graficoTotalGeneral2" width="400" height="180"></canvas>

                    <p id="mensajeSinDatos2" style="display: none; color: red;"><strong>No hay datos
                            ingresados.</strong>
                    </p>
                    <p id="mensajeSinDatosTotales2" style="display: none; color: red;"><strong>No hay datos
                            ingresados.</strong></p>
                </div>
            </div>


            <?php if (count($especialidades) > 0): ?>
                <div class="card shadow-sm mt-5">
                    <div class="card-header">
                        <h5 class="mb-0">Tabla de Especialidades</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">

                            <div class="row mb-3 justify-content-end">
                                <div class="col-md-3 col-sm-6 d-flex align-items-center gap-2">
                                    <label for="filtroAnioTabla2" class="form-label mb-0 fw-semibold text-dark">Año:</label>
                                    <select id="filtroAnioTabla2" class="select2" multiple>
                                        <?php
                                        $aniosUnicos = array_unique(array_column($especialidades, 'anio'));
                                        sort($aniosUnicos);
                                        foreach ($aniosUnicos as $anio):
                                        ?>
                                            <option value="<?= $anio ?>"><?= $anio ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <table class="table table-bordered align-middle text-center" id="tabla2">
                                <thead>
                                    <tr>
                                        <th>Clave</th>
                                        <th>Especialidad</th>
                                        <th>División</th>
                                        <?php
                                        $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
                                        $nombresMeses = [
                                            'ene' => 'Enero',
                                            'feb' => 'Febrero',
                                            'mar' => 'Marzo',
                                            'abr' => 'Abril',
                                            'may' => 'Mayo',
                                            'jun' => 'Junio',
                                            'jul' => 'Julio',
                                            'ago' => 'Agosto',
                                            'sep' => 'Septiembre',
                                            'oct' => 'Octubre',
                                            'nov' => 'Noviembre',
                                            'dic' => 'Diciembre'
                                        ];
                                        foreach ($meses as $mes) {
                                            echo "<th>" . $nombresMeses[$mes] . "</th>";
                                        }
                                        ?>
                                        <th>Total</th>
                                        <th>Año</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $totalesMes = array_fill_keys($meses, 0);
                                    $granTotal = 0;

                                    foreach ($especialidades as $esp):
                                        $total_general = 0;
                                    ?>
                                        <tr>
                                            <td><?= $esp['clave'] ?></td>
                                            <td><?= $esp['especialidad'] ?></td>
                                            <td><?= $esp['descripcion'] ?></td>
                                            <?php
                                            foreach ($meses as $mes) {
                                                $val1era = (int)$esp[$mes . '_1era'];
                                                $valsub = (int)$esp[$mes . '_sub'];
                                                $suma_mes = $val1era + $valsub;
                                                $totalesMes[$mes] += $suma_mes;
                                                $total_general += $suma_mes;
                                                echo "<td>$suma_mes</td>";
                                            }
                                            $granTotal += $total_general;
                                            ?>
                                            <td style="background-color: #effffbff; font-weight: bold;">
                                                <?= $total_general ?>
                                            </td>
                                            <td><?= $esp['anio'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <!-- Fila de Totales Verticales -->
                                </tbody>
                                <tfoot>
                                    <tr
                                        style="background-color: #d1e7dd; font-weight: bold; border-top: 2px solid #0f5132; color: #0f5132;">
                                        <td colspan="3" class="text-center align-middle"
                                            style="background-color: #f2fefbff;">Total General</td>
                                        <?php foreach ($meses as $mes): ?>
                                            <td style="background-color: #f2fefbff;" id="total_<?= $mes ?>">0</td>
                                        <?php endforeach; ?>
                                        <td id="total_general"
                                            style="background-color: #cfffecff; font-size: medium; font-weight: 800; color: #076b7dff;">
                                            0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>

                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p><strong>No hay datos ingresados.</strong></p>
            <?php endif; ?>

        </div>

    </div>


    <footer>
        <p>Derechos reservados &copy; IMSS 2025</p>
    </footer>



    <!-- Script para tabla de especialidades -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

    <script>
        const coloresFijos = [
            'rgba(51, 102, 204, 0.6)', 'rgba(220, 57, 18, 0.6)', 'rgba(255, 153, 0, 0.6)',
            'rgba(16, 150, 24, 0.6)', 'rgba(153, 0, 153, 0.6)', 'rgba(59, 62, 172, 0.6)',
            'rgba(0, 153, 198, 0.6)', 'rgba(221, 68, 119, 0.6)', 'rgba(102, 170, 0, 0.6)',
            'rgba(184, 46, 46, 0.6)', 'rgba(49, 99, 149, 0.6)', 'rgba(153, 68, 153, 0.6)',
            'rgba(34, 170, 153, 0.6)', 'rgba(170, 170, 17, 0.6)', 'rgba(102, 51, 204, 0.6)',
            'rgba(230, 115, 0, 0.6)', 'rgba(139, 7, 7, 0.6)', 'rgba(50, 146, 98, 0.6)',
            'rgba(85, 116, 166, 0.6)', 'rgba(59, 62, 172, 0.6)'
        ];

        const dataEspecialidades2 = <?= json_encode($especialidades); ?>;
        const meses2 = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        const nombresMeses2 = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre',
            'Octubre', 'Noviembre', 'Diciembre'
        ];

        const filtroAnio2 = document.getElementById('filtroAnio2');
        const filtroEspecialidad2 = document.getElementById('filtroEspecialidad2');
        const filtroDescripcion2 = document.getElementById('filtroDescripcion2');

        let chartMeses2 = null;
        let chartTotalGeneral2 = null;
        let chartMensualDivision2 = null;
        let mostrarTotalesGenerales = true;

        function actualizarTodosFiltros2() {
            const aniosSel = $('#filtroAnio2').val() || [];
            const espSel = $('#filtroEspecialidad2').val() || [];
            const descSel = $('#filtroDescripcion2').val() || [];

            // Obtener opciones dinámicas para años, filtrados según especialidad y división seleccionadas
            const aniosDisponibles = [...new Set(
                dataEspecialidades2.filter(e =>
                    (espSel.length === 0 || espSel.includes(String(e.especialidad))) &&
                    (descSel.length === 0 || descSel.includes(String(e.descripcion)))
                ).map(e => String(e.anio))
            )].sort();

            // Opciones dinámicas para especialidades con filtro cruzado (años + división)
            const especialidadesDisponibles = [...new Set(
                dataEspecialidades2.filter(e =>
                    (aniosSel.length === 0 || aniosSel.includes(String(e.anio))) &&
                    (descSel.length === 0 || descSel.includes(String(e.descripcion)))
                ).map(e => String(e.especialidad))
            )].sort();

            // Opciones dinámicas para descripciones con filtro cruzado (años + especialidad)
            const descripcionesDisponibles = [...new Set(
                dataEspecialidades2.filter(e =>
                    (aniosSel.length === 0 || aniosSel.includes(String(e.anio))) &&
                    (espSel.length === 0 || espSel.includes(String(e.especialidad)))
                ).map(e => String(e.descripcion))
            )].sort();

            const selects = [{
                    selector: '#filtroAnio2',
                    valores: aniosDisponibles,
                    seleccionados: aniosSel
                },
                {
                    selector: '#filtroEspecialidad2',
                    valores: especialidadesDisponibles,
                    seleccionados: espSel
                },
                {
                    selector: '#filtroDescripcion2',
                    valores: descripcionesDisponibles,
                    seleccionados: descSel
                }
            ];

            // Paso 1: destruir Select2
            selects.forEach(({
                selector
            }) => {
                if ($(selector).hasClass("select2-hidden-accessible")) {
                    $(selector).select2('destroy');
                }
            });

            // Paso 2: reconstruir opciones
            selects.forEach(({
                selector,
                valores
            }) => {
                const select = document.querySelector(selector);
                select.innerHTML = '';
                valores.forEach(v => {
                    const option = document.createElement('option');
                    option.value = v;
                    option.textContent = v;
                    select.appendChild(option);
                });
            });

            // Paso 3: reinicializar Select2
            selects.forEach(({
                selector
            }) => {
                $(selector).select2({
                    placeholder: "-- Todos --",
                    width: '100%',
                    allowClear: true,
                    closeOnSelect: false,
                    dropdownAutoWidth: true,
                    templateSelection: function(data, container) {
                        $(container).addClass('select2-chosen');
                        return data.text;
                    }
                });
            });

            // Paso 4: restaurar valores seleccionados que aún estén disponibles
            selects.forEach(({
                selector,
                seleccionados,
                valores
            }) => {
                const valoresValidos = seleccionados.filter(val => valores.includes(val));
                $(selector).val(valoresValidos).trigger('change.select2');
            });
        }


        function actualizarGraficos2() {
            clearCharts2();

            const anios = $('#filtroAnio2').val();
            const especialidades = $('#filtroEspecialidad2').val();
            const divisiones = $('#filtroDescripcion2').val();

            const datosFiltrados = dataEspecialidades2.filter(e =>
                (anios.length === 0 || anios.includes(String(e.anio))) &&
                (especialidades.length === 0 || especialidades.includes(String(e.especialidad))) &&
                (divisiones.length === 0 || divisiones.includes(String(e.descripcion)))
            );

            const mostrar = datosFiltrados.length > 0;
            document.getElementById('graficoMeses2').style.display = mostrar ? 'block' : 'none';
            document.getElementById('graficoMensualDivision2').style.display = mostrar ? 'block' : 'none';
            document.getElementById('graficoTotalGeneral2').style.display = mostrar ? 'block' : 'none';
            if (!mostrar) return;

            const datasetsMensuales = datosFiltrados.map(e => ({
                label: `${e.especialidad} (${e.anio})`,
                data: meses2.map(m => (+e[m + '_1era'] || 0) + (+e[m + '_sub'] || 0)),
                borderColor: getRandomColor(),
                fill: false,
                tension: 0.3
            }));


            if (mostrarTotalesGenerales) {
                const totalesMes = meses2.map(m =>
                    datosFiltrados.reduce((acc, e) =>
                        acc + ((+e[m + '_1era'] || 0) + (+e[m + '_sub'] || 0)), 0)
                );

                datasetsMensuales.push({
                    label: "Total General Mensual",
                    data: totalesMes,
                    borderColor: 'black',
                    borderDash: [8, 5],
                    fill: false,
                    tension: 0.3
                });
            }

            chartMeses2 = new Chart(document.getElementById('graficoMeses2').getContext('2d'), {
                type: 'line',
                data: {
                    labels: nombresMeses2,
                    datasets: datasetsMensuales
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            const resumenPorDivisionAnio = {};
            datosFiltrados.forEach(e => {
                const key = `${e.descripcion} (${e.anio})`;
                const total = meses2.reduce((acc, m) => acc + (+e[m + '_1era'] || 0) + (+e[m + '_sub'] || 0), 0);
                resumenPorDivisionAnio[key] = (resumenPorDivisionAnio[key] || 0) + total;
            });

            const labels = Object.keys(resumenPorDivisionAnio);
            const valores = Object.values(resumenPorDivisionAnio);
            const granTotal = valores.reduce((a, b) => a + b, 0);
            const incluirTotalGeneral = (especialidades.length === 0);

            if (incluirTotalGeneral) {
                labels.push("Total General");
                valores.push(granTotal);
            }

            chartTotalGeneral2 = new Chart(document.getElementById('graficoTotalGeneral2').getContext('2d'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Total por División',
                        data: valores,
                        backgroundColor: labels.map((_, i) =>
                            incluirTotalGeneral && i === labels.length - 1 ? 'black' : coloresFijos[i %
                                coloresFijos.length]),
                        borderColor: 'rgba(0, 0, 0, 0.6)',
                        borderWidth: 1
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
                            offset: 4,
                            color: '#555',
                            font: {
                                weight: 'bold',
                                size: 16
                            },
                            formatter: value => value.toLocaleString('es-MX')
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }

        function actualizarGraficoMensualDivision2() {
            const anios = $('#filtroAnio2').val();
            const especialidades = $('#filtroEspecialidad2').val();
            const divisiones = $('#filtroDescripcion2').val();

            const datosFiltrados = dataEspecialidades2.filter(e =>
                (anios.length === 0 || anios.includes(String(e.anio))) &&
                (especialidades.length === 0 || especialidades.includes(String(e.especialidad))) &&
                (divisiones.length === 0 || divisiones.includes(String(e.descripcion)))
            );

            const canvas = document.getElementById('graficoMensualDivision2');
            const mensaje = document.getElementById('mensajeSinDatosMensualDivision2');

            if (chartMensualDivision2) chartMensualDivision2.destroy();

            if (datosFiltrados.length === 0) {
                canvas.style.display = 'none';
                mensaje.style.display = 'block';
                return;
            }

            canvas.style.display = 'block';
            mensaje.style.display = 'none';

            const agrupado = {};
            datosFiltrados.forEach(e => {
                const key = `${e.descripcion} (${e.anio})`;
                if (!agrupado[key]) agrupado[key] = Array(12).fill(0);
                meses2.forEach((m, i) => agrupado[key][i] += (+e[m + '_1era'] || 0) + (+e[m + '_sub'] || 0));
            });

            const datasets = Object.entries(agrupado).map(([label, data], i) => ({
                label,
                data,
                borderColor: coloresFijos[i % coloresFijos.length],
                fill: false,
                tension: 0.3
            }));

            chartMensualDivision2 = new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: nombresMeses2,
                    datasets
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function clearCharts2() {
            if (chartMeses2) chartMeses2.destroy();
            if (chartTotalGeneral2) chartTotalGeneral2.destroy();
            if (chartMensualDivision2) chartMensualDivision2.destroy();
        }

        function getRandomColor() {
            const r = Math.floor(Math.random() * 180);
            const g = Math.floor(Math.random() * 180);
            const b = Math.floor(Math.random() * 180);
            return `rgba(${r}, ${g}, ${b}, 0.7)`;
        }

        function toggleGrafico2() {
            const contenedor = document.getElementById('contenedorGrafico2');
            const boton = document.getElementById('btnGrafico2');
            const visible = contenedor.style.display === 'block';

            contenedor.style.display = visible ? 'none' : 'block';
            boton.textContent = visible ? '📊 Ver Gráfico' : 'X Ocultar Gráfico';

            if (!visible) {
                // Solo actualizar filtros y gráficos sin limpiar selección
                setTimeout(() => {
                    actualizarTodosFiltros2();
                    actualizarGraficos2();
                    actualizarGraficoMensualDivision2();
                }, 100);
            }

        }


        let actualizando = false;

        function actualizarFiltrosYGraficos() {
            if (actualizando) return;
            actualizando = true;

            setTimeout(() => {
                actualizarTodosFiltros2(); // <-- Asegúrate que esto esté aquí
                actualizarGraficos2();
                actualizarGraficoMensualDivision2();
                actualizarTotalesEspecialidades();
                actualizando = false;
            }, 100);

            // Además de actualizar gráficos, aplica los filtros a la tabla
            const division = $('#filtroDescripcion2').val() || [];
            const especialidad = $('#filtroEspecialidad2').val() || [];
            const anios = $('#filtroAnio2').val() || [];

            const regexDiv = division.length ? '^(' + division.join('|') + ')$' : '';
            tabla2.column(2).search(regexDiv, true, false);

            const regexEsp = especialidad.length ? '^(' + especialidad.join('|') + ')$' : '';
            tabla2.column(1).search(regexEsp, true, false);

            const regexAnio = anios.length ? '^(' + anios.join('|') + ')$' : '';
            tabla2.column(<?= count($meses) + 4 ?>).search(regexAnio, true, false);

            tabla2.draw();
            actualizarTotalesEspecialidades();

        }

        // Solo se actualizan gráficos, no se reconstruyen filtros
        $('#filtroAnio2, #filtroEspecialidad2, #filtroDescripcion2').on('change', actualizarFiltrosYGraficos);

        // Sincronización con la tabla de año sin loops infinitos
        function sincronizarSelects(select1, select2) {
            $(select1).on('change', function() {
                const valores = $(this).val();
                $(select2).val(valores).trigger('change');
            });
        }

        let sincronizando = false;

        function sincronizarSelectsSeguro(origen, destino) {
            $(origen).on('change', function() {
                if (sincronizando) return;
                sincronizando = true;
                const valores = $(this).val();
                $(destino).val(valores).trigger('change');
                sincronizando = false;
            });
        }

        sincronizarSelectsSeguro('#filtroAnio2', '#filtroAnioTabla2');
        sincronizarSelectsSeguro('#filtroAnioTabla2', '#filtroAnio2');


        // Sincronización entre select de tabla y gráfico
        function sincronizarMultiplesSelects(origen, destino) {
            const valores = $(origen).val();
            $(destino).val(valores).trigger('change');
        }

        // Inicialización global
        $(document).ready(() => {
            actualizarTodosFiltros2();
            actualizarGraficos2();
            actualizarGraficoMensualDivision2();
        });
    </script>

    <script>
        let tabla2;

        $(document).ready(function() {
            // Inicializa DataTable
            tabla2 = $('#tabla2').DataTable({
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

            // Inicializa Select2
            $('#filtroAnioTabla2').select2({
                placeholder: "-- Todos --",
                width: '100%',
                allowClear: true
            });


            // Filtro de año para la tabla
            $('#filtroAnioTabla2').on('change', function() {
                const anios = $(this).val() || [];
                const regex = anios.length ? '^(' + anios.join('|') + ')$' : '';
                tabla2
                    .column(<?= count($meses) + 4 ?>)
                    .search(regex, true, false)
                    .draw();
                actualizarTotalesEspecialidades();

            });

            // Al redibujar la tabla
            tabla2.on('draw', function() {
                actualizarTotalesEspecialidades(); // actualiza totales
            });

            actualizarTotalesEspecialidades(); // al cargar

            $('#filtroAnio2, #filtroEspecialidad2, #filtroDescripcion2, #filtroAnioTabla2').val(null).trigger(
                'change');

        });

        // Calcula los totales en el pie de tabla
        function actualizarTotalesEspecialidades() {
            const columnasMeses = <?= json_encode($meses); ?>;
            let totalGeneral = 0;

            columnasMeses.forEach((mes, index) => {
                let suma = 0;
                tabla2.column(index + 3, {
                    search: 'applied'
                }).nodes().each(function(cell) {
                    const valor = parseInt($(cell).text()) || 0;
                    suma += valor;
                });
                $('#total_' + mes).text(suma.toLocaleString('es-MX'));
                totalGeneral += suma;
            });

            $('#total_general').text(totalGeneral.toLocaleString('es-MX'));
        }
    </script>

    <script>
        document.getElementById('formDescargarExcel').addEventListener('submit', function(e) {
            const getMultipleValues = (select) =>
                Array.from(select.selectedOptions).map(opt => opt.value).join(',');

            document.getElementById('inputAnio').value = getMultipleValues(document.getElementById('filtroAnio2'));
            document.getElementById('inputEspecialidad').value = getMultipleValues(document.getElementById(
                'filtroEspecialidad2'));
            document.getElementById('inputDescripcion').value = getMultipleValues(document.getElementById(
                'filtroDescripcion2'));
        });
    </script>


    <!-- Script para generar pdf con el gráfico para la sección de especialidades -->
    <script>
        document.getElementById('btnDescargarGraficoPDF2').addEventListener('click', async () => {
            const {
                jsPDF
            } = window.jspdf;

            const graficoCard = document.getElementById("graficoCard");
            const estabaOculto = graficoCard && graficoCard.style.display === "none";
            if (estabaOculto) graficoCard.style.display = "block";

            // Esperar para asegurar render completo
            await new Promise(resolve => setTimeout(resolve, 300));

            const canvasMeses = document.getElementById("graficoMeses2");
            const canvasMensualDivision = document.getElementById("graficoMensualDivision2");
            const canvasAnualDivision = document.getElementById("graficoTotalGeneral2");

            // Crear PDF tamaño legal (216 x 356 mm)
            const pdf = new jsPDF({
                orientation: "portrait",
                unit: "mm",
                format: "legal" // Tamaño legal
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
            pdf.text("ESPECIALIDADES TOTAL", 105, 24, {
                align: "center"
            });

            // Línea horizontal
            pdf.setLineWidth(0.5);
            pdf.line(10, 28, 200, 28);

            // Función para agregar gráficos
            let yOffset = 39;
            const addCanvasToPDF = (canvas, titulo, offsetY) => {
                const imgData = canvas.toDataURL("image/png");
                pdf.setFontSize(11);
                pdf.setFont(undefined, "bold");
                pdf.text(titulo, 10, offsetY);
                pdf.addImage(imgData, 'PNG', 10, offsetY + 5, 190, 85); // tamaño reducido
            };

            if (canvasMeses) {
                addCanvasToPDF(canvasMeses, "Gráfico - Mensual por Especialidad", yOffset);
                yOffset += 100;
            }

            if (canvasMensualDivision) {
                addCanvasToPDF(canvasMensualDivision, "Gráfico - Mensual por División", yOffset);
                yOffset += 100;
            }

            if (canvasAnualDivision) {
                addCanvasToPDF(canvasAnualDivision, "Gráfico - Anual por División", yOffset);
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

            pdf.save("grafico-tabla-Especialidades.pdf");

            if (estabaOculto) graficoCard.style.display = "none";
        });
    </script>

    <script>
        document.getElementById('btnDescargarImagenesCanvas2').addEventListener('click', function() {
            exportarCanvasConFondo('graficoMeses2', 'grafico_mensual_especialidad_especialidadesTotal.png');
            exportarCanvasConFondo('graficoMensualDivision2', 'grafico_mensual_division_especialidadadesTotal.png');
            exportarCanvasConFondo('graficoTotalGeneral2', 'grafico_anual_division_especialidadesTotal.png');
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

            // Dibujar el canvas original
            ctx.drawImage(canvasOriginal, 0, 0);

            // Crear enlace y descargar
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
        const filtroAnioTabla2 = document.getElementById('filtroAnioTabla2');
    </script>


    <!-- Links de librerías para pdf del gráfico -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>

    <script src="../../js/scripts.js" defer></script>
    <script src="../../js/bootstrap.bundle.min.js" defer></script>

</body>

</html>
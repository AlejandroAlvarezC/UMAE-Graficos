<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
  $_SESSION['login_error'] = "Debes iniciar sesión como administrador para acceder a esa sección.";
  header('Location: ../admin/login.php');
  exit();
}

require_once '../../modelos/vencer.php';

// --- PROCESAMIENTO PARA GRÁFICOS --- Gabriel Alvarez 
$registros = Vencer::listar();
$dataSexo = ['Hombres' => 0, 'Mujeres' => 0, 'No Definido' => 0];
$dataEventos = [];

echo "<script>var datosVencer = " . json_encode($registros, JSON_UNESCAPED_UNICODE) . ";</script>";

//Ordenar por año
$anioSeleccionado = $_GET['anio'] ?? 'todos';
$registros = Vencer::listar();
$dataSexo = ['Hombres' => 0, 'Mujeres' => 0, 'No Definido' => 0];
$dataEventos = [];

if ($registros && is_array($registros)) {
      foreach ($registros as $r) {

      //Ordenar por año 
          $fechaRaw = $r[4] ?? $r['fecha'] ?? null;
          $fechaRaw = $r[4] ?? $r['fecha'] ?? null;
          if ($fechaRaw) {
              $anioRegistro = date('Y', strtotime($fechaRaw));
              
              // Si hay un filtro activo y no coincide, saltamos este registro
              if ($anioSeleccionado !== 'todos' && $anioRegistro !== $anioSeleccionado) {
                  continue; 
              }
          }
        
        //Ordenar por sexo
        $sexoRaw = $r[5] ?? $r['sexo'] ?? 'N/E'; 
        $sexo = strtoupper(trim($sexoRaw));

          if (str_contains($sexo, 'H') || str_contains($sexo, 'MAS')) {
              $dataSexo['Hombres']++;
          } elseif (str_contains($sexo, 'M') || str_contains($sexo, 'FEM')) {
              $dataSexo['Mujeres']++;
          } else {
              $dataSexo['No Definido']++;
          }

        $evento = $r[1] ?? $r['evento'] ?? 'No especificado';
        $dataEventos[$evento] = ($dataEventos[$evento] ?? 0) + 1;

    }
}

?>

<script>
    var datosVencer = <?php echo json_encode($registros, JSON_UNESCAPED_UNICODE); ?>;
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

  <!-- CSS de Select2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <!-- JS de Select2 -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <title> VENCER | Administrador</title>

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
      background-color: rgba(16, 16, 16, 1);
      color: white;
      border: 1px solid rgba(75, 75, 75, 1);
      text-align: center;
      padding: 0.9px;
      vertical-align: middle;
    }

    table.table tbody tr {
      background-color: white;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      transition: box-shadow 0.2s ease-in-out;
    }

    table.table tbody tr:hover {
      box-shadow: 0 4px 12px rgba(122, 35, 18, 0.5);
    }

    table.table td {
      padding: 0.9px;
      text-align: center;
      vertical-align: middle;
      border: 1px solid #dee2e6;
    }

    td {
      max-width: 250px;
      max-height: 90px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* -----------------------------------
   FILTROS
----------------------------------- */
    /* Limitar ancho de los selects */
    .select2-container--default .select2-selection--multiple {
      max-width: 100%;
      white-space: nowrap;
      overflow-x: auto;
      overflow-y: hidden;
    }


    /* Opcional: mejorar la visibilidad del placeholder o texto largo */
    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
      display: block;
      max-height: 70px;
      overflow-y: auto;
      overflow-x: hidden;
    }


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
      border-color: rgba(173, 85, 17, 1);
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
      color: rgba(163, 70, 17, 1);
      font-weight: 800;
      font-size: 1.4rem;
      border-bottom: 2px solid rgba(151, 63, 19, 0.3);
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
      box-shadow: 0 0 10px rgba(164, 83, 6, 0.5);
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
      border-color: rgba(214, 89, 26, 1);
      outline: none;
      box-shadow: 0 0 6px rgba(214, 111, 26, 0.25);
    }

    /* Selector cantidad de registros */
    .dataTable-selector {
      border-radius: 12px;
      padding: 6px 12px;
      border: 1.5px solid rgb(42, 95, 17);
      background-color: #fff;
      font-weight: 500;
      color: rgba(95, 42, 17, 1);
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
      border-color: rgba(131, 69, 40, 1) !important;
      background-color: rgb(255, 255, 255);
      color: black;
    }

    .btn-urgencia {
      background-color: #d55500ff;
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
      background-color: #dc6300ff;
      /* más claro al pasar */
      box-shadow: 0 6px 16px rgba(179, 143, 0, 0.4);
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
      background: linear-gradient(135deg, rgba(110, 61, 30, 1), rgba(172, 93, 29, 1));
      border: none;
      border-radius: 12px;
      padding: 12px 30px;
      font-weight: 700;
      font-size: 1.1rem;
      color: white !important;
      box-shadow: 0 6px 15px rgba(230, 102, 28, 0.61);
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
      background: linear-gradient(135deg, rgba(255, 119, 0rgba(214, 104, 20, 1)5, 20));
      box-shadow: 0 8px 25px rgba(185, 111, 31, 0.89);
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

    /* -----------------------------------
   NAVBAR Y DROPDOWN
----------------------------------- */
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

    .dropdown-item.dropdown-submenu-toggle.active,
    .dropdown-item.dropdown-submenu-toggle:hover {
      background-color: rgb(202, 155, 26) !important;
      color: white;
    }

    /* -----------------------------------
   FOOTER
----------------------------------- */
    footer {
      background-color: #7a123a;
      ;
      width: 100%;
      text-align: center;
      color: white;
      font-weight: bold;
      padding-top: 10px;
    }

    /* -----------------------------------
   OTROS
----------------------------------- */
    .section-header {
      border-left: 5px solid rgba(172, 89, 29, 1);
      padding-left: 15px;
    }

    .hidden {
      display: none;
    }

    .mb-3 {
      margin-bottom: 1rem !important;
    }

    .section-header {
      background-color: #dee2e6;
      /* Fondo gris claro */
      border-left: 5px solid #97571cff;
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
      background-color: #d68e67ff;
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

<body style="background-color: #fffef6ff">

  <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #7a123a;;">
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
            <a class="nav-link text-white" href="./vencer.php">Vencer</a>
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
          <a class="btn btn-outline-light" href="../usuario/vencer_user.php">Vista de usuario</a>

          <div class="dropdown">
            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <i class="fas fa-user-circle"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="../usuario/vencer_user.php"><i class="fas fa-eye me-2"></i>Ver
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
    <div class="container py-4">
      <!-- Encabezado -->
      <div class="section-header">
        <h1 class="h2">VENCER</h1>
        <small class="text-muted">Listado de registros.</small>
      </div>

      <!-- Botones -->
      <div class="card shadow-sm mb-4 p-3">
        <div class="row gy-2">
          <div class="col-md-auto d-flex gap-2 flex-wrap align-items-center">
            <a href="./ingresar-vencer.php" class="btn btn-urgencia">
              ➕ Agregar manual
            </a>
            <a href="./agregar-vencer.php" class="btn btn-urgencia">
              📥 Cargar archivo
            </a>
            <a href="./actualizar-vencer.php" class="btn btn-urgencia"
              onclick="return confirm('¿Seguro que desea hacer reemplazo de datos en los registros?')">
              🔁 Actualizar datos
            </a>
          </div>

          <div class="col-md d-flex justify-content-md-end align-items-center gap-2 flex-wrap">
            <form id="formDescargarExcelVencer" action="./descargar-excel_vencer.php" method="post" class="m-0">
              <input type="hidden" name="folio" id="inputFolio">
              <input type="hidden" name="evento" id="inputEvento">
              <input type="hidden" name="ini_paciente" id="inputIniciales">
              <input type="hidden" name="seguridad_social" id="inputNSS">
              <input type="hidden" name="edad" id="inputEdad">
              <input type="hidden" name="sexo" id="inputSexo">
              <input type="hidden" name="diagnostico" id="inputDiagnostico">
              <input type="hidden" name="fecha_evento" id="inputFechaEvento">
              <input type="hidden" name="fecha_noti" id="inputFechaNotificacion">
              <input type="hidden" name="turno" id="inputTurno">
              <input type="hidden" name="servicio" id="inputServicio">
              <input type="hidden" name="categoria" id="inputCategoria">
              <input type="hidden" name="proceso" id="inputProceso">
              <input type="hidden" name="definicion" id="inputDefinicion">
              <input type="hidden" name="descripcion" id="inputDescripcion">
              <input type="hidden" name="estatus" id="inputEstatus">
              <input type="hidden" name="anio" id="inputAnio">
              <button class="btn btn-urgencia" type="submit">🟩 Descargar Excel</button>
            </form>

            <button id="btnGrafico" class="btn btn-urgencia" data-bs-toggle="modal" data-bs-target="#modalGraficos">
              📊 Ver Gráficos
            </button>
          </div>
        </div>
      </div>
      <!-- Modal de Gráficos -->
<div class="modal fade" id="modalGraficos" tabindex="-1" aria-labelledby="modalGraficosLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            
            <div class="modal-header" style="background-color: #7a123a; color: white;">
                <h5 class="modal-title fw-bold"><i class="fas fa-chart-line me-2"></i>Dashboard Estadístico VENCER</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body bg-light">

                <div class="d-flex justify-content-center align-items-center mb-4 gap-3 bg-white p-3 rounded shadow-sm border">
                    <label fw-bold>📅 Filtrar por Año:</label>
                    <select id="filtroAnio" class="form-select w-auto fw-bold shadow-sm" style="border-color: #7a123a; background-color: #fff5f5;">
                        <option value="todos">Cargando...</option>
                    </select>
                    
                    <div class="vr mx-2"></div> <div class="d-flex align-items-center">
                        <span class="text-muted small text-uppercase fw-bold me-2">Total Eventos:</span>
                        <span id="lblTotalEventos" class="fs-4 fw-bold text-danger">0</span>
                    </div>
                </div>

                <ul class="nav nav-pills nav-fill mb-4 gap-2 p-1 bg-white rounded shadow-sm" id="graficosTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#tab-general">📊 Panorama General</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold text-danger" data-bs-toggle="tab" data-bs-target="#tab-adverso">🚨 Eventos Adversos</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold text-warning" data-bs-toggle="tab" data-bs-target="#tab-cuasi">⚠️ Cuasifallas</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#tab-centinela">👁️ Eventos Centinela</button></li>
                </ul>

                <div class="tab-content" id="graficosTabContent">
                    
                    <div class="tab-pane fade show active" id="tab-general">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-secondary mb-3">DISTRIBUCIÓN POR SEXO</h6>
                                        <div style="height:250px"><canvas id="chartSexoGen"></canvas></div>
                                        <div id="tablaSexoGen" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-secondary mb-3">CLASIFICACIÓN DE EVENTOS</h6>
                                        <div style="height:250px"><canvas id="chartEventosGen"></canvas></div>
                                        <div id="tablaEventosGen" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-secondary mb-3">RANGO DE EDAD (Por Sexo)</h6>
                                        <div style="height:250px"><canvas id="chartEdadSexoGen"></canvas></div>
                                        <div id="tablaEdadSexoGen" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-success mb-3">DISTRIBUCIÓN POR TURNO</h6>
                                        <div style="height:250px"><canvas id="chartTurnoGen"></canvas></div>
                                        <div id="tablaTurnoGen" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-primary mb-3">TOP SERVICIOS CON MAYOR INCIDENCIA</h6>
                                        <div style="height:350px"><canvas id="chartTopServiciosGen"></canvas></div>
                                        <div id="tablaTopServiciosGen" class="mt-3 table-responsive" style="max-height: 200px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-adverso">
                        <div class="alert alert-danger py-2 text-center fw-bold mb-3"><i class="fas fa-exclamation-circle me-2"></i>Análisis de Eventos Adversos</div>
                        <div class="row g-3 mb-3">
                            <div class="col-lg-6">
                                <div class="card h-100 shadow-sm border-danger border-opacity-25">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-danger">1. ÁREAS CON MAYOR INCIDENCIA</h6>
                                        <div style="height: 300px;"><canvas id="chartServicioAdv"></canvas></div>
                                        <div id="tablaServicioAdv" class="mt-3 table-responsive" style="max-height: 200px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100 shadow-sm border-danger border-opacity-50" style="background-color: #fff5f5;">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-dark">2. ANÁLISIS ÁREA CRÍTICA: <span id="lblTopAreaAdv" class="text-danger">---</span></h6>
                                        <div style="height: 300px;"><canvas id="chartDrillDownAdv"></canvas></div>
                                        <div id="tablaDrillDownAdv" class="mt-3 table-responsive" style="max-height: 200px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="card h-100 shadow-sm border-danger border-opacity-25">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-danger">3. CAUSAS GLOBALES</h6>
                                        <div style="height: 300px;"><canvas id="chartDefinicionAdv"></canvas></div>
                                        <div id="tablaDefinicionAdv" class="mt-3 table-responsive" style="max-height: 200px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-cuasi">
                        <div class="alert alert-warning py-2 text-center fw-bold mb-3 text-dark"><i class="fas fa-shield-alt me-2"></i>Análisis de Cuasifallas</div>
                        <div class="row g-3 mb-3">
                            <div class="col-lg-6">
                                <div class="card h-100 shadow-sm border-warning border-opacity-25">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-warning">1. ÁREAS CON MAYOR INCIDENCIA</h6>
                                        <div style="height: 300px;"><canvas id="chartServicioCuasi"></canvas></div>
                                        <div id="tablaServicioCuasi" class="mt-3 table-responsive" style="max-height: 200px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100 shadow-sm border-warning border-opacity-50" style="background-color: #fffsf0;">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-dark">2. ANÁLISIS ÁREA CRÍTICA: <span id="lblTopAreaCuasi" class="text-warning">---</span></h6>
                                        <div style="height: 300px;"><canvas id="chartDrillDownCuasi"></canvas></div>
                                        <div id="tablaDrillDownCuasi" class="mt-3 table-responsive" style="max-height: 200px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="card h-100 shadow-sm border-warning border-opacity-25">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-warning">3. CAUSAS GLOBALES</h6>
                                        <div style="height: 300px;"><canvas id="chartDefinicionCuasi"></canvas></div>
                                        <div id="tablaDefinicionCuasi" class="mt-3 table-responsive" style="max-height: 200px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-centinela">
                        <div class="alert alert-dark py-2 text-center fw-bold mb-3">Análisis de Eventos Centinela</div>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-dark">ÁREAS CON MAYOR INCIDENCIA</h6>
                                        <div style="height: 350px;"><canvas id="chartServicioCent"></canvas></div>
                                        <div id="tablaServicioCent" class="mt-3 table-responsive" style="max-height: 200px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-dark">CAUSAS PRINCIPALES (Definición)</h6>
                                        <div style="height: 350px;"><canvas id="chartDefinicionCent"></canvas></div>
                                        <div id="tablaDefinicionCent" class="mt-3 table-responsive" style="max-height: 200px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div> <div class="row mt-4 border-top pt-3">
                    <div class="col text-center">
                        <button id="btnDescargarExcelStats" class="btn btn-success fw-bold shadow px-4 py-2">
                            <i class="fas fa-file-excel me-2"></i>Descargar Reporte (Excel)
                        </button>
                    </div>
                </div>

            </div> </div>
    </div>
</div>
<!-- Modal graficos hasta aqui -->
      <!-- Filtros -->
      <div id="filtrosContainer" class="card card-body shadow-sm mb-4" style="display: none;">
        <div class="row gy-3">
          <h4>FILTROS</h4>
          <hr>
          <div class="col-md-3">
            <label for="filterFolio" class="form-label">Folio:</label>
            <select id="filterFolio" class="form-select select-personalizado" multiple>
              <!-- Opciones dinámicas -->
            </select>
          </div>
          <div class="col-md-3">
            <label for="filterEvento" class="form-label">Evento:</label>
            <select id="filterEvento" class="form-select select-personalizado" multiple></select>
          </div>
          <div class="col-md-3">
            <label for="filterIniciales" class="form-label">Iniciales:</label>
            <select id="filterIniciales" class="form-select select-personalizado" multiple></select>
          </div>
          <div class="col-md-3">
            <label for="filterNSS" class="form-label">NSS:</label>
            <select id="filterNSS" class="form-select select-personalizado" multiple></select>
          </div>

          <div class="col-md-3">
            <label for="filterEdad" class="form-label">Edad:</label>
            <select id="filterEdad" class="form-select select-personalizado" multiple></select>
          </div>
          <div class="col-md-3">
            <label for="filterSexo" class="form-label">Sexo:</label>
            <select id="filterSexo" class="form-select select-personalizado" multiple></select>
          </div>
          <div class="col-md-3">
            <label for="filterDiagnostico" class="form-label">Diagnóstico:</label>
            <select id="filterDiagnostico" class="form-select select-personalizado" multiple></select>
          </div>
          <div class="col-md-3">
            <label for="filterFechaEvento" class="form-label">Fecha Evento:</label>
            <select id="filterFechaEvento" class="form-select select-personalizado" multiple></select>
          </div>

          <div class="col-md-3">
            <label for="filterFechaNotificacion" class="form-label">Fecha Notificación:</label>
            <select id="filterFechaNotificacion" class="form-select select-personalizado" multiple></select>
          </div>
          <div class="col-md-3">
            <label for="filterTurno" class="form-label">Turno:</label>
            <select id="filterTurno" class="form-select select-personalizado" multiple></select>
          </div>
          <div class="col-md-3">
            <label for="filterServicio" class="form-label">Servicio:</label>
            <select id="filterServicio" class="form-select select-personalizado" multiple></select>
          </div>
          <div class="col-md-3">
            <label for="filterCategoria" class="form-label">Categoría:</label>
            <select id="filterCategoria" class="form-select select-personalizado" multiple></select>
          </div>

          <div class="col-md-3">
            <label for="filterProceso" class="form-label">Proceso:</label>
            <select id="filterProceso" class="form-select select-personalizado" multiple></select>
          </div>
          <div class="col-md-3">
            <label for="filterDefinicion" class="form-label">Definición:</label>
            <select id="filterDefinicion" class="form-select select-personalizado" multiple></select>
          </div>
          <div class="col-md-3">
            <label for="filterDescripcion" class="form-label">Descripción:</label>
            <select id="filterDescripcion" class="form-select select-personalizado" multiple></select>
          </div>
          <div class="col-md-3">
            <label for="filterEstatus" class="form-label">Estatus:</label>
            <select id="filterEstatus" class="form-select select-personalizado" multiple></select>
          </div>

          <div class="col-md-3">
            <label for="filterAnio" class="form-label">Año:</label> 
            <select id="filterAnio" class="form-select select-personalizado" multiple></select>
          </div>
        </div>
      </div>


      <!-- Tabla -->
      <?php $registros = Vencer::listar(); ?>
      <?php if (count($registros) > 0): ?>
        <div class="card shadow-sm mt-4">
          <div class="card-header bg-light">
            <h5 class="mb-0">Tabla - VENCER</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered table-hover text-center align-middle" id="tabla-vencer">
                <thead class="table-light">
                  <tr>
                    <th>Folio</th>
                    <th>Evento</th>
                    <th>Iniciales</th>
                    <th>NSS</th>
                    <th>Edad</th>
                    <th>Sexo</th>
                    <th>Diagnostico</th>
                    <th>Fecha Evento</th>
                    <th>Fecha Notificacion</th>
                    <th>Turno</th>
                    <th>Servicio</th>
                    <th>Categoria</th>
                    <th>Proceso</th>
                    <th>Definicion</th>
                    <th>Descripcion</th>
                    <th>Estatus</th>
                    <th>Anio</th>
                    <th>Editar</th>
                    <th>Eliminar</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($registros as $r): ?>
                    <tr>
                      <td><?= htmlspecialchars($r['folio']) ?></td>
                      <td><?= htmlspecialchars($r['evento']) ?></td>
                      <td><?= htmlspecialchars($r['ini_paciente']) ?></td>
                      <td><?= htmlspecialchars($r['seguridad_social']) ?></td>
                      <td><?= htmlspecialchars($r['edad']) ?></td>
                      <td><?= htmlspecialchars($r['sexo']) ?></td>

                      <!-- Diagnóstico -->
                      <td><?= htmlspecialchars($r['diagnostico']) ?></td>

                      <td><?= htmlspecialchars($r['fecha_evento']) ?></td>
                      <td><?= htmlspecialchars($r['fecha_noti']) ?></td>
                      <td><?= htmlspecialchars($r['turno']) ?></td>
                      <td><?= htmlspecialchars($r['servicio']) ?></td>
                      <td><?= htmlspecialchars($r['categoria']) ?></td>

                      <!-- Proceso -->
                      <td><?= htmlspecialchars($r['proceso']) ?></td>

                      <!-- Definición -->
                      <td><?= htmlspecialchars($r['definicion']) ?></td>

                      <!-- Descripción -->
                      <td><?= htmlspecialchars($r['descripcion']) ?></td>


                      <td><?= htmlspecialchars($r['estatus']) ?></td>
                      <td><?= htmlspecialchars($r['anio']) ?></td>
                      <td>
                        <a class="btn btn-outline-primary btn-sm" href="./editar-vencer.php?id=<?= base64_encode($r['id']) ?>">Editar</a>
                      </td>
                      <td>
                        <a class="btn btn-outline-danger btn-sm" href="../../controladores/vencer.php?a=Eliminar&id=<?= base64_encode($r['id']) ?>"
                          onclick="return confirm('¿Desea eliminar este registro?')">Eliminar</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="alert alert-warning mt-4">No hay datos ingresados en la tabla Vencer.</div>
      <?php endif; ?>
    </div>
  </div>

  <footer>
    <p>Derechos reservados &copy; IMSS 2025</p>
  </footer>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("🚀 VENCER: Sistema Completo (Filtro Año Reparado)");

    // CONFIGURACIÓN DE COLUMNAS (Ajusta si cambia tu tabla)
    const COL = { EVENTO: 1, EDAD: 4, SEXO: 5, TURNO: 9, SERVICIO: 10, DEFINICION: 13, ANIO: 16 };

    // --- A. TABLA PRINCIPAL ---
    const tabla = $('#tabla-vencer').DataTable({
        pageLength: 10, order: [[0, 'asc']],
        language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
    });

    // --- B. FILTROS DE TABLA (Select2) ---
    function llenarFiltros() {
        const headers = ['Folio', 'Evento', 'Iniciales', 'NSS', 'Edad', 'Sexo', 'Diagnostico', 'FechaEvento', 'FechaNotificacion', 'Turno', 'Servicio', 'Categoria', 'Proceso', 'Definicion', 'Descripcion', 'Estatus', 'Anio'];
        headers.forEach((h, i) => {
            const select = $(`#filter${h}`);
            if(!select.length) return;
            const values = new Set();
            tabla.column(i).nodes().to$().each(function() {
                let v = $(this).text().replace(/Ver más|Ver menos/g, '').trim() || 'NULO';
                values.add(v);
            });
            select.empty().append(new Option('NULO', 'NULO'));
            Array.from(values).sort().forEach(v => { if(v !== 'NULO') select.append(new Option(v, v)); });
        });
    }
    $('.select-personalizado').select2({ placeholder: 'Filtrar...', allowClear: true, width: '100%' });
    $('.select-personalizado').on('change', () => tabla.draw());
    
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'tabla-vencer') return true;
        const headers = ['Folio', 'Evento', 'Iniciales', 'NSS', 'Edad', 'Sexo', 'Diagnostico', 'FechaEvento', 'FechaNotificacion', 'Turno', 'Servicio', 'Categoria', 'Proceso', 'Definicion', 'Descripcion', 'Estatus', 'Anio'];
        for (let i = 0; i < headers.length; i++) {
            const vals = $(`#filter${headers[i]}`).val();
            if (vals && vals.length > 0 && !vals.includes(data[i].trim() || 'NULO')) return false;
        }
        return true;
    });
    llenarFiltros();
    $('#filtrosContainer').show();

    // --- C. LÓGICA DEL FILTRO DE AÑO (REPARADO) ---
    // Esta función busca los años en la columna 16 y llena el select
    function inicializarFiltroAnio() {
        const fAnio = document.getElementById('filtroAnio');
        if(fAnio) {
            // Obtener años únicos de la columna 16
            const anios = [...new Set(tabla.column(COL.ANIO).data().toArray())]
                .filter(x => x && x !== 'NULO')
                .sort()
                .reverse();
            
            // Limpiar "Cargando..." y llenar opciones
            fAnio.innerHTML = '<option value="todos">📅 Todos los años</option>';
            anios.forEach(a => fAnio.append(new Option(a, a)));
            
            // Evento de cambio
            fAnio.addEventListener('change', function() {
                const val = this.value;
                // Filtrar tabla principal por columna Año
                tabla.column(COL.ANIO).search(val === 'todos' ? '' : val).draw();
                // Regenerar gráficos con los datos filtrados
                generarGraficos();
            });
        }
    }
    // Ejecutar inmediatamente
    inicializarFiltroAnio();


    // --- D. MOTOR DE GRÁFICOS ---
    let charts = {};

    function generarGraficos() {
        setTimeout(() => {
            const rows = tabla.rows({ search: 'applied' }).data().toArray();
            const lbl = document.getElementById('lblTotalEventos');
            if(lbl) lbl.textContent = rows.length;
            if (rows.length === 0) return;

            // Procesar Datos
            let s = { 
                General: { Sexo:{}, Evento:{}, Turno:{}, Servicio:{}, Edad:{} },
                Adverso: { Servicio:{}, Definicion:{} },
                Cuasi: { Servicio:{}, Definicion:{} },
                Centinela: { Servicio:{}, Definicion:{} }
            };

            rows.forEach(r => {
                const clean = (txt) => String(txt).replace(/<[^>]*>?/gm, '').replace(/Ver más|Ver menos/g, '').trim() || 'NULO';
                let ev = clean(r[COL.EVENTO]), ed = clean(r[COL.EDAD]), sx = clean(r[COL.SEXO]), tu = clean(r[COL.TURNO]), sv = clean(r[COL.SERVICIO]), def = clean(r[COL.DEFINICION]);

                // General
                s.General.Sexo[sx] = (s.General.Sexo[sx] || 0) + 1;
                s.General.Evento[ev] = (s.General.Evento[ev] || 0) + 1;
                s.General.Turno[tu] = (s.General.Turno[tu] || 0) + 1;
                s.General.Servicio[sv] = (s.General.Servicio[sv] || 0) + 1;
                if (!s.General.Edad[ed]) s.General.Edad[ed] = { H: 0, M: 0 };
                (sx.toUpperCase().includes('MASC') || sx.toUpperCase().includes('HOMBRE')) ? s.General.Edad[ed].H++ : s.General.Edad[ed].M++;

                // Específicos
                let evUpper = ev.toUpperCase();
                if (evUpper.includes('ADVERSO')) { s.Adverso.Servicio[sv]=(s.Adverso.Servicio[sv]||0)+1; s.Adverso.Definicion[def]=(s.Adverso.Definicion[def]||0)+1; }
                else if (evUpper.includes('CUASI')) { s.Cuasi.Servicio[sv]=(s.Cuasi.Servicio[sv]||0)+1; s.Cuasi.Definicion[def]=(s.Cuasi.Definicion[def]||0)+1; }
                else if (evUpper.includes('CENTINELA')) { s.Centinela.Servicio[sv]=(s.Centinela.Servicio[sv]||0)+1; s.Centinela.Definicion[def]=(s.Centinela.Definicion[def]||0)+1; }
            });

            const draw = (id, type, labels, data, colors, extraOpts={}) => {
                const cvs = document.getElementById(id); if (!cvs) return;
                if (charts[id]) charts[id].destroy();
                charts[id] = new Chart(cvs.getContext('2d'), {
                    type: type, data: { labels: labels, datasets: [{ label: 'Total', data: data, backgroundColor: colors }] },
                    options: { responsive: true, maintainAspectRatio: false, animation: false, indexAxis: 'y', ...extraOpts }
                });
            };

            const dibujarTabla = (idTabla, dataObj, tituloColumna) => {
                const el = document.getElementById(idTabla); 
                if(!el) return;
                const entries = Object.entries(dataObj).sort((a,b) => {
                    const valA = (typeof a[1] === 'object') ? (a[1].H + a[1].M) : a[1];
                    const valB = (typeof b[1] === 'object') ? (b[1].H + b[1].M) : b[1];
                    return valB - valA;
                });
                let html = `<table class="table table-sm table-striped table-bordered text-center small mb-0"><thead class="table-dark"><tr><th>${tituloColumna}</th><th>Total</th></tr></thead><tbody>`;
                entries.forEach(([k, v]) => {
                    let val = (typeof v === 'object') ? (v.H + v.M) : v;
                    html += `<tr><td class="text-start text-break">${k}</td><td class="fw-bold">${val}</td></tr>`;
                });
                if(entries.length === 0) html += `<tr><td colspan="2">Sin datos</td></tr>`;
                el.innerHTML = html + '</tbody></table>';
            };

            // 1. GENERALES
            draw('chartSexoGen', 'pie', Object.keys(s.General.Sexo), Object.values(s.General.Sexo), ['#0d6efd','#dc3545','#ffc107'], {indexAxis:'x'});
            dibujarTabla('tablaSexoGen', s.General.Sexo, 'Sexo');
            draw('chartEventosGen', 'bar', Object.keys(s.General.Evento), Object.values(s.General.Evento), '#7a123a', {indexAxis:'x'});
            dibujarTabla('tablaEventosGen', s.General.Evento, 'Evento');
            draw('chartTurnoGen', 'bar', Object.keys(s.General.Turno), Object.values(s.General.Turno), '#198754');
            dibujarTabla('tablaTurnoGen', s.General.Turno, 'Turno');
            const topS = Object.entries(s.General.Servicio).sort((a,b)=>b[1]-a[1]).slice(0,10);
            draw('chartTopServiciosGen', 'bar', topS.map(x=>x[0]), topS.map(x=>x[1]), '#0288d1');
            dibujarTabla('tablaTopServiciosGen', s.General.Servicio, 'Servicio'); 
            const cvsEd = document.getElementById('chartEdadSexoGen');
            if(cvsEd) {
                const edades = Object.keys(s.General.Edad).sort((a,b) => parseInt(a)-parseInt(b));
                if(charts['chartEdadSexoGen']) charts['chartEdadSexoGen'].destroy();
                charts['chartEdadSexoGen'] = new Chart(cvsEd, {
                    type: 'bar', data: { labels: edades, datasets: [{ label: 'H', data: edades.map(e=>s.General.Edad[e].H), backgroundColor: '#0d6efd'}, {label: 'M', data: edades.map(e=>s.General.Edad[e].M), backgroundColor: '#dc3545'}] },
                    options: { responsive: true, maintainAspectRatio: false, scales: {x:{stacked:true}, y:{stacked:true}} }
                });
                dibujarTabla('tablaEdadSexoGen', s.General.Edad, 'Edad');
            }

            // 2. DRILL DOWN (Adversos y Cuasi)
            const generarDrillDown = (tipo, colorBarra, ids) => {
                const dataObj = s[tipo];
                const tServ = Object.entries(dataObj.Servicio).sort((a,b)=>b[1]-a[1]);
                
                draw(ids.servChart, 'bar', tServ.slice(0,10).map(x=>x[0]), tServ.slice(0,10).map(x=>x[1]), colorBarra);
                dibujarTabla(ids.servTable, dataObj.Servicio, 'Servicio');

                const tDef = Object.entries(dataObj.Definicion).sort((a,b)=>b[1]-a[1]).slice(0,10);
                draw(ids.defChart, 'bar', tDef.map(x=>x[0]), tDef.map(x=>x[1]), colorBarra);
                dibujarTabla(ids.defTable, dataObj.Definicion, 'Causa');

                if (tServ.length > 0) {
                    const topAreaName = tServ[0][0];
                    if(document.getElementById(ids.lbl)) document.getElementById(ids.lbl).textContent = topAreaName;
                    
                    let drillCounts = {};
                    rows.forEach(r => {
                        const clean = (txt) => String(txt).replace(/<[^>]*>?/gm, '').replace(/Ver más|Ver menos/g, '').trim() || 'NULO';
                        let evUpper = clean(r[COL.EVENTO]).toUpperCase();
                        let sv = clean(r[COL.SERVICIO]);
                        let coincide = (tipo === 'Adverso' && evUpper.includes('ADVERSO')) || (tipo === 'Cuasi' && evUpper.includes('CUASI'));
                        if (coincide && sv === topAreaName) { drillCounts[clean(r[COL.DEFINICION])] = (drillCounts[clean(r[COL.DEFINICION])] || 0) + 1; }
                    });
                    
                    const tDrill = Object.entries(drillCounts).sort((a,b)=>b[1]-a[1]).slice(0,10);
                    draw(ids.drillChart, 'bar', tDrill.map(x=>x[0]), tDrill.map(x=>x[1]), '#212529');
                    dibujarTabla(ids.drillTable, drillCounts, 'Causa Específica');
                } else {
                    if(document.getElementById(ids.lbl)) document.getElementById(ids.lbl).textContent = "Sin Datos";
                    dibujarTabla(ids.drillTable, {}, 'Sin Datos');
                }
            };

            generarDrillDown('Adverso', '#dc3545', {
                servChart: 'chartServicioAdv', servTable: 'tablaServicioAdv',
                drillChart: 'chartDrillDownAdv', drillTable: 'tablaDrillDownAdv',
                defChart: 'chartDefinicionAdv', defTable: 'tablaDefinicionAdv',
                lbl: 'lblTopAreaAdv'
            });

            generarDrillDown('Cuasi', '#ffc107', {
                servChart: 'chartServicioCuasi', servTable: 'tablaServicioCuasi',
                drillChart: 'chartDrillDownCuasi', drillTable: 'tablaDrillDownCuasi',
                defChart: 'chartDefinicionCuasi', defTable: 'tablaDefinicionCuasi',
                lbl: 'lblTopAreaCuasi'
            });

            // 3. CENTINELAS
            const tCentS = Object.entries(s.Centinela.Servicio).sort((a,b)=>b[1]-a[1]).slice(0,10);
            const tCentD = Object.entries(s.Centinela.Definicion).sort((a,b)=>b[1]-a[1]).slice(0,10);
            draw('chartServicioCent', 'bar', tCentS.map(x=>x[0]), tCentS.map(x=>x[1]), '#212529');
            dibujarTabla('tablaServicioCent', s.Centinela.Servicio, 'Servicio');

            draw('chartDefinicionCent', 'bar', tCentD.map(x=>x[0]), tCentD.map(x=>x[1]), '#212529');
            dibujarTabla('tablaDefinicionCent', s.Centinela.Definicion, 'Causa');

        }, 200);
    }

    // --- E. EXCEL MASTER ---
    $(document).on('click', '#btnDescargarExcelStats', async function() {
        const btn = this; const originalText = btn.innerHTML;
        btn.innerHTML = "⏳ Generando..."; btn.disabled = true;

        try {
            const rows = tabla.rows({ search: 'applied' }).data().toArray();
            if (rows.length === 0) throw new Error("No hay datos");

            let s = { General: { Sexo:{}, Evento:{}, Turno:{}, Servicio:{}, Edad:{} }, Adverso:{Servicio:{}, Definicion:{}}, Cuasi:{Servicio:{}, Definicion:{}}, Centinela:{Servicio:{}, Definicion:{}} };
            rows.forEach(r => {
                const clean = (txt) => String(txt).replace(/<[^>]*>?/gm, '').replace(/Ver más|Ver menos/g, '').trim() || 'NULO';
                let ev = clean(r[COL.EVENTO]), ed = clean(r[COL.EDAD]), sx = clean(r[COL.SEXO]), tu = clean(r[COL.TURNO]), sv = clean(r[COL.SERVICIO]), def = clean(r[COL.DEFINICION]);

                s.General.Sexo[sx] = (s.General.Sexo[sx] || 0) + 1; s.General.Evento[ev] = (s.General.Evento[ev] || 0) + 1;
                s.General.Turno[tu] = (s.General.Turno[tu] || 0) + 1; s.General.Servicio[sv] = (s.General.Servicio[sv] || 0) + 1;
                if (!s.General.Edad[ed]) s.General.Edad[ed] = { H: 0, M: 0 };
                (sx.toUpperCase().includes('MASC') || sx.toUpperCase().includes('HOMBRE')) ? s.General.Edad[ed].H++ : s.General.Edad[ed].M++;

                if(ev.toUpperCase().includes('ADVERSO')) { s.Adverso.Servicio[sv]=(s.Adverso.Servicio[sv]||0)+1; s.Adverso.Definicion[def]=(s.Adverso.Definicion[def]||0)+1; }
                else if(ev.toUpperCase().includes('CUASI')) { s.Cuasi.Servicio[sv]=(s.Cuasi.Servicio[sv]||0)+1; s.Cuasi.Definicion[def]=(s.Cuasi.Definicion[def]||0)+1; }
                else if(ev.toUpperCase().includes('CENTINELA')) { s.Centinela.Servicio[sv]=(s.Centinela.Servicio[sv]||0)+1; s.Centinela.Definicion[def]=(s.Centinela.Definicion[def]||0)+1; }
            });

            const workbook = new ExcelJS.Workbook();
            const generarImagenFantasma = async (type, labels, data, color, title) => {
                const canvas = document.createElement('canvas'); canvas.width = 800; canvas.height = 400;
                const ctx = canvas.getContext('2d'); ctx.fillStyle = '#ffffff'; ctx.fillRect(0, 0, canvas.width, canvas.height);
                const tempChart = new Chart(ctx, { type: type, data: { labels: labels, datasets: [{ label: 'Total', data: data, backgroundColor: color }] }, options: { responsive: false, animation: false, indexAxis: (type==='pie'?'x':'y'), plugins: { title: { display: true, text: title, font: {size: 18} }, legend: {display: (type==='pie')} } } });
                const b64 = canvas.toDataURL('image/png'); tempChart.destroy(); return b64;
            };

            const addSection = async (sheet, title, dataObj, chartConfig, startRow) => {
                sheet.getCell(`A${startRow}`).value = title; sheet.getCell(`A${startRow}`).font = { size: 14, bold: true, color: { argb: 'FF7A123A' } };
                sheet.getCell(`A${startRow+1}`).value = "Concepto"; sheet.getCell(`B${startRow+1}`).value = "Total";
                sheet.getCell(`A${startRow+1}`).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF333333' } }; sheet.getCell(`A${startRow+1}`).font = { color: { argb: 'FFFFFFFF' } };
                sheet.getCell(`B${startRow+1}`).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF333333' } }; sheet.getCell(`B${startRow+1}`).font = { color: { argb: 'FFFFFFFF' } };
                let currentRow = startRow + 2;
                let entries = Object.entries(dataObj).sort((a,b) => (typeof b[1]==='number' ? b[1]-a[1] : 0));
                let gLabels = [], gData = [];
                entries.forEach(([k, v]) => {
                    let val = (typeof v === 'object') ? (v.H + v.M) : v;
                    sheet.getCell(`A${currentRow}`).value = k; sheet.getCell(`B${currentRow}`).value = val; currentRow++;
                    if (gLabels.length < 10) { gLabels.push(k); gData.push(val); }
                });
                if (gLabels.length > 0 && chartConfig) {
                    try {
                        const imgB64 = await generarImagenFantasma(chartConfig.type, gLabels, gData, chartConfig.color, title);
                        const imgId = workbook.addImage({ base64: imgB64, extension: 'png' });
                        sheet.addImage(imgId, { tl: { col: 3, row: startRow }, ext: { width: 500, height: 300 } });
                    } catch(e) {}
                }
                return Math.max(currentRow, startRow + 16) + 2;
            };

            const wsGen = workbook.addWorksheet('Generales'); wsGen.getColumn(1).width = 40;
            let r = 1;
            r = await addSection(wsGen, "SEXO", s.General.Sexo, {type:'pie', color:['#0d6efd','#dc3545','#ffc107']}, r);
            r = await addSection(wsGen, "TIPO EVENTO", s.General.Evento, {type:'bar', color:'#7a123a'}, r);
            r = await addSection(wsGen, "EDAD", s.General.Edad, {type:'bar', color:'#0d6efd'}, r);
            r = await addSection(wsGen, "TURNOS", s.General.Turno, {type:'bar', color:'#198754'}, r);
            r = await addSection(wsGen, "TOP SERVICIOS", s.General.Servicio, {type:'bar', color:'#0288d1'}, r);

            const addTabSheet = async (name, data, color, typeFilter) => {
                const ws = workbook.addWorksheet(name); ws.getColumn(1).width = 45;
                let rx = 1;
                rx = await addSection(ws, `TOP ÁREAS (${name})`, data.Servicio, {type:'bar', color: color}, rx);
                
                const sortedServ = Object.entries(data.Servicio).sort((a,b)=>b[1]-a[1]);
                if (sortedServ.length > 0 && typeFilter) {
                    const topArea = sortedServ[0][0];
                    let drillCounts = {};
                    rows.forEach(row => {
                         const clean = (txt) => String(txt).replace(/<[^>]*>?/gm, '').replace(/Ver más|Ver menos/g, '').trim() || 'NULO';
                         let evUpper = clean(row[COL.EVENTO]).toUpperCase();
                         let sv = clean(row[COL.SERVICIO]);
                         let coincide = (name === 'Adversos' && evUpper.includes('ADVERSO')) || (name === 'Cuasifallas' && evUpper.includes('CUASI'));
                         if(coincide && sv === topArea) { drillCounts[clean(row[COL.DEFINICION])] = (drillCounts[clean(row[COL.DEFINICION])] || 0) + 1; }
                    });
                    rx = await addSection(ws, `ANÁLISIS ÁREA CRÍTICA: ${topArea}`, drillCounts, {type:'bar', color: '#212529'}, rx);
                }
                rx = await addSection(ws, `CAUSAS GLOBALES (${name})`, data.Definicion, {type:'bar', color: color}, rx);
            }

            await addTabSheet('Adversos', s.Adverso, '#dc3545', 'Adverso');
            await addTabSheet('Cuasifallas', s.Cuasi, '#ffc107', 'Cuasi');
            await addTabSheet('Centinelas', s.Centinela, '#212529', null);

            const buffer = await workbook.xlsx.writeBuffer();
            saveAs(new Blob([buffer]), 'Reporte_VENCER_Final.xlsx');

        } catch (err) { console.error(err); alert("Error generando Excel."); } 
        finally { btn.innerHTML = originalText; btn.disabled = false; }
    });

    $('#modalGraficos').on('shown.bs.modal', generarGraficos);
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', generarGraficos);
    $(document).on('click', '.toggle-text', function() { $(this).parent().find('.text-full, .text-short').toggleClass('d-none'); });
});
</script>

</body>

</html>
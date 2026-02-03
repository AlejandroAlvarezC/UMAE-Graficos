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
                <h5 class="modal-title" id="modalGraficosLabel">📊 Análisis Estadístico VENCER</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body" style="background-color: #f8f9fa;">

                  <div class="row mb-3 justify-content-center">
                      <div class="col-md-auto d-flex align-items-center gap-2">
                          <!--<label for="filtroAnio" class="form-label fw-bold m-0">📅 Año:</label> -->
                          
                          <select class="form-select form-select-sm d-inline-block w-auto" id="filtroAnio">
                              <option value="todos">Cargando...</option>
                          </select>

                          <span class="badge rounded-pill bg-dark border border-light shadow-sm py-2 px-3">
                              Total de Eventos: <span id="lblTotalEventos" class="fw-bold text-warning" style="font-size: 1rem;">0</span>
                          </span>
                      </div>
                  </div>

                <ul class="nav nav-tabs nav-fill mb-3" id="graficosTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="general-tab" data-bs-toggle="tab" data-bs-target="#tab-general" type="button" role="tab" aria-selected="true">
                            📊 Generales
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-warning" id="cuasi-tab" data-bs-toggle="tab" data-bs-target="#tab-cuasi" type="button" role="tab" aria-selected="false">
                            ⚠️ Cuasifallas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-danger" id="adverso-tab" data-bs-toggle="tab" data-bs-target="#tab-adverso" type="button" role="tab" aria-selected="false">
                            🚨 Adversos y Centinelas
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="graficosTabContent">

                  <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
    
                      <div class="row g-4 mb-4">
                          <div class="col-md-6">
                              <div class="card border-0 shadow-sm h-100">
                                  <div class="card-body">
                                      <h6 class="text-center fw-bold">Distribución Global por Sexo</h6>
                                      <div style="height: 300px;"><canvas id="chartSexoGen"></canvas></div>
                                      <div id="tablaSexoGen" class="mt-3 table-responsive" style="max-height: 200px;"></div>
                                  </div>
                              </div>
                          </div>
                          <div class="col-md-6">
                              <div class="card border-0 shadow-sm h-100">
                                  <div class="card-body">
                                      <h6 class="text-center fw-bold">Conteo Total por Tipo de Evento</h6>
                                      <div style="height: 300px;"><canvas id="chartEventosGen"></canvas></div>
                                      <div id="tablaEventosGen" class="mt-3 table-responsive" style="max-height: 200px;"></div>
                                  </div>
                              </div>
                          </div>
                      </div>

                      <div class="row g-4">
                          <div class="col-md-6">
                              <div class="card border-0 shadow-sm h-100">
                                  <div class="card-body">
                                      <h6 class="text-center fw-bold">Edad Agrupada por Sexo</h6>
                                      <div style="height: 300px;"><canvas id="chartEdadSexoGen"></canvas></div>
                                      <div id="tablaEdadSexoGen" class="mt-3 table-responsive" style="max-height: 200px;"></div>
                                  </div>
                              </div>
                          </div>
                          <div class="col-md-6">
                              <div class="card border-0 shadow-sm h-100">
                                  <div class="card-body">
                                      <h6 class="text-center fw-bold" style="color: #198754;">Eventos por Turno</h6>
                                      <div style="height: 300px;"><canvas id="chartTurnoGen"></canvas></div>
                                      <div id="tablaTurnoGen" class="mt-3 table-responsive" style="max-height: 200px;"></div>
                                  </div>
                              </div>
                          </div>
                      </div>
                        <div class="row g-4 mt-1">
                                  <div class="col-12">
                                      <div class="card border-0 shadow-sm h-100">
                                          <div class="card-body">
                                              <h6 class="text-center fw-bold text-primary">Top Áreas con Mayor Incidencia</h6>
                                              <div style="height: 400px;">
                                                  <canvas id="chartTopServiciosGen"></canvas>
                                              </div>
                                              <div id="tablaTopServiciosGen" class="mt-3 table-responsive" style="max-height: 250px;"></div>
                                          </div>
                                      </div>
                                  </div>
                        </div>
                  </div>

                    <div class="tab-pane fade" id="tab-cuasi" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-warning">Sexo en Cuasifallas</h6>
                                        <div style="height: 300px;"><canvas id="chartSexoCuasi"></canvas></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-warning">Cuasifallas por Servicio</h6>
                                        <div style="height: 300px;"><canvas id="chartServicioCuasi"></canvas></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-adverso" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-danger">Sexo en Eventos Adversos</h6>
                                        <div style="height: 300px;"><canvas id="chartSexoAdv"></canvas></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-danger">Eventos Adversos por Servicio</h6>
                                        <div style="height: 300px;"><canvas id="chartServicioAdv"></canvas></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div> <div class="row mt-4 pt-3 border-top">
                    <div class="col-12 d-flex justify-content-center align-items-center gap-3 flex-wrap">
                        
                        <button onclick="exportarCanvasConFondo('chartSexoGen', 'Grafico_Sexo.png')" 
                                class="btn btn-outline-primary d-flex align-items-center gap-2 shadow-sm">
                            <i class="fas fa-image"></i> Imagen Sexo
                        </button>
                        
                        <button onclick="exportarCanvasConFondo('chartEventosGen', 'Grafico_Eventos.png')" 
                                class="btn btn-outline-primary d-flex align-items-center gap-2 shadow-sm">
                            <i class="fas fa-image"></i> Imagen Eventos
                        </button>

                        <button id="btnDescargarPDF" 
                                class="btn btn-danger d-flex align-items-center gap-2 shadow-sm">
                            <i class="fas fa-file-pdf"></i> Descargar Reporte PDF
                        </button>

                    </div>
                </div>

            </div> <div class="modal-footer"></div>
        </div>
    </div>
</div>
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

        <div style="display: none;">
            <button id="btnDescargarGraficoPDF"></button>
            <canvas id="graficoUrgencias"></canvas>
            <canvas id="graficoMensualDivision"></canvas>
            <canvas id="graficoTotales"></canvas>
        </div>


  <footer>
    <p>Derechos reservados &copy; IMSS 2025</p>
  </footer>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("🚀 Script VENCER iniciado...");

    // --- A. TABLA Y FILTROS PRINCIPALES ---
    const tabla = $('#tabla-vencer').DataTable({
        pageLength: 10,
        order: [[0, 'asc']],
        language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
    });

    // Toggle para textos largos
    $(document).on('click', '.toggle-text', function() {
        const full = $(this).parent().find('.text-full');
        const short = $(this).parent().find('.text-short');
        if (full.hasClass('d-none')) {
            full.removeClass('d-none'); short.addClass('d-none'); $(this).text('Ver menos');
        } else {
            full.addClass('d-none'); short.removeClass('d-none'); $(this).text('Ver más');
        }
    });

    // FUNCIÓN PARA RELLENAR LOS SELECTS DE FILTROS (Restaurado)
    function llenarFiltros() {
        const headers = ['Folio', 'Evento', 'Iniciales', 'NSS', 'Edad', 'Sexo', 'Diagnostico', 'FechaEvento', 'FechaNotificacion', 'Turno', 'Servicio', 'Categoria', 'Proceso', 'Definicion', 'Descripcion', 'Estatus', 'Anio'];
        
        headers.forEach((h, i) => {
            const select = $(`#filter${h}`);
            if(!select.length) return;
            
            const values = new Set();
            // Obtener valores únicos de la columna i
            tabla.column(i).nodes().to$().each(function() {
                let v = $(this).text().trim() || 'NULO';
                // Limpiar basura de espacios o tabs
                v = v.replace(/[\r\n\t]+/g, ' ').replace(/\s\s+/g, ' ').trim();
                values.add(v);
            });

            select.empty().append(new Option('NULO', 'NULO'));
            Array.from(values).sort().forEach(v => {
                if(v !== 'NULO') select.append(new Option(v, v));
            });
        });
    }

    $('.select-personalizado').select2({ placeholder: 'Selecciona opciones', allowClear: true, width: '100%' });

    // MOTOR DE BÚSQUEDA PERSONALIZADO (Restaurado)
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'tabla-vencer') return true;

        const headers = ['Folio', 'Evento', 'Iniciales', 'NSS', 'Edad', 'Sexo', 'Diagnostico', 'FechaEvento', 'FechaNotificacion', 'Turno', 'Servicio', 'Categoria', 'Proceso', 'Definicion', 'Descripcion', 'Estatus', 'Anio'];
        
        for (let i = 0; i < headers.length; i++) {
            const selectId = `#filter${headers[i]}`;
            const selectedVals = $(selectId).val();
            
            if (selectedVals && selectedVals.length > 0) {
                if (selectedVals.length === 1 && selectedVals[0] === 'NULO') continue;
                
                let cellText = data[i] || 'NULO';
                cellText = cellText.replace(/[\r\n\t]+/g, ' ').replace(/\s\s+/g, ' ').trim();
                
                if (!selectedVals.includes(cellText)) return false;
            }
        }
        return true;
    });

    $('.select-personalizado').on('change', function() {
        tabla.draw();
        generarGraficos(); // Sincroniza gráficos al filtrar
    });

    // --- B. LÓGICA DE GRÁFICOS ---
    var instanceCharts = {};

    function generarGraficos() {
        const datosFiltrados = tabla.rows({ filter: 'applied' }).data().toArray();
        
        // Actualizar etiqueta de Conteo Total
        const lblTotal = document.getElementById('lblTotalEventos');
        if(lblTotal) lblTotal.textContent = datosFiltrados.length;

        if (datosFiltrados.length === 0) return;

        let stats = { sexo: {}, evento: {}, turno: {}, servicio: {}, edad: {} };

        datosFiltrados.forEach(row => {
            const clean = (val) => String(val).replace(/<[^>]*>?/gm, '').trim() || 'NULO';

            let ev = clean(row[1]);
            let ed = clean(row[4]);
            let sx = clean(row[5]);
            let tu = clean(row[9]);
            let sv = clean(row[10]);

            stats.sexo[sx] = (stats.sexo[sx] || 0) + 1;
            stats.evento[ev] = (stats.evento[ev] || 0) + 1;
            stats.turno[tu] = (stats.turno[tu] || 0) + 1;
            stats.servicio[sv] = (stats.servicio[sv] || 0) + 1;
            
            if(!stats.edad[ed]) stats.edad[ed] = { M:0, F:0 };
            if(sx.toUpperCase().includes('MASC')) stats.edad[ed].M++;
            else stats.edad[ed].F++;
        });

        const draw = (id, type, labels, datasets, opts = {}) => {
            const canvas = document.getElementById(id);
            if (!canvas) return;
            if (instanceCharts[id]) instanceCharts[id].destroy();
            const ctx = canvas.getContext('2d', { willReadFrequently: true });
            instanceCharts[id] = new Chart(ctx, {
                type, data: { labels, datasets },
                options: { responsive: true, maintainAspectRatio: false, animation: false, ...opts }
            });
        };

        draw('chartSexoGen', 'pie', Object.keys(stats.sexo), [{ data: Object.values(stats.sexo), backgroundColor: ['#0d6efd', '#dc3545', '#ffc107', '#6c757d'] }]);
        draw('chartEventosGen', 'bar', Object.keys(stats.evento), [{ label: 'Total', data: Object.values(stats.evento), backgroundColor: '#7a123a' }]);
        draw('chartTurnoGen', 'bar', Object.keys(stats.turno), [{ label: 'Turno', data: Object.values(stats.turno), backgroundColor: '#198754' }], { indexAxis: 'y' });
        const edL = Object.keys(stats.edad).sort();
        draw('chartEdadSexoGen', 'bar', edL, [
            { label: 'Hombres', data: edL.map(e => stats.edad[e].M), backgroundColor: '#0d6efd' },
            { label: 'Mujeres', data: edL.map(e => stats.edad[e].F), backgroundColor: '#dc3545' }
        ], { scales: { x: { stacked: true }, y: { stacked: true } } });
        const topS = Object.entries(stats.servicio).sort((a,b) => b[1]-a[1]).slice(0,10);
        draw('chartTopServiciosGen', 'bar', topS.map(s => s[0]), [{ label: 'Eventos', data: topS.map(s => s[1]), backgroundColor: '#0288d1' }], { indexAxis: 'y' });

        actualizarTablas(stats);
    }

    function actualizarTablas(stats) {
        const renderTable = (id, data, title) => {
            const container = document.getElementById(id);
            if (!container) return;
            let total = Object.values(data).reduce((a, b) => (typeof b === 'number' ? a + b : a + b.M + b.F), 0);
            let html = `<table class="table table-sm table-bordered text-center mb-0" style="font-size:0.75rem;"><thead class="table-dark"><tr><th>${title}</th><th>Total</th><th>%</th></tr></thead><tbody>`;
            Object.entries(data).sort((a,b) => (typeof b[1] === 'number' ? b[1]-a[1] : 0)).forEach(([k, v]) => {
                let val = typeof v === 'number' ? v : (v.M + v.F);
                let pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                html += `<tr><td class="text-start">${k}</td><td class="fw-bold">${val}</td><td>${pct}%</td></tr>`;
            });
            container.innerHTML = html + `</tbody></table>`;
        };
        renderTable('tablaSexoGen', stats.sexo, 'Sexo');
        renderTable('tablaEventosGen', stats.evento, 'Evento');
        renderTable('tablaTurnoGen', stats.turno, 'Turno');
        renderTable('tablaTopServiciosGen', stats.servicio, 'Área');
    }

    // --- C. GENERACIÓN DE PDF ---
    const btnPDF = document.getElementById('btnDescargarPDF');
    if (btnPDF) {
        btnPDF.addEventListener('click', async function() {
            const originalBtnText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = "Procesando...";
            const modalBody = document.querySelector('#modalGraficos .modal-body');
            const canvases = modalBody.querySelectorAll('canvas');
            const tempImgs = [];

            canvases.forEach(cvs => {
                const img = document.createElement('img');
                img.src = cvs.toDataURL('image/png');
                img.style.width = '100%';
                img.style.height = cvs.offsetHeight + 'px';
                cvs.style.display = 'none';
                cvs.parentNode.insertBefore(img, cvs);
                tempImgs.push({ cvs, img });
            });

            const opt = {
                margin: 0.2,
                filename: 'Reporte_Estadistico_Vencer.pdf',
                image: { type: 'png' },
                html2canvas: { scale: 2, logging: false, useCORS: true },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'landscape' }
            };

            try {
                await html2pdf().set(opt).from(modalBody).save();
            } catch (err) {
                console.error("❌ Error en PDF:", err);
            }

            tempImgs.forEach(item => {
                item.img.remove();
                item.cvs.style.display = 'block';
            });
            this.disabled = false;
            this.innerHTML = originalBtnText;
        });
    }

    // --- D. LOGICA DE EXCEL (Restaurado) ---
    document.querySelector('#formDescargarExcelVencer').addEventListener('submit', function(e) {
        const headers = ['Folio', 'Evento', 'Iniciales', 'NSS', 'Edad', 'Sexo', 'Diagnostico', 'FechaEvento', 'FechaNotificacion', 'Turno', 'Servicio', 'Categoria', 'Proceso', 'Definicion', 'Descripcion', 'Estatus', 'Anio'];
        headers.forEach(h => {
            const select = document.getElementById(`filter${h}`);
            const input = document.getElementById(`input${h}`);
            if(select && input) {
                input.value = Array.from(select.selectedOptions).map(o => o.value).join(',');
            }
        });
    });

    // --- E. INICIO ---
    const filterAnioModal = document.getElementById('filtroAnio');
    if(filterAnioModal) {
        const anios = [...new Set(tabla.column(16).data().toArray())].filter(x => x && x!=='NULO').sort().reverse();
        filterAnioModal.innerHTML = '<option value="todos">Todos los años</option>';
        anios.forEach(a => filterAnioModal.append(new Option(a, a)));
        
        filterAnioModal.addEventListener('change', function() {
            tabla.column(16).search(this.value === 'todos' ? '' : this.value).draw();
        });
    }

    llenarFiltros();
    $('#filtrosContainer').show();

    $('#modalGraficos').on('shown.bs.modal', function() {
        setTimeout(generarGraficos, 200);
    });

    if(!document.getElementById('btnDescargarGraficoPDF')) {
        const b = document.createElement('button'); b.id='btnDescargarGraficoPDF'; b.style.display='none'; document.body.appendChild(b);
    }
});
</script>

</body>

</html>
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

            <!--<a id="btnDescargarGraficoPDF" class="btn btn-success">📈 Descargar PDF</a>
                    <a id="btnDescargarImagenesCanvasUrgencias" class="btn btn-success">🖼️ Descargar Imagen</a>-->
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
              <!-- -->    

<!-- -->
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" style="background-color: #f8f9fa;">
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="card border-0 shadow-sm">
                    <div class="card-body">
                      <h6 class="text-center fw-bold">Distribución por Sexo</h6>
<!-- Filtros por año -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                              <label for="filtroAnio" class="form-label fw-bold">📅 Seleccionar Año:</label>
                              <select class="form-select form-select-sm" id="filtroAnio" name="anio" onchange="cambiarAnio(this.value)">
                                  <option value="todos">Todos los años</option>
                              </select>
                            </div>
                        </div> 
 <!-- -->
                      <div style="height: 300px;">
                        <canvas id="chartSexo"></canvas>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="card border-0 shadow-sm">
                    <div class="card-body">
                      <h6 class="text-center fw-bold">Tipos de Eventos Registrados</h6>
                      <div style="height: 300px;">
                        <canvas id="chartEventos"></canvas>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
                <button onclick="descargarImagen('chartSexo', 'Distribucion_Sexo')" class="btn btn-sm btn-outline-primary">
                  🖼️ Imagen
                </button>

                <button onclick="descargarImagen('chartEventos', 'Tipos_Eventos')" class="btn btn-sm btn-outline-primary">
                  🖼️ Imagen
                </button>

                <button onclick="generarReportePDF()" class="btn btn-danger">
                  📄 Descargar Reporte Completo (PDF)
                </button>
        <div class="modal-footer">
             
            </div>
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


  <footer>
    <p>Derechos reservados &copy; IMSS 2025</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
  <script>
    $(document).ready(function() {
      const tabla = $('#tabla-vencer').DataTable({
        pageLength: 10,
        order: [
          [0, 'asc']
        ],
        language: {
          url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        }
      });

      function limpiarHTML(html) {
        return $('<div>').html(html).text().trim();
      }

      function getUniqueColumnValues(colIndex) {
        const nodes = tabla.column(colIndex).nodes();
        const values = new Set();

        $(nodes).each(function() {
          let valor = '';

          if ($(this).find('span.text-short').length) {
            valor = $(this).find('span.text-short').text().trim();
          } else {
            valor = $(this).text().trim();
          }

          // Reemplaza saltos de línea, tabs, múltiples espacios por espacio simple
          valor = valor.replace(/[\r\n\t]+/g, ' ').replace(/\s\s+/g, ' ').trim();

          if (valor === '' || valor === '-') valor = 'NULO';

          values.add(valor);
        });

        return Array.from(values).sort((a, b) => {
          if (a === 'NULO') return -1;
          if (b === 'NULO') return 1;
          return a.localeCompare(b);
        });
      }


      function llenarFiltros() {
        for (let col = 0; col <= 16; col++) {
          const valores = getUniqueColumnValues(col);
          console.log(`Columna ${col} (${tabla.column(col).header().textContent}):`, valores); // << Aquí

          const colName = tabla.column(col).header().textContent.replace(/\s+/g, '');
          const select = $(`#filter${colName}`);
          select.empty();

          select.append(new Option('NULO', 'NULO')); // Agrega NULO primero

          valores
            .filter(v => v !== 'NULO') // Evita duplicarlo si ya está en valores
            .forEach(v => {
              select.append(new Option(v, v));
            });

        }
      }


      $('.select-personalizado').select2({
        placeholder: 'Selecciona opciones',
        allowClear: true,
        width: '100%'
      });

      $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'tabla-vencer') return true;

        const filtros = [{
            id: 'filterFolio',
            col: 0
          },
          {
            id: 'filterEvento',
            col: 1
          },
          {
            id: 'filterIniciales',
            col: 2
          },
          {
            id: 'filterNSS',
            col: 3
          },
          {
            id: 'filterEdad',
            col: 4
          },
          {
            id: 'filterSexo',
            col: 5
          },
          {
            id: 'filterDiagnostico',
            col: 6
          },
          {
            id: 'filterFechaEvento',
            col: 7
          },
          {
            id: 'filterFechaNotificacion',
            col: 8
          },
          {
            id: 'filterTurno',
            col: 9
          },
          {
            id: 'filterServicio',
            col: 10
          },
          {
            id: 'filterCategoria',
            col: 11
          },
          {
            id: 'filterProceso',
            col: 12
          },
          {
            id: 'filterDefinicion',
            col: 13
          },
          {
            id: 'filterDescripcion',
            col: 14
          },
          {
            id: 'filterEstatus',
            col: 15
          },
          {
            id: 'filterAnio',
            col: 16
          }
        ];

        for (const filtro of filtros) {
          const valores = $(`#${filtro.id}`).val();
          const valorCelda = tabla.cell(dataIndex, filtro.col).node();
          const textoPlano = $(valorCelda).text().trim() || 'NULO';

          if (valores && valores.length > 0) {
            if (valores.length === 1 && valores[0] === 'NULO') continue;
            if (!valores.includes(textoPlano)) return false;
          }
        }

        return true;
      });

      function actualizarColumnas() {
        const filtros = [{
            id: 'filterFolio',
            col: 0
          },
          {
            id: 'filterEvento',
            col: 1
          },
          {
            id: 'filterIniciales',
            col: 2
          },
          {
            id: 'filterNSS',
            col: 3
          },
          {
            id: 'filterEdad',
            col: 4
          },
          {
            id: 'filterSexo',
            col: 5
          },
          {
            id: 'filterDiagnostico',
            col: 6
          },
          {
            id: 'filterFechaEvento',
            col: 7
          },
          {
            id: 'filterFechaNotificacion',
            col: 8
          },
          {
            id: 'filterTurno',
            col: 9
          },
          {
            id: 'filterServicio',
            col: 10
          },
          {
            id: 'filterCategoria',
            col: 11
          },
          {
            id: 'filterProceso',
            col: 12
          },
          {
            id: 'filterDefinicion',
            col: 13
          },
          {
            id: 'filterDescripcion',
            col: 14
          },
          {
            id: 'filterEstatus',
            col: 15
          },
          {
            id: 'filterAnio',
            col: 16
          }
        ];

        filtros.forEach(filtro => {
          const valores = $(`#${filtro.id}`).val() || [];
          if (valores.length === 1 && valores[0] === 'NULO') {
            tabla.column(filtro.col).visible(false);
          } else {
            tabla.column(filtro.col).visible(true);
          }
        });
      }

      $('.select-personalizado').on('change', function() {
        actualizarColumnas();
        tabla.draw();
      });

      llenarFiltros();
      $('#filtrosContainer').show();
      actualizarColumnas();
    });
  </script>

  <script>
    document.querySelector('#formDescargarExcelVencer').addEventListener('submit', function(e) {
      function valoresSeleccionados(idSelect) {
        const select = document.getElementById(idSelect);
        if (!select) return '';
        return Array.from(select.selectedOptions).map(o => o.value).join(',');
      }

      document.getElementById('inputFolio').value = valoresSeleccionados('filterFolio');
      document.getElementById('inputEvento').value = valoresSeleccionados('filterEvento');
      document.getElementById('inputIniciales').value = valoresSeleccionados('filterIniciales');
      document.getElementById('inputNSS').value = valoresSeleccionados('filterNSS');
      document.getElementById('inputEdad').value = valoresSeleccionados('filterEdad');
      document.getElementById('inputSexo').value = valoresSeleccionados('filterSexo');
      document.getElementById('inputDiagnostico').value = valoresSeleccionados('filterDiagnostico');
      document.getElementById('inputFechaEvento').value = valoresSeleccionados('filterFechaEvento');
      document.getElementById('inputFechaNotificacion').value = valoresSeleccionados('filterFechaNotificacion');
      document.getElementById('inputTurno').value = valoresSeleccionados('filterTurno');
      document.getElementById('inputServicio').value = valoresSeleccionados('filterServicio');
      document.getElementById('inputCategoria').value = valoresSeleccionados('filterCategoria');
      document.getElementById('inputProceso').value = valoresSeleccionados('filterProceso');
      document.getElementById('inputDefinicion').value = valoresSeleccionados('filterDefinicion');
      document.getElementById('inputDescripcion').value = valoresSeleccionados('filterDescripcion');
      document.getElementById('inputEstatus').value = valoresSeleccionados('filterEstatus');
      document.getElementById('inputAnio').value = valoresSeleccionados('filterAnio');
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

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.toggle-text').forEach(button => {
        button.addEventListener('click', function() {
          const td = this.closest('td');
          const short = td.querySelector('.text-short');
          const full = td.querySelector('.text-full');
          const isHidden = full.classList.contains('d-none');

          if (isHidden) {
            short.classList.add('d-none');
            full.classList.remove('d-none');
            this.textContent = 'Ver menos';
          } else {
            short.classList.remove('d-none');
            full.classList.add('d-none');
            this.textContent = 'Ver más';
          }
        });
      });
    });
  </script>




 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
    var datosVencer = <?php echo json_encode($registros, JSON_UNESCAPED_UNICODE); ?>;
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
    var datosVencer = <?php echo json_encode($registros, JSON_UNESCAPED_UNICODE); ?>;
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // VARIABLES GLOBALES
    var chartSexo = null;
    var chartEventos = null;
    
    // VARIABLES DE COLUMNAS (Inicializan en null para saber si las encontramos)
    var colSexo = null;
    var colEvento = null;
    var colAnio = null;

    // --- 1. DETECTAR COLUMNAS (PRIORIDAD: NOMBRE) ---
    function detectarColumnas() {
        if (!datosVencer || datosVencer.length === 0) return;

        let fila = datosVencer[0]; // Usamos la primera fila como mapa

        console.log("🕵️ Buscando columnas en:", fila);

        // A. BUSQUEDA POR NOMBRE EXACTO (Lo que tú me dijiste)
        for (let key in fila) {
            let nombreColumna = key.toLowerCase(); // Convertimos a minúsculas para comparar
            
            // Buscar AÑO
            if (nombreColumna === 'anio' || nombreColumna === 'año' || nombreColumna === 'year') {
                colAnio = key;
                console.log("✅ AÑO encontrado por nombre:", colAnio);
            }

            // Buscar SEXO
            if (nombreColumna === 'sexo' || nombreColumna === 'genero') {
                colSexo = key;
                console.log("✅ SEXO encontrado por nombre:", colSexo);
            }

            // Buscar EVENTO
            if (nombreColumna === 'evento' || nombreColumna === 'tipo') {
                colEvento = key;
                console.log("✅ EVENTO encontrado por nombre:", colEvento);
            }
        }

        // B. BUSQUEDA POR CONTENIDO (Plan B si los nombres no coinciden)
        // Solo buscamos si no encontramos alguna arriba
        if (!colSexo || !colAnio || !colEvento) {
            console.log("⚠️ Faltan columnas por nombre, escaneando contenido...");
            
            for (let key in fila) {
                let valor = String(fila[key]).toUpperCase().trim();

                // Si falta Sexo y el valor parece sexo...
                if (!colSexo && (valor === 'MASCULINO' || valor === 'FEMENINO' || valor === 'M' || valor === 'F')) {
                    colSexo = key;
                }
                // Si falta Evento y parece evento...
                if (!colEvento && (valor.includes('ADVERSO') || valor.includes('CUASI'))) {
                    colEvento = key;
                }
                // Si falta Año y parece año...
                if (!colAnio && valor.length === 4 && valor.startsWith('20')) {
                    colAnio = key;
                }
            }
        }

        // C. FALLBACK FINAL (Si todo falla, usamos los índices que vimos en tu consola anterior)
        if (!colSexo) colSexo = 5;      // Índice probable
        if (!colEvento) colEvento = 1;  // Índice probable
        if (!colAnio) colAnio = 'anio'; // Forzamos lo que dijiste
        
        console.log(`🎯 RESULTADO FINAL: Año=[${colAnio}], Sexo=[${colSexo}], Evento=[${colEvento}]`);
    }

    // --- 2. CARGAR SELECTOR ---
    function cargarAnios() {
        const selector = document.getElementById('filtroAnio');
        if (!selector) return;

        const anios = new Set();
        datosVencer.forEach(r => {
            // Usamos la columna detectada
            let val = r[colAnio]; 
            if (val) anios.add(String(val).trim());
        });

        selector.innerHTML = '<option value="todos">📅 Todos</option>';
        Array.from(anios).sort().reverse().forEach(a => {
            let op = document.createElement('option');
            op.value = a;
            op.textContent = a;
            selector.appendChild(op);
        });
    }

    // --- 3. DIBUJAR GRÁFICOS ---
    function actualizarGraficos() {
        const selector = document.getElementById('filtroAnio');
        const anioSel = selector ? selector.value : 'todos';
        
        // FILTRAR
        const datos = datosVencer.filter(r => {
            if (anioSel === 'todos') return true;
            return String(r[colAnio]).trim() == anioSel;
        });

        // CONTAR
        let conteoSexo = { 'Hombres': 0, 'Mujeres': 0, 'NE': 0 };
        let mapaEventos = {};

        datos.forEach(r => {
            // SEXO (Limpieza profunda)
            let rawSexo = String(r[colSexo] || '').toUpperCase().trim();
            
            if (rawSexo === 'MASCULINO' || rawSexo === 'HOMBRE' || rawSexo === 'M' || rawSexo.includes('MASC')) {
                conteoSexo['Hombres']++;
            } 
            else if (rawSexo === 'FEMENINO' || rawSexo === 'MUJER' || rawSexo === 'F' || rawSexo.includes('FEM')) {
                conteoSexo['Mujeres']++;
            } 
            else {
                conteoSexo['NE']++;
            }

            // EVENTOS
            let rawEvento = r[colEvento];
            let etiqueta = rawEvento ? String(rawEvento).trim() : "Sin Dato";
            mapaEventos[etiqueta] = (mapaEventos[etiqueta] || 0) + 1;
        });

        // DIBUJAR SEXO
        const ctxS = document.getElementById('chartSexo');
        if (ctxS) {
            if (chartSexo) chartSexo.destroy();
            chartSexo = new Chart(ctxS.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: ['Hombres', 'Mujeres', 'N/E'],
                    datasets: [{
                        data: Object.values(conteoSexo),
                        backgroundColor: ['#0d6efd', '#dc3545', '#adb5bd']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // DIBUJAR EVENTOS
        const ctxE = document.getElementById('chartEventos');
        if (ctxE) {
            if (chartEventos) chartEventos.destroy();
            chartEventos = new Chart(ctxE.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: Object.keys(mapaEventos),
                    datasets: [{
                        label: 'Eventos',
                        data: Object.values(mapaEventos),
                        backgroundColor: '#7a123a'
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
    }

    // --- 4. PDF ---
    function generarPDF() {
        const elemento = document.getElementById('modalGraficos');
        const btnClose = elemento.querySelector('.btn-close');
        if(btnClose) btnClose.style.display = 'none'; // Ocultar X para la foto

        const opt = { 
            margin: 0.2, 
            filename: 'Reporte_Vencer.pdf', 
            image: { type: 'jpeg', quality: 0.98 }, 
            html2canvas: { scale: 2 }, 
            jsPDF: { unit: 'in', format: 'letter', orientation: 'landscape' } 
        };

        if(typeof html2pdf !== 'undefined') {
            html2pdf().set(opt).from(elemento).save().then(() => {
                 if(btnClose) btnClose.style.display = 'block'; // Mostrar X
            });
        }
    }

    // --- INICIALIZACIÓN ---
    detectarColumnas(); // <--- AQUÍ SE APLICA TU CORRECCIÓN
    cargarAnios();

    const filtro = document.getElementById('filtroAnio');
    if (filtro) filtro.addEventListener('change', actualizarGraficos);

    const modal = document.getElementById('modalGraficos');
    if (modal) {
        modal.addEventListener('shown.bs.modal', function() {
            setTimeout(actualizarGraficos, 200);
        });
    }

    const btnPDF = document.getElementById('btnDescargarPDF');
    if (btnPDF) btnPDF.addEventListener('click', generarPDF);
});
</script>

</body>

</html>
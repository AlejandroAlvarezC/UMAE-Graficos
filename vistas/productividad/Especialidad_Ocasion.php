<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['login_error'] = "Debes iniciar sesión como administrador para acceder a esa sección.";
    header('Location: ../admin/login.php');  // O la ruta correcta de tu login
    exit();
}

require_once '../../modelos/Especialidad_Ocasion.php';
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
    <!-- jQuery (necesario para DataTables) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<!-- DataTables base + Bootstrap 5 skin -->

<!-- CSS de Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- JS de Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <title>Especialidad de Ocasión | Administrador</title>

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
            background-color: rgba(74, 74, 83, 1);
            color: white;
            border: 1px solid rgba(32, 185, 129, 1);
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
            border: 1px solid #9ca3abff;
            background-color: white;
        }


   /* -----------------------------------
   FILTROS
----------------------------------- */

        /* === Buscador === */
.dataTables_filter input {
    border-radius: 12px;
    padding: 6px 12px;
    border: 1.5px solid #60746cff;
    background-color: #f8f9fa;
    color: #252c32ff;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
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

        .form-select,
        .select-personalizado {
            width: 100%;
            border-radius: 14px;
            padding: 10px 16px;
            font-size: 1rem;
            font-weight: 500;
            color: #1a1a1a;
            background-color: #ffffff;
            border: 1.5px solid #ccddd7ff;
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
            border: 1.5px solid #3c5a4eff;
            border-radius: 16px;
            padding: 28px 32px;
            box-shadow: 0 8px 20px rgba(68, 77, 94, 0.12);
            margin-bottom: 40px;
            transition: box-shadow 0.3s ease, transform 0.2s ease;
        }

        #graficoCard:hover {
            box-shadow: 0 12px 32px rgba(53, 63, 106, 0.18);
            transform: scale(1.01);
        }

        #graficoCard h4 {
            color: #414454ff;
            font-weight: 800;
            font-size: 1.4rem;
            border-bottom: 2px solid rgba(49, 67, 78, 0.3);
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
            box-shadow: 0 0 10px rgba(122, 18, 58, 0.5);
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

/* === Selector de cantidad y filtros === */
.dataTables_length select,
#filtroAnioTabla {
    min-width: 120px;
    padding: 6px 12px;
    padding-right: 2.5rem;
    border-radius: 12px;
    border: 1.5px solid #6c757d;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658c-.566-.647.106-1.658.808-1.658h9.482c.702 0 1.374 1.01.808 1.658l-4.796 5.482a1 1 0 0 1-1.516 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
}


#filtroAnioTabla {
    width: 250px;
    height: 45px;

}

/* === Paginación Bootstrap 5 sin azul === */
.dataTables_wrapper .dataTables_paginate .pagination .page-item .page-link {
    background-color: #f5f5f5 !important;
    color:rgb(36, 41, 33) !important;
    border: 1px solid #212529 !important;
    border-radius: 6px;
}

.dataTables_wrapper .dataTables_paginate .pagination .page-item.active .page-link {
    background-color:rgb(18, 122, 54) !important;
    color: #fff !important;
    border-color:rgb(18, 122, 72) !important;
}

.dataTables_wrapper .dataTables_paginate .pagination .page-item:hover .page-link {
    background-color:rgb(40, 66, 48) !important;
    color: #fff !important;
    border-color:rgb(32, 75, 48) !important;
}

/* === Botones personalizados === */
.btn-light {
     border-color:rgba(55, 86, 69, 1) !important;
    background-color:rgb(255, 255, 255);
    color: black;
}


/* === Contenedor de filtros === */


#selectorAnioContainer {
    max-width: 200px;
}

#selectorAnioContainer,
#selectorEspecialidadContainer,
#selectorDescripcionContainer {
    min-width: 180px;
    flex: 1 1 auto;
}

/* === Tarjeta del gráfico === */
#graficoCard {
    border: 1.5px solid #6c757d;
    border-radius: 10px;
    padding: 20px;
    background-color: white;
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.15);
    margin-bottom: 30px;
}

#graficoCard h4 {
    color: #495057;
    font-weight: 700;
    margin-bottom: 15px;
}

/* === Layout general === */
html, body {
    height: 100%;
    margin: 0;
    display: flex;
    flex-direction: column;
}

.content {
    flex: 1;
}

footer {
    background-color: #7a123a;
    width: 100%;
    text-align: center;
    color: white;
    font-weight: bold;
    padding-top: 10px;
}

/* === Sección header === */
.section-header {
    background-color: #dee2e6;
    border-left: 5px solid #495057;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    margin-bottom: 20px;
}

.section-header h1 {
    color: #343a40;
    font-weight: 700;
}

.section-header small {
    color: #6c757d;
    font-weight: 500;
}

/* === Navbar y dropdown === */
.nav-link,
.navbar-brand {
    transition: color 0.3s;
}

.nav-link:hover,
.navbar-brand:hover {
    color: #9A7D0A !important;
}

.dropdown-submenu {
    display: none;
    margin-left: 1rem;
}

.dropdown-submenu-toggle.active + .dropdown-submenu {
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

/* === Otros === */
.hidden {
    display: none;
}

.mb-3 {
    margin-bottom: 1rem !important;
}

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
    border-color: rgba(175, 212, 196, 0.87);
}

table.dataTable tbody tr:hover {
    box-shadow: 0 4px 12px rgba(18, 122, 86, 0.5);
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
    color: #11375fff;
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
    background:rgb(255, 255, 255);
    color: black !important;
    transition: background 0.3s ease;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background:rgba(255, 255, 255, 0.68);
    color: black !important;
}

/* Para que Select2 use todo el ancho y sea legible */
.select2-container {
  width: 100% !important;
  font-size: 1rem;
}
.select2-selection {
  min-height: 38px;
  font-size: 1rem;
}
.select2-results__option {
  font-size: 1rem;
  padding: 8px;
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
  border-color: #61cab5ff;
  box-shadow: 0 0 0 0.2rem rgba(7, 186, 141, 0.25);
}

/* Tags seleccionados */
.select2-container--default .select2-selection--multiple .select2-selection__choice {
  background-color: #93b7adff;
  border: none;
  border-radius: 3px;
  padding: 4px 10px;
  font-size: 0.9rem;
  color: #212925ff;
  font-weight: 500;
  margin-top: 6px;
}

/* Botón de eliminar etiqueta */
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
  color: #2a352fff;
  margin-right: 6px;
  font-weight: bold;
  transition: color 0.2s ease;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
  color: #dc9435ff;
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

/* Contenedor general de filtros */
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

/* Cada grupo (label + select) */
.filter-group {
    min-width: 220px;
    flex: 1 1 250px;
}

/* Estilo de las etiquetas */
.filter-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #2b2b2b;
    font-size: 0.95rem;
}

/* Ajuste para selects de Select2 */
.select2-container--default .select2-selection--multiple {
    min-height: 38px;
    padding: 4px 8px;
    border-radius: 12px;
    border: 1px solid #ccc;
    font-size: 0.95rem;
    background-color: white;
}

/* Responsive para móviles */
@media (max-width: 576px) {
    .filter-container-inline {
        flex-direction: column;
        align-items: stretch;
    }
}

.btn-urgencia {
            background-color: #242424ff;
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
            box-shadow: 0 4px 10px rgba(57, 57, 57, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-urgencia:hover {
            background-color: #4b4b4bff;
            /* más claro al pasar */
            box-shadow: 0 6px 16px rgba(14, 14, 14, 0.4);
            transform: translateY(-2px);
            text-decoration: none;
            color: white;
        }

        .btn-urgencia:active {
            transform: translateY(0);
            box-shadow: 0 3px 8px rgba(73, 72, 72, 0.2);
        }

        .btn-urgencia:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(41, 41, 41, 0.3);
        }

           .btn-success {
            background: linear-gradient(135deg, rgba(46, 48, 47, 1), rgba(29, 172, 110, 1));
            border: none;
            border-radius: 12px;
            padding: 9px 16px;
            font-weight: 700;
            font-size: 1.1rem;
            color: white !important;
            box-shadow: 0 6px 15px rgba(50, 58, 65, 0.61);
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
            background: linear-gradient(135deg, rgba(0, 212, 135, 1), rgba(43, 47, 45, 1));
            box-shadow: 0 8px 25px rgba(31, 185, 108, 0.89);
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
                    <a class="btn btn-outline-light" onclick="anterior()" href="#" title="Atrás"><i class="bi bi-arrow-left"
                            ></i></a>
                    <a class="btn btn-outline-light" href="../usuario/EspecilidadOcas_user.php">Vista de usuario</a>

                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../usuario/EspecilidadOcas_user.php"><i class="fas fa-eye me-2"></i>Ver
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
                <h1 class="h2 mb-0">Especialidades de Ocasión</h1>
                <small class="text-muted">Listado de registros.</small>
            </div>
            <hr>

            <div class="d-flex flex-wrap justify-content-between align-items-center my-3 gap-2">
                <div class="d-flex flex-wrap gap-2">
                    <a href="ingresar_EspecialidadOcasion.php" class="btn btn-urgencia">➕ Agregar manual</a>
                    <a href="agregarEspecialidad_Ocasion.php" class="btn btn-urgencia">📥 Cargar archivo</a>
                    <a href="actualizarEspecialidad_Ocasion.php" class="btn btn-urgencia"
                        onclick="return confirm('¿Seguro que desea hacer reemplazo de datos en los registros?')">
                        🔁 Actualizar datos
                    </a>
                </div>
                <div class="d-flex justify-content-center align-items-center gap-2">
                    <a class="btn btn-success"  href="./Especialidades_inicio.php">📈 Ir a Especialidades</a>
                    <form id="formDescargarExcel" action="descargar_excel.php" method="post" class="m-0">
                        <input type="hidden" name="anio" id="inputAnio" value="">
                        <input type="hidden" name="especialidad" id="inputEspecialidad" value="">
                        <input type="hidden" name="descripcion" id="inputDescripcion" value="">
                        <button class="btn btn-success" type="submit">🟩 Descargar Excel con gráfico</button>
                    </form>

                    <a id="btnDescargarGraficoPDF2" class="btn btn-success">📋 Descargar PDF</a>
                    <a id="btnDescargarImagenesCanvas3" class="btn btn-success">🖼️ Descargar Imagen</a>
                    <a id="btnGrafico" class="btn btn-success" onclick="toggleGrafico()">📊 Ver Gráfico</a>
                </div>
            </div>


            <hr>

          <!-- Tus selects -->
<div class="filter-container-inline">
  <div class="filter-group" id="selectorAnioContainer" style="display:none;">
    <label for="selectAnio">Año:</label>
    <select id="selectAnio" class="select2" multiple></select>
  </div>

  <div class="filter-group" id="selectorDescripcionContainer" style="display:none;">
    <label for="selectDescripcion">División:</label>
    <select id="selectDescripcion" class="select2" multiple></select>
  </div>

  <div class="filter-group" id="selectorEspecialidadContainer" style="display:none;">
    <label for="selectEspecialidad">Especialidad:</label>
    <select id="selectEspecialidad" class="select2" multiple></select>
  </div>
</div>


            <div id="graficoCard" class="info-card" style="display: none;">
                <h4>Gráfico - Mensual por Especialidad - Primera Vez</h4>
                <canvas id="graficoPrimeraVez" width="400" height="180"></canvas>
                <br><br>
                <h4>Gráfico - Mensual por Especialidad - Subsecuente</h4>
                <canvas id="graficoSubsecuente" width="400" height="180"></canvas>
                <p id="mensajeSinDatos" style="display: none; color: red;"><strong>No hay datos ingresados.</strong></p>
            </div>

            <div>
                <?php $especialidades = Especialidad_Ocasion::listar();
                if (count($especialidades) > 0) { ?>
                <div class="card shadow-sm mt-5">
                    <div class="card-header">
                        <h5 class="mb-0">Tabla de Especialidades de Ocasión</h5>
                    </div>
                     <div class="card-body">
                        <div class="table-responsive">
<div class="row mb-3 justify-content-end">
                      <div class="col-md-3 col-sm-6 d-flex align-items-center gap-2">
  <label for="filtroAnioTabla" class="form-label mb-0 fw-semibold text-dark">Año:</label>
  <select id="filtroAnioTabla" class=" select2" multiple>
    <?php
    $aniosUnicos = array_unique(array_column($especialidades, 'anio'));
    sort($aniosUnicos);
    foreach ($aniosUnicos as $anio) {
        echo "<option value=\"$anio\">$anio</option>";
    }
    ?>
  </select>
</div>
</div>



                        <table class="table table-bordered align-middle text-center" id="tabla">
                         <thead>
    <tr>
        <th rowspan="2">Clave</th>
        <th rowspan="2">Especialidad</th>
        <th rowspan="2">División</th>
        <?php
        $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        foreach ($meses as $mes) {
            echo '<th colspan="2">' . ucfirst($mes) . '</th>';
        }
        ?>
        <th rowspan="2">Año</th>
        <th colspan="2">Opciones</th>
    </tr>
    <tr>
        <?php foreach ($meses as $mes): ?>
            <th>1era</th>
            <th>Sub</th>
        <?php endforeach; ?>
        <th>Editar</th>
        <th>Eliminar</th>
    </tr>
</thead>



                            <tbody>
                                <?php foreach ($especialidades as $esp) { ?>
                                    <tr>
                                        <td><?= $esp['clave'] ?></td>
                                        <td><?= $esp['especialidad'] ?></td>
                                        <td><?= $esp['descripcion'] ?></td>
                                        <?php
                                        foreach ($meses as $mes) {
                                            echo "<td>{$esp[$mes . '_1era']}</td><td>{$esp[$mes . '_sub']}</td>";
                                        }
                                        ?>
                                        <td><?= $esp['anio'] ?></td>
                                        <td>
                                            <a class="btn btn-light btn-sm"
    href="editarEspecialidad_Ocasion.php?id=<?= base64_encode($esp['id']) ?>">Editar</a>

                                        </td>
                                        <td>
                                            <a class="btn btn-light btn-sm"
    href="../../controladores/Especialidad_Ocasion.php?a=Eliminar&id=<?= base64_encode($esp['id']) ?>"
    onclick="return confirm('¿Desea eliminar?')">Eliminar</a>

                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <p><strong>No hay datos ingresados.</strong></p>
                <?php } ?>
            </div>
        </div>

    </div>
    </div>
    <br><br>

    <footer>
        <p>Derechos reservados &copy; IMSS 2025</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 <script>
$(document).ready(function () {
  // Inicializar Select2
  $('.select2').select2({
    closeOnSelect: false,
    placeholder: '-- Todos --',
    allowClear: true,
    width: '100%',
  });

  // Inicializar DataTable
  const tabla = $('#tabla').DataTable({
    paging: true,
    searching: true,
    info: true,
    order: [],
    pageLength: 5,
    language: {
      url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
    }
  });

  // Cargar opciones iniciales en selects gráficos
  inicializarFiltrosGrafico(tabla);

  // Filtro personalizado
  $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
    const aniosFiltro = $('#filtroAnioTabla').val() || [];
    const divisionesFiltro = $('#selectDescripcion').val() || [];
    const especialidadesFiltro = $('#selectEspecialidad').val() || [];

    const anioFila = data[27];
    const divisionFila = data[2];
    const especialidadFila = data[1];

    const cumpleAnio = aniosFiltro.length === 0 || aniosFiltro.includes(anioFila);
    const cumpleDivision = divisionesFiltro.length === 0 || divisionesFiltro.includes(divisionFila);
    const cumpleEspecialidad = especialidadesFiltro.length === 0 || especialidadesFiltro.includes(especialidadFila);

    return cumpleAnio && cumpleDivision && cumpleEspecialidad;
  });

  // Listeners de cambios
  $('#filtroAnioTabla').on('change', function () {
    tabla.draw();
    sincronizarFiltros('tabla');
    actualizarDependencias(tabla);
    actualizarGrafico(tabla);
  });

  $('#selectAnio, #selectDescripcion, #selectEspecialidad').on('change', function () {
    sincronizarFiltros('grafico');
    tabla.draw();
    actualizarDependencias(tabla);
    actualizarGrafico(tabla);
  });
});

function inicializarFiltrosGrafico(tabla) {
  const aniosSet = new Set();
  const divisionesSet = new Set();
  const especialidadesSet = new Set();

  tabla.rows().every(function () {
    const celdas = $(this.node()).find('td');
    aniosSet.add(celdas.eq(27).text());
    divisionesSet.add(celdas.eq(2).text());
    especialidadesSet.add(celdas.eq(1).text());
  });

  rellenarSelect($('#selectAnio'), aniosSet);
  rellenarSelect($('#selectDescripcion'), divisionesSet);
  rellenarSelect($('#selectEspecialidad'), especialidadesSet);

  $('#selectAnio').trigger('change.select2');
  $('#selectDescripcion').trigger('change.select2');
  $('#selectEspecialidad').trigger('change.select2');
}

function rellenarSelect(select, set) {
  const current = select.val() || [];

  // Guardar mapa de opciones actuales para evitar duplicados
  const opcionesActuales = new Set();
  select.find('option').each(function () {
    opcionesActuales.add($(this).val());
  });

  // Agregar solo las nuevas que faltan
  set.forEach(item => {
    if (item && !opcionesActuales.has(item)) {
      select.append(new Option(item, item));
    }
  });

  // Eliminar las que ya no deben estar
  select.find('option').each(function () {
    const val = $(this).val();
    if (!set.has(val)) {
      $(this).remove();
    }
  });

  // Mantener solo los valores seleccionados que siguen siendo válidos
  const seleccionValida = current.filter(v => set.has(v));
  select.val(seleccionValida).trigger('change.select2');
}


function sincronizarFiltros(origen) {
  if (origen === 'tabla') {
    const anios = $('#filtroAnioTabla').val() || [];
    $('#selectAnio').val(anios).trigger('change.select2');
  } else {
    const anios = $('#selectAnio').val() || [];
    $('#filtroAnioTabla').val(anios).trigger('change.select2');
  }
}

function actualizarDependencias(tabla) {
  const anios = $('#selectAnio').val() || [];
  const filas = tabla.rows().nodes();
  const divisionesSet = new Set();
  const especialidadesSet = new Set();

  $(filas).each(function () {
    const celdas = $(this).find('td');
    const anio = celdas.eq(27).text();
    const division = celdas.eq(2).text();
    const especialidad = celdas.eq(1).text();

    if (anios.length === 0 || anios.includes(anio)) {
      if (division) divisionesSet.add(division);
      if (especialidad) especialidadesSet.add(especialidad);
    }
  });

  rellenarSelect($('#selectDescripcion'), divisionesSet);
  rellenarSelect($('#selectEspecialidad'), especialidadesSet);

  // Reset selects si ya no aplica con el año seleccionado
  const divisionActual = $('#selectDescripcion').val() || [];
  $('#selectDescripcion').val(divisionActual.filter(v => divisionesSet.has(v))).trigger('change.select2');

  const especialidadActual = $('#selectEspecialidad').val() || [];
  $('#selectEspecialidad').val(especialidadActual.filter(v => especialidadesSet.has(v))).trigger('change.select2');
}

function obtenerDatosFiltrados(tabla) {
  const anios = $('#selectAnio').val() || [];
  const divisiones = $('#selectDescripcion').val() || [];
  const especialidades = $('#selectEspecialidad').val() || [];

  const filas = tabla.rows({ filter: 'applied' }).nodes();
  const datos = [];

  $(filas).each(function () {
    const celdas = $(this).find('td');
    const especialidad = celdas.eq(1).text();
    const division = celdas.eq(2).text();
    const anio = celdas.eq(27).text();

    if (
      (anios.length === 0 || anios.includes(anio)) &&
      (divisiones.length === 0 || divisiones.includes(division)) &&
      (especialidades.length === 0 || especialidades.includes(especialidad))
    ) {
      const clave = especialidad + ' (' + anio + ')';
      const existente = datos.find(d => d.especialidad === clave);
      if (!existente) {
        const datosEspecialidad = {
          especialidad: clave,
          primera: [],
          subsecuente: []
        };

        for (let i = 3; i <= 26; i += 2) {
          datosEspecialidad.primera.push(parseInt(celdas.eq(i).text()) || 0);
          datosEspecialidad.subsecuente.push(parseInt(celdas.eq(i + 1).text()) || 0);
        }

        datos.push(datosEspecialidad);
      }
    }
  });

  return datos;
}

let chartPrimera = null;
let chartSub = null;

function actualizarGrafico(tabla) {
  const datos = obtenerDatosFiltrados(tabla);
  const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

  if (chartPrimera) chartPrimera.destroy();
  if (chartSub) chartSub.destroy();

  if (datos.length === 0) {
    $('#mensajeSinDatos').show();
    return;
  } else {
    $('#mensajeSinDatos').hide();
  }

  const ctx1 = document.getElementById('graficoPrimeraVez').getContext('2d');
  const ctx2 = document.getElementById('graficoSubsecuente').getContext('2d');

  chartPrimera = new Chart(ctx1, {
    type: 'line',
    data: {
      labels: meses,
      datasets: datos.map(d => ({
        label: d.especialidad,
        data: d.primera,
        fill: false,
        borderColor: getRandomColor(),
        tension: 0.1
      }))
    }
  });

  chartSub = new Chart(ctx2, {
    type: 'line',
    data: {
      labels: meses,
      datasets: datos.map(d => ({
        label: d.especialidad,
        data: d.subsecuente,
        fill: false,
        borderColor: getRandomColor(),
        tension: 0.1
      }))
    }
  });
}

function getRandomColor() {
  const letters = '0123456789ABCDEF';
  let color = '#';
  for (let i = 0; i < 6; i++) {
    color += letters[Math.floor(Math.random() * 16)];
  }
  return color;
}

function toggleGrafico() {
  const visible = $('#graficoCard').is(':visible');
  $('#graficoCard').toggle(!visible);
  $('#selectorAnioContainer').toggle(!visible);
  $('#selectorDescripcionContainer').toggle(!visible);
  $('#selectorEspecialidadContainer').toggle(!visible);

  if (!visible) {
    const tabla = $('#tabla').DataTable();
    actualizarGrafico(tabla);
    $('#btnGrafico').text('❌ Ocultar gráfico');
  } else {
    $('#btnGrafico').text('Ver gráfico 📊');
  }
}
</script>

  <script>
    document.getElementById('formDescargarExcel').addEventListener('submit', function(e) {
        const anioSelect = document.getElementById('selectAnio');
        const especialidadSelect = document.getElementById('selectEspecialidad');
        const descripcionSelect = document.getElementById('selectDescripcion');

        const getMultipleValues = (select) =>
            Array.from(select.selectedOptions).map(opt => opt.value).join(',');

        document.getElementById('inputAnio').value = getMultipleValues(anioSelect);
        document.getElementById('inputEspecialidad').value = getMultipleValues(especialidadSelect);
        document.getElementById('inputDescripcion').value = getMultipleValues(descripcionSelect);
    });
</script>


 <script>
document.getElementById('btnDescargarGraficoPDF2').addEventListener('click', async () => {
    const { jsPDF } = window.jspdf;

    const graficoCard = document.getElementById("graficoCard");
    const estabaOculto = graficoCard && graficoCard.style.display === "none";
    if (estabaOculto) graficoCard.style.display = "block";

    await new Promise(resolve => setTimeout(resolve, 300));

    const canvasPrimera = document.getElementById("graficoPrimeraVez");
    const canvasSub = document.getElementById("graficoSubsecuente");

    const pdf = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: 'letter' // Tamaño carta: 216 x 279 mm
    });

    // Encabezado
    pdf.setFontSize(12);
    pdf.setFont(undefined, "bold");
    pdf.text("UNIDAD MÉDICA DE ALTA ESPECIALIDAD", 105, 14, { align: "center" });
    pdf.setFont(undefined, "normal");
    pdf.text("HOSPITAL DE GINECO - PEDIATRÍA No. 48", 105, 21, { align: "center" });
    pdf.setFont(undefined, "bold");
    pdf.text("ESPECIALIDADES DE OCASIÓN", 105, 28, { align: "center" });

    // Línea bajo el encabezado
    pdf.setLineWidth(0.5);
    pdf.line(10, 32, 200, 32);

    let yOffset = 42;

    const addCanvasToPDF = (canvas, titulo, offsetY) => {
        const imgData = canvas.toDataURL("image/png");
        pdf.setFontSize(11);
        pdf.setFont(undefined, "normal");
        pdf.text(titulo, 10, offsetY);
        pdf.addImage(imgData, 'PNG', 10, offsetY + 5, 190, 95); // tamaño igual al de referencia
    };

    if (canvasPrimera) {
        addCanvasToPDF(canvasPrimera, "Gráfico - Mensual por Especialidad - Primera Vez", yOffset);
        yOffset += 110;
    }

    if (canvasSub) {
        addCanvasToPDF(canvasSub, "Gráfico - Mensual por Especialidad - Subsecuente", yOffset);
        yOffset += 110;
    }

    // Fecha centrada al pie
    const fecha = new Date().toLocaleDateString();
    const pageHeight = pdf.internal.pageSize.getHeight();
    pdf.setFontSize(9);
    pdf.setFont(undefined, "italic");
    pdf.text(`Fecha: ${fecha}`, 105, pageHeight - 18, { align: "center" });

    // Pie institucional
    pdf.setFontSize(10);
    pdf.text("Instituto Mexicano del Seguro Social", 105, pageHeight - 12, { align: "center" });
    pdf.text("UMAE HGP 48", 105, pageHeight - 6, { align: "center" });

    // Guardar PDF
    pdf.save("grafico-tabla-EspecialidadOcasion.pdf");

    if (estabaOculto) graficoCard.style.display = "none";
});
</script>

<script>
document.getElementById('btnDescargarImagenesCanvas3').addEventListener('click', function () {
    exportarCanvasConFondo('graficoPrimeraVez', 'grafico_mensual_especialidad_primera_vez.png');
    exportarCanvasConFondo('graficoSubsecuente', 'grafico_mensual_especialidad_subsecuente.png');
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

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>

    <script src="../../js/scripts.js" defer></script>
    <script src="../../js/bootstrap.bundle.min.js" defer></script>

</body>

</html>
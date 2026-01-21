<?php include('verificar_sesion.php');
require_once '../../modelos/UsuariosAdmin.php';

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['login_error'] = "Debes iniciar sesión como administrador para acceder a esa sección.";
    header('Location: ../admin/login.php');  // O la ruta correcta de tu login
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de cuentas | Administrador</title>
    <link rel="icon" href="../../logo-imss.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            color: #212529;
        }

        .footer-fijo {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: 999;
        }

        .navbar {
            background-color: #7a123a;
        }

        .table thead {
            background-color: #00664d;
            color: white;
        }

        .bg-verde {
            background-color: #00664d;
            color: white;
            border-radius: 8px;
        }

        .bg-verde:hover {
            background-color: #004d3a;
            color: white;
        }

        .btn-dark-custom {
            background-color: #212529;
            color: white;
            border-radius: 6px;
        }

        .btn-dark-custom:hover {
            background-color: rgb(129, 13, 53);
            color: white;
        }

        /* Paginación DataTables con Bootstrap 5 sin azul */
        .dataTables_wrapper .dataTables_paginate .pagination .page-item .page-link {
            background-color: #f5f5f5 !important;
            color: #212529 !important;
            border: 1px solid #212529 !important;
        }

        .dataTables_wrapper .dataTables_paginate .pagination .page-item.active .page-link {
            background-color: #7a123a !important;
            /* guinda IMSS para el actual */
            color: #fff !important;
            border-color: #7a123a !important;
        }

        .dataTables_wrapper .dataTables_paginate .pagination .page-item:hover .page-link {
            background-color: #212529 !important;
            /* gris oscuro al pasar */
            color: #fff !important;
            border-color: #212529 !important;
        }

        /* hover nav */
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

        .card-submenu {
            display: none;
            transition: all 0.3s ease;
        }

        .card-submenu-toggle.active+.card-submenu {
            display: block;
        }

        .card-submenu-toggle {
            cursor: pointer;
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

        /* ----------------------------------------------
           Footer
        ------------------------------------------------*/

        footer {
            background-color: #7a123a;
            width: 100%;
            text-align: center;
            color: white;
            font-weight: bold;
            padding: 10px 0;
        }

        .btn-outline-warning2 {
            color: white;
            background-color: #00796b;
        }

        .btn-outline-warning2:hover {
            background-color: #00685cff;
            color: white;
            border-color: #00796b;
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
                                    <li><a class="dropdown-item" href="../productividad/unidades_que_reportan.php">Total Productividad</a></li>
                                    <li><a class="dropdown-item" href="../productividad/paramedicos.php">Paramédicos</a>
                                    </li>

                                    <!-- Especialidades -->
                                    <li>
                                        <a class="dropdown-item dropdown-submenu-toggle">Especialidades</a>
                                        <ul class="dropdown-submenu">
                                            <li><a class="dropdown-item"
                                                    href="../productividad/especialidades_inicio.php">Especialidades</a>
                                            </li>
                                            <li><a class="dropdown-item" href="../productividad/Especialidad_Ocasion.php">Especialidad
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
                    <a href="../admin/admin.php" class="btn btn btn-outline-light"><i class="bi bi-arrow-left"
                            title="Atrás"></i></a>

                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
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


    <!-- CONTENIDO -->
    <div class="container my-4 p-4 bg-white rounded-4 shadow">
        <a href="ingresarAdmin.php" class="btn btn-dark-custom">Ingresar nuevo</a>
        <br><br>
        <div class="table-responsive">
            <table id="tablaUsuarios" class="table table-bordered align-middle text-center">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Contraseña</th>
                        <th>Editar</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (Usuarios::listar() as $fila) { ?>
                        <tr>
                            <td><?= $fila[0] ?></td>
                            <td><?= $fila[1] ?></td>
                            <td><?= $fila[2] ?></td>
                            <td><?= $fila[3] ?></td>
                            <td>
                                <a class="btn btn-dark-custom" href="editarUsersAdmin.php?id=<?= base64_encode($fila[0]) ?>">Editar</a>
                            </td>
                            <td>
                                <a class="btn btn-dark-custom"
                                    href="../../controladores/UsuariosAdmin.php?a=Eliminar&id=<?= base64_encode($fila[0]) ?>"
                                    onclick="return confirm('¿Desea eliminar?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="footer-fijo text-white text-center py-3" style="background-color: #7a123a;">
        <div class="container">
            <p class="mb-0">© <?= date("Y") ?> IMSS. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- SCRIPTS -->
    <script src="../../js/scripts.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tablaUsuarios').DataTable({
                language: {
                    lengthMenu: "Mostrar _MENU_ registros por página",
                    zeroRecords: "No se encontraron resultados",
                    info: "Mostrando página _PAGE_ de _PAGES_",
                    infoEmpty: "No hay registros disponibles",
                    infoFiltered: "(filtrado de _MAX_ registros totales)",
                    search: "Buscar:",
                    paginate: {
                        previous: "Anterior",
                        next: "Siguiente"
                    }
                }
            });
        });
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
</body>

</html>
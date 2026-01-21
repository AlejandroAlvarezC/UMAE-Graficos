<?php
session_start();
require_once '../../../modelos/Personal.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../../../logo-imss.png" />

    <!-- Bootstrap y Iconos -->
    <link rel="stylesheet" href="../../../css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <!-- DataTables con estilo Bootstrap -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <title>Personal | Organigrama</title>

    <style>
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

        thead {
            background-color: #00664d;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }

        .bg-verde {
            background-color: #00664d;
            color: white;
            border-radius: 6px;
            padding: 8px 16px;
            transition: background-color 0.2s ease;
        }

        .bg-verde:hover {
            background-color: #001a15ff;
            color: white;
        }

        .avatar-sm {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #ccc;
            transition: transform 0.2s ease-in-out;
        }

        .avatar-sm:hover {
            transform: scale(1.08);
        }

        table.dataTable tbody tr:hover {
            background-color: #eef6f3;
        }

        .table th,
        .table td {
            vertical-align: middle !important;
            font-size: 14px;
        }

        .btn-sm i {
            font-size: 1.1rem;
        }

        .btn-outline-primary.btn-sm:hover,
        .btn-outline-danger.btn-sm:hover {
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
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
            width: 100%;
            text-align: center;
            color: white;
            font-weight: bold;
            padding-top: 10px;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #7a123a;">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link text-white" href="../admin.php">INICIO</a></li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#"
                            data-bs-toggle="dropdown">Productividad</a>
                        <ul class="dropdown-menu" data-bs-auto-close="outside">

                            <!-- Consulta externa -->
                            <li>
                                <a class="dropdown-item dropdown-submenu-toggle">Consulta externa</a>
                                <ul class="dropdown-submenu">
                                    <li><a class="dropdown-item" href="../../productividad/unidades_que_reportan.php">Productividad Total</a></li>
                                    <li><a class="dropdown-item" href="../../productividad/paramedicos.php">Paramédicos</a>
                                    </li>

                                    <!-- Especialidades -->
                                    <li>
                                        <a class="dropdown-item dropdown-submenu-toggle">Especialidades</a>
                                        <ul class="dropdown-submenu">
                                            <li><a class="dropdown-item"
                                                    href="../../productividad/especialidades_inicio.php">Especialidades</a>
                                            </li>
                                            <li><a class="dropdown-item" href="../../productividad/Especialidad_Ocasion.php">Especialidad
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
                                    <li><a class="dropdown-item" href="../../hospitalizacion/ingresos_inicio.php">Ingresos</a></li>
                                    <li><a class="dropdown-item" href="../../hospitalizacion/egresos_inicio.php">Egresos</a></li>
                                    <li><a class="dropdown-item" href="../../hospitalizacion/pacientes_inicio.php">Días Paciente</a></li>
                                    <li><a class="dropdown-item" href="#">Días Cama</a></li>
                                </ul>
                            </li>

                            <!-- Cirugía -->
                            <li><a class="dropdown-item" href="../../cirugia/cirugia_inicio.php">Cirugía</a></li>

                            <!-- Urgencias -->
                            <li><a class="dropdown-item" href="../../productividad/urgencias_inicio.php">Urgencias</a></li>

                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="../../admin/Personal/personal.php">Organigrama</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-white" href="../../productividad/vencer.php">Vencer</a>
                    </li>

                    <!-- Normatividad -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../../normatividad/normatividad_inicio.php">Normatividad</a>
                    </li>

                    <li class="nav-item"><a class="nav-link text-white" href="../usuariosAdmin.php">Usuario</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <a href="../admin.php" class="btn btn btn-outline-light"><i class="bi bi-arrow-left"
                            title="Atrás"></i></a>
                    <a class="btn btn-outline-light" href="../../usuario/organigrama.php">Vista de usuario</a>

                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../../usuario/organigrama.php"><i class="fas fa-eye me-2"></i>Ver
                                    como Usuario</a></li>
                            <li><a class="dropdown-item" href="../usuariosAdmin.php"><i
                                        class="fas fa-id-badge me-2"></i>Perfil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="../logout.php"><i
                                        class="fas fa-sign-out-alt me-2"></i>Cerrar sesión</a></li>
                        </ul>
                    </div>
                </div>
            </div>
    </nav>

    <div class="content">
        <!-- Contenido principal -->
        <div class="container my-4 p-3 bg-white rounded shadow-sm">
            <a href="ingresarPersonal.php" class="btn bg-verde mb-3">
                Agregar Personal
            </a>
            <div class="table-responsive">
                <table id="tablaPersonal" class="table table-hover table-striped text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Ap. Paterno</th>
                            <th>Ap. Materno</th>
                            <th>Área</th>
                            <th>Puesto</th>
                            <th>Teléfono</th>
                            <th>Ext</th>
                            <th>Correo</th>
                            <th>Foto</th>
                            <th>Jefe</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (Personal::listar() as $f): ?>
                            <tr>
                                <td><?= $f[0] ?></td>
                                <td><?= htmlspecialchars($f[1]) ?></td>
                                <td><?= htmlspecialchars($f[2]) ?></td>
                                <td><?= htmlspecialchars($f[3]) ?></td>
                                <td><?= htmlspecialchars($f[4]) ?></td>
                                <td><?= htmlspecialchars($f[5]) ?></td>
                                <td><?= htmlspecialchars($f[6]) ?></td>
                                <td><?= htmlspecialchars($f[7]) ?></td>
                                <td><a href="mailto:<?= htmlspecialchars($f[8]) ?>"><?= htmlspecialchars($f[8]) ?></a></td>
                                <td>
                                    <?php
                                    $ruta = "../../../img/" . $f[9];
                                    if (!empty($f[9]) && file_exists($ruta)): ?>
                                        <img src="<?= $ruta ?>" class="avatar-sm" alt="Foto <?= htmlspecialchars($f[1]) ?>">
                                    <?php else: ?>
                                        <i class="bi bi-person-circle text-muted" style="font-size: 1.5rem;"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($f[10]) ?></td>
                                <td>
                                    <a class="btn btn-outline-primary btn-sm" title="Editar"
                                        href="editarPersonal.php?id=<?= base64_encode($f[0]) ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                                <td>
                                    <a class="btn btn-outline-danger btn-sm" title="Eliminar"
                                        href="../../../controladores/PersonalController.php?a=Eliminar&id=<?= base64_encode($f[0]) ?>"
                                        onclick="return confirm('¿Desea eliminar este registro?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- Footer -->
    <footer>
        <p>Derechos reservados &copy; IMSS 2025</p>
    </footer>


    <!-- Scripts -->
    <script src="../../../js/bootstrap.bundle.min.js"></script>
    <script src="../../../js/jquery-3.7.1.js"></script>

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tablaPersonal').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [5, 10, 15, 20],
                columnDefs: [{
                    orderable: false,
                    targets: [9, 11, 12]
                }],
                language: {
                    decimal: "",
                    emptyTable: "No hay datos disponibles en la tabla",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    infoEmpty: "Mostrando 0 a 0 de 0 registros",
                    infoFiltered: "(filtrado de _MAX_ registros totales)",
                    infoPostFix: "",
                    thousands: ",",
                    lengthMenu: "Mostrar _MENU_ registros",
                    loadingRecords: "Cargando...",
                    processing: "Procesando...",
                    search: "Buscar:",
                    zeroRecords: "No se encontraron resultados",
                    paginate: {
                        first: "Primero",
                        last: "Último",
                        next: "Siguiente",
                        previous: "Anterior"
                    },
                    aria: {
                        sortAscending: ": activar para ordenar columna ascendente",
                        sortDescending: ": activar para ordenar columna descendente"
                    }
                }

            });
        });

        $.fn.dataTable.ext.type.search.string = function(data) {
            return !data ?
                '' :
                typeof data === 'string' ?
                data
                .normalize('NFD') // separa caracteres con tilde
                .replace(/[\u0300-\u036f]/g, '') // elimina tildes
                .toLowerCase() // convierte a minúsculas
                :
                data;
        };

        // SUBMENU --- //
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
            const anio = document.getElementById('selectAnio').value;
            const especialidad = document.getElementById('selectEspecialidad').value;
            const division = document.getElementById('selectDivision').value;

            document.getElementById('inputAnio').value = anio;
            document.getElementById('inputEspecialidad').value = especialidad;
            document.getElementById('inputDivision').value = division;
        });
    </script>
</body>

</html>
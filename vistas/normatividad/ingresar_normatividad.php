<?php
session_start();

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
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="icon" type="image/png" href="../../logo-imss.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>
    <title>Subir Manual | Organigrama</title>

    <style>
        body {
            background-color: aliceblue;
        }

        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 2rem;
        }

        h1.h3 {
            color: #333;
            font-weight: bold;
        }

        p.text-muted {
            margin-top: -0.5rem;
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 600;
        }

        .form-control,
        .form-select {
            border-radius: 0.5rem;
        }

        .btn-primary {
            background-color: rgb(25, 102, 202);
            border: none;
        }

        .btn-primary:hover {
            background-color: rgb(6, 78, 136);
        }

        .btn+.btn {
            margin-left: 10px;
        }

        .section-header {
            border-left: 5px solid #0000FF;
            padding-left: 15px;
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
    </style>

</head>

<body style="background-color: aliceblue;">

    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #7a123a;">
        <div class="container-fluid">

            <!-- Botón para colapsar el menú en pantallas pequeñas -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menú principal -->
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
                                    <li><a class="dropdown-item" href="../productividad/unidades_que_reportan.php">Productividad total</a></li>
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

                    <li class="nav-item">
                        <a class="nav-link text-white" href="../normatividad/normatividad_inicio.php">Normatividad</a>
                    </li>

                    <li class="nav-item"><a class="nav-link text-white" href="../admin/usuariosAdmin.php">Usuario</a>
                    </li>
                </ul>

                <!-- Botones a la derecha -->
                <div class="d-flex align-items-center gap-2">
                    <a class="btn btn-outline-light" onclick="anterior()" href="#" title="Atrás"><i class="bi bi-arrow-left"></i></a>
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
        </div>
    </nav>




    <div class="container mt-5">
        <div class="section-header mb-4">
            <h2 class="h4 mb-0">Agregar Nuevo</h2>
            <small class="text-muted">Carga e ingresa un nuevo manual</small>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="../../controladores/Normatividades.php" method="post" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label for="normatividad" class="form-label">Normatividad:</label>
                        <select class="form-select" id="normatividad" name="normatividad" required>
                            <option value="">Seleccione un tipo de normatividad</option>
                            <option value="Externa">Externa</option>
                            <option value="Interna">Interna</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del manual</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ingrese el nombre del manual" required>
                    </div>

                    <div class="mb-3">
                        <label for="anio" class="form-label">Año</label>
                        <input type="date" class="form-control" id="anio" name="anio" placeholder="Ingrese el año" required>
                    </div>

                    <div class="mb-3">
                        <label for="entidad" class="form-label">Entidad</label>
                        <input type="text" class="form-control" id="entidad" name="entidad" placeholder="Ingrese la entidad responsable" required>
                    </div>

                    <div class="mb-3">
                        <label for="servicio" class="form-label">Servicio</label>
                        <input type="text" class="form-control" id="servicio" name="servicio" placeholder="Ingrese el servicio relacionado" required>
                    </div>

                    <div class="mb-3">
                        <label for="fecha" class="form-label">Clave</label>
                        <input type="text" class="form-control" id="fecha" name="fecha"
                            placeholder="Ej: MNL2024 o 123ABC"
                            maxlength="20"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección:</label>
                        <select class="form-select" id="direccion" name="direccion" required>
                            <option value="">Seleccione un tipo de dirección</option>
                            <option value="Dirección Médica">Dirección Médica</option>
                            <option value="Dirección General">Dirección General</option>
                            <option value="Dirección Educación e Investigación en Salud">Dirección Educación e Investigación en Salud</option>
                            <option value="Dirección Enfermería">Dirección Enfermería</option>
                            <option value="Dirección Administrativa">Dirección Administrativa</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="archivo" class="form-label">Archivo PDF</label>
                        <input class="form-control" type="file" id="archivo" name="archivo" accept="application/pdf" required>
                    </div>

                    <a href="normatividad_inicio.php" class="btn btn-outline-secondary">Regresar</a>
                    <button type="submit" name="a" value="Subir" class="btn btn-primary">Subir Manual</button>
                </form>
            </div>
        </div>
    </div>



    <!-- Footer -->
    <footer class="text-white text-center py-3 mt-5" style="background-color: #7a123a;">
        <div class="container">
            <p class="mb-0">© <?php echo date("Y"); ?> IMSS. Todos los derechos reservados.</p>
        </div>
    </footer>



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


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

    <script src="../../js/scripts.js" defer></script>
    <script src="../../js/bootstrap.bundle.min.js" defer></script>

</body>

</html>
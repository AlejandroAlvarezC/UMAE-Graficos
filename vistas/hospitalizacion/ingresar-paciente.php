<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['login_error'] = "Debes iniciar sesión como administrador para acceder a esa sección.";
    header('Location: ../admin/login.php');  // O la ruta correcta de tu login
    exit();
}

require_once '../../modelos/dias_paciente.php';

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../../logo-imss.png">
    <link rel="stylesheet" href="../../css/bootstrap.min.css" defer>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <title>Ingresar datos | Días Paciente</title>

    <style>
        th,
        td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
        }

        .info-card {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 0;
        }

        .info-card h4 {
            margin-bottom: 15px;
        }

        .info-card .icon {
            font-size: 40px;
            color: #007bff;
            margin-right: 10px;
        }

        .card-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .card-container .card .info-card {
            flex-basis: 48%;
            margin-bottom: 0;
        }

        .info-card .btn {
            position: relative;
            border: none;
            color: white;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: none;
            margin-top: 10px;
        }

        .info-card .btn:hover {
            background-color: #006666;
        }

        .bg-verde {
            background-color: #00664d;
            color: white;
            border-radius: 12px;
            padding: 10px 20px;
            text-decoration: none;
        }

        .bg-verde:hover {
            background-color: #004d3a;
            color: white;
        }

        .acciones a {
            margin-right: 10px;
            text-decoration: none;
            background-color: #7a123a;
            color: white;
            border-radius: 12px;
            padding: 8px 15px;
            display: inline-block;
        }

        .acciones a:hover {
            background-color: #520d2a;
            color: white;
        }

        .table {
            padding: 4px 4px !important;
            font-size: small;
        }

        input[readonly] {
            background-color: rgba(0, 107, 194, 0.1);
            /* negro con 10% de opacidad */
            color: #000;
            /* color de texto negro */
        }

        /* Estructura del layout */
        html,
        body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        /* Contenedor principal que ocupa el espacio disponible */
        .content {
            flex: 1;
        }

        /* Estilos del footer */
        /* Estilos del footer */
        footer {
            background-color: #971c1c;
            width: 100%;
            text-align: center;
            color: white;
            font-weight: bold;
            padding-top: 10px;
        }

        input.is-invalid {
            border-color: #dc3545;
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

    <div class="content">

        <div class="container mt-4">
            <div class="card p-4">
                <h4 class="text-center">Ingresar datos - Días Paciente</h4>
                <?php if (isset($_GET['error'])): ?>
                    <div id="mensaje-error" class="alert alert-danger text-center">
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['msg'])): ?>
                    <div id="mensaje-exito" class="alert alert-success text-center">
                        <?= htmlspecialchars($_GET['msg']) ?>
                    </div>
                <?php endif; ?>
                <br>
                <form id="formPaciente" action="../../controladores/dias_pacientes.php" method="post">
                    <input type="hidden" name="a" value="Ingresar">
                    <input type="hidden" name="modo" id="modo" value="insertar">

                    <div class="row mb-3">
                        <div class="col-md-4 mb-2">
                            <label for="anio" class="form-label" style="margin-bottom: 2px;">Año</label>
                            <input id="anio" name="anio" type="number" class="form-control" placeholder="Ej. 2025" min="2010" required>
                        </div>
                        <div class="col-md-8">
                            <label for="clave" class="form-label" style="margin-bottom: 2px;">Clave</label>
                            <div class="input-group">
                                <input id="clave" name="clave" type="text" class="form-control" placeholder="Ej. 1000" min="0" required>
                                <button type="button" class="btn btn-warning" id="buscarClave">Buscar</button>
                                <button type="button" class="btn btn-danger" id="limpiarFormulario">Limpiar</button>
                            </div>
                        </div>
                    </div>

                    <input id="especialidad" name="especialidad" placeholder="Especialidad" class="form-control mb-2" required>

                    <input id="division" name="division" placeholder="División" class="form-control mb-2" required>

                    <h5>Valores por mes</h5>
                    <div class="row">
                        <?php
                        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
                        foreach ($meses as $m) {
                            echo "<div class='col-6 col-md-3 mb-2'>
                            <label for='{$m}'>{$m}</label>
                            <input name='{$m}' id='{$m}' type='number' class='form-control'>
                          </div>";
                        }
                        ?>
                    </div>

                    <div class="d-flex justify-content-center gap-2 mt-4">
                        <a href="./pacientes_inicio.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save"></i> Guardar Cambios
                        </button>
                        <a href="./pacientes_inicio.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div><br>

    </div>
    <br><br><br>


    <!-- Footer -->
    <footer class="text-white text-center py-3 mt-5" style="background-color: #7a123a;">
        <p>Derechos reservados &copy; IMSS 2025</p>
    </footer>



    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>

    <script src="../../js/scripts.js" defer></script>
    <script src="../../js/bootstrap.bundle.min.js" defer></script>



    <script>
        document.getElementById('formPaciente').addEventListener('submit', function(e) {
            const inputs = document.querySelectorAll('input[type="number"]');
            let isValid = true;
            inputs.forEach(input => {
                if (input.value !== '' && Number(input.value) < 0) {
                    input.classList.add('is-invalid'); // opcional: clase de Bootstrap para marcarlo
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault(); // Evita enviar el formulario
                alert('Por favor, ingresa solo valores positivos o cero en los campos numéricos.');
            }
        });
    </script>


    <script>
        // Función para limpiar formulario
        $('#limpiarFormulario').on('click', function() {
            $('#formPaciente')[0].reset(); // limpiar campos
            $('#modo').val('insertar'); // Volvemos a modo insertar

            // Desbloquear todos los campos que fueron bloqueados
            $('#formPaciente input').each(function() {
                $(this).prop('readonly', false); // quitar readonly
                $(this).removeClass('is-invalid'); // limpiar validaciones visuales
            });

            $('#clave').focus(); // reenfocar
        });

        // Función para buscar datos solo al dar clic en el botón "Buscar"
        $('#buscarClave').on('click', function() {
            var clave = $('#clave').val().trim();
            var anio = $('#anio').val().trim();

            if (clave === '' || anio === '') {
                Swal.fire({
                    title: 'Faltan datos',
                    text: 'Debes ingresar el año y la clave.',
                    icon: 'warning'
                });
                return;
            }

            $.ajax({
                type: 'GET',
                url: '../../controladores/buscarPacientesPorClave.php',
                data: {
                    clave: clave,
                    anio: anio
                },
                dataType: 'json',
                success: function(response) {
                    if (response.existe) {
                        Swal.fire({
                            title: 'Clave encontrada',
                            text: 'La clave existe. ¿Desea continuar con estos datos?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Sí',
                            cancelButtonText: 'No'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Rellenar campos
                                $('#especialidad').val(response.especialidad);
                                $('#division').val(response.division);
                                $('#anio').val(response.anio);

                                const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
                                meses.forEach(function(m) {
                                    $('#' + m).val(response[m]);
                                });

                                bloquearCamposSegunValor();
                                $('#modo').val('actualizar'); // <-- Agregado aquí
                            } else {
                                $('#limpiarFormulario').click();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'No encontrada',
                            text: 'La clave no coincide con ningún paramédico.',
                            icon: 'error'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error al consultar al paramédico.',
                        icon: 'error'
                    });
                }
            });
        });

        // Función para bloquear campos que ya tienen valores
        function bloquearCamposSegunValor() {
            $('#formPaciente input').each(function() {
                const id = $(this).attr('id');
                if (id === 'clave') {
                    $(this).prop('readonly', false);
                    return;
                }

                const val = $(this).val().trim();
                if (val === "" || val === "0") {
                    $(this).prop('readonly', false);
                } else {
                    $(this).prop('readonly', true);
                }
            });
        }



        // Menú desplegable (si tienes menús con subniveles)
        document.querySelectorAll('.dropdown-submenu-toggle').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const parentMenu = el.closest('ul');
                parentMenu.querySelectorAll('.dropdown-submenu-toggle').forEach(function(toggle) {
                    if (toggle !== el) toggle.classList.remove('active');
                });
                el.classList.toggle('active');
            });
        });
    </script>


    <script>
        // Ocultar mensajes después de 10 segundos (10000 milisegundos)
        setTimeout(function() {
            const errorMsg = document.getElementById('mensaje-error');
            const successMsg = document.getElementById('mensaje-exito');
            if (errorMsg) errorMsg.style.display = 'none';
            if (successMsg) successMsg.style.display = 'none';
        }, 6000); // 10 segundos
    </script>



</body>

</html>
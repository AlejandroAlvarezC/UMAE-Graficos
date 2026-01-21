<?php
session_start();
require_once '../../../modelos/Personal.php';

if (!isset($_GET['id'])) {
    header('Location: ./personal.php');
    exit;
}

$id = base64_decode($_GET['id']);
$datos = Personal::obtenerPorId($id);
if (!$datos) {
    echo "Personal no encontrado.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Personal</title>
    <link rel="stylesheet" href="../../../css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../../../logo-imss.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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

        .form-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 25px 20px;
        }

        .form-label {
            font-size: 0.95rem;
        }

        .form-control {
            font-size: 0.9rem;
            padding: 5px 9px;
        }

        .btn-sm {
            padding: 6px 16px;
            font-size: 0.875rem;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: start;
            margin-top: 20px;
        }

        .select2-selection__clear {
            font-size: 1.3rem !important;
            height: 100%;
            display: flex;
            align-items: center;
        }

        img.rounded-circle {
            border: 2px solid #ccc;
            margin-top: 5px;
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

<body class="bg-light">

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
                    <li class="nav-item"><a class="nav-link text-white" href="../../admin/admin.php">INICIO</a></li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#"
                            data-bs-toggle="dropdown">Productividad</a>
                        <ul class="dropdown-menu" data-bs-auto-close="outside">

                            <!-- Consulta externa -->
                            <li>
                                <a class="dropdown-item dropdown-submenu-toggle">Consulta externa</a>
                                <ul class="dropdown-submenu">
                                    <li><a class="dropdown-item" href="../../productividad/unidades_que_reportan.php">Productividad total</a></li>
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

                    <li class="nav-item">
                        <a class="nav-link text-white" href="../../normatividad/normatividad_inicio.php">Normatividad</a>
                    </li>

                    <li class="nav-item"><a class="nav-link text-white" href="../../admin/usuariosAdmin.php">Usuario</a>
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
                            <li><a class="dropdown-item" href="../../admin/usuariosAdmin.php"><i
                                        class="fas fa-id-badge me-2"></i>Perfil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="../../admin/logout.php"><i
                                        class="fas fa-sign-out-alt me-2"></i>Cerrar sesión</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>



    <div class="container form-container bg-white rounded shadow-sm">
        <h4 class="mb-3 text-center">Editar Personal</h4>
        <form action="../../../controladores/PersonalController.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="a" value="Actualizar">
            <input type="hidden" name="id" value="<?= base64_encode($datos[0]) ?>">

            <div class="row mb-2">
                <div class="col-md-4">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control"
                        value="<?= htmlspecialchars($datos[1]) ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="apaterno" class="form-label">Apellido Paterno</label>
                    <input type="text" name="apaterno" id="apaterno" class="form-control"
                        value="<?= htmlspecialchars($datos[2]) ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="amaterno" class="form-label">Apellido Materno</label>
                    <input type="text" name="amaterno" id="amaterno" class="form-control"
                        value="<?= htmlspecialchars($datos[3]) ?>" required>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-6">
                    <label for="area" class="form-label">Área</label>
                    <input type="text" name="area" id="area" class="form-control"
                        value="<?= htmlspecialchars($datos[4]) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="puesto" class="form-label">Puesto</label>
                    <input type="text" name="puesto" id="puesto" class="form-control"
                        value="<?= htmlspecialchars($datos[5]) ?>" required>
                </div>
            </div>

            <div class="mb-2">
                <label for="jefe_id" class="form-label">Jefe Directo</label>
                <select name="jefe_id" class="form-select js-jefe-select" style="font-size: 0.9rem; padding: 5px 9px;">
                    <option value="">-- Ninguno --</option>
                    <?php foreach (Personal::listar() as $jefe): ?>
                        <?php if ($jefe[0] != $datos[0]): ?>
                            <option value="<?= $jefe[0] ?>" <?= ($datos[10] ?? '') == $jefe[0] ? 'selected' : '' ?>>
                                <?= $jefe[1] . ' ' . ($jefe[2] ?? '') . ' ' . ($jefe[3] ?? '') ?>
                                (<?= $jefe[4] ?>)
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row mb-2">
                <div class="col-md-4">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" name="telefono" id="telefono" class="form-control"
                        value="<?= htmlspecialchars($datos[6]) ?>" required maxlength="10">
                </div>
                <div class="col-md-4">
                    <label for="extension" class="form-label">Extensión</label>
                    <input type="text" name="extension" id="extension" class="form-control"
                        value="<?= htmlspecialchars($datos[7]) ?>">
                </div>
                <div class="col-md-4">
                    <label for="correo" class="form-label">Correo</label>
                    <input type="email" name="correo" id="correo" class="form-control"
                        value="<?= htmlspecialchars($datos[8]) ?>" required>
                </div>
            </div>

            <div class="mb-2">
                <label for="foto" class="form-label">Cambiar Foto (opcional)</label>
                <input type="file" name="foto" id="foto" class="form-control">
                <input type="hidden" name="foto_actual" value="<?= htmlspecialchars($datos[9]) ?>">
                <?php if (!empty($datos[9])): ?>
                    <div class="mt-2">
                        <small>Foto actual:</small><br>
                        <img src="../../../img/<?= htmlspecialchars($datos[9]) ?>" width="130" height="110"
                            class="rounded-circle border shadow-sm">
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" name="a" value="Editar" class="btn btn-primary btn-sm">Actualizar</button>
                <a href="personal.php" class="btn btn-secondary btn-sm">Cancelar</a>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer>
        <p>Derechos reservados &copy; IMSS 2025</p>
    </footer>

    <script src="../../../js/bootstrap.bundle.min.js"></script>
    <script src="../../../js/jquery-3.7.1.min.js"></script>
    <!-- Select2 (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            $('.js-jefe-select').select2({
                placeholder: "-- Ninguno --",
                allowClear: true,
                width: '100%'
            });

            $('.js-jefe-select').on('select2:open', function() {
                setTimeout(() => {
                    let input = document.querySelector(
                        '.select2-container input.select2-search__field');
                    // Aquí podrías agregar validaciones si lo deseas
                }, 100);
            });
        });
    </script>

</body>

</html>
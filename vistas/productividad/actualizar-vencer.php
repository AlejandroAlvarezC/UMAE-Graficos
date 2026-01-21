<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['login_error'] = "Debes iniciar sesión como administrador para acceder a esa sección.";
    header('Location: ../admin/login.php');
    exit();
}

require_once '../../modelos/conexion.php';

if (isset($_GET['action']) && $_GET['action'] === 'filtrar') {
    // Petición AJAX para obtener opciones filtradas

    $anio = $_GET['anio'] ?? '';
    $folio = $_GET['folio'] ?? '';
    $evento = $_GET['evento'] ?? '';

    $conexion = new Conexion();
    $where = [];

    if ($anio !== '' && $anio !== 'ninguno') $where[] = "anio = " . intval($anio);
    if ($folio !== '' && $folio !== 'ninguno') $where[] = "folio = '" . mysqli_real_escape_string($conexion->getConexion(), $folio) . "'";
    if ($evento !== '' && $evento !== 'ninguno') $where[] = "evento = '" . mysqli_real_escape_string($conexion->getConexion(), $evento) . "'";

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $sql = "SELECT DISTINCT folio, evento, anio FROM vencer $whereSQL";
    $resultado = $conexion->consultar($sql);
    $conexion->cerrar();

    $folios = [];
    $eventos = [];
    $anios = [];

    foreach ($resultado as $fila) {
        if (!in_array($fila[0], $folios)) $folios[] = $fila[0];
        if (!in_array($fila[1], $eventos)) $eventos[] = $fila[1];
        if (!in_array($fila[2], $anios)) $anios[] = $fila[2];
    }

    header('Content-Type: application/json');
    echo json_encode(['folios' => $folios, 'eventos' => $eventos, 'anios' => $anios]);
    exit;
}

// Si no es petición AJAX, carga las opciones completas para el formulario
$conexion = new Conexion();
$consulta = $conexion->consultar('SELECT DISTINCT folio, evento, anio FROM vencer');
$conexion->cerrar();

$opcionesFolio = [];
$opcionesEvento = [];
$opcionesAnio = [];

$optionsFolio = "";
$optionsEvento = "";
$optionsAnio = "";

if (!empty($consulta)) {
    foreach ($consulta as $fila) {
        if (!in_array($fila[0], $opcionesFolio)) {
            $optionsFolio .= "<option value='{$fila[0]}'>{$fila[0]}</option>";
            $opcionesFolio[] = $fila[0];
        }
        if (!in_array($fila[1], $opcionesEvento)) {
            $optionsEvento .= "<option value='{$fila[1]}'>{$fila[1]}</option>";
            $opcionesEvento[] = $fila[1];
        }
        if (!in_array($fila[2], $opcionesAnio)) {
            $optionsAnio .= "<option value='{$fila[2]}'>{$fila[2]}</option>";
            $opcionesAnio[] = $fila[2];
        }
    }
} else {
    $optionsFolio = "<option value=''>No hay registros</option>";
    $optionsEvento = "<option value=''>No hay registros</option>";
    $optionsAnio = "<option value=''>No hay registros</option>";
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar datos | VENCER | Administrador</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../../logo-imss.png">
    <style>
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
    </style>
</head>

<body style="background-color: #fffef6ff">


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
                                    <li><a class="dropdown-item" href="./unidades_que_reportan.php">Productividad Total</a></li>
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
                            <li><a class="dropdown-item" href="./urgencias_inicio.php">Urgencias</a></li>

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
                    <a href="./paramedicos.php" class="btn btn btn-outline-light"><i class="bi bi-arrow-left"
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
        </div>
    </nav>




    <div class="content">
        <div class="container my-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-dark text-white rounded-top-4">
                    <h4 class="mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i> Reemplazar Registros - VENCER</h4>
                </div>

                <div class="card-body">
                    <!-- Alerta informativa -->
                    <div class="alert alert-warning d-flex align-items-center mb-4 rounded-3 shadow-sm">
                        <i class="bi bi-exclamation-circle-fill me-3 fs-4"></i>
                        <div>
                            <strong>Atención:</strong> Esto eliminará los registros actuales y los reemplazará con los del nuevo archivo cargado, según los filtros seleccionados.
                        </div>
                    </div>

                    <!-- Alertas dinámicas flotantes -->
                    <div id="alert-container" style="position: fixed; top: 20px; right: 20px; z-index: 1050;"></div>

                    <form id="form-reemplazo-vencer" enctype="multipart/form-data" method="post">
                        <input type="hidden" name="a" value="ReemplazarCSVFiltrado">

                        <!-- Filtros -->
                        <div class="row g-4 mb-2">

                            <div class="col-md-4">
                                <label for="filtro_evento" class="form-label fw-semibold">Evento (opcional)</label>
                                <select name="filtro_evento" id="filtro_evento" class="form-select rounded-3 shadow-sm">
                                    <option value="ninguno">Todos</option>
                                    <?= $optionsEvento ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="filtro_anio" class="form-label fw-semibold">Año <span class="text-danger">*</span></label>
                                <select name="filtro_anio" id="filtro_anio" class="form-select rounded-3 shadow-sm">
                                    <option value="ninguno">Todos</option>
                                    <?= $optionsAnio ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="filtro_folio" class="form-label fw-semibold">Folio (opcional)</label>
                                <select name="filtro_folio" id="filtro_folio" class="form-select rounded-3 shadow-sm">
                                    <option value="ninguno">Todos</option>
                                    <?= $optionsFolio ?>
                                </select>
                            </div>

                        </div>

                        <!-- Archivo CSV + Botón -->
                        <div class="row align-items-end g-3 mt-3">
                            <div class="col-md-9">
                                <label for="csv" class="form-label fw-semibold">Archivo CSV</label>
                                <input type="file" name="csv" id="csv" class="form-control rounded-3 shadow-sm" accept=".csv" required>
                            </div>
                            <div class="col-md-3 text-end">
                                <button type="submit" class="btn btn-urgencia w-100 py-2 shadow-sm">
                                    <i class="bi bi-upload me-1"></i> Actualizar solo coincidencias
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Resultado -->
                    <div id="resultado-reemplazo-vencer" class="mt-4"></div>
                </div>

                <div class="card-footer text-end bg-light rounded-bottom-4">
                    <a href="./vencer.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <br><br><br>

    <!-- Footer -->
    <footer class="text-white text-center py-3 mt-5" style="background-color: #7a123a;">
        <div class="container">
            <p class="mb-0">© <?php echo date("Y"); ?> IMSS. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Scripts -->

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>

    <script src="../../js/scripts.js" defer></script>
    <script src="../../js/bootstrap.bundle.min.js" defer></script>


    <script>
        document.getElementById('form-reemplazo-vencer').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('../../controladores/vencer.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    const contenedor = document.getElementById('resultado-reemplazo-vencer');
                    contenedor.innerHTML = '';

                    const alert = document.createElement('div');
                    alert.className = `alert alert-${data.status === 'success' ? 'success' : 'danger'}`;

                    if (data.status === 'success' && data.resumen) {
                        alert.innerHTML = `
                <strong>${data.message}</strong><br>
                Registros actualizados: ${data.resumen.actualizados}<br>
                Sin coincidencias con filtros: ${data.resumen.sinCoincidencia}<br>
                Filas con formato inválido: ${data.resumen.malFormateadas}
            `;

                        contenedor.appendChild(alert);

                        setTimeout(() => {
                            window.location.href = './vencer.php';
                        }, 5000); // Redirige tras 4 segundos
                    } else {
                        alert.textContent = data.message || 'Ocurrió un error';
                        contenedor.appendChild(alert);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const contenedor = document.getElementById('resultado-reemplazo-vencer');
                    contenedor.innerHTML = '<div class="alert alert-danger">Error al procesar el archivo.</div>';
                });
        });
    </script>


    <script>
        function actualizarFiltros() {
            const anio = document.getElementById('filtro_anio').value;
            const folio = document.getElementById('filtro_folio').value;
            const evento = document.getElementById('filtro_evento').value;

            const params = new URLSearchParams({
                action: 'filtrar',
                anio,
                folio,
                evento
            });

            fetch(`?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    const folioSelect = document.getElementById('filtro_folio');
                    const eventoSelect = document.getElementById('filtro_evento');
                    const anioSelect = document.getElementById('filtro_anio');

                    // Actualiza opciones sin perder el valor seleccionado actual
                    const selFolio = folioSelect.value;
                    const selEvento = eventoSelect.value;
                    const selAnio = anioSelect.value;

                    folioSelect.innerHTML = '<option value="ninguno">Ninguno</option>';
                    data.folios.forEach(f => {
                        folioSelect.innerHTML += `<option value="${f}">${f}</option>`;
                    });
                    if (data.folios.includes(selFolio)) folioSelect.value = selFolio;

                    eventoSelect.innerHTML = '<option value="ninguno">Ninguno</option>';
                    data.eventos.forEach(e => {
                        eventoSelect.innerHTML += `<option value="${e}">${e}</option>`;
                    });
                    if (data.eventos.includes(selEvento)) eventoSelect.value = selEvento;

                    anioSelect.innerHTML = '<option value="ninguno">Ninguno</option>';
                    data.anios.forEach(a => {
                        anioSelect.innerHTML += `<option value="${a}">${a}</option>`;
                    });
                    if (data.anios.includes(selAnio)) anioSelect.value = selAnio;
                });
        }

        document.getElementById('filtro_anio').addEventListener('change', actualizarFiltros);
        document.getElementById('filtro_folio').addEventListener('change', actualizarFiltros);
        document.getElementById('filtro_evento').addEventListener('change', actualizarFiltros);
    </script>


    <script>
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
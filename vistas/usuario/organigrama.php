<?php require_once '../../modelos/Personal.php';
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../../logo-imss.png">
    <title>UMAE-48 | Organigrama</title>

</head>
<style>
    html,
    body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        min-height: 100vh;
        display: flex;
        flex-direction: column;

    }

    .content {
        flex: 1 0 auto;
        display: flex;
        flex-direction: column;
        min-height: 0;

    }

    #organigrama-container {
        padding: 10px;
        overflow-x: auto;
    }

    .centrar-organigrama {
        margin-left: auto;
        margin-right: auto;
        width: max-content;

    }

    .nodo {
        text-align: center;
        margin: 10px;
        position: relative;
    }

    .card {
        background-color: #ffffff;
        border: 1px solid #dee2e6;
        padding: 10px;
        min-width: 220px;
        max-width: 280px;
        margin: 0 auto;
        border-radius: 8px;
        box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        transition: transform 0.2s;
    }

    .card:hover {
        background-color: #f4f7f6;
        transform: scale(1.03);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease-in-out, box-shadow 0.2s ease-in-out;
        cursor: pointer;
    }

    .card strong {
        font-size: 1.1rem;
        color: #004d3f;
    }

    .card img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 45%;
        border: 1.5px solid #ccc;
    }

    .card small {
        font-size: 1em;
        color: #444;
    }

    .card strong {
        font-size: 1.2rem;
        margin: 0;
    }

    .card .puesto {
        font-size: 0.9em;
        color: #666;
        margin: 0;
    }

    .card .extension {
        color: #333;
        font-size: 1em;
    }

    .card .correo {
        font-size: 0.95em;
    }

    .card .correo:hover {
        font-weight: bold;
    }


    .hijos {
        display: flex;
        margin: 20px auto 0 auto;
        /* Cambia de margin-top a margin: 20px auto 0 auto */
        flex-wrap: nowrap;
        width: max-content;
        /* Cambia de 100% a max-content */
        justify-content: center;
        /* Cambia a center */
        margin-top: 20px;
        position: relative;
        min-width: 0;
    }


    .nodo::after {
        content: '';
        position: absolute;
        top: -15px;
        left: 50%;
        width: 2px;
        height: 15px;
        background: #999;
    }

    .hijos::before {
        content: '';
        position: absolute;
        top: -15px;
        left: 0;
        right: 0;
        height: 2px;
        background: #999;
        width: 100%;
    }

    .hijos .nodo::before {
        content: '';
        position: absolute;
        top: -15px;
        left: 50%;
        width: 2px;
        height: 2px;
        background: #999;

    }

    .hijos-vertical {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: max-content;
        margin: 20px auto 0 auto;
        position: relative;
    }

    .linea-Horizontal {
        width: 100%;
        height: 2px;
        /* Ajusta según separación */
        background: #999;
        margin: 0 auto 10px auto;
    }

    .hijos,
    .hijos-vertical {

        opacity: 1;
        margin-top: 20px;
        display: flex;
        transition: max-height 0.6s ease, opacity 0.6s ease, margin-top 0.5s ease;
    }

    .hijos.colapsando,
    .hijos-vertical.colapsando {
        max-height: 0;
        opacity: 0;
        margin-top: 0;
    }

    .hijos.oculto-display,
    .hijos-vertical.oculto-display {
        display: none !important;
    }

    /* ----------------------------------------------
           Footer
        ------------------------------------------------*/

    footer {
        background-color: #00664d;
        width: 100%;
        text-align: center;
        color: white;
        font-weight: bold;
        padding-top: 10px;
    }

    /* ----------------------------------------------
           Dropdown submenu y navegación
        ------------------------------------------------*/

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
        background-color: #00664d !important;
        color: white;
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

    /* -- SIDEBAR -- */
    /* ========================
   SIDEBAR estilo personalizado
   ======================== */
    #sidebar {
        background-color: #004d40;
        /* Verde oscuro */
        padding: 1rem;
        height: 100%;
        color: white;
        width: 270px;
        min-width: 0;
        flex-shrink: 0;
        overflow-y: auto;
        transition: margin-left 0.3s, width 0.3s;
    }

    #sidebar h5 {
        font-size: 1.1rem;
        font-weight: bold;
        margin-bottom: 15px;
        color: white;
    }

    /* Botones de área */
    #sidebar .btn {
        background-color: transparent;
        color: white;
        border: none;
        text-align: left;
        font-size: 1rem;
        padding: 5px 10px;
        margin-bottom: 3px;
        border-radius: 0;
        transition: background-color 0.3s ease;
        width: 100%;
    }

    #sidebar .btn:hover,
    #sidebar .btn:focus {
        background-color: #356a64ff;
        /* Hover más claro */
        color: white;
    }

    #sidebar .btn.btn-primary {
        background-color: #367a72ff;
        /* Botón activo */
        font-weight: bold;
        color: white;
    }

    #sidebar.oculto {
        margin-left: -270px;
        /* Oculta el sidebar */
        width: 260px;
        min-width: 0;
        padding: 0;
        overflow: hidden;
    }

    .flex-main {
        display: flex;
        align-items: flex-start;
        width: 100%;
        flex: 1 1 auto;
        min-height: 0;

    }

    .flex-main.sidebar-oculto main {
        width: 100%;
        flex-grow: 1;
    }

    /* Submenú de vistas */
    .subvista-wrapper {
        margin-left: 10px;
    }

    .subvista-wrapper button {
        background-color: transparent;
        color: white;
        border: 1px solid white;
        font-size: 0.85rem;
        margin-top: 3px;
        margin-right: 4px;
        border-radius: 0;
        padding: 5px 8px;
        transition: background-color 0.2s ease;
    }

    .subvista-wrapper button:hover {
        background-color: #00968729
    }

    .subvista-wrapper button.active {
        background-color: #00968729 !important;
        color: white;
        font-weight: bold;
        border-color: #009688;
    }

    #toggle-sidebar {
        transition: left 0.3s;
    }
</style>

<body>
    <div class="content">

        <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #00664d;">

            <div class="container-fluid">

                <!-- Logo para pantallas grandes (antes del enlace "Inicio") -->
                <a href="../roles/index.php" class="me-2 d-none d-lg-block">
                    <img src="../../img/umae-48.jpg" alt="Logo UMAE" height="80" class="rounded-circle">
                </a>


                <a class="navbar-brand" href="../roles/index.php">Inicio</a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                    aria-controls="menuPrincipal" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="menuPrincipal">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                        <!-- PRODUCTIVIDAD -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#"
                                data-bs-toggle="dropdown">Productividad</a>
                            <ul class="dropdown-menu" data-bs-auto-close="outside">

                                <!-- Consulta externa -->
                                <li>
                                    <a class="dropdown-item dropdown-submenu-toggle">Consulta externa</a>
                                    <ul class="dropdown-submenu">
                                        <li><a class="dropdown-item" href="./unidadesreportan_user.php">Productividad Total</a></li>
                                        <li><a class="dropdown-item" href="./paramedicos_user.php">Paramédicos</a>
                                        </li>

                                        <!-- Especialidades -->
                                        <li>
                                            <a class="dropdown-item dropdown-submenu-toggle">Especialidades</a>
                                            <ul class="dropdown-submenu">
                                                <li><a class="dropdown-item"
                                                        href="./especialidades_user.php">Especialidades</a>
                                                </li>
                                                <li><a class="dropdown-item"
                                                        href="./EspecilidadOcas_user.php">Especialidad de
                                                        ocasión</a></li>
                                            </ul>
                                        </li>

                                    </ul>
                                </li>

                                <!-- Hospitalización -->
                                <li>
                                    <a class="dropdown-item dropdown-submenu-toggle">Hospitalización</a>
                                    <ul class="dropdown-submenu">
                                        <li><a class="dropdown-item" href="./ingresos_user.php">Ingresos</a></li>
                                        <li><a class="dropdown-item" href="./egresos_user.php">Egresos</a></li>
                                        <li><a class="dropdown-item" href="./paciente_user.php">Días Paciente</a></li>
                                        <li><a class="dropdown-item" href="#">Días Cama</a></li>
                                    </ul>
                                </li>

                                <!-- Cirugía -->
                                <li><a class="dropdown-item" href="./cirugia_user.php">Cirugía</a></li>

                                <!-- Urgencias -->
                                <li><a class="dropdown-item" href="./urgencias_user.php">Urgencias</a></li>

                            </ul>
                        </li>

                        <!-- Organigrama -->
                        <li class="nav-item">
                            <a class="nav-link text-white" href="./organigrama.php">Organigrama</a>
                        </li>

                        <!-- Normatividad -->
                        <li class="nav-item">
                            <a class="nav-link text-white" href="../usuario/normatividad_user.php">Normatividad</a>
                        </li>

                        <!-- Vencer -->
                        <li class="nav-item">
                            <a class="nav-link text-white" href="./vencer_user.php">Vencer</a>
                        </li>

                        <!-- Sitios de interés -->
                        <li class="nav-item">
                            <a class="nav-link text-white" href="./sitios_interes-user.php">Sitios de interés</a>
                        </li>
                    </ul>



                    <!-- Logo centrado SOLO para móviles -->
                    <div class="d-block d-lg-none w-100 text-center my-2">
                        <a href="../roles/index.php">
                            <img src="../../img/umae-48.jpg" alt="Logo UMAE" height="60" class="rounded-circle">
                        </a>
                    </div>



                    <!-- Sesión -->
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <?php if (isset($_SESSION['admin_name'])): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-white" href="../admin/admin.php"
                                    data-bs-toggle="dropdown">
                                    Ir a Panel de <?php echo $_SESSION['admin_name']; ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="../admin/admin.php">
                                            <i class="bi bi-speedometer2 me-1"></i>Volver al panel</a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="../admin/logout.php">
                                            <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión</a>
                                    </li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <div class="d-flex align-items-center gap-2">
                                <a class="btn btn-outline-light" onclick="anterior()" href="#" title="Atrás"><i class="bi bi-arrow-left"></i></a>
                                <a class="btn btn-outline-light" href="../admin/login.php">Iniciar Sesión</a>
                            </div>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>






        <!-- Dentro de tu .container-fluid d-flex flex-grow-1 -->
        <!-- Contenedor principal con flex -->
        <div class="flex-main" id="main-flex" style="position: relative;">
            <!-- Sidebar -->
            <aside id="sidebar" class="p-3">
                <h5 style="margin-bottom: 5px;">Filtrar por área</h5>
                <div class="btn-group-vertical w-100" role="group" id="btn-puestos">
                    <!-- Botones se insertan dinámicamente aquí -->
                </div>
            </aside>
            <!-- Botón para ocultar/mostrar el sidebar, alineado al nivel del título -->
            <button id="toggle-sidebar" class="btn btn-outline-secondary ms-1 " type="button"
                style="position: absolute; top: 10px; left: 275px; height: 38px; width: 39px; padding: 0;">
                <i class="bi bi-list"></i>
            </button>
            <!-- Contenido principal -->
            <main class="flex-grow-1 p-3" style="overflow-x: auto;">
                <h2 class="text-center mb-4" id="titulo-organigrama">Organigrama - UMAE</h2>
                <div id="organigrama-container">
                    <div id="organigrama"></div>
                </div>
            </main>
        </div>


    </div>

    <footer>
        <p>Derechos reservados &copy; IMSS 2025</p>
    </footer>

    <script src="../../js/bootstrap.bundle.min.js"></script>
    <script src="../../js/jquery-3.7.1.min.js"></script>

    <script>
        // ...dentro del <script>...

        let datosOrganigrama = [];
        let vistaAlternativa = null;
        let areaSeleccionada = null; // NUEVO: para recordar el área activa

        const CONFIG_VISTAS = {
            "direccion umae": {
                vista1: "Divisiones",
                vista2: "Direcciones"
            },
            "direccion u.m.a.e": {
                vista1: "Divisiones",
                vista2: "Direcciones"
            },
            "direccion de la umae": {
                vista1: "Divisiones",
                vista2: "Direcciones"
            },
            "direccion medica": {
                vista1: "Vista 1",
                vista2: "Vista 2"
            }
            // Agrega más si necesitas
        };

        function normalizarTexto(texto) {
            return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
        }

        // Ya no se usa filtrarPorPuesto, pero la dejamos por si la necesitas en el futuro
        function filtrarPorPuesto(nombrePuesto) {
            // ...tu código actual...
        }

        // NUEVA función para filtrar por área y mostrar botones de vista
        function filtrarPorArea(nombreArea) {
            areaSeleccionada = nombreArea;
            const contenedor = document.getElementById('organigrama');
            contenedor.innerHTML = '';

            const areaNormalizada = normalizarTexto(nombreArea);
            const configVistas = CONFIG_VISTAS[areaNormalizada];

            if (configVistas) {

                contenedor.classList.add('centrar-organigrama');
                if (!vistaAlternativa) vistaAlternativa = Object.keys(configVistas)[0];


            } else {
                vistaAlternativa = null;
                contenedor.classList.remove('centrar-organigrama');
            }

            const nodosRaiz = datosOrganigrama.filter(p =>
                normalizarTexto(p.area) === areaNormalizada &&
                (
                    p.jefe_id === null ||
                    p.jefe_id === 0 ||
                    !datosOrganigrama.some(j => j.id === p.jefe_id && normalizarTexto(j.area) === areaNormalizada)
                )
            );

            if (!nodosRaiz.length) {
                contenedor.innerHTML = "<p class='text-danger'>No se encontró personal en el área indicada.</p>";
                return;
            }

            nodosRaiz.forEach(nodoRaiz => {
                nodoRaiz.hijos = construirArbol(datosOrganigrama, nodoRaiz.id, 2);
            });

            renderizarArbol(nodosRaiz, contenedor, 1, nodosRaiz[0]?.area);
            document.querySelectorAll('#organigrama > .nodo').forEach(n => n.classList.remove('oculto'));
            document.getElementById("titulo-organigrama").textContent = `Organigrama - ${nombreArea}`;
        }

        function construirArbol(data, jefe_id = null, profundidad = 2, nivel = 1) {
            if (nivel > profundidad) return [];

            let hijos = data.filter(p => p.jefe_id == jefe_id);

            // Filtrado por vistas específicas según área seleccionada
            const areaActiva = normalizarTexto(areaSeleccionada || '');

            if (areaActiva === 'direccion umae') {
                if (vistaAlternativa === 'vista1') {
                    hijos = hijos.filter(h =>
                        normalizarTexto(h.area) !== 'direccion de enfermeria' &&
                        normalizarTexto(h.area) !== 'direccion medica' &&
                        normalizarTexto(h.area) !== 'direccion de educacion e investigacion en salud' &&
                        normalizarTexto(h.area) !== 'direccion administrativa'
                    );
                } else if (vistaAlternativa === 'vista2') {
                    hijos = hijos.filter(h =>
                        normalizarTexto(h.area) === 'direccion medica' ||
                        normalizarTexto(h.area) === 'direccion de enfermeria' ||
                        normalizarTexto(h.area) === 'direccion de educacion e investigacion en salud' ||
                        normalizarTexto(h.area) === 'direccion administrativa'
                    );
                }
            }

            // NUEVO: lógica personalizada para Dirección Médica
            else if (areaActiva === 'direccion medica') {
                if (vistaAlternativa === 'vista1') {
                    // Mostrar sólo puestos que sean jefaturas o coordinación
                    hijos = hijos.filter(h =>
                        normalizarTexto(h.area) !== 'departamento de anestesiologia e inhaloterapia' &&
                        normalizarTexto(h.area) !== 'oficina de trabajo social' &&
                        normalizarTexto(h.area) !== 'oficina de nutricion y dietetica' &&
                        normalizarTexto(h.area) !== 'oficina de informacion medica y archivo clinico'
                    );
                } else if (vistaAlternativa === 'vista2') {
                    // Mostrar TODO el personal de la Dirección Médica sin filtrar por puesto
                    hijos = hijos.filter(h =>
                        normalizarTexto(h.area) === 'departamento de anestesiologia e inhaloterapia' ||
                        normalizarTexto(h.area) === 'oficina de trabajo social' ||
                        normalizarTexto(h.area) === 'oficina de nutricion y dietetica' ||
                        normalizarTexto(h.area) === 'oficina de informacion medica y archivo clinico'
                    );
                }
            }

            return hijos.map(p => ({
                ...p,
                hijos: construirArbol(data, p.id, profundidad, nivel + 1)
            }));
        }


        function renderizarArbol(nodos, contenedor, nivel = 1, areaRaiz = null) {
            nodos.forEach(nodo => {
                const div = document.createElement('div');
                div.classList.add('nodo');

                const rutaFoto = nodo.foto ? `../../img/${nodo.foto}` : '../../img/default.png';

                div.innerHTML = `
       <div class="card" onclick="toggleHijos(this)">
            <img src="${rutaFoto}" alt="Foto de ${nodo.nombre}">
            <strong style=" margin: 5px 0;">${nodo.nombre} ${nodo.apaterno ?? ''} ${nodo.amaterno ?? ''}</strong>
            <small style="font-weight: bold; margin: 5px 0 0 0;">${nodo.puesto}</small>
            <small>📞 ${nodo.telefono} <strong class="extension" >Ext.</strong>${nodo.extension}</small>
            <small class="correo">✉️ ${nodo.correo}</small>
        </div>
        `;

                if (nodo.hijos && nodo.hijos.length > 0) {
                    const hijosDiv = document.createElement('div');

                    const esAreaConLinea = (
                        (normalizarTexto(areaRaiz || nodo.area) === 'direccion administrativa' ||
                            normalizarTexto(areaRaiz || nodo.area) === 'direccion medica') &&
                        nivel === 2
                    );

                    if (esAreaConLinea) {
                        hijosDiv.classList.add('hijos-vertical');


                        if (nodo.hijos.length > 0) {
                            const linea = document.createElement('div');
                            linea.className = 'linea-Horizontal';
                            hijosDiv.appendChild(linea);
                        }
                    } else {
                        hijosDiv.classList.add('hijos');
                    }

                    renderizarArbol(nodo.hijos, hijosDiv, nivel + 1, areaRaiz || nodo.area);
                    div.appendChild(hijosDiv);
                }
                contenedor.appendChild(div);
            });
        }

        // MODIFICADO: ahora también funciona para área
        function cambiarVista(nombreVista) {
            vistaAlternativa = nombreVista;

            // Si hay un área seleccionada, re-filtra por área
            if (areaSeleccionada) {
                filtrarPorArea(areaSeleccionada);

                // Elimina subvista anterior en todos los wrappers
                document.querySelectorAll('.subvista-wrapper').forEach(el => el.remove());

                // Vuelve a mostrar el submenú solo en el wrapper activo
                const btns = document.querySelectorAll('#btn-puestos .btn');
                btns.forEach(btn => {
                    if (btn.classList.contains('btn-primary')) {
                        const wrapper = btn.parentElement;
                        mostrarSubmenuVista(areaSeleccionada, wrapper);
                    }
                });
            }
        }

        const AREAS_PERMITIDAS = [
            'direccion umae',
            'direccion u.m.a.e',
            'direccion de la umae',
            'direccion medica',
            'direccion administrativa',
            'direccion de enfermeria',
            'direccion enfermeria',
            'direccion de educacion e investigacion en salud',
            // Agrega aquí los nombres normalizados de las áreas que SÍ quieres mostrar
        ];

        fetch('../../controladores/PersonalController.php?a=OrganigramaJerarquico')
            .then(res => res.json())
            .then(data => {
                data.forEach(p => {
                    p.jefe_id = (p.jefe_id === null || p.jefe_id === "null" || p.jefe_id === "") ? null :
                        Number(p.jefe_id);
                    p.id = Number(p.id);
                });

                datosOrganigrama = data;

                const contenedorBotones = document.getElementById('btn-puestos');

                // Filtrar solo áreas permitidas y con al menos un nodo raíz que tenga hijos
                const areasConHijos = [...new Set(
                    datosOrganigrama
                    .filter(p => {
                        const areaNormalizada = normalizarTexto(p.area);
                        const esRaiz = (
                            p.jefe_id === null ||
                            p.jefe_id === 0 ||
                            !datosOrganigrama.some(j => j.id === p.jefe_id && normalizarTexto(j
                                .area) === areaNormalizada)
                        );
                        if (!esRaiz) return false;
                        // ¿Tiene hijos y está permitida?
                        const tieneHijos = datosOrganigrama.some(h => h.jefe_id === p.id);
                        return tieneHijos && AREAS_PERMITIDAS.includes(areaNormalizada);
                    })
                    .map(p => p.area)
                )];

                areasConHijos.forEach(area => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'area-wrapper mb-2';

                    const btn = document.createElement('button');
                    btn.className = 'btn btn-outline-primary w-100 text-start';
                    btn.textContent = area;
                    btn.onclick = () => {
                        document.querySelectorAll('#btn-puestos .btn').forEach(b => b.classList.remove(
                            'btn-primary'));
                        btn.classList.add('btn-primary');
                        filtrarPorArea(area);

                        // Elimina subvista anterior en todos los wrappers
                        document.querySelectorAll('.subvista-wrapper').forEach(el => el.remove());

                        // Agrega subvista solo en el wrapper actual
                        mostrarSubmenuVista(area, wrapper);
                    };

                    wrapper.appendChild(btn);
                    contenedorBotones.appendChild(wrapper);
                });

                // Mostrar el primero automáticamente
                if (areasConHijos.length > 0) {
                    document.querySelectorAll('#btn-puestos .btn')[0].click();
                }
            })
            .catch(error => {
                console.error("Error al cargar el organigrama:", error);
                document.getElementById('organigrama').innerHTML =
                    "<p class='text-danger'>Error al cargar el organigrama.</p>";
            });

        function toggleHijos(cardElement) {
            const nodo = cardElement.parentElement;
            const hijos = nodo.querySelector('.hijos, .hijos-vertical');
            if (!hijos) return;

            if (hijos.classList.contains('oculto-display')) {
                hijos.classList.remove('oculto-display');
                // Forzar reflow para que detecte el cambio
                void hijos.offsetHeight;
                hijos.classList.remove('colapsando');
            } else {
                hijos.classList.add('colapsando');
                setTimeout(() => {
                    hijos.classList.add('oculto-display');
                }, 400); // Tiempo de transición
            }
        }

        function mostrarSubmenuVista(area, wrapper) {
            // Elimina cualquier submenú existente SOLO en el wrapper actual
            wrapper.querySelectorAll('.subvista-wrapper').forEach(el => el.remove());

            const areaNormalizada = normalizarTexto(area);
            let configVistas = null;

            if (areaNormalizada === 'direccion umae' || areaNormalizada === 'direccion u.m.a.e' || areaNormalizada ===
                'direccion de la umae') {
                configVistas = {
                    vista1: "Divisiones",
                    vista2: "Direcciones"
                };
            } else if (areaNormalizada === 'direccion medica') {
                configVistas = {
                    vista1: "Vista 1",
                    vista2: "Vista 2"
                };
            }

            if (configVistas) {
                let subVistaHTML = `<div class="subvista-wrapper mt-1 ms-3">`;
                Object.entries(configVistas).forEach(([clave, texto]) => {
                    subVistaHTML +=
                        `<button class="btn btn-outline-secondary btn-sm me-1${vistaAlternativa === clave ? ' active' : ''}" data-vista="${clave}">${texto}</button>`;
                });
                subVistaHTML += `</div>`;
                wrapper.insertAdjacentHTML('beforeend', subVistaHTML);

                // Asigna el evento a los botones recién creados
                wrapper.querySelectorAll('.subvista-wrapper button').forEach(btn => {
                    btn.onclick = function() {
                        cambiarVista(this.getAttribute('data-vista'));
                    };
                });
            }
        }


        // SUBMENU DE NAVEGACIÓN -------- //
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

        document.getElementById('toggle-sidebar').onclick = function() {
            const sidebar = document.getElementById('sidebar');
            const mainFlex = document.getElementById('main-flex');
            sidebar.classList.toggle('oculto');
            mainFlex.classList.toggle('sidebar-oculto', sidebar.classList.contains('oculto'));
            this.innerHTML = sidebar.classList.contains('oculto') ?
                '<i class="bi bi-arrow-right-square"></i>' :
                '<i class="bi bi-list"></i>';
            this.style.left = sidebar.classList.contains('oculto') ? '3px' : '275px';
        };
    </script>

</body>

</html>
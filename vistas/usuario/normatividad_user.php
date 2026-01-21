<?php require_once '../../modelos/Normatividad.php';

session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/bootstrap.min.css" defer>
    <link rel="stylesheet" href="../../css/styles.css" defer>
    <link rel="icon" type="image/png" href="../../logo-imss.png">
    <!-- Bootstrap y estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <title>UMAE-48 | Normatividad</title>

    <style>
        /* ----------------------------------------------
   Layout y estructura general
------------------------------------------------*/

        html,
        body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .content {
            flex: 1;
            padding: 15px;
            background-color: #f5f8fb;
        }

        /* Botones modernos y llamativos personalizados */
        .btn-success,
        .btn.btn-success,
        .btn-success:link,
        .btn-success:visited {
            background: linear-gradient(135deg, #28a745, #00664d);
            border: none;
            border-radius: 12px;
            padding: 12px 30px;
            font-weight: 700;
            font-size: 1.1rem;
            color: white !important;
            box-shadow: 0 6px 15px rgba(0, 102, 77, 0.5);
            cursor: pointer;
            transition: background 0.4s ease, box-shadow 0.3s ease, transform 0.15s ease;
            user-select: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            text-align: center;
        }

        .btn-success:hover,
        .btn-success:focus {
            background: linear-gradient(135deg, #00c853, #004d3a);
            box-shadow: 0 8px 25px rgba(0, 204, 83, 0.8);
            transform: scale(1.05);
            outline: none;
        }

        .btn-success:active {
            transform: scale(0.98);
            box-shadow: 0 4px 12px rgba(0, 102, 77, 0.6);
        }

        .btn-success svg,
        .btn-success img {
            width: 20px;
            height: 20px;
        }

        /* 
        ----------------------------------------------------------
        Encabezado azul oscuro  TABLA DE MANUALES
        ---------------------------------------------------------- */
        #tablaManuales {
            width: auto;
            table-layout: auto;
            min-width: 100%;
        }

        #tablaManuales td,
        #tablaManuales th {
            border: 1px solid #007BFF;
            vertical-align: middle;
            white-space: nowrap;
            /* Evita que los textos y botones se partan */
        }

        #tablaManuales thead th {
            background-color: rgb(20, 90, 180);
            color: white;
        }

        #tablaManuales tbody tr:hover {
            background-color: #cce5ff;
        }

        /* Para el td de botones "Archivo" y que no se amontonen */
        #tablaManuales td:nth-child(7) {
            white-space: normal;
            max-width: 180px;
            padding: 0.3rem 0.5rem;
        }

        #tablaManuales td:nth-child(7) a.btn {
            display: block;
            margin-bottom: 6px;
            margin-right: 0;
        }


        #tablaManuales td:nth-child(8) {
            white-space: nowrap;
        }

        #tablaManuales td:nth-child(8) a.btn {
            margin-right: 6px;
            white-space: nowrap;
        }

        .btn-success {
            background-color: #198754;
            border-color: #157347;
        }

        .btn-outline-primary {
            border-color: #1b6ec2;
            color: #1b6ec2;
        }

        .btn-outline-primary:hover {
            background-color: #1b6ec2;
            color: #fff;
        }

        .section-header {
            border-left: 5px solid #7a123a;
            padding-left: 15px;
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
            padding: 10px 0;
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
    </style>
</head>

<body style="background-color: aliceblue;">


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




    <div class="content">

        <h1 style="text-align: center; color:#00664d; font-weight:bold">Normatividad</h1>


        <main class="container my-5">
            <div class="d-flex align-items-center mb-4">
                <div class="ms-auto">
                    <button id="btnExportarPDF" class="btn btn-success">
                        <i class="bi bi-download"></i> Descargar tabla
                    </button>
                </div>
            </div>


            <div class="table-responsive">
                <table id="tablaManuales" class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Normatividad</th>
                            <th>Nombre</th>
                            <th>Año</th>
                            <th>Entidad Emisora</th>
                            <th>Proceso o Servicio</th>
                            <th>Clave</th>
                            <th>Dirección</th>
                            <th>Archivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (Manual::listar() as $manual) { ?>
                            <tr>
                                <td><?= htmlspecialchars(ucfirst($manual[1])) ?></td> <!-- normatividad -->
                                <td><?= htmlspecialchars($manual[2]) ?></td> <!-- nombre -->
                                <td><?= htmlspecialchars($manual[3]) ?></td> <!-- año -->
                                <td><?= htmlspecialchars($manual[4]) ?></td> <!-- entidad -->
                                <td><?= htmlspecialchars($manual[5]) ?></td> <!-- servicio -->
                                <td><?= htmlspecialchars($manual[6]) ?></td> <!-- clave (fecha) -->
                                <td><?= htmlspecialchars($manual[8]) ?></td> <!-- dirección -->
                                <td>
                                    <?php
                                    $rutaArchivo = '../../archivos/manuales/' . $manual[7];
                                    if (!empty($manual[7]) && file_exists($rutaArchivo)): ?>
                                        <a class="btn btn-outline-primary btn-sm" href="<?= htmlspecialchars($rutaArchivo) ?>" target="_blank">Ver PDF</a>
                                        <a class="btn btn-outline-success btn-sm" href="<?= htmlspecialchars($rutaArchivo) ?>" download>Descargar PDF</a>
                                    <?php else: ?>
                                        <span class="text-muted">Sin archivo</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </main>



    </div>



    <footer>
        <p>Derechos reservados &copy; IMSS 2025</p>
    </footer>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.70/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.70/vfs_fonts.js"></script>

    <script>
        function convertirImagenARutaBase64(url, callback) {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.src = url;

            img.onload = function() {
                const canvas = document.createElement('canvas');
                canvas.width = img.width;
                canvas.height = img.height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
                const base64 = canvas.toDataURL();
                callback(base64);
            };

            img.onerror = function() {
                console.error("No se pudo cargar la imagen desde la ruta:", url);
                callback(null);
            };
        }

        document.getElementById("btnExportarPDF").addEventListener("click", function(e) {
            if (!confirm("¿Desea descargar la tabla de manuales?")) {
                e.preventDefault();
                return false;
            }

            convertirImagenARutaBase64('../../img/imss.png', function(logoBase64) {
                const tabla = $('#tablaManuales').DataTable();
                const data = tabla.rows({
                    search: 'applied'
                }).data().toArray();

                const contenido = data.map(row => [{
                        text: row[0],
                        style: 'tableBody'
                    }, // Normatividad
                    {
                        text: row[1],
                        style: 'tableBody'
                    }, // Nombre
                    {
                        text: row[2],
                        style: 'tableBody'
                    }, // Año
                    {
                        text: row[3],
                        style: 'tableBody'
                    }, // Entidad Emisora
                    {
                        text: row[4],
                        style: 'tableBody'
                    }, // Proceso o Servicio
                    {
                        text: row[5],
                        style: 'tableBody'
                    }, // Clave
                    {
                        text: row[6],
                        style: 'tableBody'
                    } // Dirección
                ]);

                const ahora = new Date();
                const fechaStr = ahora.toLocaleDateString('es-MX');
                const horaStr = ahora.toLocaleTimeString('es-MX');

                const docDefinition = {
                    pageMargins: [40, 130, 40, 60],
                    pageOrientation: 'portrait',
                    header: function() {
                        return {
                            margin: [40, 20, 40, 0],
                            stack: [{
                                    columns: [
                                        logoBase64 ? {
                                            image: logoBase64,
                                            width: 60,
                                            height: 60
                                        } : {},
                                        {
                                            width: '*',
                                            alignment: 'center',
                                            stack: [{
                                                    text: 'UNIDAD MÉDICA DE ALTA ESPECIALIDAD',
                                                    style: 'headerTitle'
                                                },
                                                {
                                                    text: 'HOSPITAL DE GINECO - PEDIATRÍA No. 48',
                                                    style: 'headerSubtitle'
                                                },
                                                {
                                                    text: 'LISTADO DE MANUALES',
                                                    style: 'headerSubtitle'
                                                }
                                            ]
                                        },
                                        {
                                            width: 60,
                                            text: ''
                                        }
                                    ]
                                },
                                {
                                    canvas: [{
                                        type: 'line',
                                        x1: 0,
                                        y1: 10,
                                        x2: 520,
                                        y2: 10,
                                        lineWidth: 1
                                    }]
                                }
                            ]
                        };
                    },
                    footer: function(currentPage, pageCount) {
                        return {
                            margin: [40, 0, 40, 20],
                            columns: [{
                                    text: `Fecha: ${fechaStr} ${horaStr}`,
                                    alignment: 'left',
                                    style: 'footerText'
                                },
                                {
                                    text: `Página ${currentPage} de ${pageCount}`,
                                    alignment: 'center',
                                    style: 'footerText'
                                },
                                {
                                    text: 'IMSS - UMAE HGP 48',
                                    alignment: 'right',
                                    style: 'footerText'
                                }
                            ]
                        };
                    },
                    content: [{
                        style: 'tableContent',
                        table: {
                            headerRows: 1,
                            dontBreakRows: true,
                            widths: ['12%', '20%', '10%', '17%', '16%', '8%', '18%'],
                            body: [
                                [{
                                        text: 'Normatividad',
                                        style: 'tableHeader'
                                    },
                                    {
                                        text: 'Nombre',
                                        style: 'tableHeader'
                                    },
                                    {
                                        text: 'Año',
                                        style: 'tableHeader'
                                    },
                                    {
                                        text: 'Entidad Emisora',
                                        style: 'tableHeader'
                                    },
                                    {
                                        text: 'Proceso o Servicio',
                                        style: 'tableHeader'
                                    },
                                    {
                                        text: 'Clave',
                                        style: 'tableHeader'
                                    },
                                    {
                                        text: 'Dirección',
                                        style: 'tableHeader'
                                    }
                                ],
                                ...contenido.map(row => row.map(cell => ({
                                    ...cell,
                                    alignment: 'left'
                                })))
                            ]
                        },
                        layout: {
                            fillColor: function(rowIndex, node, columnIndex) {
                                return rowIndex === 0 ? '#eeeeee' : null;
                            },
                            hLineWidth: () => 0.5,
                            vLineWidth: () => 0.5,
                            hLineColor: () => '#aaa',
                            vLineColor: () => '#aaa'
                        }
                    }],
                    styles: {
                        headerTitle: {
                            fontSize: 13,
                            bold: true
                        },
                        headerSubtitle: {
                            fontSize: 11,
                            margin: [0, 2, 0, 2]
                        },
                        footerText: {
                            fontSize: 9,
                            italics: true
                        },
                        tableHeader: {
                            fontSize: 9,
                            bold: true,
                            fillColor: '#f5f5f5',
                            color: '#333'
                        },
                        tableBody: {
                            fontSize: 8
                        },
                        tableContent: {
                            margin: [0, 10, 0, 0]
                        }
                    },
                    defaultStyle: {
                        fontSize: 8
                    }
                };

                pdfMake.createPdf(docDefinition).download('manuales_filtrados.pdf');
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


    <script>
        $(document).ready(function() {
            const tabla = $('#tablaManuales').DataTable({
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });

        });
    </script>

</body>

</html>
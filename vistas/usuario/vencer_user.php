<?php
// Sin validación de redirección, vista 100% pública
require_once '../../modelos/conexion.php';
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tablero VENCER</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* ========================================= */
        /* LA MAGIA PARA OCULTAR LA TABLA CRUDA      */
        /* Bloquea la orden del JS de mostrarla      */
        /* ========================================= */
        #contenedorTabla {
            display: none !important; 
            visibility: hidden !important;
            height: 0 !important;
            overflow: hidden !important;
        }
    </style>
</head>
<body>

    <div class="container text-center" style="margin-top: 10vh; min-height: 60vh;">
        <h1 class="fw-bold" style="color: #00664d; font-size: 3.5rem;">
            <i class="fas fa-shield-alt me-3"></i>Sistema VENCER
        </h1>
        <p class="lead text-muted mt-3 fs-4">
            Módulo de consulta pública y análisis de eventos adversos, cuasifallas y centinelas.
        </p>
        
        <div class="d-flex justify-content-center align-items-center gap-4 mt-5 flex-wrap">
            
            <a href="../roles/index.php" class="btn btn-lg shadow-sm px-4 py-3 fw-bold text-secondary" 
               style="background-color: #e9ecef; border-radius: 50px; border: 1px solid #ced4da; transition: transform 0.2s;"
               onmouseover="this.style.transform='scale(1.05)'; this.style.backgroundColor='#dee2e6';" 
               onmouseout="this.style.transform='scale(1)'; this.style.backgroundColor='#e9ecef';">
                <i class="fas fa-arrow-left me-2 fs-4 align-middle"></i> Regresar al Inicio
            </a>

            <button type="button" class="btn btn-primary shadow" data-bs-toggle="modal" data-bs-target="#modalGraficos">
                <i class="bi bi-bar-chart-fill me-2"></i> Ver Tablero de Gráficos
            </button>
            
        </div>
    </div>

    <div id="contenedorTabla">
        <table id="tabla-vencer" class="table">
            <thead>
                <tr>
                    <th>Folio</th><th>Evento</th><th>Iniciales</th><th>NSS</th><th>Edad</th>
                    <th>Sexo</th><th>Diagnóstico</th><th>Fecha Evento</th><th>Fecha Noti</th>
                    <th>Turno</th><th>Servicio</th><th>Categoría</th><th>Proceso</th>
                    <th>Definición</th><th>Descripción</th><th>Estatus</th><th>Año</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="modal fade" id="modalGraficos" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content border-0 bg-transparent">
                
                <div class="modal-body p-0" style="overflow: hidden; height: 100vh; background-color: #f8fafc;">
                    <iframe src="/graficos/index.html" style="width: 100%; height: 100%; border: none; display: block;"></iframe>
                </div>
                
            </div>
        </div>
    </div>

    <?php require_once '../../includes/modal_graficos.php'; ?>

    <footer class="text-white text-center py-3 mt-auto w-100" style="background-color: #00664d; position: fixed; bottom: 0;">
        <div class="container">
            <p class="mb-0">© <?php echo date("Y"); ?> IMSS. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

    <script src="../../js/vencer.js?v=<?php echo time(); ?>"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let btnDash = document.getElementById('btnAbrirDashboard');
            let txtBtn = document.getElementById('textoBtnDash');
            btnDash.disabled = true; // Desactivamos el botón temporalmente

            // El "Vigilante": Revisa cada 500ms si los datos ya llegaron
            let checkData = setInterval(function() {
                if (window.dbDatos && window.dbDatos.length > 0) {
                    clearInterval(checkData); // Ya cargaron los datos, detenemos el vigilante
                    
                    // Activamos el botón y cambiamos el texto
                    btnDash.disabled = false;
                    txtBtn.innerHTML = '<i class="fas fa-chart-pie me-2 fs-3 align-middle"></i> Abrir Tablero Estadístico';
                    
                    // CAMBIO 2: Comentamos la apertura automática del modal antiguo
                    /*
                    var myModal = new bootstrap.Modal(document.getElementById('modalGraficos'), {
                        keyboard: false
                    });
                    myModal.show();
                    */
                    
                    // Aseguramos que los gráficos antiguos se dibujen en segundo plano (por si quieres abrirlo manual después)
                    setTimeout(() => {
                        if(typeof generarGraficos === 'function') generarGraficos();
                    }, 500);
                }
            }, 500); // Revisa cada medio segundo
        });
    </script>
</body>
</html>
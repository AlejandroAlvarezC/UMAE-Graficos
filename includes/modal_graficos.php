<style>
    .barra-procesos {
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    .barra-procesos:hover {
        background-color: rgba(111, 66, 193, 0.25) !important;
    }
    .icono-colapso {
        transition: transform 0.3s ease;
    }
    /* Gira la flecha hacia abajo cuando la sección está oculta (colapsada) */
    .barra-procesos.collapsed .icono-colapso {
        transform: rotate(180deg);
    }
</style>

<div class="modal fade" id="modalGraficos" tabindex="-1" aria-labelledby="modalGraficosLabel">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            
            <div class="modal-header" style="background-color: #7a123a; color: white;">
                <h5 class="modal-title fw-bold" id="modalGraficosLabel"><i class="fas fa-chart-line me-2"></i>VENCER</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body bg-light">

                <div class="d-flex justify-content-center align-items-center mb-4 gap-3 bg-white p-3 rounded shadow-sm border flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <label class="fw-bold text-secondary small">AÑO:</label>
                        <select id="filtroAnio" class="form-select form-select-sm w-auto fw-bold shadow-sm border-danger">
                            <option value="todos">Cargando...</option>
                        </select>
                    </div>
                    <div class="vr mx-2"></div>
                    <div class="d-flex align-items-center gap-2 border p-1 rounded bg-light">
                        <label class="fw-bold text-secondary small ms-1">MESES:</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-0 fw-bold text-muted" style="font-size: 0.8rem;">De:</span>
                            <select id="filtroMesInicio" class="form-select form-select-sm fw-bold shadow-sm border-secondary" style="max-width: 110px;">
                                <option value="1">Enero</option><option value="2">Febrero</option><option value="3">Marzo</option><option value="4">Abril</option><option value="5">Mayo</option><option value="6">Junio</option><option value="7">Julio</option><option value="8">Agosto</option><option value="9">Septiembre</option><option value="10">Octubre</option><option value="11">Noviembre</option><option value="12">Diciembre</option>
                            </select>
                        </div>
                        <span class="fw-bold text-muted">-</span>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-0 fw-bold text-muted" style="font-size: 0.8rem;">A:</span>
                            <select id="filtroMesFin" class="form-select form-select-sm fw-bold shadow-sm border-secondary" style="max-width: 110px;">
                                <option value="1">Enero</option><option value="2">Febrero</option><option value="3">Marzo</option><option value="4">Abril</option><option value="5">Mayo</option><option value="6">Junio</option><option value="7">Julio</option><option value="8">Agosto</option><option value="9">Septiembre</option><option value="10">Octubre</option><option value="11">Noviembre</option><option value="12" selected>Diciembre</option>
                            </select>
                        </div>
                    </div>
                    <div class="vr mx-2"></div>
                    <label class="small fw-bold ms-2">Servicio:</label>
                    <select id="filtroServicioGrafico" class="form-select form-select-sm shadow-sm" style="max-width: 200px;">
                        <option value="">Todos</option>
                    </select>
                    <div class="d-flex align-items-center bg-light px-3 py-1 rounded border">
                        <span class="text-muted small text-uppercase fw-bold me-2">Total:</span>
                        <span id="lblTotalEventos" class="fs-5 fw-bold text-danger">0</span>
                    </div>
                </div>

                <ul class="nav nav-pills nav-fill mb-4 gap-2 p-1 bg-white rounded shadow-sm" id="graficosTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#tab-general">📊 Panorama General</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold text-danger" data-bs-toggle="tab" data-bs-target="#tab-adverso">🚨 Eventos Adversos</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold text-warning" data-bs-toggle="tab" data-bs-target="#tab-cuasi">⚠️ Cuasifallas</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold text-dark" data-bs-toggle="tab" data-bs-target="#tab-centinela">Eventos Centinela</button></li>
                </ul>

                <div class="tab-content" id="graficosTabContent">
    
                    <div class="tab-pane fade show active" id="tab-general">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6"><div class="card h-100 shadow-sm border-0"><div class="card-body"><h6 class="text-center fw-bold text-secondary mb-3">DISTRIBUCIÓN POR SEXO</h6><div style="height:250px; position: relative;"><canvas id="chartSexoGen"></canvas></div><div id="tablaSexoGen" class="mt-3 table-responsive" style="max-height: 150px;"></div></div></div></div>
                            <div class="col-md-6"><div class="card h-100 shadow-sm border-0"><div class="card-body"><h6 class="text-center fw-bold text-secondary mb-3">CLASIFICACIÓN DE EVENTOS</h6><div style="height:250px; position: relative;"><canvas id="chartEventosGen"></canvas></div><div id="tablaEventosGen" class="mt-3 table-responsive" style="max-height: 150px;"></div></div></div></div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6"><div class="card h-100 shadow-sm border-0"><div class="card-body"><h6 class="text-center fw-bold text-secondary mb-3">PIRÁMIDE POBLACIONAL</h6><div style="height: 350px; position: relative;"><canvas id="chartPiramide"></canvas></div><div id="tablaEdadSexoGen" class="mt-3 table-responsive" style="max-height: 150px;"></div></div></div></div>
                            <div class="col-md-6"><div class="card h-100 shadow-sm border-0"><div class="card-body"><h6 class="text-center fw-bold text-success mb-3">TURNOS</h6><div style="height:250px; position: relative;"><canvas id="chartTurnoGen"></canvas></div><div id="tablaTurnoGen" class="mt-3 table-responsive" style="max-height: 150px;"></div></div></div></div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-12"><div class="card h-100 shadow-sm border-0"><div class="card-body"><h6 class="text-center fw-bold text-primary mb-3">TOP SERVICIOS</h6><div style="height:350px; position: relative;"><canvas id="chartTopServiciosGen"></canvas></div><div id="tablaTopServiciosGen" class="mt-3 table-responsive" style="max-height: 200px;"></div></div></div></div>
                        </div>
                        
                        <div class="alert py-2 fs-5 text-center fw-bold mb-3 mt-4 shadow-sm barra-procesos collapsed" data-bs-toggle="collapse" data-bs-target="#collapseProcesosGen" role="button" aria-expanded="false" aria-controls="collapseProcesosGen" style="background-color: rgba(111, 66, 193, 0.15); color: #5a369e; border: 1px solid rgba(111, 66, 193, 0.3);">
                            <div><i class="fas fa-cogs me-2"></i>Análisis de Procesos Relacionados (General)</div>
                            <i class="fas fa-chevron-up mt-1 icono-colapso"></i>
                        </div>
                        
                        <div class="collapse" id="collapseProcesosGen">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="card h-100 shadow-sm border-0">
                                        <div class="card-body">
                                            <h6 class="text-center fw-bold mb-3" style="color: #6f42c1 !important;">PROCESOS CON MAYOR INCIDENCIA</h6>
                                            <div style="height:300px; position: relative;"><canvas id="chartProcesoGen"></canvas></div>
                                            <div id="tablaProcesoGen" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 shadow-sm border-0" style="background-color: rgba(111, 66, 193, 0.05);">
                                        <div class="card-body">
                                            <h6 class="text-center fw-bold text-dark mb-3">#1 CAUSAS: <span id="lblTopProcGen1" style="color: #6f42c1 !important;">---</span></h6>
                                            <div style="height:300px; position: relative;"><canvas id="chartDrillProcGen1"></canvas></div>
                                            <div id="tablaDrillProcGen1" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card h-100 shadow-sm border-0" style="background-color: rgba(111, 66, 193, 0.03);">
                                        <div class="card-body">
                                            <h6 class="text-center fw-bold text-dark mb-3">#2 CAUSAS: <span id="lblTopProcGen2" style="color: #6f42c1 !important;">---</span></h6>
                                            <div style="height:300px; position: relative;"><canvas id="chartDrillProcGen2"></canvas></div>
                                            <div id="tablaDrillProcGen2" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 shadow-sm border-0" style="background-color: rgba(111, 66, 193, 0.01);">
                                        <div class="card-body">
                                            <h6 class="text-center fw-bold text-dark mb-3">#3 CAUSAS: <span id="lblTopProcGen3" style="color: #6f42c1 !important;">---</span></h6>
                                            <div style="height:300px; position: relative;"><canvas id="chartDrillProcGen3"></canvas></div>
                                            <div id="tablaDrillProcGen3" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> </div>

                    <div class="tab-pane fade" id="tab-adverso">
                        <div class="alert alert-danger py-2 text-center fw-bold mb-3"><i class="fas fa-exclamation-circle me-2"></i>Análisis de Eventos Adversos</div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm border-danger border-opacity-25">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-danger">ÁREAS CON MAYOR INCIDENCIA</h6>
                                        <div style="height: 250px; position: relative;"><canvas id="chartServicioAdv"></canvas><canvas id="chartSexoAdverso" style="display: none;"></canvas></div>
                                        <div id="tablaServicioAdv" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm border-danger border-opacity-50" id="cardTopAdv1" style="background-color: #fff5f5;">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-dark">#1 ANÁLISIS: <span id="lblTopAreaAdv1" class="text-danger">---</span></h6>
                                        <div style="height: 250px; position: relative;"><canvas id="chartDrillDownAdv1"></canvas></div>
                                        <div id="tablaDrillDownAdv1" class="mt-2 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                                <div class="card h-100 shadow-sm border-danger border-opacity-25" id="colPiramideAdverso" style="display: none;">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-danger">DISTRIBUCIÓN EDAD/SEXO</h6>
                                        <div style="height: 250px; position: relative;"><canvas id="chartPiramideAdverso"></canvas></div>
                                        <div id="tablaEdadSexoAdverso" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3 contenedor-tops-adverso" id="rowTopsAdv23">
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm border-danger border-opacity-50" style="background-color: #fffaf0;">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-dark">#2 ANÁLISIS: <span id="lblTopAreaAdv2" class="text-danger">---</span></h6>
                                        <div style="height: 250px; position: relative;"><canvas id="chartDrillDownAdv2"></canvas></div>
                                        <div id="tablaDrillDownAdv2" class="mt-2 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm border-danger border-opacity-50" style="background-color: #f0fff4;">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-dark">#3 ANÁLISIS: <span id="lblTopAreaAdv3" class="text-danger">---</span></h6>
                                        <div style="height: 250px; position: relative;"><canvas id="chartDrillDownAdv3"></canvas></div>
                                        <div id="tablaDrillDownAdv3" class="mt-2 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <div class="card h-100 shadow-sm border-danger border-opacity-25">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-danger">CAUSAS GLOBALES (Definición)</h6>
                                        <div style="height: 250px; position: relative;"><canvas id="chartDefinicionAdv"></canvas></div>
                                        <div id="tablaDefinicionAdv" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert py-2 fs-5 text-center fw-bold mb-3 mt-4 shadow-sm barra-procesos collapsed" data-bs-toggle="collapse" data-bs-target="#collapseProcesosAdv" role="button" aria-expanded="false" aria-controls="collapseProcesosAdv" style="background-color: rgba(111, 66, 193, 0.15); color: #5a369e; border: 1px solid rgba(111, 66, 193, 0.3);">
                            <div><i class="fas fa-cogs me-2"></i>Análisis de Procesos Relacionados (Eventos Adversos)</div>
                            <i class="fas fa-chevron-up mt-1 icono-colapso"></i>
                        </div>

                        <div class="collapse" id="collapseProcesosAdv">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="card h-100 shadow-sm border-danger border-opacity-25">
                                        <div class="card-body">
                                            <h6 class="text-center fw-bold text-danger">PROCESOS CON MAYOR INCIDENCIA</h6>
                                            <div style="height: 300px; position: relative;"><canvas id="chartProcesoAdv"></canvas></div>
                                            <div id="tablaProcesoAdv" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 shadow-sm border-danger border-opacity-50" style="background-color: #fff5f5;">
                                        <div class="card-body">
                                            <h6 class="text-center fw-bold text-dark">#1 CAUSAS: <span id="lblTopProcAdv1" class="text-danger">---</span></h6>
                                            <div style="height: 300px; position: relative;"><canvas id="chartDrillProcAdv1"></canvas></div>
                                            <div id="tablaDrillProcAdv1" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card h-100 shadow-sm border-danger border-opacity-50" style="background-color: #fffaf0;">
                                        <div class="card-body">
                                            <h6 class="text-center fw-bold text-dark">#2 CAUSAS: <span id="lblTopProcAdv2" class="text-danger">---</span></h6>
                                            <div style="height: 300px; position: relative;"><canvas id="chartDrillProcAdv2"></canvas></div>
                                            <div id="tablaDrillProcAdv2" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 shadow-sm border-danger border-opacity-50" style="background-color: #f0fff4;">
                                        <div class="card-body">
                                            <h6 class="text-center fw-bold text-dark">#3 CAUSAS: <span id="lblTopProcAdv3" class="text-danger">---</span></h6>
                                            <div style="height: 300px; position: relative;"><canvas id="chartDrillProcAdv3"></canvas></div>
                                            <div id="tablaDrillProcAdv3" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> </div>

                    <div class="tab-pane fade" id="tab-cuasi">
                        <div class="alert alert-warning py-2 text-center fw-bold mb-3 text-dark"><i class="fas fa-shield-alt me-2"></i>Análisis de Cuasifallas</div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm border-warning border-opacity-25">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-warning">ÁREAS CON MAYOR INCIDENCIA</h6>
                                        <div style="height: 250px; position: relative;"><canvas id="chartServicioCuasi"></canvas><canvas id="chartSexoCuasi" style="display: none;"></canvas></div>
                                        <div id="tablaServicioCuasi" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm border-warning border-opacity-50" id="cardTopCuasi1" style="background-color: #fffff0;">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-dark">#1 ANÁLISIS: <span id="lblTopAreaCuasi1" class="text-warning">---</span></h6>
                                        <div style="height: 250px; position: relative;"><canvas id="chartDrillDownCuasi1"></canvas></div>
                                        <div id="tablaDrillDownCuasi1" class="mt-2 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                                <div class="card h-100 shadow-sm border-warning border-opacity-25" id="colPiramideCuasi" style="display: none;">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-warning">DISTRIBUCIÓN EDAD/SEXO</h6>
                                        <div style="height: 250px; position: relative;"><canvas id="chartPiramideCuasi"></canvas></div>
                                        <div id="tablaEdadSexoCuasi" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3 contenedor-tops-cuasi" id="rowTopsCuasi23">
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm border-warning border-opacity-50" style="background-color: #fffaf0;">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-dark">#2 ANÁLISIS: <span id="lblTopAreaCuasi2" class="text-warning">---</span></h6>
                                        <div style="height: 250px; position: relative;"><canvas id="chartDrillDownCuasi2"></canvas></div>
                                        <div id="tablaDrillDownCuasi2" class="mt-2 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm border-warning border-opacity-50" style="background-color: #f0fff4;">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-dark">#3 ANÁLISIS: <span id="lblTopAreaCuasi3" class="text-warning">---</span></h6>
                                        <div style="height: 250px; position: relative;"><canvas id="chartDrillDownCuasi3"></canvas></div>
                                        <div id="tablaDrillDownCuasi3" class="mt-2 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <div class="card h-100 shadow-sm border-warning border-opacity-25">
                                    <div class="card-body">
                                        <h6 class="text-center fw-bold text-warning">CAUSAS GLOBALES (Definición)</h6>
                                        <div style="height: 250px; position: relative;"><canvas id="chartDefinicionCuasi"></canvas></div>
                                        <div id="tablaDefinicionCuasi" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert py-2 fs-5 text-center fw-bold mb-3 mt-4 shadow-sm barra-procesos collapsed" data-bs-toggle="collapse" data-bs-target="#collapseProcesosCuasi" role="button" aria-expanded="false" aria-controls="collapseProcesosCuasi" style="background-color: rgba(111, 66, 193, 0.15); color: #5a369e; border: 1px solid rgba(111, 66, 193, 0.3);">
                            <div><i class="fas fa-cogs me-2"></i>Análisis de Procesos Relacionados (Cuasifallas)</div>
                            <i class="fas fa-chevron-up mt-1 icono-colapso"></i>
                        </div>

                        <div class="collapse" id="collapseProcesosCuasi">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="card h-100 shadow-sm border-warning border-opacity-25">
                                        <div class="card-body">
                                            <h6 class="text-center fw-bold text-warning">PROCESOS CON MAYOR INCIDENCIA</h6>
                                            <div style="height: 300px; position: relative;"><canvas id="chartProcesoCuasi"></canvas></div>
                                            <div id="tablaProcesoCuasi" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 shadow-sm border-warning border-opacity-50" style="background-color: #fffff0;">
                                        <div class="card-body">
                                            <h6 class="text-center fw-bold text-dark">#1 CAUSAS: <span id="lblTopProcCuasi1" class="text-warning">---</span></h6>
                                            <div style="height: 300px; position: relative;"><canvas id="chartDrillProcCuasi1"></canvas></div>
                                            <div id="tablaDrillProcCuasi1" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card h-100 shadow-sm border-warning border-opacity-50" style="background-color: #fffaf0;">
                                        <div class="card-body">
                                            <h6 class="text-center fw-bold text-dark">#2 CAUSAS: <span id="lblTopProcCuasi2" class="text-warning">---</span></h6>
                                            <div style="height: 300px; position: relative;"><canvas id="chartDrillProcCuasi2"></canvas></div>
                                            <div id="tablaDrillProcCuasi2" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 shadow-sm border-warning border-opacity-50" style="background-color: #f0fff4;">
                                        <div class="card-body">
                                            <h6 class="text-center fw-bold text-dark">#3 CAUSAS: <span id="lblTopProcCuasi3" class="text-warning">---</span></h6>
                                            <div style="height: 300px; position: relative;"><canvas id="chartDrillProcCuasi3"></canvas></div>
                                            <div id="tablaDrillProcCuasi3" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> </div>

                    <div class="tab-pane fade" id="tab-centinela">
                        <div class="alert alert-dark py-2 text-center fw-bold mb-3">Análisis de Eventos Centinela</div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6"><div class="card h-100 shadow-sm"><div class="card-body"><h6 class="text-center fw-bold text-dark">ÁREAS CON MAYOR INCIDENCIA</h6><div style="height: 300px; position: relative;"><canvas id="chartServicioCent"></canvas></div><div id="tablaServicioCent" class="mt-3 table-responsive" style="max-height: 150px;"></div></div></div></div>
                            <div class="col-md-6"><div class="card h-100 shadow-sm"><div class="card-body"><h6 class="text-center fw-bold text-dark">CAUSAS PRINCIPALES (Definición)</h6><div style="height: 300px; position: relative;"><canvas id="chartDefinicionCent"></canvas></div><div id="tablaDefinicionCent" class="mt-3 table-responsive" style="max-height: 150px;"></div></div></div></div>
                        </div>

                        <div class="alert py-2 fs-5 text-center fw-bold mb-3 mt-4 shadow-sm barra-procesos collapsed" data-bs-toggle="collapse" data-bs-target="#collapseProcesosCent" role="button" aria-expanded="false" aria-controls="collapseProcesosCent" style="background-color: rgba(111, 66, 193, 0.15); color: #5a369e; border: 1px solid rgba(111, 66, 193, 0.3);">
                            <div><i class="fas fa-cogs me-2"></i>Análisis de Procesos Relacionados (Eventos Centinela)</div>
                            <i class="fas fa-chevron-up mt-1 icono-colapso"></i>
                        </div>

                        <div class="collapse" id="collapseProcesosCent">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="card h-100 shadow-sm">
                                        <div class="card-body">
                                            <h6 class="text-center fw-bold text-dark">PROCESOS CON MAYOR INCIDENCIA</h6>
                                            <div style="height: 300px; position: relative;"><canvas id="chartProcesoCent"></canvas></div>
                                            <div id="tablaProcesoCent" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 shadow-sm bg-light">
                                        <div class="card-body">
                                            <h6 class="text-center fw-bold text-dark">#1 CAUSAS: <span id="lblTopProcCent1" class="text-secondary">---</span></h6>
                                            <div style="height: 300px; position: relative;"><canvas id="chartDrillProcCent1"></canvas></div>
                                            <div id="tablaDrillProcCent1" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card h-100 shadow-sm bg-light">
                                        <div class="card-body">
                                            <h6 class="text-center fw-bold text-dark">#2 CAUSAS: <span id="lblTopProcCent2" class="text-secondary">---</span></h6>
                                            <div style="height: 300px; position: relative;"><canvas id="chartDrillProcCent2"></canvas></div>
                                            <div id="tablaDrillProcCent2" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 shadow-sm bg-light">
                                        <div class="card-body">
                                            <h6 class="text-center fw-bold text-dark">#3 CAUSAS: <span id="lblTopProcCent3" class="text-secondary">---</span></h6>
                                            <div style="height: 300px; position: relative;"><canvas id="chartDrillProcCent3"></canvas></div>
                                            <div id="tablaDrillProcCent3" class="mt-3 table-responsive" style="max-height: 150px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> </div>

                </div>

                <div class="row mt-4 border-top pt-3">
                    <div class="col text-center">
                        <button id="btnDescargarExcelStats" class="btn btn-excel-verde fw-bold shadow px-4 py-2" style="background-color: #217346; color: white;">
                            <i class="fas fa-file-excel me-2"></i>Descargar Reporte (Excel)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
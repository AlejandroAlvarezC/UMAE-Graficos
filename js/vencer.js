document.addEventListener('DOMContentLoaded', function() {
    console.log("🚀 VENCER: Dashboard Final (Procesos en todas las pestañas)");

    const COL = { EVENTO: 1, EDAD: 4, SEXO: 5, FECHA: 7, TURNO: 9, SERVICIO: 10, DEFINICION: 13, ANIO: 16 };
    window.dbDatos = []; 
    let tabla; 

    // --- CLEANER ---
    const clean = (txt) => {
        if (txt === null || txt === undefined) return 'NULO';
        let str = String(txt).replace(/<[^>]*>?/gm, '').replace(/Ver más|Ver menos/g, '').trim();
        try { return decodeURIComponent(escape(str)); } 
        catch (e) {
            const mapa = {'Ã±':'ñ','Ã‘':'Ñ','Ã¡':'á','Ã©':'é','Ãed':'í','Ã³':'ó','Ãº':'ú','Ã':'Á','Ã‰':'É','Ã':'Í','Ã“':'Ó','Ãš':'Ú','&nbsp;':' ','&amp;':'&','&lt;':'<','&gt;':'>'};
            return str.replace(/Ã±|Ã‘|Ã¡|Ã©|Ãed|Ã³|Ãº|Ã|Ã‰|Ã|Ã“|Ãš|&nbsp;|&amp;|&lt;|&gt;/g, m => mapa[m]);
        }
    };

    // --- TRUNCAR TEXTO PARA GRÁFICOS ---
    const truncarTexto = (texto, max = 40) => {
        if (texto.length > max) return texto.substring(0, max) + '...';
        return texto;
    };

    // --- CARGA AJAX ---
    const cargarDatos = async () => {
        const loader = document.getElementById('loaderTabla');
        try {
            const response = await fetch('../../controladores/vencer.php?a=ObtenerDatosJSON');
            if (!response.ok) throw new Error("Error red");
            const data = await response.json();
            if (Array.isArray(data)) {
                window.dbDatos = data;
                inicializarTabla();
                initFiltros(); 
                if(loader) loader.style.display = 'none';
                document.getElementById('contenedorTabla').style.display = 'block';
            } else { throw new Error(data.message || "Formato inválido"); }
        } catch (error) { if(loader) loader.innerHTML = `<div class="alert alert-danger">${error.message}</div>`; }
    };

    // --- TABLA ---
    const inicializarTabla = () => {
        if ($.fn.DataTable.isDataTable('#tabla-vencer')) $('#tabla-vencer').DataTable().destroy();
        tabla = $('#tabla-vencer').DataTable({
            data: window.dbDatos,
            columns: [
                { data: 'folio' }, { data: 'evento', render: d=>clean(d) }, { data: 'ini_paciente', render: d=>clean(d) }, { data: 'seguridad_social' },
                { data: 'edad', render: d=>clean(d) }, { data: 'sexo', render: d=>clean(d) }, { data: 'diagnostico', render: d=>clean(d) },
                { data: 'fecha_evento' }, { data: 'fecha_noti' }, { data: 'turno', render: d=>clean(d) }, { data: 'servicio', render: d=>clean(d) },
                { data: 'categoria', render: d=>clean(d) }, { data: 'proceso', render: d=>clean(d) }, { data: 'definicion', render: d=>clean(d) },
                { data: 'descripcion', render: d=>clean(d) }, { data: 'estatus', render: d=>`<span class="badge bg-secondary">${clean(d)}</span>` },
                { data: 'anio' },
                { data: null, orderable: false, render: (d,t,r) => {
                    let idEnc=''; try{idEnc=btoa(r.id)}catch(e){idEnc=r.id}
                    return `<div class="d-flex gap-1 justify-content-center"><a class="btn btn-outline-primary btn-sm" href="./editar-vencer.php?id=${idEnc}"><i class="bi bi-pencil"></i></a><a class="btn btn-outline-danger btn-sm" href="../../controladores/vencer.php?a=Eliminar&id=${idEnc}" onclick="return confirm('¿Borrar?')"><i class="bi bi-trash"></i></a></div>`;
                }}
            ],
            pageLength: 10, order: [[0, 'desc']], deferRender: true, processing: true,
            language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
        });
    };

    const initFiltros = () => {
        const fAnio = document.getElementById('filtroAnio');
        if(fAnio) {
            const anios = [...new Set(window.dbDatos.map(d => d.anio))].filter(x => x).sort().reverse();
            fAnio.innerHTML = '<option value="todos">Todos los años</option>';
            anios.forEach(a => fAnio.append(new Option(a, a)));
        }
        const mapIndices = {'Folio':'folio', 'Evento':'evento', 'Iniciales':'ini_paciente', 'NSS':'seguridad_social', 'Edad':'edad', 'Sexo':'sexo', 'Diagnostico':'diagnostico', 'FechaEvento':'fecha_evento', 'FechaNotificacion':'fecha_noti', 'Turno':'turno', 'Servicio':'servicio', 'Categoria':'categoria', 'Proceso':'proceso', 'Definicion':'definicion', 'Descripcion':'descripcion', 'Estatus':'estatus', 'Anio':'anio'};
        Object.keys(mapIndices).forEach(key => {
            const select = document.getElementById(`filter${key}`);
            if (!select) return;
            const uniqueValues = new Set();
            window.dbDatos.forEach(row => { let txt = clean(row[mapIndices[key]]); if(txt && txt!=='NULO') uniqueValues.add(txt); });
            const frag = document.createDocumentFragment();
            Array.from(uniqueValues).sort().forEach(v => frag.appendChild(new Option(v, v)));
            select.innerHTML = ''; select.appendChild(frag);
        });
        $('.select-personalizado').not('#filtroAnio, #filtroMesInicio, #filtroMesFin, #filtroServicioGrafico').select2({ placeholder: 'Filtrar...', allowClear: true, width: '100%' });
        $('.select-personalizado').on('change', () => { if(tabla) tabla.draw(); generarGraficos(); });
    };

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'tabla-vencer') return true;
        const row = window.dbDatos[dataIndex] || {}; 
        const anioSel = $('#filtroAnio').val(), mesIni = parseInt($('#filtroMesInicio').val())||1, mesFin = parseInt($('#filtroMesFin').val())||12;
        let okAnio = (anioSel === 'todos' || String(row.anio) === anioSel);
        let okMes = true;
        if(okAnio) {
            let m=0, f=String(row.fecha_evento);
            if(f.includes('-')) m=parseInt(f.split('-')[1]); else if(f.includes('/')) m=parseInt(f.split('/')[1]);
            if(m<mesIni || m>mesFin) okMes=false;
        }
        let okCols = true;
        const mapIndices = { 'Folio':'folio', 'Evento':'evento', 'Iniciales':'ini_paciente', 'NSS':'seguridad_social', 'Edad':'edad', 'Sexo':'sexo', 'Diagnostico':'diagnostico', 'FechaEvento':'fecha_evento', 'FechaNotificacion':'fecha_noti', 'Turno':'turno', 'Servicio':'servicio', 'Categoria':'categoria', 'Proceso':'proceso', 'Definicion':'definicion', 'Descripcion':'descripcion', 'Estatus':'estatus', 'Anio':'anio' };
        for (const [key, field] of Object.entries(mapIndices)) {
            const val = $(`#filter${key}`).val();
            if (val && val.length > 0 && !val.includes(clean(row[field]))) { okCols = false; break; }
        }
        return okAnio && okMes && okCols;
    });

    let charts = {};
    let filtroServicioLlenado = false; 

    const generarGraficos = () => {
        if (!tabla) return; 

        setTimeout(() => {
            const allRows = tabla.rows({ search: 'applied' }).data().toArray();
            
            const elSelectServicio = document.getElementById('filtroServicioGrafico');
            const servicioSeleccionado = elSelectServicio ? elSelectServicio.value : "";
            
            if (elSelectServicio && (!filtroServicioLlenado || elSelectServicio.options.length <= 1)) {
                const serviciosUnicos = new Set();
                window.dbDatos.forEach(r => { let s = clean(r.servicio); if (s && s!=='NULO') serviciosUnicos.add(s); });
                const prev = elSelectServicio.value; elSelectServicio.innerHTML = '<option value="">Todos los Servicios</option>';
                Array.from(serviciosUnicos).sort().forEach(s => elSelectServicio.add(new Option(s, s)));
                elSelectServicio.value = prev; filtroServicioLlenado = true;
            }

            let rows = allRows;
            if (servicioSeleccionado !== "") rows = allRows.filter(r => clean(r.servicio) === servicioSeleccionado);
            
            const lbl = document.getElementById('lblTotalEventos'); if(lbl) lbl.textContent = rows.length;
            if (rows.length === 0) return;

            // --- RECOLECCIÓN DE DATOS ---
            let s = { 
                General: { Sexo:{}, Evento:{}, Turno:{}, Servicio:{}, Edad:{}, Proceso:{} }, 
                Adverso: { Servicio:{}, Definicion:{}, Sexo:{}, Edad:{}, Proceso:{} }, 
                Cuasi:   { Servicio:{}, Definicion:{}, Sexo:{}, Edad:{}, Proceso:{} }, 
                Centinela: { Servicio:{}, Definicion:{}, Proceso:{} } // <--- AÑADIDO PROCESO EN CENTINELA
            };

            rows.forEach(r => {
                let ev = clean(r.evento), ed = clean(r.edad), sx = clean(r.sexo), tu = clean(r.turno), sv = clean(r.servicio), def = clean(r.definicion), pro = clean(r.proceso);
                
                s.General.Sexo[sx] = (s.General.Sexo[sx] || 0) + 1; 
                s.General.Evento[ev] = (s.General.Evento[ev] || 0) + 1; 
                s.General.Turno[tu] = (s.General.Turno[tu] || 0) + 1; 
                s.General.Servicio[sv] = (s.General.Servicio[sv] || 0) + 1;
                s.General.Proceso[pro] = (s.General.Proceso[pro] || 0) + 1; 
                
                if (!s.General.Edad[ed]) s.General.Edad[ed] = { H: 0, M: 0 };
                let esHombre = sx.toUpperCase().includes('MASC') || sx.toUpperCase().startsWith('H') || sx.toUpperCase() === 'M';
                esHombre ? s.General.Edad[ed].H++ : s.General.Edad[ed].M++;
                
                let evUpper = ev.toUpperCase();
                if(evUpper.includes('ADVERSO')) { 
                    s.Adverso.Servicio[sv]=(s.Adverso.Servicio[sv]||0)+1; s.Adverso.Definicion[def]=(s.Adverso.Definicion[def]||0)+1; 
                    s.Adverso.Sexo[sx]=(s.Adverso.Sexo[sx]||0)+1;
                    s.Adverso.Proceso[pro]=(s.Adverso.Proceso[pro]||0)+1; 
                    if (!s.Adverso.Edad[ed]) s.Adverso.Edad[ed] = { H: 0, M: 0 };
                    esHombre ? s.Adverso.Edad[ed].H++ : s.Adverso.Edad[ed].M++;
                }
                else if(evUpper.includes('CUASI')) { 
                    s.Cuasi.Servicio[sv]=(s.Cuasi.Servicio[sv]||0)+1; s.Cuasi.Definicion[def]=(s.Cuasi.Definicion[def]||0)+1;
                    s.Cuasi.Sexo[sx]=(s.Cuasi.Sexo[sx]||0)+1;
                    s.Cuasi.Proceso[pro]=(s.Cuasi.Proceso[pro]||0)+1; 
                    if (!s.Cuasi.Edad[ed]) s.Cuasi.Edad[ed] = { H: 0, M: 0 };
                    esHombre ? s.Cuasi.Edad[ed].H++ : s.Cuasi.Edad[ed].M++;
                }
                else if(evUpper.includes('CENTINELA')) { 
                    s.Centinela.Servicio[sv]=(s.Centinela.Servicio[sv]||0)+1; s.Centinela.Definicion[def]=(s.Centinela.Definicion[def]||0)+1; 
                    s.Centinela.Proceso[pro]=(s.Centinela.Proceso[pro]||0)+1; // <--- LLENAR PROCESO CENTINELA
                }
            });

            const draw = (id, t, l, d, c, opts={}) => {
                const el = document.getElementById(id); if(!el) return;
                if(charts[id]) charts[id].destroy();
                charts[id] = new Chart(el.getContext('2d'), { type: t, data: { labels: l, datasets: [{ label: 'Total', data: d, backgroundColor: c }] }, options: { responsive: true, maintainAspectRatio: false, animation: false, ...opts } });
            };

            const drawTable = (id, obj, t) => {
                const el = document.getElementById(id); if(!el) return;
                const ent = Object.entries(obj).sort((a,b)=>((typeof b[1]==='object'?b[1].H+b[1].M:b[1])-(typeof a[1]==='object'?a[1].H+a[1].M:a[1])));
                let sumaTotal = 0;
                let h = `<table class="table table-sm table-striped table-bordered text-center small mb-0"><thead class="table-dark"><tr><th>${t}</th><th>Total</th></tr></thead><tbody>`;
                ent.forEach(([k,v])=>{ let val = (typeof v === 'object') ? (v.H + v.M) : v; sumaTotal += val; h+=`<tr><td class="text-start">${k}</td><td class="fw-bold">${val}</td></tr>`; });
                if(ent.length > 0) h += `<tr class="table-secondary" style="border-top: 2px solid #7a123a;"><td class="text-end fw-bold">TOTAL</td><td class="fw-bold text-danger">${sumaTotal}</td></tr>`;
                else h += `<tr><td colspan="2">Sin datos</td></tr>`;
                el.innerHTML = h+'</tbody></table>';
            };

            const drawPiramide = (canvasId, tablaId, dataEdad) => {
                const elCanvas = document.getElementById(canvasId); if(!elCanvas) return;
                const rangosOrden = ["<1 de un año", "1 a 4 años", "5 a 9", "10 a 14", "15 a 19", "20 a 29", "30 a 39", "40 a 49", "50 a 59", ">60"];
                let dataH = [], dataM = [];
                rangosOrden.forEach(rango => {
                    let key = Object.keys(dataEdad).find(k => k.includes(rango.split(' ')[0])); 
                    if (key && dataEdad[key]) { dataH.push(0 - dataEdad[key].H); dataM.push(dataEdad[key].M); } 
                    else { dataH.push(0); dataM.push(0); }
                });
                if(charts[canvasId]) charts[canvasId].destroy();
                charts[canvasId] = new Chart(elCanvas, {
                    type: 'bar',
                    data: { labels: rangosOrden, datasets: [{ label: 'Hombres', data: dataH, backgroundColor: 'rgba(54, 162, 235, 0.7)', borderWidth: 1 }, { label: 'Mujeres', data: dataM, backgroundColor: 'rgba(255, 99, 132, 0.7)', borderWidth: 1 }] },
                    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, scales: { x: { stacked: true, ticks: { callback: v => Math.abs(v) } }, y: { stacked: true, grid: { display: false } } }, plugins: { tooltip: { callbacks: { label: c => c.dataset.label + ': ' + Math.abs(c.raw) } } } }
                });
                drawTable(tablaId, dataEdad, 'Edad');
            };

            // --- 1. GENERAL ---
            draw('chartSexoGen', 'pie', Object.keys(s.General.Sexo), Object.values(s.General.Sexo), ['#0d6efd','#dc3545','#ffc107']); drawTable('tablaSexoGen', s.General.Sexo, 'Sexo');
            draw('chartEventosGen', 'bar', Object.keys(s.General.Evento), Object.values(s.General.Evento), '#7a123a'); drawTable('tablaEventosGen', s.General.Evento, 'Evento');
            draw('chartTurnoGen', 'bar', Object.keys(s.General.Turno), Object.values(s.General.Turno), '#198754'); drawTable('tablaTurnoGen', s.General.Turno, 'Turno');
            
            const canvasTop = document.getElementById('chartTopServiciosGen');
            const containerTop = canvasTop ? (canvasTop.closest('.row')) : null; 
            if (servicioSeleccionado !== "") { if(containerTop) containerTop.style.display = 'none'; } 
            else { if(containerTop) containerTop.style.display = 'flex'; const ts = Object.entries(s.General.Servicio).sort((a,b)=>b[1]-a[1]).slice(0,10); draw('chartTopServiciosGen', 'bar', ts.map(x=>x[0]), ts.map(x=>x[1]), '#0288d1'); drawTable('tablaTopServiciosGen', s.General.Servicio, 'Servicio'); }
            
            drawPiramide('chartPiramide', 'tablaEdadSexoGen', s.General.Edad);
            
            const tProcGen = Object.entries(s.General.Proceso).sort((a,b)=>b[1]-a[1]);
            draw('chartProcesoGen', 'bar', tProcGen.map(x=>x[0]), tProcGen.map(x=>x[1]), '#6f42c1', { indexAxis: 'y' }); 
            drawTable('tablaProcesoGen', s.General.Proceso, 'Proceso Relacionado');

            // --- FUNCIÓN MAESTRA (ADVERSOS Y CUASIFALLAS) ---
            const generarMultiDrill = (tipo, colorBarra, configIds, idsControl) => {
                const dataObj = s[tipo];
                const tServ = Object.entries(dataObj.Servicio).sort((a,b)=>b[1]-a[1]);
                
                // 1. Swap: Áreas vs Sexo
                const canvasArea = document.getElementById(configIds.mainChart);
                const canvasSexo = document.getElementById(configIds.sexChart);
                if (servicioSeleccionado !== "") {
                    if(canvasArea) canvasArea.style.display = 'none';
                    if(canvasSexo) { canvasSexo.style.display = 'block'; draw(configIds.sexChart, 'pie', Object.keys(dataObj.Sexo), Object.values(dataObj.Sexo), ['#36A2EB', '#FF6384']); }
                } else {
                    if(canvasArea) { canvasArea.style.display = 'block'; draw(configIds.mainChart, 'bar', tServ.slice(0,10).map(x=>x[0]), tServ.slice(0,10).map(x=>x[1]), colorBarra); }
                    if(canvasSexo) canvasSexo.style.display = 'none';
                }
                drawTable(configIds.mainTable, dataObj.Servicio, 'Servicio');

                // 2. Pirámide vs Top 1
                const cardTop1 = document.getElementById(idsControl.cardTop1);
                const colPiramide = document.getElementById(idsControl.colPiramide);
                
                if (servicioSeleccionado !== "") {
                    if(cardTop1) cardTop1.style.display = 'none';
                    if(colPiramide) colPiramide.style.display = 'block';
                    drawPiramide(configIds.piramideChart, configIds.piramideTable, dataObj.Edad);
                } else {
                    if(cardTop1) cardTop1.style.display = 'block'; 
                    if(colPiramide) colPiramide.style.display = 'none';
                }

                // 3. Fila de Tops 2 y 3
                const rowTops23 = document.getElementById(idsControl.rowTops23);
                if (servicioSeleccionado !== "") {
                    if(rowTops23) rowTops23.style.display = 'none'; 
                } else {
                    if(rowTops23) rowTops23.style.display = 'flex'; 
                }

                // 4. Llenar los Tops con TEXTO TRUNCADO EN GRÁFICOS
                [0, 1, 2].forEach(index => {
                    const chartId = configIds.drills[index].chart;
                    const tableId = configIds.drills[index].table;
                    const labelId = configIds.drills[index].lbl;
                    
                    const canvasEl = document.getElementById(chartId);
                    const colParent = canvasEl ? canvasEl.closest('.col-md-6') : null; 

                    let mostrarColumna = (tServ.length > index);
                    if (servicioSeleccionado !== "" && index > 0) mostrarColumna = false; 

                    if (mostrarColumna) {
                        if(colParent) colParent.style.display = 'block';
                        
                        const areaName = tServ[index][0];
                        const areaVal = tServ[index][1];
                        const elLbl = document.getElementById(labelId); if(elLbl) elLbl.textContent = `${areaName} (${areaVal})`;
                        
                        let drillCounts = {};
                        rows.forEach(r => {
                            let evUpper = clean(r.evento).toUpperCase(), sv = clean(r.servicio);
                            let coincide = (tipo === 'Adverso' && evUpper.includes('ADVERSO')) || (tipo === 'Cuasi' && evUpper.includes('CUASI'));
                            if (coincide && sv === areaName) { let d = clean(r.definicion); drillCounts[d] = (drillCounts[d] || 0) + 1; }
                        });
                        const tDrill = Object.entries(drillCounts).sort((a,b)=>b[1]-a[1]).slice(0,10);
                        
                        // TRUNCAR TEXTO PARA EL GRÁFICO (El tooltip mostrará la info truncada, la tabla abajo lo muestra completo)
                        const etiquetasTruncadas = tDrill.map(x => truncarTexto(x[0], 35)); 
                        
                        draw(chartId, 'bar', etiquetasTruncadas, tDrill.map(x=>x[1]), '#212529'); 
                        drawTable(tableId, drillCounts, 'Causa Específica');
                    } else {
                        if(colParent) colParent.style.display = 'none';
                    }
                });

                // 5. Causas Globales
                const tDef = Object.entries(dataObj.Definicion).sort((a,b)=>b[1]-a[1]).slice(0,10);
                draw(configIds.defChart, 'bar', tDef.map(x=>x[0]), tDef.map(x=>x[1]), colorBarra); drawTable(configIds.defTable, dataObj.Definicion, 'Causa');

                // 6. Proceso Relacionado (Horizontal)
                if (configIds.procChart && dataObj.Proceso) {
                    const tProc = Object.entries(dataObj.Proceso).sort((a,b)=>b[1]-a[1]);
                    draw(configIds.procChart, 'bar', tProc.map(x=>x[0]), tProc.map(x=>x[1]), colorBarra, { indexAxis: 'y' }); 
                    drawTable(configIds.procTable, dataObj.Proceso, 'Proceso Relacionado');
                }
            };

            // EJECUTAR LÓGICA
            generarMultiDrill('Adverso', '#dc3545', { 
                mainChart: 'chartServicioAdv', sexChart: 'chartSexoAdverso', mainTable: 'tablaServicioAdv', 
                piramideChart: 'chartPiramideAdverso', piramideTable: 'tablaEdadSexoAdverso',
                defChart: 'chartDefinicionAdv', defTable: 'tablaDefinicionAdv', 
                procChart: 'chartProcesoAdv', procTable: 'tablaProcesoAdv',
                drills: [{ chart: 'chartDrillDownAdv1', table: 'tablaDrillDownAdv1', lbl: 'lblTopAreaAdv1' }, { chart: 'chartDrillDownAdv2', table: 'tablaDrillDownAdv2', lbl: 'lblTopAreaAdv2' }, { chart: 'chartDrillDownAdv3', table: 'tablaDrillDownAdv3', lbl: 'lblTopAreaAdv3' }] 
            }, { cardTop1: 'cardTopAdv1', colPiramide: 'colPiramideAdverso', rowTops23: 'rowTopsAdv23' }); 

            generarMultiDrill('Cuasi', '#ffc107', { 
                mainChart: 'chartServicioCuasi', sexChart: 'chartSexoCuasi', mainTable: 'tablaServicioCuasi', 
                piramideChart: 'chartPiramideCuasi', piramideTable: 'tablaEdadSexoCuasi',
                defChart: 'chartDefinicionCuasi', defTable: 'tablaDefinicionCuasi', 
                procChart: 'chartProcesoCuasi', procTable: 'tablaProcesoCuasi',
                drills: [{ chart: 'chartDrillDownCuasi1', table: 'tablaDrillDownCuasi1', lbl: 'lblTopAreaCuasi1' }, { chart: 'chartDrillDownCuasi2', table: 'tablaDrillDownCuasi2', lbl: 'lblTopAreaCuasi2' }, { chart: 'chartDrillDownCuasi3', table: 'tablaDrillDownCuasi3', lbl: 'lblTopAreaCuasi3' }] 
            }, { cardTop1: 'cardTopCuasi1', colPiramide: 'colPiramideCuasi', rowTops23: 'rowTopsCuasi23' }); 

            // --- 4. CENTINELA ---
            const tCentS = Object.entries(s.Centinela.Servicio).sort((a,b)=>b[1]-a[1]).slice(0,10);
            const tCentD = Object.entries(s.Centinela.Definicion).sort((a,b)=>b[1]-a[1]).slice(0,10);
            const tCentP = Object.entries(s.Centinela.Proceso).sort((a,b)=>b[1]-a[1]); // DIBUJAR PROCESO
            
            draw('chartServicioCent', 'bar', tCentS.map(x=>x[0]), tCentS.map(x=>x[1]), '#212529'); drawTable('tablaServicioCent', s.Centinela.Servicio, 'Servicio');
            draw('chartDefinicionCent', 'bar', tCentD.map(x=>x[0]), tCentD.map(x=>x[1]), '#212529'); drawTable('tablaDefinicionCent', s.Centinela.Definicion, 'Causa');
            draw('chartProcesoCent', 'bar', tCentP.map(x=>x[0]), tCentP.map(x=>x[1]), '#212529', { indexAxis: 'y' }); drawTable('tablaProcesoCent', s.Centinela.Proceso, 'Proceso Relacionado');
            
        }, 200);
    };

    // --- EXCEL ---
    $(document).on('click', '#btnDescargarExcelStats', async function() {
        const btn = this; const originalText = btn.innerHTML; 
        btn.innerHTML = "⏳ Generando..."; btn.disabled = true;
        try {
            const rows = tabla ? tabla.rows({ search: 'applied' }).data().toArray() : window.dbDatos;
            if (rows.length === 0) throw new Error("No hay datos");
            
            let s = { General: { Sexo:{}, Evento:{}, Turno:{}, Servicio:{}, Edad:{}, Proceso:{} }, Adverso:{Servicio:{}, Definicion:{}, Proceso:{}}, Cuasi:{Servicio:{}, Definicion:{}, Proceso:{}}, Centinela:{Servicio:{}, Definicion:{}, Proceso:{}} };
            rows.forEach(r => {
                let ev = clean(r.evento), ed = clean(r.edad), sx = clean(r.sexo), tu = clean(r.turno), sv = clean(r.servicio), def = clean(r.definicion), pro = clean(r.proceso);
                s.General.Sexo[sx] = (s.General.Sexo[sx] || 0) + 1; s.General.Evento[ev] = (s.General.Evento[ev] || 0) + 1; s.General.Turno[tu] = (s.General.Turno[tu] || 0) + 1; s.General.Servicio[sv] = (s.General.Servicio[sv] || 0) + 1;
                s.General.Proceso[pro] = (s.General.Proceso[pro] || 0) + 1;
                
                if (!s.General.Edad[ed]) s.General.Edad[ed] = { H: 0, M: 0 };
                (sx.toUpperCase().includes('MASC') || sx.toUpperCase().includes('HOMBRE') || sx==='M') ? s.General.Edad[ed].H++ : s.General.Edad[ed].M++;
                let evUpper = ev.toUpperCase();
                if(evUpper.includes('ADVERSO')) { s.Adverso.Servicio[sv]=(s.Adverso.Servicio[sv]||0)+1; s.Adverso.Definicion[def]=(s.Adverso.Definicion[def]||0)+1; s.Adverso.Proceso[pro]=(s.Adverso.Proceso[pro]||0)+1;}
                else if(evUpper.includes('CUASI')) { s.Cuasi.Servicio[sv]=(s.Cuasi.Servicio[sv]||0)+1; s.Cuasi.Definicion[def]=(s.Cuasi.Definicion[def]||0)+1; s.Cuasi.Proceso[pro]=(s.Cuasi.Proceso[pro]||0)+1;}
                else if(evUpper.includes('CENTINELA')) { s.Centinela.Servicio[sv]=(s.Centinela.Servicio[sv]||0)+1; s.Centinela.Definicion[def]=(s.Centinela.Definicion[def]||0)+1; s.Centinela.Proceso[pro]=(s.Centinela.Proceso[pro]||0)+1;}
            });

            const workbook = new ExcelJS.Workbook();
            const generarImagenFantasma = (type, labels, data, color, title) => {
                return new Promise((resolve, reject) => {
                    const canvas = document.createElement('canvas'); canvas.width = 800; canvas.height = 400; const ctx = canvas.getContext('2d');
                    const whiteBackground = { id: 'custom_bg', beforeDraw: (chart) => { const ctx = chart.canvas.getContext('2d'); ctx.save(); ctx.globalCompositeOperation = 'destination-over'; ctx.fillStyle = 'white'; ctx.fillRect(0, 0, chart.width, chart.height); ctx.restore(); } };
                    const tempChart = new Chart(ctx, { type: type, data: { labels: labels, datasets: [{ label: 'Total', data: data, backgroundColor: color }] }, plugins: [whiteBackground], options: { indexAxis: (type==='bar'&&title.includes('PROCESO')?'y':'x'), responsive: false, animation: { onComplete: function() { try { const b64 = canvas.toDataURL('image/png'); tempChart.destroy(); resolve(b64); } catch (e) { reject(e); } } }, plugins: { title: { display: true, text: title, font: {size: 18} }, legend: {display: (type==='pie')} } } });
                });
            };

            const addSection = async (ws, title, obj, chartConfig, startRow) => {
                ws.getCell(`A${startRow}`).value = title; ws.getCell(`A${startRow}`).font = {bold:true, size:14, color:{argb:'FF7A123A'}};
                ws.getCell(`A${startRow+1}`).value = "Concepto"; ws.getCell(`B${startRow+1}`).value = "Total";
                ws.getCell(`A${startRow+1}`).fill = {type:'pattern', pattern:'solid', fgColor:{argb:'FF333333'}}; ws.getCell(`A${startRow+1}`).font = {color:{argb:'FFFFFFFF'}}; ws.getCell(`B${startRow+1}`).fill = {type:'pattern', pattern:'solid', fgColor:{argb:'FF333333'}}; ws.getCell(`B${startRow+1}`).font = {color:{argb:'FFFFFFFF'}};
                let cr = startRow + 2; let gl = [], gd = [];
                Object.entries(obj).sort((a,b) => ((typeof b[1]==='object'?b[1].H+b[1].M:b[1])-(typeof a[1]==='object'?a[1].H+a[1].M:a[1]))).forEach(([k,v]) => {
                    let val = typeof v === 'object' ? v.H+v.M : v;
                    ws.getCell(`A${cr}`).value = k; ws.getCell(`B${cr}`).value = val; cr++;
                    if(gl.length < 10) { gl.push(k); gd.push(val); }
                });
                if(gl.length > 0 && chartConfig) { try { const imgB64 = await generarImagenFantasma(chartConfig.type, gl, gd, chartConfig.color, title); const imgId = workbook.addImage({ base64: imgB64, extension: 'png' }); ws.addImage(imgId, { tl: { col: 3, row: startRow }, ext: { width: 500, height: 300 } }); } catch(e) { console.warn("Error gráfico excel:", e); } }
                return Math.max(cr, startRow + 16) + 2;
            };

            const wsG = workbook.addWorksheet('Generales'); wsG.getColumn(1).width=40; let r = 1;
            r = await addSection(wsG, "SEXO", s.General.Sexo, {type:'pie',color:['#0d6efd','#dc3545','#ffc107']}, r);
            r = await addSection(wsG, "EVENTOS", s.General.Evento, {type:'bar',color:'#7a123a'}, r);
            r = await addSection(wsG, "EDAD", s.General.Edad, {type:'bar',color:'#0d6efd'}, r);
            r = await addSection(wsG, "TURNOS", s.General.Turno, {type:'bar',color:'#198754'}, r);
            r = await addSection(wsG, "SERVICIOS", s.General.Servicio, {type:'bar',color:'#0288d1'}, r);
            r = await addSection(wsG, "PROCESOS", s.General.Proceso, {type:'bar',color:'#6f42c1'}, r);

            const addT = async (n, d, c, f) => {
                const ws = workbook.addWorksheet(n); ws.getColumn(1).width=45; let rx=1;
                rx = await addSection(ws, `TOP ÁREAS (${n})`, d.Servicio, {type:'bar',color:c}, rx);
                rx = await addSection(ws, `CAUSAS GLOBALES (${n})`, d.Definicion, {type:'bar',color:c}, rx);
                if(d.Proceso) rx = await addSection(ws, `PROCESOS RELACIONADOS (${n})`, d.Proceso, {type:'bar',color:c}, rx);
            };
            await addT('Adversos', s.Adverso, '#dc3545', true);
            await addT('Cuasifallas', s.Cuasi, '#ffc107', true);
            await addT('Centinelas', s.Centinela, '#212529', false);

            const buff = await workbook.xlsx.writeBuffer();
            saveAs(new Blob([buff]), 'Reporte_VENCER_Final.xlsx');
        } catch (err) { console.error(err); alert("Error al generar Excel: " + err.message); } 
        finally { btn.innerHTML = originalText; btn.disabled = false; }
    });

    // LISTENERS
    $('#filtroAnio, #filtroMesInicio, #filtroMesFin').on('change', function() { if(tabla) tabla.draw(); generarGraficos(); });
    $('#modalGraficos').on('shown.bs.modal', generarGraficos);
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', generarGraficos);
    $(document).on('click', '.toggle-text', function() { $(this).parent().find('.text-full, .text-short').toggleClass('d-none'); });
    $(document).on('change', '#filtroServicioGrafico', generarGraficos);

    cargarDatos();
});
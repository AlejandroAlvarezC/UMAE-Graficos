document.addEventListener('DOMContentLoaded', function() {
    console.log("🚀 VENCER: Iniciando... (Versión Anti-Caché V4)");

    const COL = { EVENTO: 1, EDAD: 4, SEXO: 5, FECHA: 7, TURNO: 9, SERVICIO: 10, DEFINICION: 13, ANIO: 16 };
    window.dbDatos = []; 
    let tabla; 

    const clean = (txt) => {
        if (txt === null || txt === undefined) return 'NULO';
        let str = String(txt);
        str = str.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&');
        str = str.replace(/<\/?(span|div|p|br|b|strong|i|em|a|ul|li|table|tr|td|th)[^>]*>/gi, '');
        str = str.replace(/Ver más|Ver menos/g, '').trim();
        try { return decodeURIComponent(escape(str)); } 
        catch (e) {
            const mapa = {'Ã±':'ñ','Ã‘':'Ñ','Ã¡':'á','Ã©':'é','Ãed':'í','Ã³':'ó','Ãº':'ú','Ã':'Á','Ã‰':'É','Ã':'Í','Ã“':'Ó','Ãš':'Ú'};
            return str.replace(/Ã±|Ã‘|Ã¡|Ã©|Ãed|Ã³|Ãº|Ã|Ã‰|Ã|Ã“|Ãš/g, m => mapa[m] || m);
        }
    };

    const categorizarEdad = (textoBruto) => {
        if (!textoBruto || textoBruto === 'NULO') return "No especificado";
        let str = String(textoBruto).toUpperCase().trim();
        
        if (str.includes('<') || str.includes('MES') || str.includes('DIA') || str.includes('DÍA') || str.includes('MENOR')) return "Menores de 1 año";
        
        let match = str.match(/\d+/); 
        if (match) {
            let num = parseInt(match[0], 10);
            if (num === 0) return "Menores de 1 año";
            if (num >= 1 && num <= 4) return "1 a 4 años";
            if (num >= 5 && num <= 9) return "5 a 9 años";
            if (num >= 10 && num <= 14) return "10 a 14 años";
            if (num >= 15 && num <= 19) return "15 a 19 años";
            if (num >= 20 && num <= 29) return "20 a 29 años";
            if (num >= 30 && num <= 39) return "30 a 39 años";
            if (num >= 40 && num <= 49) return "40 a 49 años";
            if (num >= 50 && num <= 59) return "50 a 59 años";
            if (num >= 60) return "60 y más";
        }
        if (str.includes('MAYOR')) return "60 y más";
        return str; 
    };

    const truncarTexto = (texto, max = 35) => {
        if (texto.length > max) return texto.substring(0, max) + '...';
        return texto;
    };

    const cargarDatos = async () => {
        const loader = document.getElementById('loaderTabla');
        try {
            const response = await fetch('../../controladores/vencer.php?a=ObtenerDatosJSON');
            if (!response.ok) throw new Error("Error red");
            const data = await response.json();
            if (Array.isArray(data)) {
                window.dbDatos = data;
                
                // =========================================================
                // 🕵️ MODO DETECTIVE: IMPRIMIR LAS EDADES REALES EN CONSOLA
                // =========================================================
                let edadesUnicas = new Set();
                window.dbDatos.forEach(r => edadesUnicas.add(r.edad));
                console.log("🚨 EDADES CRUDAS EN LA BASE DE DATOS:", Array.from(edadesUnicas));
                // =========================================================

                inicializarTabla();
                initFiltros(); 
                if(loader) loader.style.display = 'none';
            } else { throw new Error(data.message || "Formato inválido"); }
        } catch (error) { if(loader) loader.innerHTML = `<div class="alert alert-danger">${error.message}</div>`; }
    };

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
                { data: null, orderable: false, render: () => '' }
            ],
            pageLength: 10, deferRender: true
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
            window.dbDatos.forEach(row => { let txt = clean(row[mapIndices[key]]); if(txt && txt!=='NULO' && txt!=='') uniqueValues.add(txt); });
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
        return okAnio && okMes;
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

            let s = { 
                General: { Sexo:{}, Evento:{}, Turno:{}, Servicio:{}, Edad:{}, Proceso:{} }, 
                Adverso: { Servicio:{}, Definicion:{}, Sexo:{}, Edad:{}, Proceso:{} }, 
                Cuasi:   { Servicio:{}, Definicion:{}, Sexo:{}, Edad:{}, Proceso:{} }, 
                Centinela: { Servicio:{}, Definicion:{}, Proceso:{} } 
            };

            rows.forEach(r => {
                let ev = clean(r.evento), sx = clean(r.sexo), tu = clean(r.turno), sv = clean(r.servicio), def = clean(r.definicion), pro = clean(r.proceso);
                let catEdad = categorizarEdad(r.edad); 
                
                s.General.Sexo[sx] = (s.General.Sexo[sx] || 0) + 1; 
                s.General.Evento[ev] = (s.General.Evento[ev] || 0) + 1; 
                s.General.Turno[tu] = (s.General.Turno[tu] || 0) + 1; 
                s.General.Servicio[sv] = (s.General.Servicio[sv] || 0) + 1;
                s.General.Proceso[pro] = (s.General.Proceso[pro] || 0) + 1; 
                
                if (!s.General.Edad[catEdad]) s.General.Edad[catEdad] = { H: 0, M: 0 };
                let esHombre = sx.toUpperCase().includes('MASC') || sx.toUpperCase().startsWith('H') || sx.toUpperCase() === 'M';
                esHombre ? s.General.Edad[catEdad].H++ : s.General.Edad[catEdad].M++;
                
                let evUpper = ev.toUpperCase();
                if(evUpper.includes('ADVERSO')) { 
                    s.Adverso.Servicio[sv]=(s.Adverso.Servicio[sv]||0)+1; s.Adverso.Definicion[def]=(s.Adverso.Definicion[def]||0)+1; 
                    s.Adverso.Sexo[sx]=(s.Adverso.Sexo[sx]||0)+1;
                    s.Adverso.Proceso[pro]=(s.Adverso.Proceso[pro]||0)+1; 
                    if (!s.Adverso.Edad[catEdad]) s.Adverso.Edad[catEdad] = { H: 0, M: 0 };
                    esHombre ? s.Adverso.Edad[catEdad].H++ : s.Adverso.Edad[catEdad].M++;
                }
                else if(evUpper.includes('CUASI')) { 
                    s.Cuasi.Servicio[sv]=(s.Cuasi.Servicio[sv]||0)+1; s.Cuasi.Definicion[def]=(s.Cuasi.Definicion[def]||0)+1;
                    s.Cuasi.Sexo[sx]=(s.Cuasi.Sexo[sx]||0)+1;
                    s.Cuasi.Proceso[pro]=(s.Cuasi.Proceso[pro]||0)+1; 
                    if (!s.Cuasi.Edad[catEdad]) s.Cuasi.Edad[catEdad] = { H: 0, M: 0 };
                    esHombre ? s.Cuasi.Edad[catEdad].H++ : s.Cuasi.Edad[catEdad].M++;
                }
                else if(evUpper.includes('CENTINELA')) { 
                    s.Centinela.Servicio[sv]=(s.Centinela.Servicio[sv]||0)+1; s.Centinela.Definicion[def]=(s.Centinela.Definicion[def]||0)+1; 
                    s.Centinela.Proceso[pro]=(s.Centinela.Proceso[pro]||0)+1; 
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
                el.style.display = 'block'; // FORZAR VISIBILIDAD DE LA TABLA
            };

            const drawPiramide = (canvasId, tablaId, dataEdad) => {
                const elCanvas = document.getElementById(canvasId); if(!elCanvas) return;
                
                if (elCanvas.parentNode) elCanvas.parentNode.style.height = '250px';

                let rangosOrden = ["Menores de 1 año", "1 a 4 años", "5 a 9 años", "10 a 14 años", "15 a 19 años", "20 a 29 años", "30 a 39 años", "40 a 49 años", "50 a 59 años", "60 y más", "No especificado"];
                
                Object.keys(dataEdad).forEach(k => {
                    if (!rangosOrden.includes(k)) rangosOrden.push(k);
                });

                let dataH = [], dataM = [];
                let objTabla = {};
                
                rangosOrden.forEach(rango => {
                    let hCount = dataEdad[rango] ? dataEdad[rango].H : 0;
                    let mCount = dataEdad[rango] ? dataEdad[rango].M : 0;
                    dataH.push(0 - hCount);
                    dataM.push(mCount);
                    if(hCount > 0 || mCount > 0) objTabla[rango] = { H: hCount, M: mCount };
                });

                if(charts[canvasId]) charts[canvasId].destroy();
                charts[canvasId] = new Chart(elCanvas, {
                    type: 'bar',
                    data: { labels: rangosOrden, datasets: [{ label: 'Hombres', data: dataH, backgroundColor: 'rgba(54, 162, 235, 0.7)', borderWidth: 1 }, { label: 'Mujeres', data: dataM, backgroundColor: 'rgba(255, 99, 132, 0.7)', borderWidth: 1 }] },
                    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, scales: { x: { stacked: true, ticks: { callback: v => Math.abs(v) } }, y: { stacked: true, grid: { display: false } } }, plugins: { tooltip: { callbacks: { label: c => c.dataset.label + ': ' + Math.abs(c.raw) } } } }
                });
                
                drawTable(tablaId, objTabla, 'Rango de Edad');
            };

            // DIBUJAR TODO LO DEMÁS
            draw('chartSexoGen', 'pie', Object.keys(s.General.Sexo), Object.values(s.General.Sexo), ['#0d6efd','#dc3545','#ffc107']); drawTable('tablaSexoGen', s.General.Sexo, 'Sexo');
            draw('chartEventosGen', 'bar', Object.keys(s.General.Evento), Object.values(s.General.Evento), '#7a123a'); drawTable('tablaEventosGen', s.General.Evento, 'Evento');
            drawPiramide('chartPiramide', 'tablaEdadSexoGen', s.General.Edad);
            
        }, 200);
    };

    $('#modalGraficos').on('shown.bs.modal', generarGraficos);
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', generarGraficos);
    $(document).on('change', '#filtroServicioGrafico', generarGraficos);

    cargarDatos();
});
import React, { useState, useEffect, useMemo } from 'react';
import axios from 'axios';
import localforage from 'localforage';
import IndicadoresHosp from './IndicadoresHosp.jsx'; 
import { Target, Calendar, PieChart, Activity, LayoutDashboard, Users, FileText, Filter, ClipboardList, ChevronLeft, ChevronRight, Loader2 } from 'lucide-react';
import { Line } from 'react-chartjs-2';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

// ==========================================
// CONFIGURACIÓN GLOBAL DE GRÁFICAS (FUENTE)
// ==========================================
ChartJS.defaults.font.family = 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif';
ChartJS.defaults.color = '#475569'; 

const MESES = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];

// ==========================================
// FUNCIONES UTILITARIAS INDEPENDIENTES
// ==========================================
const obtenerDiasOperativos = (mesSeleccionado, anioSeleccionado) => {
    const anioAnterior = mesSeleccionado === 0 ? anioSeleccionado - 1 : anioSeleccionado;
    const mesAnterior = mesSeleccionado === 0 ? 11 : mesSeleccionado - 1;
    const inicio = new Date(anioAnterior, mesAnterior, 26, 12, 0, 0);
    const fin = new Date(anioSeleccionado, mesSeleccionado, 25, 12, 0, 0);
    
    const dias = [];
    let actual = new Date(inicio);
    while (actual <= fin) {
        dias.push(new Date(actual));
        actual.setDate(actual.getDate() + 1);
    }
    return dias;
};

// ==========================================
// ALGORITMO DE CALENDARIO OPERATIVO 
// Regla: S1 (26 al domingo), Intermedias (Lun-Dom), Final (hasta el 25)
// ==========================================
const generarCalendarioIMSS = (mesSeleccionado, anioSeleccionado) => {
    const anioAnterior = mesSeleccionado === 0 ? anioSeleccionado - 1 : anioSeleccionado;
    const mesAnterior = mesSeleccionado === 0 ? 11 : mesSeleccionado - 1;
    
    // Rango Operativo estricto
    const fechaInicio = new Date(anioAnterior, mesAnterior, 26, 12, 0, 0);
    const fechaFin = new Date(anioSeleccionado, mesSeleccionado, 25, 12, 0, 0);

    let semanas = [];
    let fechaActual = new Date(fechaInicio);
    let numeroSemana = 1;
    let diasDeEstaSemana = [];

    while (fechaActual <= fechaFin) {
        const yyyy = fechaActual.getFullYear();
        const mm = String(fechaActual.getMonth() + 1).padStart(2, '0');
        const dd = String(fechaActual.getDate()).padStart(2, '0');
        const iso = `${yyyy}-${mm}-${dd}`;
        
        diasDeEstaSemana.push(iso);

        // REGLA DE CORTE:
        // Se cierra la semana si es DOMINGO o si es el DÍA 25 (fin de mes operativo)
        const esDomingo = fechaActual.getDay() === 0;
        const esDia25 = fechaActual.getDate() === 25;

        if (esDomingo || esDia25) {
            semanas.push({
                semana: numeroSemana,
                diasISO: [...diasDeEstaSemana]
            });
            
            numeroSemana++;
            diasDeEstaSemana = []; 
        }
        
        fechaActual.setDate(fechaActual.getDate() + 1);
    }

    const nombresMeses = ["ene", "feb", "mar", "abr", "may", "jun", "jul", "ago", "sep", "oct", "nov", "dic"];
    
    // Generar etiquetas visuales descriptivas
    semanas.forEach(s => {
        if (s.diasISO.length > 0) {
            const firstISO = s.diasISO[0].split('-');
            const lastISO = s.diasISO[s.diasISO.length - 1].split('-');
            
            const d1 = firstISO[2];
            const m1 = nombresMeses[parseInt(firstISO[1], 10) - 1];
            
            const d2 = lastISO[2];
            const m2 = nombresMeses[parseInt(lastISO[1], 10) - 1];
            
            s.label = `S${s.semana} (${d1}-${m1} al ${d2}-${m2})`;
        }
    });

    return semanas;
};

const nivelarTexto = (texto) => String(texto || '').trim().toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

const encontrarFecha = (obj) => {
    const keys = Object.keys(obj);
    for (let k of keys) { 
        const lk = k.toLowerCase();
        if (lk === 'fecha' || lk === 'fecha_cita' || lk === 'fecha_consulta') return obj[k]; 
    }
    for (let k of keys) { 
        const lk = k.toLowerCase();
        if (lk.includes('fecha') && !lk.includes('nacimiento') && !lk.includes('alta')) return obj[k]; 
    }
    return null;
};

const extraerFechaLimpiaYMD = (f_val) => {
    if (!f_val) return null;
    if (f_val instanceof Date && !isNaN(f_val)) {
        return { anio: f_val.getFullYear(), mes: f_val.getMonth() + 1, dia: f_val.getDate() };
    }
    let str = String(f_val).trim().split('T')[0].split(' ')[0];
    const p = str.includes('-') ? str.split('-') : str.split('/');
    if (p.length >= 3) {
        let a, m, d;
        if (p[0].length === 4) { a = p[0]; m = p[1]; d = p[2]; }
        else { a = p[2]; m = p[1]; d = p[0]; }
        if (!isNaN(a) && !isNaN(m) && !isNaN(d)) {
            return { anio: parseInt(a, 10), mes: parseInt(m, 10), dia: parseInt(d, 10) };
        }
    }
    const fallback = new Date(f_val);
    if (!isNaN(fallback)) {
        return { anio: fallback.getFullYear(), mes: fallback.getMonth() + 1, dia: fallback.getDate() };
    }
    return null; 
};

// ==========================================
// ELIMINADOR DE DUPLICADOS (VERSIÓN PURA)
// ==========================================
const unificarNombreConsultorio = (crudo) => {
    if (!crudo) return "SIN ESPECIFICAR";
    let str = String(crudo).toUpperCase().trim();
    if (str === "" || str === "0" || str === "NULL" || str === "SIN ESPECIFICAR") {
        return "SIN ESPECIFICAR";
    }
    if (str.endsWith('.0')) str = str.replace('.0', '');
    str = str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    let raiz = str.replace(/CONSULTORIO/g, '').trim();
    if (raiz === "") return "SIN ESPECIFICAR";
    if (!isNaN(raiz)) {
        return `CONSULTORIO ${parseInt(raiz, 10)}`;
    }
    return str; 
};

// ==========================================
// COMPONENTE PRINCIPAL
// ==========================================
const ModuloIndicadores = () => {
    const [tabActiva, setTabActiva] = useState('mensual'); 
    const [seccionSidebar, setSeccionSidebar] = useState('consulta_externa');
    const [cargandoDatos, setCargandoDatos] = useState(true);
    
    const [datos, setDatos] = useState([]);
    const [diccionarioEspecialidades, setDiccionarioEspecialidades] = useState({});

    const fechaActual = new Date();
    const [mesGraficoMeta, setMesGraficoMeta] = useState(fechaActual.getMonth()); 
    const [anioGraficoMeta, setAnioGraficoMeta] = useState(fechaActual.getFullYear());

    const [sidebarColapsada, setSidebarColapsada] = useState(false);

    const cargarDatos = async () => {
        try {
            const datosLocales = await localforage.getItem('cache_productividad_vencer');
            if (datosLocales && datosLocales.length > 0) setDatos(datosLocales); 
            const resDatos = await axios.get('/api/api_productividad.php');
            if (Array.isArray(resDatos.data)) setDatos(resDatos.data);
        } catch (err) { console.error(err); } finally { setCargandoDatos(false); }
    };

    const cargarCatalogos = async () => {
        try {
            const resEsp = await axios.get(`/api/api_crud_especialidades.php?t=${new Date().getTime()}`);
            if (Array.isArray(resEsp.data)) {
                const diccEsp = resEsp.data.reduce((acc, item) => {
                    const clave = String(item.clave).trim().toUpperCase();
                    if (clave) acc[clave] = { nombre: item.nombre, division: item.division };
                    return acc;
                }, {});
                setDiccionarioEspecialidades(diccEsp);
            }
        } catch (err) { console.error("Error cargando catálogos", err); }
    };

    useEffect(() => { 
        cargarDatos(); 
        cargarCatalogos();
    }, []);

    const traducirEspecialidad = (valorCrudo) => {
        if (!valorCrudo) return 'Desconocida';
        let espRaw = String(valorCrudo).trim().toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        espRaw = espRaw.replace('COD:', '').replace('COD: ', '').replace('.0', '').trim();
        const respaldoInquebrantable = { '6300': 'TRABAJO SOCIAL', '6600': 'PSICOLOGIA', '6900': 'NUTRICION', '5001': 'CONSULTAS EN PRIMER CONTACTO', 'A600': 'URGENCIAS TOCO CIRUGIA' };
        return diccionarioEspecialidades[espRaw]?.nombre || respaldoInquebrantable[espRaw] || espRaw;
    };

    const aniosDisponibles = useMemo(() => {
        const anios = new Set();
        datos.forEach(d => {
            let a = d.anio || d.Anio || d.ANIO || d.año || d.Año || d.AÑO;
            if (a) anios.add(String(a));
        });
        return [...anios].sort().reverse();
    }, [datos]);

    const datosConsultaExterna = useMemo(() => {
        if (!datos || datos.length === 0) return [];
        return datos.filter(d => {
            const espNivelada = nivelarTexto(d.especialidad || d.ESPECIALIDAD);
            const espTraducida = nivelarTexto(traducirEspecialidad(d.especialidad || d.ESPECIALIDAD));
            const ignorar = ['TOCO', 'PRIMER CONTACTO', '5001', '6300', '6600', '6900', 'NUTRICION', 'TRABAJO SOCIAL', 'PSICOLOGIA','URGENCIAS', 'ADMISION CONTINUA'];
            return !ignorar.some(ig => espNivelada.includes(ig) || espTraducida.includes(ig));
        });
    }, [datos, diccionarioEspecialidades]);

    // ==========================================
    // TABLA: FUSIÓN DE DATOS Y ORDEN ALFANUMÉRICO
    // ==========================================
    const tablaProductividadConsultorios = useMemo(() => {
        if (seccionSidebar !== 'consulta_externa' || datosConsultaExterna.length === 0) return null;

        const dias = obtenerDiasOperativos(mesGraficoMeta, anioGraficoMeta);
        
        // Formato estricto YYYY-MM-DD
        const diasISO = dias.map(d => {
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        });
        
        const consultoriosMap = {};
        let totalGeneral = 0; 

        // Asegurar que "SIN ESPECIFICAR" siempre exista
        if (!consultoriosMap["SIN ESPECIFICAR"]) {
            consultoriosMap["SIN ESPECIFICAR"] = {};
            diasISO.forEach(iso => consultoriosMap["SIN ESPECIFICAR"][iso] = 0);
        }

        // Iterar TODO el universo de Consulta Externa (Citados y No Citados)
        datosConsultaExterna.forEach(d => {
            const f = encontrarFecha(d);
            const fechaObj = extraerFechaLimpiaYMD(f);
            
            if (fechaObj) {
                const fechaISO = `${fechaObj.anio}-${String(fechaObj.mes).padStart(2,'0')}-${String(fechaObj.dia).padStart(2,'0')}`;
                
                if (diasISO.includes(fechaISO)) {
                    let idCrudo = d.nombre_consultorio || d.NOMBRE_CONSULTORIO || d.consultorio || d.CONSULTORIO || d.Consultorio || d.desc_consultorio; 
                    
                    if (!idCrudo || String(idCrudo).trim() === '') {
                        idCrudo = "SIN ESPECIFICAR";
                    }

                    const nombreUnificado = unificarNombreConsultorio(idCrudo);
                    
                    if (!consultoriosMap[nombreUnificado]) {
                        consultoriosMap[nombreUnificado] = {};
                        diasISO.forEach(iso => consultoriosMap[nombreUnificado][iso] = 0);
                    }
                    
                    consultoriosMap[nombreUnificado][fechaISO]++;
                    totalGeneral++; 
                }
            }
        });

        const filas = Object.entries(consultoriosMap)
            .map(([nombre, conteos]) => {
                return {
                    nombre: nombre, 
                    conteos,
                    totalFila: Object.values(conteos).reduce((a, b) => a + b, 0)
                };
            })
            .filter(f => f.nombre !== "SIN ESPECIFICAR" || f.totalFila > 0)
            .sort((a, b) => b.nombre.localeCompare(a.nombre, undefined, { numeric: true, sensitivity: 'base' }));

        const totalesPorDia = {};
        diasISO.forEach(iso => {
            totalesPorDia[iso] = filas.reduce((sum, fila) => sum + (fila.conteos[iso] || 0), 0);
        });

        return { dias, diasISO, filas, totalGeneral, totalesPorDia };
    }, [datosConsultaExterna, mesGraficoMeta, anioGraficoMeta, seccionSidebar]);

    // ==========================================
    // GRÁFICA DE METAS SEMANALES
    // ==========================================
    const chartMetas = useMemo(() => {
        if (seccionSidebar !== 'consulta_externa' || datosConsultaExterna.length === 0) return null;
        
        const semanasOperativas = generarCalendarioIMSS(mesGraficoMeta, anioGraficoMeta);
        const labelsSemanas = semanasOperativas.map(s => s.label);
        
        const citasPorSemana = new Array(semanasOperativas.length).fill(0);
        let metasPorSemana = new Array(semanasOperativas.length).fill(2646);
        if (Number(mesGraficoMeta) === 0 && metasPorSemana.length > 0) metasPorSemana[0] = 1134; 

        // Creamos un diccionario ultra rápido y blindado (ej: "2026-03-30" => Semana 1)
        const mapaDiasASemana = {};
        semanasOperativas.forEach((sem, index) => {
            sem.diasISO.forEach(iso => {
                mapaDiasASemana[iso] = index;
            });
        });

        datosConsultaExterna.forEach(d => {
            const f = encontrarFecha(d);
            const fechaObj = extraerFechaLimpiaYMD(f);
            
            if (fechaObj) {
                const fechaISO = `${fechaObj.anio}-${String(fechaObj.mes).padStart(2,'0')}-${String(fechaObj.dia).padStart(2,'0')}`;
                
                // Si esta fecha exacta le pertenece a una de nuestras semanas, la sumamos a la gráfica
                if (mapaDiasASemana[fechaISO] !== undefined) {
                    const indiceSemana = mapaDiasASemana[fechaISO];
                    citasPorSemana[indiceSemana]++;
                }
            }
        });

        return {
            labels: labelsSemanas,
            datasets: [
                { 
                    label: 'Consultas Reales', 
                    data: citasPorSemana, 
                    borderColor: '#0d9488', 
                    backgroundColor: 'rgba(13, 148, 136, 0.1)', 
                    borderWidth: 3, 
                    tension: 0.3, 
                    fill: true, 
                    pointBackgroundColor: '#0d9488', 
                    pointRadius: 5 
                },
                { 
                    label: 'Meta Esperada', 
                    data: metasPorSemana, 
                    borderColor: '#64748b', 
                    backgroundColor: 'transparent', 
                    borderDash: [5, 5], 
                    borderWidth: 2, 
                    tension: 0, 
                    pointRadius: 0, 
                    fill: false 
                }
            ]
        };
    }, [datosConsultaExterna, mesGraficoMeta, anioGraficoMeta, seccionSidebar]);

    return (
        <div className="flex h-screen bg-slate-50 overflow-hidden">
            {/* SIDEBAR MENÚ LATERAL */}
           {/* SIDEBAR MENÚ LATERAL COLAPSABLE */}
            <aside className={`${sidebarColapsada ? 'w-20' : 'w-64'} bg-white border-r border-slate-200 flex flex-col z-20 shrink-0 transition-all duration-300 ease-in-out relative`}>
                
                {/* BOTÓN FLOTANTE PARA COLAPSAR/EXPANDIR */}
                <button 
                    onClick={() => setSidebarColapsada(!sidebarColapsada)}
                    className="absolute -right-3 top-8 bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 rounded-full p-1 shadow-md transition-colors z-50 flex items-center justify-center"
                >
                    {sidebarColapsada ? <ChevronRight size={16} /> : <ChevronLeft size={16} />}
                </button>

                {/* TÍTULO DE LA SECCIÓN */}
                <div className={`p-6 text-xs font-bold text-slate-400 uppercase tracking-wider transition-all duration-300 overflow-hidden whitespace-nowrap ${sidebarColapsada ? 'text-center px-0 text-[10px]' : ''}`}>
                    {sidebarColapsada ? 'Menú' : 'Categorías'}
                </div>

                {/* BOTONES DE NAVEGACIÓN */}
                <nav className="flex-1 px-4 space-y-2">
                    
                    {/* Botón HOSP */}
                    <button 
                        onClick={() => setSeccionSidebar('hosp')} 
                        title="Indicadores HOSP"
                        className={`w-full flex items-center ${sidebarColapsada ? 'justify-center p-3' : 'gap-3 px-4 py-3'} rounded-xl text-sm font-medium transition-all ${seccionSidebar === 'hosp' ? "bg-blue-600 text-white shadow-lg" : "text-slate-500 hover:bg-blue-50 hover:text-blue-700"}`}
                    >
                        <LayoutDashboard size={20} className="shrink-0" /> 
                        {!sidebarColapsada && <span className="whitespace-nowrap animate-in fade-in duration-300">Indicadores HOSP</span>}
                    </button>

                    {/* Botón Consulta Externa */}
                    <button 
                        onClick={() => setSeccionSidebar('consulta_externa')} 
                        title="Indicadores Consulta Externa"
                        className={`w-full flex items-center ${sidebarColapsada ? 'justify-center p-3' : 'gap-3 px-4 py-3'} rounded-xl text-sm font-medium transition-all ${seccionSidebar === 'consulta_externa' ? "bg-emerald-600 text-white shadow-lg" : "text-slate-500 hover:bg-emerald-50 hover:text-emerald-700"}`}
                    >
                        <Users size={20} className="shrink-0" /> 
                        {!sidebarColapsada && <span className="whitespace-nowrap animate-in fade-in duration-300">Ind. Consulta Externa</span>}
                    </button>

                </nav>
            </aside>

            {/* CONTENIDO PRINCIPAL */}
            <main className="flex-1 flex flex-col overflow-hidden relative">
                
                {/* CABECERA (HEADER) */}
                <header className="bg-white border-b border-slate-200 px-8 py-6 flex justify-between items-center shrink-0">
                    <div>
                        <h1 className="text-3xl font-black text-slate-800">
                            {seccionSidebar === 'consulta_externa' ? 'Indicadores: Consulta Externa' : 'Indicadores: HOSP'}
                        </h1>
                    </div>
                    <div className="flex bg-slate-100 p-1 rounded-xl shadow-inner border border-slate-200">
                        <button onClick={() => setTabActiva('mensual')} className={`flex items-center gap-2 px-6 py-2 rounded-lg text-sm font-bold transition-all ${tabActiva === 'mensual' ? "bg-white text-emerald-700 shadow-md" : "text-slate-400"}`}><Calendar size={16}/> Mensual</button>
                        <button onClick={() => setTabActiva('acumulado')} className={`flex items-center gap-2 px-6 py-2 rounded-lg text-sm font-bold transition-all ${tabActiva === 'acumulado' ? "bg-white text-emerald-700 shadow-md" : "text-slate-400"}`}><PieChart size={16}/> Acumulado</button>
                    </div>
                </header>

                {/* ZONA DINÁMICA DE PANTALLAS */}
                <section className="flex-1 overflow-y-auto p-8 custom-scrollbar">
                    
                    {/* ESTADO 1: CARGANDO DATOS INICIALES */}
                    {cargandoDatos && (
                        <div className="flex flex-col justify-center items-center h-full text-emerald-600 font-bold">
                            <Activity className="animate-spin mb-4" size={48} />
                            <p>Cargando Indicadores...</p>
                        </div>
                    )}

                    {/* ESTADO 2: DATOS LISTOS (Aquí se deciden las vistas) */}
                    {!cargandoDatos && (
                        <>
                            {/* VISTA A: NUEVO MÓDULO HOSP */}
                            {seccionSidebar === 'hosp' && (
                                <IndicadoresHosp tabActiva={tabActiva} />
                            )}

                            {/* VISTA B: CONSULTA EXTERNA (Gráfica y Tabla) */}
                            {seccionSidebar === 'consulta_externa' && chartMetas && tablaProductividadConsultorios && (
                                <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-10">
                                    
                                    {/* Gráfica de Metas */}
                                    <div className="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                                        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                                            <div className="flex items-center gap-3">
                                                <div className="bg-emerald-50 p-2 rounded-lg"><Target size={24} className="text-emerald-600"/></div>
                                                <div><h3 className="font-bold text-slate-800 text-lg">Cumplimiento de Metas Semanales</h3><p className="text-xs font-medium text-slate-500">Calendario Operativo IMSS</p></div>
                                            </div>
                                            <div className="flex items-center gap-2 bg-slate-50 rounded-lg p-2 border border-slate-200 shadow-inner">
                                                <Filter size={16} className="text-slate-400 ml-1" />
                                                <select className="bg-transparent font-bold text-emerald-700 text-sm outline-none cursor-pointer" value={mesGraficoMeta} onChange={e => setMesGraficoMeta(Number(e.target.value))}>{MESES.map((m, i) => <option key={i} value={i}>{m}</option>)}</select>
                                                <div className="w-px h-5 bg-slate-300 mx-1"></div>
                                                <select className="bg-transparent font-bold text-emerald-700 text-sm outline-none cursor-pointer" value={anioGraficoMeta} onChange={e => setAnioGraficoMeta(Number(e.target.value))}>{aniosDisponibles.map(a => <option key={a} value={a}>{a}</option>)}</select>
                                            </div>
                                        </div>
                                        <div className="h-80 w-full"><Line data={chartMetas} options={{ responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', align: 'end' } }, scales: { y: { beginAtZero: true, grid: { color: '#f8fafc' } }, x: { grid: { display: false } } } }} /></div>
                                    </div>

                                    {/* Tabla de Productividad de Consultorios */}
                                    <div className="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                                        <div className="flex items-center gap-3 mb-6">
                                            <div className="bg-emerald-50 p-2 rounded-lg"><ClipboardList size={24} className="text-emerald-600"/></div>
                                            <div>
                                                <h3 className="font-bold text-slate-800 text-lg">Productividad Diaria por Consultorio</h3>
                                                <div className="flex items-center gap-3 mt-1">
                                                    <span className="flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded"><div className="w-2 h-2 rounded-full bg-emerald-500"></div> Óptimo (≥24)</span>
                                                    <span className="flex items-center gap-1 text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded"><div className="w-2 h-2 rounded-full bg-amber-500"></div> Regular (16-23)</span>
                                                    <span className="flex items-center gap-1 text-[10px] font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded"><div className="w-2 h-2 rounded-full bg-rose-500"></div> Bajo (1-15)</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div className="overflow-x-auto custom-scrollbar border rounded-2xl shadow-sm">
                                            <table className="w-full text-left text-[11px] border-collapse">
                                                <thead className="bg-slate-50 sticky top-0 z-20">
                                                    <tr>
                                                        <th className="p-3 border-b border-r font-black text-slate-600 bg-slate-100 sticky left-0 z-30 min-w-[140px] shadow-[4px_0_10px_rgba(0,0,0,0.03)]">Consultorio</th>
                                                        {tablaProductividadConsultorios.dias.map((d, i) => (
                                                            <th key={i} className="p-2 border-b border-r border-slate-200/50 text-center min-w-[45px] font-bold text-slate-500">
                                                                {d.getDate()}<br/><span className="text-[9px] uppercase font-medium">{MESES[d.getMonth()]}</span>
                                                            </th>
                                                        ))}
                                                        <th className="p-3 border-b border-l font-black text-slate-600 bg-slate-100 sticky right-0 z-30 shadow-[-4px_0_10px_rgba(0,0,0,0.03)]">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {tablaProductividadConsultorios.filas.map((fila, idx) => (
                                                        <tr key={idx} className="hover:bg-slate-50 transition-colors border-b border-slate-100 group">
                                                            <td className="p-3 border-r font-bold text-slate-700 bg-white sticky left-0 z-10 group-hover:bg-slate-50 shadow-[4px_0_10px_rgba(0,0,0,0.03)] uppercase">
                                                                {fila.nombre}
                                                            </td>
                                                            {tablaProductividadConsultorios.diasISO.map((iso, i) => {
                                                                const valor = fila.conteos[iso] || 0;
                                                                let colorCelda = "text-slate-300"; 
                                                                if (valor >= 24) colorCelda = "bg-emerald-100 text-emerald-800 font-black";
                                                                else if (valor >= 16) colorCelda = "bg-amber-100 text-amber-800 font-black";
                                                                else if (valor > 0) colorCelda = "bg-rose-100 text-rose-800 font-black";
                                                                return (
                                                                    <td key={i} className={`p-2 text-center border-r border-slate-100/50 transition-colors ${colorCelda}`}>{valor > 0 ? valor : '-'}</td>
                                                                );
                                                            })}
                                                            <td className="p-3 border-l font-black text-slate-800 bg-slate-50 group-hover:bg-slate-100 sticky right-0 z-10 text-right shadow-[-4px_0_10px_rgba(0,0,0,0.03)]">{fila.totalFila}</td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                                <tfoot className="bg-slate-100 font-black text-slate-800 sticky bottom-0 z-20 shadow-[0_-4px_10px_rgba(0,0,0,0.03)]">
                                                    <tr>
                                                        <td className="p-3 border-t border-r sticky left-0 z-30 bg-slate-100 uppercase text-xs">Total Diario</td>
                                                        {tablaProductividadConsultorios.diasISO.map((iso, i) => (
                                                            <td key={i} className="p-2 border-t border-r border-slate-200/50 text-center text-emerald-700">
                                                                {tablaProductividadConsultorios.totalesPorDia[iso] > 0 ? tablaProductividadConsultorios.totalesPorDia[iso] : '-'}
                                                            </td>
                                                        ))}
                                                        <td className="p-3 border-t border-l sticky right-0 z-30 bg-emerald-600 text-white text-right text-sm shadow-[-4px_0_10px_rgba(0,0,0,0.05)]">
                                                            {tablaProductividadConsultorios.totalGeneral.toLocaleString()}
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            )}

                            {/* VISTA C: PANTALLA VACÍA (Si faltan datos) */}
                            {seccionSidebar === 'consulta_externa' && (!chartMetas || !tablaProductividadConsultorios) && (
                                <div className="flex flex-col items-center justify-center h-full text-slate-300">
                                    <FileText size={64} className="mb-4 opacity-20" />
                                    <p className="font-medium text-lg text-slate-400">Selecciona una categoría para visualizar.</p>
                                </div>
                            )}
                        </>
                    )}
                </section>
            </main>
        </div>
    );
};

export default ModuloIndicadores;
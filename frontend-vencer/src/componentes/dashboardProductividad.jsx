import React, { useState, useEffect, useMemo } from 'react';
import axios from 'axios';
import { UploadCloud, Activity, Users, CalendarCheck, Clock, ArrowLeft, BarChart2, Database, TableProperties, Stethoscope, Ambulance, Bed, Syringe, Siren, ChevronLeft, ChevronRight, Download, Filter, Menu, Award } from 'lucide-react';
import { Bar, Doughnut } from 'react-chartjs-2';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement } from 'chart.js';

// Registrar componentes de Chart.js
ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement);

const MESES = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];

// ==========================================
// SUB-COMPONENTE: Tabla de Datos
// ==========================================
const TablaDatos = ({ titulo1, titulo2, labels, data, total = true }) => {
    if (!labels || !data) return null;
    return (
        <div className="mt-4 border-t border-slate-100 pt-4 animate-in fade-in slide-in-from-top-2 duration-300 h-full">
            <div className="max-h-[350px] overflow-y-auto pr-2 custom-scrollbar">
                <table className="w-full text-left text-sm text-slate-600">
                    <thead className="text-xs text-slate-400 uppercase bg-slate-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th className="py-2 px-3 font-bold rounded-l-lg">{titulo1}</th>
                            <th className="py-2 px-3 font-bold text-right rounded-r-lg">{titulo2}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {labels.map((label, index) => (
                            <tr key={index} className="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                                <td className="py-2 px-3">{label}</td>
                                <td className="py-2 px-3 text-right font-bold">{data[index].toLocaleString()}</td>
                            </tr>
                        ))}
                    </tbody>
                    {total && (
                        <tfoot className="bg-slate-50 font-bold sticky bottom-0 z-10 shadow-sm">
                            <tr>
                                <td className="py-2 px-3 rounded-l-lg">Total</td>
                                <td className="py-2 px-3 text-right rounded-r-lg">{data.reduce((a, b) => a + b, 0).toLocaleString()}</td>
                            </tr>
                        </tfoot>
                    )}
                </table>
            </div>
        </div>
    );
};

export default function DashboardProductividad({ isAdmin }) {
    // ESTADOS DE NAVEGACIÓN
    const [vistaActiva, setVistaActiva] = useState('menu');
    const [areaSidebar, setAreaSidebar] = useState('consulta_externa'); 
    const [mostrarTablas, setMostrarTablas] = useState(false);
    const [sidebarCollapsed, setSidebarCollapsed] = useState(false);

    // ESTADOS DE FILTROS 
    const [anioSeleccionado, setAnioSeleccionado] = useState('todos');
    const [mesSeleccionado, setMesSeleccionado] = useState('todos'); 
    const [mesInicio, setMesInicio] = useState(0); 
    const [mesFin, setMesFin] = useState(11); 
    const [divisionSeleccionada, setDivisionSeleccionada] = useState('todas');
    const [especialidadSeleccionada, setEspecialidadSeleccionada] = useState('todas'); // <-- NUEVO FILTRO

    // ESTADOS DE DATOS
    const [archivo, setArchivo] = useState(null);
    const [mensaje, setMensaje] = useState('');
    const [cargandoSubida, setCargandoSubida] = useState(false);
    const [datos, setDatos] = useState([]);
    const [cargandoDatos, setCargandoDatos] = useState(false);
    const [error, setError] = useState(null);

    const cargarDatos = () => {
        setCargandoDatos(true);
        axios.get('/api/api_productividad.php') 
            .then(res => {
                if (Array.isArray(res.data)) {
                    const datosLimpios = res.data.filter(d => {
                        const esp = (d.especialidad || '').toUpperCase();
                        return !esp.includes('TOCO') && !esp.includes('PRIMER CONTACTO');
                    });
                    setDatos(datosLimpios);
                } else setDatos([]);
                setCargandoDatos(false);
            })
            .catch(err => {
                setError("Error al cargar los datos de productividad.");
                setCargandoDatos(false);
            });
    };

    useEffect(() => {
        if (vistaActiva === 'dashboard' && areaSidebar === 'consulta_externa') cargarDatos();
    }, [vistaActiva, areaSidebar]);

    // RESETEAR ESPECIALIDAD CUANDO CAMBIA LA DIVISIÓN
    useEffect(() => {
        setEspecialidadSeleccionada('todas');
    }, [divisionSeleccionada]);

    const handleSubirArchivo = async (e) => {
        e.preventDefault();
        if (!archivo) { setMensaje('Por favor selecciona un archivo CSV.'); return; }
        setCargandoSubida(true);
        setMensaje('Procesando datos...');

        const formData = new FormData();
        formData.append('archivo_csv', archivo);

        try {
            const respuesta = await axios.post('/api/upload_productividad.php', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            if (respuesta.data.success) {
                setMensaje(`✅ ¡Éxito! ${respuesta.data.message}`);
                setArchivo(null);
            } else setMensaje(`❌ Error: ${respuesta.data.message}`);
        } catch (error) { setMensaje('❌ Error al conectar con el servidor.'); } 
        finally { setCargandoSubida(false); }
    };

    // ==========================================
    // LÓGICA INTELIGENTE DE FILTRADO (TRIPLE CAPA)
    // ==========================================
    const encontrarFecha = (obj) => {
        const keys = Object.keys(obj);
        for (let k of keys) {
            if (k.toLowerCase().includes('fecha')) return obj[k];
        }
        return null;
    };

    const aniosDisponibles = useMemo(() => {
        const anios = new Set();
        datos.forEach(d => {
            let a = d.anio || d.Anio || d.ANIO || d.año || d.Año || d.AÑO;
            if (a) {
                anios.add(String(a));
            } else {
                const f = encontrarFecha(d);
                if (f) {
                    if (f.includes('-')) {
                        const p = f.split('-');
                        anios.add(p[0].length === 4 ? p[0] : p[2]);
                    } else if (f.includes('/')) {
                        const p = f.split('/');
                        anios.add(p[0].length === 4 ? p[0] : p[2]);
                    }
                }
            }
        });
        return [...anios].sort().reverse();
    }, [datos]);

    // CAPA 1: Filtro solo por Fecha (Base para rankings globales)
    const datosFiltradosFecha = useMemo(() => {
        return datos.filter(item => {
            let a = item.anio || item.Anio || item.ANIO || item.año || item.Año || item.AÑO;
            let m = item.mes || item.Mes || item.MES;

            if (!a || !m) {
                const f = encontrarFecha(item);
                if (f) {
                    if (f.includes('-')) {
                        const parts = f.split('-');
                        if (parts[0].length === 4) { a = a || parts[0]; m = m || parts[1]; }
                        else { a = a || parts[2]; m = m || parts[1]; }
                    } else if (f.includes('/')) {
                        const parts = f.split('/');
                        if (parts[0].length === 4) { a = a || parts[0]; m = m || parts[1]; }
                        else { a = a || parts[2]; m = m || parts[1]; }
                    }
                }
            }

            if (!a) return anioSeleccionado === 'todos'; 
            if (!m) m = '1'; 

            const mesIdx = parseInt(m, 10) - 1; 
            const pasaAnio = anioSeleccionado === 'todos' || String(a) === String(anioSeleccionado);
            
            let pasaMes = true;
            if (mesSeleccionado === 'rango') {
                pasaMes = mesIdx >= mesInicio && mesIdx <= mesFin;
            } else if (mesSeleccionado !== 'todos') {
                pasaMes = mesIdx === Number(mesSeleccionado);
            }

            return pasaAnio && pasaMes;
        });
    }, [datos, anioSeleccionado, mesSeleccionado, mesInicio, mesFin]);

    // RANKING DE DIVISIONES (Global)
    const rankingDivisiones = useMemo(() => {
        const conteo = {};
        datosFiltradosFecha.forEach(d => {
            const div = (d.division || 'Sin Asignar').trim();
            conteo[div] = (conteo[div] || 0) + 1;
        });
        return Object.entries(conteo).sort((a, b) => b[1] - a[1]);
    }, [datosFiltradosFecha]);

    const divisionesDisponibles = useMemo(() => {
        return rankingDivisiones.map(item => item[0]).sort();
    }, [rankingDivisiones]);

    // RANKING E INFO DE ESPECIALIDADES (Global)
    const infoEspecialidades = useMemo(() => {
        const conteo = {};
        const divMap = {};
        datosFiltradosFecha.forEach(d => {
            const esp = (d.especialidad || 'Desconocida').trim();
            const div = (d.division || 'Sin Asignar').trim();
            conteo[esp] = (conteo[esp] || 0) + 1;
            if (!divMap[esp]) divMap[esp] = div; // Guardamos a qué división pertenece
        });
        const ranking = Object.entries(conteo).sort((a, b) => b[1] - a[1]);
        return { ranking, divMap };
    }, [datosFiltradosFecha]);

    // CAPA 2: Filtro por División
    const datosFiltradosDivision = useMemo(() => {
        return datosFiltradosFecha.filter(item => {
            if (divisionSeleccionada === 'todas') return true;
            const div = (item.division || 'Sin Asignar').trim();
            return div === divisionSeleccionada;
        });
    }, [datosFiltradosFecha, divisionSeleccionada]);

    // ESPECIALIDADES DISPONIBLES (En base a la división seleccionada)
    const especialidadesDisponibles = useMemo(() => {
        const setEsp = new Set();
        datosFiltradosDivision.forEach(d => {
            setEsp.add((d.especialidad || 'Desconocida').trim());
        });
        return [...setEsp].sort();
    }, [datosFiltradosDivision]);

    // CAPA 3: Filtro Final por Especialidad
    const datosFiltrados = useMemo(() => {
        return datosFiltradosDivision.filter(item => {
            if (especialidadSeleccionada === 'todas') return true;
            const esp = (item.especialidad || 'Desconocida').trim();
            return esp === especialidadSeleccionada;
        });
    }, [datosFiltradosDivision, especialidadSeleccionada]);


    // ==========================================
    // CÁLCULOS PARA GRÁFICAS
    // ==========================================
    const kpis = useMemo(() => {
        let citados = 0, primeraVez = 0;
        datosFiltrados.forEach(d => {
            if (d.citado === 'Citado') citados++;
            if (d.primera_vez === 'Primera Vez') primeraVez++;
        });
        return {
            total: datosFiltrados.length,
            citados, espontaneos: datosFiltrados.length - citados,
            primeraVez, subsecuentes: datosFiltrados.length - primeraVez
        };
    }, [datosFiltrados]);

    const chartDivisiones = useMemo(() => {
        const conteo = datosFiltrados.reduce((acc, curr) => {
            const div = curr.division || 'Sin Asignar';
            acc[div] = (acc[div] || 0) + 1;
            return acc;
        }, {});
        return {
            labels: Object.keys(conteo),
            datasets: [{ data: Object.values(conteo), backgroundColor: ['#822626', '#D4C19C', '#475569', '#1e293b', '#b45309'], borderWidth: 0 }]
        };
    }, [datosFiltrados]);

    const chartTurnos = useMemo(() => {
        const conteo = datosFiltrados.reduce((acc, curr) => {
            const turno = curr.turno || 'Sin Asignar';
            acc[turno] = (acc[turno] || 0) + 1;
            return acc;
        }, {});
        const ordenados = Object.entries(conteo).sort((a, b) => b[1] - a[1]);
        return {
            labels: ordenados.map(item => item[0]),
            datasets: [{ data: ordenados.map(item => item[1]), backgroundColor: ['#475569', '#822626', '#D4C19C', '#1e293b', '#b45309'], borderWidth: 0 }]
        };
    }, [datosFiltrados]);

    const chartEspecialidades = useMemo(() => {
        const conteo = datosFiltrados.reduce((acc, curr) => {
            const esp = curr.especialidad || 'Desconocida';
            acc[esp] = (acc[esp] || 0) + 1;
            return acc;
        }, {});
        const ordenados = Object.entries(conteo).sort((a, b) => b[1] - a[1]);
        return {
            labels: ordenados.map(item => item[0]),
            datasets: [{ label: 'Consultas', data: ordenados.map(item => item[1]), backgroundColor: '#334155', borderRadius: 4 }]
        };
    }, [datosFiltrados]);

    const chartMedicos = useMemo(() => {
        const conteo = datosFiltrados.reduce((acc, curr) => {
            const medico = curr.matricula_medico || 'Sin Matrícula';
            acc[medico] = (acc[medico] || 0) + 1;
            return acc;
        }, {});
        const ordenados = Object.entries(conteo).sort((a, b) => b[1] - a[1]);
        return {
            labels: ordenados.map(item => `Matr. ${item[0]}`),
            datasets: [{ label: 'Consultas', data: ordenados.map(item => item[1]), backgroundColor: '#822626', borderRadius: 4 }]
        };
    }, [datosFiltrados]);

    const chartConsultorios = useMemo(() => {
        const conteo = datosFiltrados.reduce((acc, curr) => {
            const consultorio = curr.consultorio || 'Sin Asignar';
            acc[consultorio] = (acc[consultorio] || 0) + 1;
            return acc;
        }, {});
        const ordenados = Object.entries(conteo).sort((a, b) => b[1] - a[1]);
        return {
            labels: ordenados.map(item => `Cons. ${item[0]}`),
            datasets: [{ label: 'Consultas', data: ordenados.map(item => item[1]), backgroundColor: '#b45309', borderRadius: 4 }]
        };
    }, [datosFiltrados]);

    const chartDiagnosticos = useMemo(() => {
        const conteo = datosFiltrados.reduce((acc, curr) => {
            const diag = curr.diagnostico_principal || 'No Especificado';
            acc[diag] = (acc[diag] || 0) + 1;
            return acc;
        }, {});
        const ordenados = Object.entries(conteo).sort((a, b) => b[1] - a[1]).slice(0, 20);
        return {
            labels: ordenados.map(item => item[0]),
            datasets: [{ label: 'Frecuencia', data: ordenados.map(item => item[1]), backgroundColor: '#1e293b', borderRadius: 4 }]
        };
    }, [datosFiltrados]);

    const anchoDinamico = (cantidadItems) => Math.max(800, cantidadItems * 40); 
    const chartOptionsVertical = {
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { maxRotation: 45, minRotation: 45, autoSkip: false } },
            y: { grid: { display: true, color: '#f1f5f9' } }
        }
    };

    // ==========================================
    // VISTA 1: MENÚ PRINCIPAL
    // ==========================================
    if (vistaActiva === 'menu') {
        return (
            <div className="bg-slate-50 min-h-screen flex flex-col items-center pt-20 px-8">
                <div className="text-center mb-12">
                    <h1 className="text-4xl font-black text-[#822626] mb-2">Módulo de Productividad</h1>
                    <p className="text-slate-500 font-bold uppercase tracking-widest text-sm">UMAE 48 - Panel Central</p>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 w-full max-w-4xl">
                    <button onClick={() => setVistaActiva('dashboard')} className="bg-white p-8 rounded-2xl shadow-sm border border-red-100 hover:shadow-md hover:border-red-300 transition-all flex flex-col items-center text-center group">
                        <div className="bg-red-50 p-4 rounded-full mb-4 group-hover:bg-[#822626] group-hover:text-white text-[#822626] transition-colors"><BarChart2 size={40} /></div>
                        <h2 className="text-xl font-black text-slate-800 mb-2">Tableros de Indicadores</h2>
                        <p className="text-slate-500 text-sm">Ingresa para visualizar las gráficas de Consulta Externa, Hospitalización, Cirugía y más.</p>
                    </button>
                    {isAdmin && (
                        <button onClick={() => { setVistaActiva('subir'); setMensaje(''); }} className="bg-white p-8 rounded-2xl shadow-sm border border-red-100 hover:shadow-md hover:border-red-300 transition-all flex flex-col items-center text-center group">
                            <div className="bg-red-50 p-4 rounded-full mb-4 group-hover:bg-[#822626] group-hover:text-white text-[#822626] transition-colors"><Database size={40} /></div>
                            <h2 className="text-xl font-black text-slate-800 mb-2">Actualizar Base de Datos</h2>
                            <p className="text-slate-500 text-sm">Sube el CSV de productividad para actualizar la información de las bases.</p>
                        </button>
                    )}
                </div>
            </div>
        );
    }

    // ==========================================
    // VISTA 2: SUBIR ARCHIVO (Admin)
    // ==========================================
    if (vistaActiva === 'subir') {
        return (
            <div className="bg-slate-50 min-h-screen p-8">
                <button onClick={() => setVistaActiva('menu')} className="flex items-center text-[#822626] hover:text-[#5e1919] font-bold mb-8 transition-colors"><ArrowLeft size={20} className="mr-2" /> Volver al Menú</button>
                <div className="max-w-2xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-red-100">
                    <h2 className="text-2xl font-black text-[#822626] mb-2 flex items-center gap-3"><UploadCloud size={28} /> Subir Productividad (CSV)</h2>
                    <form onSubmit={handleSubirArchivo} className="flex flex-col gap-6 mt-6">
                        <input type="file" accept=".csv" onChange={(e) => setArchivo(e.target.files[0])} className="block w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-red-50 file:text-red-900 hover:file:bg-red-100 cursor-pointer border-2 border-dashed border-slate-200 rounded-xl p-4"/>
                        <button type="submit" disabled={cargandoSubida || !archivo} className="bg-[#822626] text-white font-bold py-3 px-6 rounded-xl hover:bg-[#6b1f1f] transition disabled:bg-slate-300 disabled:cursor-not-allowed w-full shadow-md">{cargandoSubida ? 'Procesando archivo...' : 'Cargar a Base de Datos'}</button>
                    </form>
                    {mensaje && <div className={`mt-6 p-4 rounded-xl text-sm font-bold ${mensaje.includes('✅') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'}`}>{mensaje}</div>}
                </div>
            </div>
        );
    }

    // ==========================================
    // VISTA 3: DASHBOARDS 
    // ==========================================
    return (
        <div className="flex h-screen bg-slate-50 overflow-hidden font-sans">
            
            {/* PANEL LATERAL (SIDEBAR) */}
            <aside className={`bg-[#822626] text-slate-100 flex flex-col hidden md:flex shrink-0 shadow-xl z-20 transition-all duration-300 ${sidebarCollapsed ? 'w-20' : 'w-64'}`}>
                
                {/* BOTÓN COLAPSAR/EXPANDIR (ARRIBA) */}
                <div className="h-16 flex items-center justify-center border-b border-[#6b1f1f] shrink-0">
                    <button 
                        onClick={() => setSidebarCollapsed(!sidebarCollapsed)} 
                        title={sidebarCollapsed ? "Expandir menú" : "Ocultar menú"}
                        className="w-full h-full flex items-center justify-center hover:bg-[#6b1f1f] transition-colors text-white"
                    >
                        {sidebarCollapsed ? <ChevronRight size={24} /> : <ChevronLeft size={24} />}
                    </button>
                </div>

                {/* BOTONES DE NAVEGACIÓN */}
                <nav className="flex-1 overflow-y-auto py-4 px-3 space-y-2 overflow-x-hidden custom-scrollbar">
                    <button onClick={() => setAreaSidebar('consulta_externa')} title="Consulta Externa" className={`w-full flex items-center rounded-xl transition-all ${sidebarCollapsed ? 'justify-center p-3' : 'px-4 py-3 gap-3'} ${areaSidebar === 'consulta_externa' ? 'bg-[#6b1f1f] text-white font-bold shadow-md border-l-4 border-white' : 'hover:bg-[#962e2e] text-red-100 border-l-4 border-transparent'}`}>
                        <Stethoscope size={20} className="shrink-0" /> {!sidebarCollapsed && <span className="whitespace-nowrap">Consulta Externa</span>}
                    </button>
                    <button onClick={() => setAreaSidebar('paramedicos')} title="Paramédicos" className={`w-full flex items-center rounded-xl transition-all ${sidebarCollapsed ? 'justify-center p-3' : 'px-4 py-3 gap-3'} ${areaSidebar === 'paramedicos' ? 'bg-[#6b1f1f] text-white font-bold shadow-md border-l-4 border-white' : 'hover:bg-[#962e2e] text-red-100 border-l-4 border-transparent'}`}>
                        <Ambulance size={20} className="shrink-0" /> {!sidebarCollapsed && <span className="whitespace-nowrap">Paramédicos</span>}
                    </button>
                    <button onClick={() => setAreaSidebar('cirugia')} title="Cirugía" className={`w-full flex items-center rounded-xl transition-all ${sidebarCollapsed ? 'justify-center p-3' : 'px-4 py-3 gap-3'} ${areaSidebar === 'cirugia' ? 'bg-[#6b1f1f] text-white font-bold shadow-md border-l-4 border-white' : 'hover:bg-[#962e2e] text-red-100 border-l-4 border-transparent'}`}>
                        <Syringe size={20} className="shrink-0" /> {!sidebarCollapsed && <span className="whitespace-nowrap">Cirugía</span>}
                    </button>
                    <button onClick={() => setAreaSidebar('hospitalizacion')} title="Hospitalización" className={`w-full flex items-center rounded-xl transition-all ${sidebarCollapsed ? 'justify-center p-3' : 'px-4 py-3 gap-3'} ${areaSidebar === 'hospitalizacion' ? 'bg-[#6b1f1f] text-white font-bold shadow-md border-l-4 border-white' : 'hover:bg-[#962e2e] text-red-100 border-l-4 border-transparent'}`}>
                        <Bed size={20} className="shrink-0" /> {!sidebarCollapsed && <span className="whitespace-nowrap">Hospitalización</span>}
                    </button>
                    <button onClick={() => setAreaSidebar('urgencias')} title="Urgencias" className={`w-full flex items-center rounded-xl transition-all ${sidebarCollapsed ? 'justify-center p-3' : 'px-4 py-3 gap-3'} ${areaSidebar === 'urgencias' ? 'bg-[#6b1f1f] text-white font-bold shadow-md border-l-4 border-white' : 'hover:bg-[#962e2e] text-red-100 border-l-4 border-transparent'}`}>
                        <Siren size={20} className="shrink-0" /> {!sidebarCollapsed && <span className="whitespace-nowrap">Urgencias</span>}
                    </button>
                </nav>

                {/* CONTROLES INFERIORES */}
                <div className="p-3 border-t border-[#6b1f1f] flex flex-col gap-2 shrink-0">
                    
                    {/* Botón Mostrar/Ocultar Tablas */}
                    <button 
                        onClick={() => setMostrarTablas(!mostrarTablas)} 
                        title="Mostrar/Ocultar Tablas"
                        className={`w-full flex items-center rounded-xl transition-all ${sidebarCollapsed ? 'justify-center p-3' : 'px-4 py-3 gap-3'} ${mostrarTablas ? 'bg-[#5e1919] text-white shadow-inner' : 'hover:bg-[#962e2e] text-red-100'}`}
                    >
                        <TableProperties size={20} className="shrink-0" /> 
                        {!sidebarCollapsed && <span className="whitespace-nowrap font-bold text-sm">{mostrarTablas ? 'Ocultar Tablas' : 'Mostrar Tablas'}</span>}
                    </button>

                    {/* Botón Descargar Excel */}
                    <button 
                        onClick={() => { /* Funcionalidad futura */ }} 
                        title="Descargar Reporte Excel"
                        className={`w-full flex items-center rounded-xl transition-all ${sidebarCollapsed ? 'justify-center p-3 bg-emerald-600 hover:bg-emerald-500' : 'px-4 py-3 gap-3 bg-emerald-600 hover:bg-emerald-500'} text-white font-bold shadow-md`}
                    >
                        <Download size={20} className="shrink-0 text-white" /> 
                        {!sidebarCollapsed && <span className="whitespace-nowrap text-sm">Descargar Excel</span>}
                    </button>

                    {/* Botón Volver al Inicio */}
                    <button 
                        onClick={() => setVistaActiva('menu')} 
                        title="Volver al Inicio" 
                        className={`w-full flex items-center bg-slate-800 hover:bg-slate-900 text-white rounded-xl transition-colors font-bold text-sm mt-2 ${sidebarCollapsed ? 'justify-center p-3' : 'justify-center gap-2 p-3'}`}
                    >
                        <ArrowLeft size={16} className="shrink-0" /> {!sidebarCollapsed && <span className="whitespace-nowrap">Volver al Inicio</span>}
                    </button>
                </div>
            </aside>

            {/* ÁREA PRINCIPAL DE CONTENIDO */}
            <div className="flex-1 flex flex-col h-screen overflow-hidden relative">
                
                {/* ENCABEZADO SUPERIOR LIMPIO CON FILTROS */}
                <header className="bg-white border-b border-slate-200 shrink-0 px-4 md:px-8 py-3 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 z-10 transition-all duration-300 min-h-[70px]">
                    <div className="flex items-center gap-4">
                        <button onClick={() => setSidebarCollapsed(!sidebarCollapsed)} className="p-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition-colors hidden md:block" title={sidebarCollapsed ? "Expandir menú" : "Ocultar menú"}>
                            <Menu size={20} />
                        </button>
                        <div>
                            <h1 className="text-xl md:text-2xl font-black text-slate-800 capitalize">{areaSidebar.replace('_', ' ')}</h1>
                            <p className="text-xs text-slate-500 font-bold uppercase tracking-wider hidden sm:block">Tablero de Indicadores</p>
                        </div>
                    </div>
                    
                    {/* CONTENEDOR DERECHO: Filtros */}
                    <div className="flex flex-wrap items-center gap-3 w-full xl:w-auto">
                        {areaSidebar === 'consulta_externa' && datos.length > 0 && !cargandoDatos && !error && (
                            <div className="flex items-center gap-2 bg-slate-50 rounded-lg p-1.5 border border-slate-200 shadow-inner flex-wrap w-full xl:w-auto">
                                <Filter size={14} className="text-[#822626] ml-2 hidden sm:block"/>
                                
                                {/* FILTRO DE AÑO Y MES */}
                                <span className="font-bold text-slate-500 text-[10px] uppercase ml-1">Año:</span>
                                <select className="bg-transparent font-bold text-[#822626] text-sm outline-none cursor-pointer pr-1" value={anioSeleccionado} onChange={e=>setAnioSeleccionado(e.target.value)}>
                                    <option value="todos">Todos</option>
                                    {aniosDisponibles.map(a=><option key={a} value={a}>{a}</option>)}
                                </select>
                                <div className="w-px h-4 bg-slate-300 mx-1"></div>
                                <span className="font-bold text-slate-500 text-[10px] uppercase">Mes:</span>
                                <select className="bg-transparent font-bold text-[#822626] text-sm outline-none cursor-pointer pr-1" value={mesSeleccionado} onChange={e=>setMesSeleccionado(e.target.value)}>
                                    <option value="todos">Todos</option>
                                    {MESES.map((m, i) => <option key={i} value={i}>{m}</option>)}
                                    <option disabled>──────────</option>
                                    <option value="rango">Rango...</option>
                                </select>

                                {/* RANGO DE MESES */}
                                {mesSeleccionado === 'rango' && (
                                    <>
                                        <div className="w-px h-4 bg-slate-300 mx-1"></div>
                                        <span className="font-bold text-slate-500 text-[10px] uppercase">De:</span>
                                        <select className="bg-transparent font-bold text-[#822626] text-sm outline-none cursor-pointer" value={mesInicio} onChange={e=>setMesInicio(Number(e.target.value))}>
                                            {MESES.map((m, i) => <option key={i} value={i}>{m}</option>)}
                                        </select>
                                        <span className="text-slate-400 font-bold">-</span>
                                        <span className="font-bold text-slate-500 text-[10px] uppercase">A:</span>
                                        <select className="bg-transparent font-bold text-[#822626] text-sm outline-none cursor-pointer" value={mesFin} onChange={e=>setMesFin(Number(e.target.value))}>
                                            {MESES.map((m, i) => <option key={i} value={i}>{m}</option>)}
                                        </select>
                                    </>
                                )}

                                {/* FILTRO DE DIVISIÓN */}
                                <div className="w-px h-4 bg-slate-300 mx-1"></div>
                                <span className="font-bold text-slate-500 text-[10px] uppercase">División:</span>
                                <select className="bg-transparent font-bold text-[#822626] text-sm outline-none cursor-pointer pr-1 max-w-[100px] sm:max-w-[150px] truncate" value={divisionSeleccionada} onChange={e=>setDivisionSeleccionada(e.target.value)}>
                                    <option value="todas">Todas</option>
                                    {divisionesDisponibles.map(d=><option key={d} value={d}>{d}</option>)}
                                </select>

                                {/* NUEVO FILTRO: ESPECIALIDAD */}
                                <div className="w-px h-4 bg-slate-300 mx-1"></div>
                                <span className="font-bold text-slate-500 text-[10px] uppercase">Especialidad:</span>
                                <select className="bg-transparent font-bold text-[#822626] text-sm outline-none cursor-pointer pr-1 max-w-[100px] sm:max-w-[150px] truncate" value={especialidadSeleccionada} onChange={e=>setEspecialidadSeleccionada(e.target.value)}>
                                    <option value="todas">Todas</option>
                                    {especialidadesDisponibles.map(e=><option key={e} value={e}>{e}</option>)}
                                </select>
                            </div>
                        )}
                    </div>
                </header>

                {/* CONTENIDO DESPLAZABLE */}
                <main className="flex-1 overflow-y-auto p-4 md:p-8 custom-scrollbar bg-slate-50">
                    {areaSidebar === 'consulta_externa' && (
                        <>
                            {cargandoDatos ? (
                                <div className="flex justify-center items-center py-20 text-[#822626] font-bold"><Activity className="animate-spin mr-3"/> Calculando estadísticas...</div>
                            ) : error ? (
                                <div className="text-red-600 font-bold text-center py-20">{error}</div>
                            ) : datosFiltrados.length === 0 ? (
                                <div className="text-center p-16 border-2 border-dashed border-slate-300 rounded-2xl text-slate-400 mt-10">
                                    <Activity size={48} className="mx-auto mb-4 opacity-50" />
                                    <p className="font-bold text-lg">No hay datos para esta selección</p>
                                    <p className="text-sm">Intenta cambiando el filtro de fecha o eligiendo otra área.</p>
                                </div>
                            ) : (
                                <div className="max-w-[1600px] mx-auto w-full pb-8">
                                    
                                    {/* 1. KPIs PRINCIPALES */}
                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                        <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 border-t-4 border-t-[#822626]"><div className="flex items-center gap-3 text-slate-500 mb-2"><Users size={18}/><h3 className="text-xs font-bold uppercase tracking-widest">Total Consultas</h3></div><p className="text-4xl font-black text-[#822626]">{kpis.total.toLocaleString()}</p></div>
                                        <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200"><div className="flex items-center gap-3 text-slate-500 mb-2"><CalendarCheck size={18}/><h3 className="text-xs font-bold uppercase tracking-widest">Citados</h3></div><p className="text-4xl font-black text-slate-700">{kpis.citados.toLocaleString()}</p><p className="text-xs font-bold text-slate-400 mt-1">Vs {kpis.espontaneos} Espontáneos</p></div>
                                        <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200"><div className="flex items-center gap-3 text-slate-500 mb-2"><Clock size={18}/><h3 className="text-xs font-bold uppercase tracking-widest">Primera Vez</h3></div><p className="text-4xl font-black text-[#c2410c]">{kpis.primeraVez.toLocaleString()}</p><p className="text-xs font-bold text-slate-400 mt-1">Vs {kpis.subsecuentes} Subsecuentes</p></div>
                                    </div>

                                    {/* 2. FILA DE TARJETAS PREMIUM DE RANKING */}
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                        
                                        {/* TARJETA PREMIUM: DIVISIÓN */}
                                        <div className="bg-gradient-to-br from-slate-800 to-slate-900 p-6 rounded-xl shadow-lg border border-slate-700 flex flex-col justify-center text-white relative overflow-hidden min-h-[220px]">
                                            <div className="absolute -bottom-4 -right-4 p-4 opacity-5 transform rotate-12">
                                                <Award size={180} />
                                            </div>
                                            
                                            <div className="relative z-10 flex flex-col items-start h-full justify-between">
                                                <div className="flex items-center gap-3 text-slate-300 w-full border-b border-slate-700/50 pb-3 mb-4">
                                                    <div className="bg-amber-400/20 p-2 rounded-lg">
                                                        <Award size={20} className="text-amber-400"/>
                                                    </div>
                                                    <h3 className="text-sm font-bold uppercase tracking-widest truncate w-full" title={divisionSeleccionada === 'todas' ? 'Panorama de Divisiones' : divisionSeleccionada}>
                                                        {divisionSeleccionada === 'todas' ? 'Panorama de Divisiones' : divisionSeleccionada}
                                                    </h3>
                                                </div>
                                                
                                                <div className="mb-4">
                                                    <p className="text-sm text-slate-400 font-bold mb-1">
                                                        {divisionSeleccionada === 'todas' ? 'Divisiones Activas' : 'Total Consultas en División'}
                                                    </p>
                                                    <p className="text-5xl font-black text-white drop-shadow-md">
                                                        {divisionSeleccionada === 'todas' ? rankingDivisiones.length : (rankingDivisiones.find(r => r[0] === divisionSeleccionada)?.[1] || 0).toLocaleString()}
                                                    </p>
                                                </div>

                                                <div className="inline-block bg-slate-950/50 border border-slate-700 px-4 py-2 rounded-xl mt-auto w-full">
                                                    <p className="text-sm font-bold text-amber-400 flex items-center gap-2">
                                                        <Activity size={16} />
                                                        {divisionSeleccionada === 'todas' 
                                                            ? 'Mostrando todas las áreas' 
                                                            : `Ranking: #${rankingDivisiones.findIndex(r => r[0] === divisionSeleccionada) + 1} de ${rankingDivisiones.length} divisiones`
                                                        }
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        {/* TARJETA PREMIUM: ESPECIALIDAD */}
                                        <div className="bg-gradient-to-br from-slate-800 to-slate-900 p-6 rounded-xl shadow-lg border border-slate-700 flex flex-col justify-center text-white relative overflow-hidden min-h-[220px]">
                                            <div className="absolute -bottom-4 -right-4 p-4 opacity-5 transform rotate-12">
                                                <Stethoscope size={180} />
                                            </div>
                                            
                                            <div className="relative z-10 flex flex-col items-start h-full justify-between">
                                                <div className="flex items-center gap-3 text-slate-300 w-full border-b border-slate-700/50 pb-3 mb-4">
                                                    <div className="bg-emerald-400/20 p-2 rounded-lg">
                                                        <Stethoscope size={20} className="text-emerald-400"/>
                                                    </div>
                                                    <h3 className="text-sm font-bold uppercase tracking-widest truncate w-full" title={especialidadSeleccionada === 'todas' ? 'Panorama de Especialidades' : especialidadSeleccionada}>
                                                        {especialidadSeleccionada === 'todas' ? 'Panorama de Especialidades' : especialidadSeleccionada}
                                                    </h3>
                                                </div>
                                                
                                                <div className="mb-4">
                                                    <p className="text-sm text-slate-400 font-bold mb-1">
                                                        {especialidadSeleccionada === 'todas' ? 'Especialidades en la vista' : 'Total Consultas en Especialidad'}
                                                    </p>
                                                    <p className="text-5xl font-black text-white drop-shadow-md">
                                                        {especialidadSeleccionada === 'todas' ? especialidadesDisponibles.length : (infoEspecialidades.ranking.find(r => r[0] === especialidadSeleccionada)?.[1] || 0).toLocaleString()}
                                                    </p>
                                                </div>

                                                <div className="inline-block bg-slate-950/50 border border-slate-700 px-4 py-2 rounded-xl mt-auto w-full">
                                                    <p className="text-sm font-bold text-emerald-400 flex flex-col gap-1">
                                                        <span className="flex items-center gap-2"><Activity size={16} />
                                                        {especialidadSeleccionada === 'todas' 
                                                            ? 'Mostrando todas las especialidades' 
                                                            : `Global: #${infoEspecialidades.ranking.findIndex(r => r[0] === especialidadSeleccionada) + 1} de ${infoEspecialidades.ranking.length} especialidades`
                                                        }
                                                        </span>
                                                        {especialidadSeleccionada !== 'todas' && (
                                                            <span className="text-xs text-slate-400 font-normal">Pertenece a: {infoEspecialidades.divMap[especialidadSeleccionada]}</span>
                                                        )}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    {/* 3. GRÁFICAS DE DONA (Se oculta Division si es != todas) */}
                                    <div className={`grid grid-cols-1 ${divisionSeleccionada === 'todas' ? 'lg:grid-cols-2' : ''} gap-6 mb-6 items-start`}>
                                        
                                        {divisionSeleccionada === 'todas' && (
                                            <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col h-full min-h-[300px]">
                                                <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Distribución por División</h3>
                                                <div className="relative flex-1 min-h-[220px]"><Doughnut data={chartDivisiones} options={{ maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }} /></div>
                                                {mostrarTablas && <TablaDatos titulo1="División" titulo2="Consultas" labels={chartDivisiones.labels} data={chartDivisiones.datasets[0].data} />}
                                            </div>
                                        )}

                                        <div className={`bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col h-full min-h-[300px] ${divisionSeleccionada !== 'todas' ? 'w-full lg:w-1/2 mx-auto' : ''}`}>
                                            <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Consultas por Turno</h3>
                                            <div className="relative flex-1 min-h-[220px]"><Doughnut data={chartTurnos} options={{ maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }} /></div>
                                            {mostrarTablas && <TablaDatos titulo1="Turno" titulo2="Consultas" labels={chartTurnos.labels} data={chartTurnos.datasets[0].data} />}
                                        </div>
                                    </div>

                                    {/* 4. GRÁFICAS DE BARRAS VERTICALES CON SCROLL HORIZONTAL */}
                                    <div className="flex flex-col gap-6">
                                        
                                        {/* Especialidades (Se oculta si ya seleccionaste una) */}
                                        {especialidadSeleccionada === 'todas' && (
                                            <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col">
                                                <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Distribución por Especialidades</h3>
                                                <div className={`flex-1 grid grid-cols-1 ${mostrarTablas ? 'lg:grid-cols-5 gap-6' : 'lg:grid-cols-1'}`}>
                                                    <div className={`relative overflow-x-auto custom-scrollbar pb-4 ${mostrarTablas ? 'lg:col-span-3' : 'lg:col-span-1'}`} style={{ height: '400px' }}>
                                                        <div style={{ width: `${anchoDinamico(chartEspecialidades.labels.length)}px`, height: '100%' }}>
                                                            <Bar data={chartEspecialidades} options={chartOptionsVertical} />
                                                        </div>
                                                    </div>
                                                    {mostrarTablas && <div className="lg:col-span-2 h-[400px] overflow-hidden"><TablaDatos titulo1="Especialidad" titulo2="Consultas" labels={chartEspecialidades.labels} data={chartEspecialidades.datasets[0].data} total={true} /></div>}
                                                </div>
                                            </div>
                                        )}

                                        {/* Médicos */}
                                        <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col">
                                            <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Productividad por Médico (Matrícula)</h3>
                                            <div className={`flex-1 grid grid-cols-1 ${mostrarTablas ? 'lg:grid-cols-5 gap-6' : 'lg:grid-cols-1'}`}>
                                                <div className={`relative overflow-x-auto custom-scrollbar pb-4 ${mostrarTablas ? 'lg:col-span-3' : 'lg:col-span-1'}`} style={{ height: '400px' }}>
                                                    <div style={{ width: `${anchoDinamico(chartMedicos.labels.length)}px`, height: '100%' }}>
                                                        <Bar data={chartMedicos} options={chartOptionsVertical} />
                                                    </div>
                                                </div>
                                                {mostrarTablas && <div className="lg:col-span-2 h-[400px] overflow-hidden"><TablaDatos titulo1="Matrícula" titulo2="Consultas" labels={chartMedicos.labels} data={chartMedicos.datasets[0].data} total={true} /></div>}
                                            </div>
                                        </div>

                                        {/* Consultorios */}
                                        <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col">
                                            <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Productividad por Consultorio</h3>
                                            <div className={`flex-1 grid grid-cols-1 ${mostrarTablas ? 'lg:grid-cols-5 gap-6' : 'lg:grid-cols-1'}`}>
                                                <div className={`relative overflow-x-auto custom-scrollbar pb-4 ${mostrarTablas ? 'lg:col-span-3' : 'lg:col-span-1'}`} style={{ height: '400px' }}>
                                                    <div style={{ width: `${anchoDinamico(chartConsultorios.labels.length)}px`, height: '100%' }}>
                                                        <Bar data={chartConsultorios} options={chartOptionsVertical} />
                                                    </div>
                                                </div>
                                                {mostrarTablas && <div className="lg:col-span-2 h-[400px] overflow-hidden"><TablaDatos titulo1="Consultorio" titulo2="Consultas" labels={chartConsultorios.labels} data={chartConsultorios.datasets[0].data} total={true} /></div>}
                                            </div>
                                        </div>

                                        {/* Diagnósticos */}
                                        <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col">
                                            <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Top 20 Diagnósticos Principales</h3>
                                            <div className={`flex-1 grid grid-cols-1 ${mostrarTablas ? 'lg:grid-cols-5 gap-6' : 'lg:grid-cols-1'}`}>
                                                <div className={`relative overflow-x-auto custom-scrollbar pb-4 ${mostrarTablas ? 'lg:col-span-3' : 'lg:col-span-1'}`} style={{ height: '400px' }}>
                                                    <div style={{ width: `${anchoDinamico(chartDiagnosticos.labels.length)}px`, height: '100%' }}>
                                                        <Bar data={chartDiagnosticos} options={chartOptionsVertical} />
                                                    </div>
                                                </div>
                                                {mostrarTablas && <div className="lg:col-span-2 h-[400px] overflow-hidden"><TablaDatos titulo1="Diagnóstico" titulo2="Frecuencia" labels={chartDiagnosticos.labels} data={chartDiagnosticos.datasets[0].data} total={false} /></div>}
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            )}
                        </>
                    )}

                    {/* --- PANTALLAS EN CONSTRUCCIÓN --- */}
                    {areaSidebar !== 'consulta_externa' && (
                        <div className="flex flex-col items-center justify-center h-full text-slate-400 p-16 border-2 border-dashed border-slate-300 rounded-3xl bg-slate-100/50">
                            <Activity size={64} className="mb-6 opacity-40 text-[#822626]" />
                            <h2 className="text-2xl font-black text-slate-500 mb-2">Módulo en Construcción</h2>
                            <p className="text-center max-w-md">
                                El área de <strong>{areaSidebar.replace('_', ' ')}</strong> está siendo preparada. Próximamente podrás visualizar sus gráficas y estadísticas aquí.
                            </p>
                        </div>
                    )}
                </main>
            </div>
        </div>
    );
}
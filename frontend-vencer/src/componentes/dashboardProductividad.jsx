import React, { useState, useEffect, useMemo } from 'react';
import axios from 'axios';
import { UploadCloud, Activity, Users, CalendarCheck, Clock, ArrowLeft, BarChart2, Database, TableProperties } from 'lucide-react';
import { Bar, Doughnut } from 'react-chartjs-2';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement } from 'chart.js';

// Registrar componentes de Chart.js
ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement);

// ==========================================
// SUB-COMPONENTE: Tabla de Datos
// ==========================================
const TablaDatos = ({ titulo1, titulo2, labels, data, total = true }) => {
    if (!labels || !data) return null;
    return (
        <div className="mt-4 border-t border-slate-100 pt-4 animate-in fade-in slide-in-from-top-2 duration-300">
            <div className="max-h-48 overflow-y-auto pr-2">
                <table className="w-full text-left text-sm text-slate-600">
                    <thead className="text-xs text-slate-400 uppercase bg-slate-50 sticky top-0 z-10">
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
                        <tfoot className="bg-slate-50 font-bold sticky bottom-0 z-10">
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
    const [vistaActiva, setVistaActiva] = useState('menu');
    const [mostrarTablas, setMostrarTablas] = useState(false); // <-- ESTADO DEL BOTÓN DE TABLAS

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
                if (Array.isArray(res.data)) setDatos(res.data);
                else setDatos([]);
                setCargandoDatos(false);
            })
            .catch(err => {
                setError("Error al cargar los datos de productividad.");
                setCargandoDatos(false);
            });
    };

    useEffect(() => {
        if (vistaActiva === 'dashboard') cargarDatos();
    }, [vistaActiva]);

    const handleSubirArchivo = async (e) => {
        e.preventDefault();
        if (!archivo) { setMensaje('Por favor selecciona un archivo CSV.'); return; }
        setCargandoSubida(true);
        setMensaje('Procesando datos...');

        const formData = new FormData();
        formData.append('archivo_csv', archivo);

        try {
            const respuesta = await axios.post('/graficos/api/upload_productividad.php', formData, {
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
    // CÁLCULOS PARA GRÁFICAS
    // ==========================================
    const kpis = useMemo(() => {
        let citados = 0, primeraVez = 0;
        datos.forEach(d => {
            if (d.citado === 'Citado') citados++;
            if (d.primera_vez === 'Primera Vez') primeraVez++;
        });
        return {
            total: datos.length,
            citados, espontaneos: datos.length - citados,
            primeraVez, subsecuentes: datos.length - primeraVez
        };
    }, [datos]);

    const chartDivisiones = useMemo(() => {
        const conteo = datos.reduce((acc, curr) => {
            const div = curr.division || 'Sin Asignar';
            acc[div] = (acc[div] || 0) + 1;
            return acc;
        }, {});
        return {
            labels: Object.keys(conteo),
            datasets: [{ data: Object.values(conteo), backgroundColor: ['#005C46', '#D4C19C', '#003B2D', '#1e293b', '#64748b'], borderWidth: 0 }]
        };
    }, [datos]);

    const chartEspecialidades = useMemo(() => {
        const conteo = datos.reduce((acc, curr) => {
            const esp = curr.especialidad || 'Desconocida';
            acc[esp] = (acc[esp] || 0) + 1;
            return acc;
        }, {});
        const ordenados = Object.entries(conteo).sort((a, b) => b[1] - a[1]).slice(0, 10);
        return {
            labels: ordenados.map(item => item[0].length > 25 ? item[0].substring(0,25)+'...' : item[0]),
            datasets: [{ label: 'Consultas', data: ordenados.map(item => item[1]), backgroundColor: '#007A5E', borderRadius: 4 }]
        };
    }, [datos]);

    const chartTurnos = useMemo(() => {
        const conteo = datos.reduce((acc, curr) => {
            const turno = curr.turno || 'Sin Asignar';
            acc[turno] = (acc[turno] || 0) + 1;
            return acc;
        }, {});
        const ordenados = Object.entries(conteo).sort((a, b) => b[1] - a[1]);
        return {
            labels: ordenados.map(item => item[0]),
            datasets: [{ data: ordenados.map(item => item[1]), backgroundColor: ['#D4C19C', '#003B2D', '#0f172a', '#f59e0b', '#64748b'], borderWidth: 0 }]
        };
    }, [datos]);

    const chartMedicos = useMemo(() => {
        const conteo = datos.reduce((acc, curr) => {
            const medico = curr.matricula_medico || 'Sin Matrícula';
            acc[medico] = (acc[medico] || 0) + 1;
            return acc;
        }, {});
        const ordenados = Object.entries(conteo).sort((a, b) => b[1] - a[1]).slice(0, 10);
        return {
            labels: ordenados.map(item => `Matr. ${item[0]}`),
            datasets: [{ label: 'Consultas', data: ordenados.map(item => item[1]), backgroundColor: '#005C46', borderRadius: 4 }]
        };
    }, [datos]);

    const chartConsultorios = useMemo(() => {
        const conteo = datos.reduce((acc, curr) => {
            const consultorio = curr.consultorio || 'Sin Asignar';
            acc[consultorio] = (acc[consultorio] || 0) + 1;
            return acc;
        }, {});
        const ordenados = Object.entries(conteo).sort((a, b) => b[1] - a[1]).slice(0, 10);
        return {
            labels: ordenados.map(item => `Cons. ${item[0]}`),
            datasets: [{ label: 'Consultas', data: ordenados.map(item => item[1]), backgroundColor: '#1e293b', borderRadius: 4 }]
        };
    }, [datos]);

    const chartDiagnosticos = useMemo(() => {
        const conteo = datos.reduce((acc, curr) => {
            const diag = curr.diagnostico_principal || 'No Especificado';
            acc[diag] = (acc[diag] || 0) + 1;
            return acc;
        }, {});
        const ordenados = Object.entries(conteo).sort((a, b) => b[1] - a[1]).slice(0, 10);
        return {
            labels: ordenados.map(item => item[0].length > 40 ? item[0].substring(0,40)+'...' : item[0]),
            datasets: [{ label: 'Frecuencia', data: ordenados.map(item => item[1]), backgroundColor: '#D4C19C', borderRadius: 4 }]
        };
    }, [datos]);

    // ==========================================
    // VISTAS
    // ==========================================
    if (vistaActiva === 'menu') {
        return (
            <div className="bg-slate-50 min-h-screen flex flex-col items-center pt-20 px-8">
                <div className="text-center mb-12">
                    <h1 className="text-4xl font-black text-[#005C46] mb-2">Consulta Externa</h1>
                    <p className="text-slate-500 font-bold uppercase tracking-widest text-sm">Módulo de Productividad UMAE 48</p>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 w-full max-w-4xl">
                    <button onClick={() => setVistaActiva('dashboard')} className="bg-white p-8 rounded-2xl shadow-sm border border-emerald-100 hover:shadow-md hover:border-emerald-300 transition-all flex flex-col items-center text-center group">
                        <div className="bg-emerald-50 p-4 rounded-full mb-4 group-hover:bg-[#005C46] group-hover:text-white text-[#005C46] transition-colors"><BarChart2 size={40} /></div>
                        <h2 className="text-xl font-black text-slate-800 mb-2">Tablero de Indicadores</h2>
                        <p className="text-slate-500 text-sm">Visualiza gráficas, médicos top y estadísticas de productividad.</p>
                    </button>
                    {isAdmin && (
                        <button onClick={() => { setVistaActiva('subir'); setMensaje(''); }} className="bg-white p-8 rounded-2xl shadow-sm border border-emerald-100 hover:shadow-md hover:border-emerald-300 transition-all flex flex-col items-center text-center group">
                            <div className="bg-emerald-50 p-4 rounded-full mb-4 group-hover:bg-[#005C46] group-hover:text-white text-[#005C46] transition-colors"><Database size={40} /></div>
                            <h2 className="text-xl font-black text-slate-800 mb-2">Actualizar Base de Datos</h2>
                            <p className="text-slate-500 text-sm">Sube el CSV de productividad para actualizar la información.</p>
                        </button>
                    )}
                </div>
            </div>
        );
    }

    if (vistaActiva === 'subir') {
        return (
            <div className="bg-slate-50 min-h-screen p-8">
                <button onClick={() => setVistaActiva('menu')} className="flex items-center text-emerald-700 hover:text-emerald-900 font-bold mb-8 transition-colors"><ArrowLeft size={20} className="mr-2" /> Volver al Menú</button>
                <div className="max-w-2xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-emerald-100">
                    <h2 className="text-2xl font-black text-[#005C46] mb-2 flex items-center gap-3"><UploadCloud size={28} /> Subir Productividad (CSV)</h2>
                    <form onSubmit={handleSubirArchivo} className="flex flex-col gap-6 mt-6">
                        <input type="file" accept=".csv" onChange={(e) => setArchivo(e.target.files[0])} className="block w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-emerald-50 file:text-emerald-800 hover:file:bg-emerald-100 cursor-pointer border-2 border-dashed border-slate-200 rounded-xl p-4"/>
                        <button type="submit" disabled={cargandoSubida || !archivo} className="bg-[#005C46] text-white font-bold py-3 px-6 rounded-xl hover:bg-[#004a38] transition disabled:bg-slate-300 disabled:cursor-not-allowed w-full shadow-md">{cargandoSubida ? 'Procesando archivo...' : 'Cargar a Base de Datos'}</button>
                    </form>
                    {mensaje && <div className={`mt-6 p-4 rounded-xl text-sm font-bold ${mensaje.includes('✅') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'}`}>{mensaje}</div>}
                </div>
            </div>
        );
    }

    return (
        <div className="bg-slate-50 min-h-screen">
            {/* ENCABEZADO CON SWITCH DE TABLAS */}
            <header className="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm px-8 py-4 flex justify-between items-center">
                <div className="flex items-center gap-4">
                    <button onClick={() => setVistaActiva('menu')} className="p-2 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors text-slate-600"><ArrowLeft size={20} /></button>
                    <div>
                        <h1 className="text-2xl font-black text-[#005C46]">Consulta Externa</h1>
                        <p className="text-xs text-slate-500 font-bold uppercase tracking-wider"></p>
                    </div>
                </div>
                
                {/* 👇 BOTÓN SWITCH MOSTRAR TABLAS 👇 */}
                {datos.length > 0 && !cargandoDatos && !error && (
                    <div className="flex items-center gap-3 bg-slate-50 border border-slate-200 px-4 py-2 rounded-xl shadow-inner">
                        <TableProperties size={18} className="text-slate-400" />
                        <span className="text-sm font-bold text-slate-600 hidden sm:inline">Mostrar Tablas</span>
                        <button 
                            onClick={() => setMostrarTablas(!mostrarTablas)}
                            className={`w-12 h-6 rounded-full transition-colors relative flex items-center ${mostrarTablas ? 'bg-[#005C46]' : 'bg-slate-300'}`}
                        >
                            <span className={`w-4 h-4 bg-white rounded-full shadow-sm absolute transition-all ${mostrarTablas ? 'left-7' : 'left-1'}`} />
                        </button>
                    </div>
                )}
            </header>

            <main className="max-w-[1400px] mx-auto px-8 pt-8 pb-16 w-full">
                {cargandoDatos ? (
                    <div className="flex justify-center items-center py-20 text-emerald-700 font-bold"><Activity className="animate-spin mr-3"/> Calculando estadísticas...</div>
                ) : error ? (
                    <div className="text-red-600 font-bold text-center py-20">{error}</div>
                ) : datos.length === 0 ? (
                    <div className="text-center p-16 border-2 border-dashed border-slate-300 rounded-2xl text-slate-400">
                        <Activity size={48} className="mx-auto mb-4 opacity-50" />
                        <p className="font-bold text-lg">No hay datos de productividad</p>
                    </div>
                ) : (
                    <>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                            <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200"><div className="flex items-center gap-3 text-slate-500 mb-2"><Users size={18}/><h3 className="text-xs font-bold uppercase tracking-widest">Total Consultas</h3></div><p className="text-4xl font-black text-[#005C46]">{kpis.total.toLocaleString()}</p></div>
                            <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200"><div className="flex items-center gap-3 text-slate-500 mb-2"><CalendarCheck size={18}/><h3 className="text-xs font-bold uppercase tracking-widest">Citados</h3></div><p className="text-4xl font-black text-blue-600">{kpis.citados.toLocaleString()}</p><p className="text-xs font-bold text-slate-400 mt-1">Vs {kpis.espontaneos} Espontáneos</p></div>
                            <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200"><div className="flex items-center gap-3 text-slate-500 mb-2"><Clock size={18}/><h3 className="text-xs font-bold uppercase tracking-widest">Primera Vez</h3></div><p className="text-4xl font-black text-amber-500">{kpis.primeraVez.toLocaleString()}</p><p className="text-xs font-bold text-slate-400 mt-1">Vs {kpis.subsecuentes} Subsecuentes</p></div>
                        </div>

                        {/* BLOQUE 1: Divisiones y Especialidades */}
                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6 items-start">
                            <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 lg:col-span-1 flex flex-col">
                                <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Distribución por División</h3>
                                <div className="relative min-h-[300px]"><Doughnut data={chartDivisiones} options={{ maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }} /></div>
                                {mostrarTablas && <TablaDatos titulo1="División" titulo2="Consultas" labels={chartDivisiones.labels} data={chartDivisiones.datasets[0].data} />}
                            </div>
                            <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 lg:col-span-2 flex flex-col">
                                <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Top 10 Especialidades</h3>
                                <div className="relative min-h-[300px]"><Bar data={chartEspecialidades} options={{ indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { grid: { display: false } } } }} /></div>
                                {mostrarTablas && <TablaDatos titulo1="Especialidad" titulo2="Consultas" labels={chartEspecialidades.labels} data={chartEspecialidades.datasets[0].data} total={false} />}
                            </div>
                        </div>

                        {/* BLOQUE 2: Turnos y Médicos */}
                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6 items-start">
                            <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 lg:col-span-1 flex flex-col">
                                <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Consultas por Turno</h3>
                                <div className="relative min-h-[300px]"><Doughnut data={chartTurnos} options={{ maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }} /></div>
                                {mostrarTablas && <TablaDatos titulo1="Turno" titulo2="Consultas" labels={chartTurnos.labels} data={chartTurnos.datasets[0].data} />}
                            </div>
                            <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 lg:col-span-2 flex flex-col">
                                <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Top 10 Médicos con más consultas</h3>
                                <div className="relative min-h-[300px]"><Bar data={chartMedicos} options={{ maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { grid: { display: false } } } }} /></div>
                                {mostrarTablas && <TablaDatos titulo1="Matrícula" titulo2="Consultas" labels={chartMedicos.labels} data={chartMedicos.datasets[0].data} total={false} />}
                            </div>
                        </div>

                        {/* BLOQUE 3: Consultorios y Diagnósticos */}
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                            <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col">
                                <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Top 10 Consultorios más productivos</h3>
                                <div className="relative min-h-[350px]"><Bar data={chartConsultorios} options={{ indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { grid: { display: false } } } }} /></div>
                                {mostrarTablas && <TablaDatos titulo1="Consultorio" titulo2="Consultas" labels={chartConsultorios.labels} data={chartConsultorios.datasets[0].data} total={false} />}
                            </div>
                            <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col">
                                <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Top 10 Diagnósticos Principales</h3>
                                <div className="relative min-h-[350px]"><Bar data={chartDiagnosticos} options={{ indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { grid: { display: false } } } }} /></div>
                                {mostrarTablas && <TablaDatos titulo1="Diagnóstico" titulo2="Frecuencia" labels={chartDiagnosticos.labels} data={chartDiagnosticos.datasets[0].data} total={false} />}
                            </div>
                        </div>
                    </>
                )}
            </main>
        </div>
    );
}
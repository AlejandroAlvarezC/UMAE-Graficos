import React, { useState, useEffect, useMemo } from 'react';
import axios from 'axios';
import { UploadCloud, Activity, Users, CalendarCheck, Clock } from 'lucide-react';
import { Bar, Doughnut } from 'react-chartjs-2';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement } from 'chart.js';

// Registrar componentes de Chart.js
ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement);

export default function DashboardProductividad({ isAdmin }) {
    // Estados para la carga de archivos
    const [archivo, setArchivo] = useState(null);
    const [mensaje, setMensaje] = useState('');
    const [cargandoSubida, setCargandoSubida] = useState(false);

    // Estados para los datos de la base de datos
    const [datos, setDatos] = useState([]);
    const [cargandoDatos, setCargandoDatos] = useState(true);
    const [error, setError] = useState(null);

    // Cargar datos al iniciar
    const cargarDatos = () => {
        setCargandoDatos(true);
        axios.get('/api/api_productividad.php') // Ajusta la ruta si es necesario
            .then(res => {
                if (Array.isArray(res.data)) {
                    setDatos(res.data);
                } else {
                    setDatos([]);
                }
                setCargandoDatos(false);
            })
            .catch(err => {
                setError("Error al cargar los datos de productividad.");
                setCargandoDatos(false);
            });
    };

    useEffect(() => {
        cargarDatos();
    }, []);

    // Manejador para subir el CSV
    const handleSubirArchivo = async (e) => {
        e.preventDefault();
        if (!archivo) {
            setMensaje('Por favor selecciona un archivo CSV.');
            return;
        }

        setCargandoSubida(true);
        setMensaje('Procesando datos y traduciendo especialidades...');

        const formData = new FormData();
        formData.append('archivo_csv', archivo);

        try {
            const respuesta = await axios.post('/graficos/api/upload_productividad.php', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            if (respuesta.data.success) {
                setMensaje(`✅ ¡Éxito! ${respuesta.data.message}`);
                cargarDatos(); // Recargar las gráficas automáticamente
            } else {
                setMensaje(`❌ Error: ${respuesta.data.message}`);
            }
        } catch (error) {
            setMensaje('❌ Error al conectar con el servidor.');
        } finally {
            setCargandoSubida(false);
            setArchivo(null); // Limpiar el input
        }
    };

    // ==========================================
    // PROCESAMIENTO DE DATOS PARA GRÁFICAS
    // ==========================================
    const kpis = useMemo(() => {
        let citados = 0;
        let primeraVez = 0;
        
        datos.forEach(d => {
            if (d.citado === 'Citado') citados++;
            if (d.primera_vez === 'Primera Vez') primeraVez++;
        });

        return {
            total: datos.length,
            citados,
            espontaneos: datos.length - citados,
            primeraVez,
            subsecuentes: datos.length - primeraVez
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
            datasets: [{
                data: Object.values(conteo),
                backgroundColor: ['#005C46', '#D4C19C', '#003B2D', '#1e293b', '#64748b'],
                borderWidth: 0
            }]
        };
    }, [datos]);

    const chartEspecialidades = useMemo(() => {
        const conteo = datos.reduce((acc, curr) => {
            const esp = curr.especialidad || 'Desconocida';
            acc[esp] = (acc[esp] || 0) + 1;
            return acc;
        }, {});

        // Ordenar de mayor a menor y tomar el Top 10
        const ordenados = Object.entries(conteo).sort((a, b) => b[1] - a[1]).slice(0, 10);

        return {
            labels: ordenados.map(item => item[0].length > 25 ? item[0].substring(0,25)+'...' : item[0]),
            datasets: [{
                label: 'Consultas',
                data: ordenados.map(item => item[1]),
                backgroundColor: '#007A5E',
                borderRadius: 4
            }]
        };
    }, [datos]);

    // ==========================================
    // RENDERIZADO VISUAL
    // ==========================================
    if (cargandoDatos) return <div className="h-screen flex items-center justify-center font-bold text-[#005C46]"><Activity className="animate-spin mr-2"/> Cargando Productividad...</div>;
    if (error) return <div className="h-screen flex items-center justify-center font-bold text-red-600">{error}</div>;

    return (
        <div className="bg-slate-50 min-h-screen">
            
            {/* ENCABEZADO */}
            <header className="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm px-8 py-4 flex justify-between items-center">
                <div>
                    <h1 className="text-2xl font-black text-[#005C46]">Productividad Externa</h1>
                    <p className="text-xs text-slate-500 font-bold uppercase tracking-wider">Análisis de Consultas UMAE 48</p>
                </div>
            </header>

            <main className="max-w-[1400px] mx-auto px-8 pt-6 pb-16 w-full">
                
                {/* VISTA DE ADMINISTRADOR (Carga de Datos) */}
                {isAdmin && (
                    <div className="bg-white p-6 rounded-xl shadow-sm border border-emerald-200 mb-8 max-w-3xl">
                        <h2 className="text-sm font-bold text-emerald-800 mb-4 flex items-center gap-2 uppercase tracking-wide">
                            <UploadCloud size={18} className="text-emerald-600" /> 
                            Actualizar Base de Datos (CSV)
                        </h2>
                        
                        <form onSubmit={handleSubirArchivo} className="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                            <input 
                                type="file" 
                                accept=".csv"
                                onChange={(e) => setArchivo(e.target.files[0])}
                                className="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer border border-slate-200 rounded-lg"
                            />
                            <button 
                                type="submit" 
                                disabled={cargandoSubida || !archivo}
                                className="bg-[#005C46] text-white font-bold py-2.5 px-6 rounded-lg hover:bg-[#004a38] transition disabled:bg-slate-300 disabled:cursor-not-allowed whitespace-nowrap shadow-sm"
                            >
                                {cargandoSubida ? 'Procesando...' : 'Subir Archivo'}
                            </button>
                        </form>

                        {mensaje && (
                            <div className={`mt-4 p-3 rounded-lg text-sm font-bold ${mensaje.includes('✅') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'}`}>
                                {mensaje}
                            </div>
                        )}
                    </div>
                )}

                {/* TARJETAS KPI */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
                        <div className="flex items-center gap-3 text-slate-500 mb-2"><Users size={18}/> <h3 className="text-xs font-bold uppercase tracking-widest">Total Consultas</h3></div>
                        <p className="text-4xl font-black text-[#005C46]">{kpis.total.toLocaleString()}</p>
                    </div>
                    <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
                        <div className="flex items-center gap-3 text-slate-500 mb-2"><CalendarCheck size={18}/> <h3 className="text-xs font-bold uppercase tracking-widest">Citados</h3></div>
                        <p className="text-4xl font-black text-blue-600">{kpis.citados.toLocaleString()}</p>
                        <p className="text-xs font-bold text-slate-400 mt-1">Vs {kpis.espontaneos} Espontáneos</p>
                    </div>
                    <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
                        <div className="flex items-center gap-3 text-slate-500 mb-2"><Clock size={18}/> <h3 className="text-xs font-bold uppercase tracking-widest">Primera Vez</h3></div>
                        <p className="text-4xl font-black text-amber-500">{kpis.primeraVez.toLocaleString()}</p>
                        <p className="text-xs font-bold text-slate-400 mt-1">Vs {kpis.subsecuentes} Subsecuentes</p>
                    </div>
                </div>

                {/* GRÁFICAS */}
                {datos.length > 0 ? (
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Gráfica de Pastel (Divisiones) */}
                        <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 lg:col-span-1 flex flex-col">
                            <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Distribución por División</h3>
                            <div className="flex-1 relative min-h-[300px]">
                                <Doughnut 
                                    data={chartDivisiones} 
                                    options={{ maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }} 
                                />
                            </div>
                        </div>

                        {/* Gráfica de Barras (Top Especialidades) */}
                        <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 lg:col-span-2 flex flex-col">
                            <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Top 10 Especialidades</h3>
                            <div className="flex-1 relative min-h-[300px]">
                                <Bar 
                                    data={chartEspecialidades} 
                                    options={{ indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { grid: { display: false } } } }} 
                                />
                            </div>
                        </div>
                    </div>
                ) : (
                    <div className="text-center p-16 border-2 border-dashed border-slate-300 rounded-2xl text-slate-400 mt-8">
                        <Activity size={48} className="mx-auto mb-4 opacity-50" />
                        <p className="font-bold text-lg">No hay datos de productividad</p>
                        <p className="text-sm">Sube un archivo CSV usando el panel de administrador para comenzar.</p>
                    </div>
                )}
            </main>
        </div>
    );
}
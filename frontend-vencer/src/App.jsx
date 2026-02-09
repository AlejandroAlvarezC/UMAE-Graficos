import { useEffect, useState, useMemo } from 'react';
import axios from 'axios';
import { Bar, Doughnut } from 'react-chartjs-2';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement } from 'chart.js';
import { Activity, LayoutDashboard, Calendar, AlertOctagon, ShieldAlert, Siren, ArrowRight, Lock, Unlock, UploadCloud, X } from 'lucide-react';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement);

const MESES = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];

// --- 1. COMPONENTE REUTILIZABLE: GRÁFICO + TABLA (La Clave) ---
const SeccionGraficoTabla = ({ titulo, datos, campo, tipo = 'bar', color = '#3b82f6', limite = 10 }) => {
    // 1. Procesar datos para esta sección específica
    const procesados = useMemo(() => {
        const conteo = datos.reduce((acc, curr) => {
            const key = (curr[campo] || 'Sin Dato').trim();
            acc[key] = (acc[key] || 0) + 1;
            return acc;
        }, {});
        
        // Ordenar de mayor a menor
        const ordenados = Object.entries(conteo)
            .sort((a, b) => b[1] - a[1]);
            
        const total = datos.length;
        
        // Cortar para la gráfica (Top N)
        const topGrafica = ordenados.slice(0, limite);
        
        return { ordenados, topGrafica, total };
    }, [datos, campo, limite]);

    if (datos.length === 0) return null;

    // Configuración ChartJS
    const chartData = {
        labels: procesados.topGrafica.map(([k]) => k),
        datasets: [{
            data: procesados.topGrafica.map(([, v]) => v),
            backgroundColor: color,
            borderRadius: 4,
            barThickness: 20
        }]
    };

    const options = {
        indexAxis: 'y',
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { x: { display: false }, y: { ticks: { font: { size: 11 }, callback: function(v){ const l=this.getLabelForValue(v); return l.length>20?l.substr(0,20)+'...':l; } }, grid: { display: false } } }
    };

    return (
        <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div className="bg-slate-50 px-6 py-3 border-b border-slate-100 flex justify-between items-center">
                <h3 className="font-bold text-slate-700">{titulo}</h3>
                <span className="text-xs font-bold bg-slate-200 text-slate-600 px-2 py-1 rounded-full">Total: {procesados.total}</span>
            </div>
            
            <div className="grid grid-cols-1 lg:grid-cols-3">
                {/* COLUMNA IZQUIERDA: GRÁFICA */}
                <div className="p-6 col-span-2 border-b lg:border-b-0 lg:border-r border-slate-100 h-[350px]">
                    <div className="w-full h-full relative">
                        {tipo === 'bar' ? <Bar data={chartData} options={options} /> : 
                         <div className="h-full flex justify-center"><Doughnut data={chartData} options={{ maintainAspectRatio: false, cutout: '70%' }} /></div>}
                    </div>
                </div>

                {/* COLUMNA DERECHA: TABLA DE RESUMEN */}
                <div className="bg-white max-h-[350px] overflow-y-auto custom-scrollbar">
                    <table className="w-full text-sm text-left text-slate-600">
                        <thead className="text-xs text-slate-400 uppercase bg-slate-50 sticky top-0">
                            <tr>
                                <th className="px-4 py-2">Concepto</th>
                                <th className="px-4 py-2 text-right">Cant.</th>
                                <th className="px-4 py-2 text-right">%</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-50">
                            {procesados.ordenados.map(([nombre, cantidad], i) => (
                                <tr key={i} className="hover:bg-blue-50 transition">
                                    <td className="px-4 py-2 font-medium truncate max-w-[150px]" title={nombre}>
                                        <div className="flex items-center gap-2">
                                            <span className="text-[10px] text-slate-300 font-mono w-4">{i+1}</span>
                                            {nombre}
                                        </div>
                                    </td>
                                    <td className="px-4 py-2 text-right font-bold text-slate-700">{cantidad}</td>
                                    <td className="px-4 py-2 text-right text-slate-400 text-xs">
                                        {((cantidad / procesados.total) * 100).toFixed(1)}%
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
};

// --- COMPONENTE: MODAL DE CARGA (Sin cambios) ---
const UploadModal = ({ isOpen, onClose, onSuccess }) => {
    const [archivo, setArchivo] = useState(null);
    const [subiendo, setSubiendo] = useState(false);
    const [mensaje, setMensaje] = useState(null);
    if (!isOpen) return null;
    const handleUpload = async (e) => {
        e.preventDefault(); if (!archivo) return; setSubiendo(true); setMensaje(null);
        const formData = new FormData(); formData.append('archivo_excel', archivo);
        try {
            await axios.post('http://localhost/paginaPrueba/api/subir_archivo.php', formData, { headers: { 'Content-Type': 'multipart/form-data' } });
            setMensaje({ tipo: 'success', texto: '¡Cargado con éxito!' }); setTimeout(() => { onSuccess(); onClose(); setMensaje(null); setArchivo(null); }, 1500);
        } catch { setMensaje({ tipo: 'error', texto: 'Error al subir.' }); } finally { setSubiendo(false); }
    };
    return (
        <div className="fixed inset-0 bg-black/50 z-[100] flex items-center justify-center p-4">
            <div className="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm animate-in zoom-in">
                <div className="flex justify-between mb-4"><h3 className="font-bold flex gap-2"><UploadCloud/> Subir Archivo</h3><button onClick={onClose}><X size={18}/></button></div>
                <form onSubmit={handleUpload} className="space-y-4">
                    <input type="file" onChange={(e) => setArchivo(e.target.files[0])} className="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:bg-blue-50 file:text-blue-700"/>
                    {mensaje && <div className={`p-2 text-xs rounded ${mensaje.tipo==='success'?'bg-green-100 text-green-700':'bg-red-100 text-red-700'}`}>{mensaje.texto}</div>}
                    <button disabled={!archivo || subiendo} className="w-full bg-blue-600 text-white py-2 rounded-lg font-bold disabled:opacity-50">{subiendo?'...':'Subir'}</button>
                </form>
            </div>
        </div>
    );
};

function App() {
  const [datos, setDatos] = useState([]);
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState(null);
  
  const [anioSeleccionado, setAnioSeleccionado] = useState('todos');
  const [mesInicio, setMesInicio] = useState(0);
  const [mesFin, setMesFin] = useState(11);
  const [pestanaActiva, setPestanaActiva] = useState('general');
  const [isAdmin, setIsAdmin] = useState(false);
  const [showModal, setShowModal] = useState(false);

  useEffect(() => {
    axios.get('http://localhost/paginaPrueba/api/api_vencer.php')
      .then(res => { setDatos(Array.isArray(res.data) ? res.data : []); setCargando(false); })
      .catch(err => { setError("Error de conexión"); setCargando(false); });
  }, []);

  const aniosDisponibles = useMemo(() => [...new Set(datos.map(d => d.anio || (d.fecha_evento ? d.fecha_evento.split('-')[0] : null)).filter(a => a))].sort().reverse(), [datos]);
  
  const datosFiltrados = useMemo(() => datos.filter(item => {
        if (!item.fecha_evento) return false;
        const [a, m] = item.fecha_evento.split('-');
        return (anioSeleccionado === 'todos' || a === anioSeleccionado) && (parseInt(m)-1 >= mesInicio && parseInt(m)-1 <= mesFin);
  }), [datos, anioSeleccionado, mesInicio, mesFin]);

  // --- FILTRADO POR TIPO DE EVENTO ---
  const dGeneral = datosFiltrados;
  const dAdversos = datosFiltrados.filter(d => (d.evento||'').toUpperCase().includes('ADVERSO'));
  const dCuasi = datosFiltrados.filter(d => (d.evento||'').toUpperCase().includes('CUASI'));
  // Nuevo: Filtro para Centinelas
  const dCentinela = datosFiltrados.filter(d => (d.evento||'').toUpperCase().includes('CENTINELA'));

  const toggleAdmin = () => { if(isAdmin) setIsAdmin(false); else if(prompt("Pass:")==="admin123") setIsAdmin(true); };

  if (cargando) return <div className="h-screen flex items-center justify-center text-slate-400">Cargando VENCER...</div>;
  if (error) return <div className="h-screen flex items-center justify-center text-red-500">{error}</div>;

  return (
    <div className="min-h-screen bg-slate-100 text-slate-800 font-sans pb-12">
      <UploadModal isOpen={showModal} onClose={() => setShowModal(false)} onSuccess={() => window.location.reload()} />

      {/* NAVBAR */}
      <nav className="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm h-16">
        <div className="max-w-7xl mx-auto px-4 h-full flex items-center justify-between">
            <div className="flex items-center gap-2">
                <div className="bg-blue-600 p-1.5 rounded text-white"><Activity size={18} /></div>
                <h1 className="font-bold text-lg">VENCER Analytics</h1>
            </div>
            <div className="flex items-center gap-3">
                {isAdmin && <button onClick={() => setShowModal(true)} className="hidden md:flex bg-blue-600 text-white px-3 py-1 rounded text-xs font-bold gap-1"><UploadCloud size={14}/> Subir</button>}
                <div className="hidden md:flex bg-slate-50 rounded p-1 border border-slate-200 text-xs">
                    <select className="bg-transparent font-bold text-slate-600 outline-none" value={anioSeleccionado} onChange={e=>setAnioSeleccionado(e.target.value)}><option value="todos">Todos</option>{aniosDisponibles.map(a=><option key={a} value={a}>{a}</option>)}</select>
                </div>
                <button onClick={toggleAdmin} className="text-slate-400 hover:text-blue-600">{isAdmin ? <Unlock size={18}/> : <Lock size={18}/>}</button>
            </div>
        </div>
      </nav>

      <main className="max-w-7xl mx-auto px-4 pt-6">
        
        {/* PESTAÑAS (Ahora son 4) */}
        <div className="flex justify-center mb-8 overflow-x-auto">
            <div className="bg-white p-1 rounded-xl shadow-sm border border-slate-200 inline-flex whitespace-nowrap">
                {[
                    { id: 'general', label: 'General', icon: LayoutDashboard, color: 'blue' },
                    { id: 'adversos', label: 'Adversos', icon: AlertOctagon, color: 'red' },
                    { id: 'cuasi', label: 'Cuasifallas', icon: ShieldAlert, color: 'amber' },
                    { id: 'centinela', label: 'Centinelas', icon: Siren, color: 'purple' } // NUEVA
                ].map((tab) => (
                    <button key={tab.id} onClick={() => setPestanaActiva(tab.id)} className={`flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold transition ${pestanaActiva===tab.id ? `bg-${tab.color}-50 text-${tab.color}-600 ring-1 ring-${tab.color}-200` : 'text-slate-500 hover:bg-slate-50'}`}>
                        <tab.icon size={16} />{tab.label} ({
                            tab.id==='general'?dGeneral.length : tab.id==='adversos'?dAdversos.length : tab.id==='cuasi'?dCuasi.length : dCentinela.length
                        })
                    </button>
                ))}
            </div>
        </div>

        {/* --- CONTENIDO DE PESTAÑAS (Usando el Componente Pareado) --- */}
        <div className="animate-in fade-in slide-in-from-bottom-4 duration-500">
            
            {/* 1. PESTAÑA GENERAL */}
            {pestanaActiva === 'general' && (
                <>
                    {/* Tarjetas KPI Simples */}
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div className="bg-white p-4 rounded-lg shadow-sm border border-slate-200 text-center">
                            <p className="text-xs font-bold text-slate-400 uppercase">Total Eventos</p>
                            <p className="text-3xl font-black text-slate-700">{dGeneral.length}</p>
                        </div>
                        <div className="bg-white p-4 rounded-lg shadow-sm border border-slate-200 text-center">
                            <p className="text-xs font-bold text-slate-400 uppercase">Eventos Adversos</p>
                            <p className="text-3xl font-black text-red-600">{dAdversos.length}</p>
                        </div>
                        <div className="bg-white p-4 rounded-lg shadow-sm border border-slate-200 text-center">
                            <p className="text-xs font-bold text-slate-400 uppercase">Cuasifallas</p>
                            <p className="text-3xl font-black text-amber-600">{dCuasi.length}</p>
                        </div>
                         <div className="bg-white p-4 rounded-lg shadow-sm border border-slate-200 text-center">
                            <p className="text-xs font-bold text-slate-400 uppercase">Centinelas</p>
                            <p className="text-3xl font-black text-purple-600">{dCentinela.length}</p>
                        </div>
                    </div>

                    {/* GRÁFICOS PAREADOS (Gráfico Izq - Tabla Der) */}
                    <SeccionGraficoTabla titulo="Eventos por Servicio" datos={dGeneral} campo="servicio" color="#3b82f6" />
                    <SeccionGraficoTabla titulo="Distribución por Sexo" datos={dGeneral} campo="sexo" tipo="doughnut" color={['#3b82f6', '#ec4899']} />
                    <SeccionGraficoTabla titulo="Eventos por Turno" datos={dGeneral} campo="turno" color="#64748b" /> {/* Si tienes campo turno */}
                </>
            )}

            {/* 2. PESTAÑA ADVERSOS */}
            {pestanaActiva === 'adversos' && (
                <>
                    <div className="bg-red-50 border border-red-100 p-4 rounded-lg mb-6 flex gap-3 items-center text-red-800">
                        <AlertOctagon /> <h2 className="font-bold text-xl">Análisis de Eventos Adversos</h2>
                    </div>
                    {dAdversos.length === 0 ? <div className="text-center p-10 text-slate-400">No hay registros</div> : (
                        <>
                            <SeccionGraficoTabla titulo="Adversos por Servicio (Top 10)" datos={dAdversos} campo="servicio" color="#ef4444" />
                            <SeccionGraficoTabla titulo="Causas / Definiciones Frecuentes" datos={dAdversos} campo="definicion" color="#b91c1c" />
                            <SeccionGraficoTabla titulo="Adversos por Sexo" datos={dAdversos} campo="sexo" tipo="doughnut" color={['#ef4444', '#f472b6']} />
                        </>
                    )}
                </>
            )}

            {/* 3. PESTAÑA CUASIFALLAS */}
            {pestanaActiva === 'cuasi' && (
                <>
                    <div className="bg-amber-50 border border-amber-100 p-4 rounded-lg mb-6 flex gap-3 items-center text-amber-800">
                        <ShieldAlert /> <h2 className="font-bold text-xl">Análisis de Cuasifallas</h2>
                    </div>
                    {dCuasi.length === 0 ? <div className="text-center p-10 text-slate-400">No hay registros</div> : (
                        <>
                            <SeccionGraficoTabla titulo="Cuasifallas por Servicio" datos={dCuasi} campo="servicio" color="#f59e0b" />
                            <SeccionGraficoTabla titulo="Causas Frecuentes" datos={dCuasi} campo="definicion" color="#d97706" />
                        </>
                    )}
                </>
            )}

            {/* 4. PESTAÑA CENTINELAS (NUEVA) */}
            {pestanaActiva === 'centinela' && (
                <>
                    <div className="bg-purple-50 border border-purple-100 p-4 rounded-lg mb-6 flex gap-3 items-center text-purple-800">
                        <Siren /> <h2 className="font-bold text-xl">Eventos Centinela (Críticos)</h2>
                    </div>
                    
                    {dCentinela.length === 0 ? (
                        <div className="flex flex-col items-center justify-center p-16 bg-white border-2 border-dashed border-slate-200 rounded-xl text-slate-400">
                            <Siren size={48} className="mb-4 text-slate-300"/>
                            <p className="font-bold text-lg">Sin Eventos Centinela</p>
                            <p className="text-sm">¡Excelente noticia! No se encontraron registros críticos con los filtros actuales.</p>
                        </div>
                    ) : (
                        <>
                            <SeccionGraficoTabla titulo="Centinelas por Servicio" datos={dCentinela} campo="servicio" color="#9333ea" />
                            <SeccionGraficoTabla titulo="Diagnóstico / Causa" datos={dCentinela} campo="definicion" color="#7e22ce" />
                        </>
                    )}
                </>
            )}

        </div>
      </main>
    </div>
  );
}

export default App;
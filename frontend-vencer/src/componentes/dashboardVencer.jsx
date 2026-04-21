import { useEffect, useState, useMemo } from 'react';
import axios from 'axios';
import { Bar, Doughnut } from 'react-chartjs-2';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement } from 'chart.js';
import { Activity, AlertOctagon, ShieldAlert, Siren, Lock, Unlock, ChevronDown, ChevronUp, Settings, Filter, Download} from 'lucide-react';
import Sidebar from './Sidebar.jsx';
import DashboardProductividad from './dashboardProductividad.jsx';
// import AdministradorUsuarios from './AdministradorUsuarios'
import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement);

const MESES = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];

// ============================================================================
// 1. COMPONENTE: GRÁFICO + TABLA (ESTÁNDAR)
// ============================================================================
const SeccionGraficoTabla = ({ idCanvas, titulo, datos, campo, tipo = 'bar', color = '#005C46', limite = 10, mostrarTablas = true }) => {
    const procesados = useMemo(() => {
        const conteo = datos.reduce((acc, curr) => {
            const key = (curr[campo] || 'Sin Dato').trim();
            acc[key] = (acc[key] || 0) + 1;
            return acc;
        }, {});
        const ordenados = Object.entries(conteo).sort((a, b) => b[1] - a[1]);
        const total = datos.length;
        const topGrafica = ordenados.slice(0, limite);
        return { ordenados, topGrafica, total };
    }, [datos, campo, limite]);

    if (datos.length === 0) return null;

    const chartData = {
        labels: procesados.topGrafica.map(([k]) => k),
        datasets: [{
            data: procesados.topGrafica.map(([, v]) => v),
            backgroundColor: color,
            borderRadius: 4,
            barThickness: tipo === 'bar' ? (mostrarTablas ? 20 : 35) : undefined 
        }]
    };

    const options = {
        indexAxis: tipo === 'bar' ? 'y' : 'x',
        maintainAspectRatio: false,
        animation: { duration: 0 }, 
        plugins: { legend: { display: tipo !== 'bar', position: 'right' } },
        scales: tipo === 'bar' ? { x: { display: false }, y: { ticks: { font: { size: 10 }, callback: function(v){ const l=this.getLabelForValue(v); return l.length>25?l.substr(0,25)+'...':l; } }, grid: { display: false } } } : {}
    };

    return (
        <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden h-full flex flex-col transition-all duration-300">
            <div className="bg-slate-50 px-4 py-3 border-b border-slate-200 flex justify-between items-center shrink-0">
                <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide">{titulo}</h3>
                <span className="text-xs font-bold bg-slate-200 text-slate-700 px-2 py-1 rounded-full">{procesados.total}</span>
            </div>
            
            <div className={`flex-1 grid grid-cols-1 ${mostrarTablas ? 'lg:grid-cols-5' : 'lg:grid-cols-1'} min-h-[300px]`}>
                <div className={`p-4 col-span-1 ${mostrarTablas ? 'lg:col-span-3 border-b lg:border-b-0 lg:border-r border-slate-100' : 'lg:col-span-1'} transition-all`}>
                    <div className="w-full h-full relative min-h-[250px]">
                        {tipo === 'bar' ? <Bar data={chartData} options={options} id={idCanvas} /> : 
                         <div className="h-full flex justify-center"><Doughnut data={chartData} options={options} id={idCanvas} /></div>}
                    </div>
                </div>

                {mostrarTablas && (
                    <div className="bg-white col-span-1 lg:col-span-2 max-h-[300px] overflow-y-auto custom-scrollbar">
                        <table className="w-full text-xs text-left text-slate-600">
                            <thead className="text-[10px] text-slate-400 uppercase bg-slate-50 sticky top-0">
                                <tr>
                                    <th className="px-3 py-2">Concepto</th>
                                    <th className="px-3 py-2 text-right">#</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-50">
                                {procesados.ordenados.map(([nombre, cantidad], i) => (
                                    <tr key={i} className="hover:bg-slate-50 transition">
                                        <td className="px-3 py-2 font-medium truncate max-w-[120px]" title={nombre}>
                                            <span className="text-[9px] text-slate-300 font-mono inline-block w-3 mr-1">{i+1}</span>
                                            {nombre}
                                        </td>
                                        <td className="px-3 py-2 text-right font-bold text-slate-700">{cantidad}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </div>
    );
};

// ============================================================================
// 2. NUEVO COMPONENTE: ANÁLISIS DE CATEGORÍAS
// ============================================================================
const AnalisisCategorias = ({ idCanvas, datos, mostrarTablas = true }) => {
    const { chartData, tableData, totalTotal } = useMemo(() => {
        const conteo = {};
        let totalGeneral = 0;

        datos.forEach(d => {
            const cat = (d.categoria || 'Sin Dato').trim();
            const ev = (d.evento || '').toUpperCase();

            if (!conteo[cat]) {
                conteo[cat] = { adversos: 0, cuasi: 0, centinelas: 0, total: 0 };
            }

            if (ev.includes('ADVERSO')) conteo[cat].adversos++;
            else if (ev.includes('CUASI')) conteo[cat].cuasi++;
            else if (ev.includes('CENTINELA')) conteo[cat].centinelas++;
            
            conteo[cat].total++;
            totalGeneral++;
        });

        const ordenados = Object.entries(conteo)
            .map(([nombre, counts]) => ({ nombre, ...counts }))
            .sort((a, b) => b.total - a.total);

        const top5 = ordenados.slice(0, 5);

        return {
            chartData: {
                labels: top5.map(i => i.nombre.length > 25 ? i.nombre.substring(0, 25) + '...' : i.nombre),
                datasets: [
                    { label: 'Adversos', data: top5.map(i => i.adversos), backgroundColor: '#ef4444', borderRadius: 4 },
                    { label: 'Cuasifallas', data: top5.map(i => i.cuasi), backgroundColor: '#f59e0b', borderRadius: 4 },
                    { label: 'Centinelas', data: top5.map(i => i.centinelas), backgroundColor: '#1e293b', borderRadius: 4 }
                ]
            },
            tableData: ordenados, 
            totalTotal: totalGeneral
        };
    }, [datos]);

    const options = {
        indexAxis: 'y',
        maintainAspectRatio: false,
        animation: { duration: 0 },
        scales: {
            x: { stacked: true, grid: { display: false } },
            y: { stacked: true, grid: { display: false } }
        },
        plugins: {
            legend: { position: 'bottom' }
        }
    };

    if (datos.length === 0) return null;

    return (
        <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden h-full flex flex-col transition-all duration-300 mb-6">
            <div className="bg-slate-50 px-4 py-3 border-b border-slate-200 flex justify-between items-center shrink-0">
                <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide">Categoría que Reporta</h3>
                <span className="text-xs font-bold bg-slate-200 text-slate-700 px-2 py-1 rounded-full">{totalTotal}</span>
            </div>
            
            <div className={`flex-1 grid grid-cols-1 ${mostrarTablas ? 'lg:grid-cols-5' : 'lg:grid-cols-1'} min-h-[300px]`}>
                <div className={`p-4 col-span-1 ${mostrarTablas ? 'lg:col-span-3 border-b lg:border-b-0 lg:border-r border-slate-100' : 'lg:col-span-1'} transition-all`}>
                    <div className="w-full h-full relative min-h-[250px]">
                        <Bar data={chartData} options={options} id={idCanvas} />
                    </div>
                </div>

                {mostrarTablas && (
                    <div className="bg-white col-span-1 lg:col-span-2 max-h-[300px] overflow-y-auto custom-scrollbar">
                        <table className="w-full text-[11px] text-left text-slate-600">
                            <thead className="text-[10px] text-slate-400 uppercase bg-slate-50 sticky top-0 shadow-sm z-10">
                                <tr>
                                    <th className="px-3 py-2">Categoría</th>
                                    <th className="px-2 py-2 text-center text-red-600" title="Adversos">Adv.</th>
                                    <th className="px-2 py-2 text-center text-amber-600" title="Cuasifallas">Cuas.</th>
                                    <th className="px-2 py-2 text-center text-slate-800" title="Centinelas">Cen.</th>
                                    <th className="px-3 py-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-50">
                                {tableData.map((row, i) => (
                                    <tr key={i} className="hover:bg-slate-50 transition">
                                        <td className="px-3 py-2 font-medium truncate max-w-[100px]" title={row.nombre}>
                                            <span className="text-[9px] text-slate-300 font-mono inline-block w-3 mr-1">{i+1}</span>
                                            {row.nombre}
                                        </td>
                                        <td className="px-2 py-2 text-center font-bold text-slate-500">{row.adversos > 0 ? row.adversos : '-'}</td>
                                        <td className="px-2 py-2 text-center font-bold text-slate-500">{row.cuasi > 0 ? row.cuasi : '-'}</td>
                                        <td className="px-2 py-2 text-center font-bold text-slate-500">{row.centinelas > 0 ? row.centinelas : '-'}</td>
                                        <td className="px-3 py-2 text-right font-black text-slate-700">{row.total}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </div>
    );
};

// ============================================================================
// 3. COMPONENTE: ANÁLISIS TOP 3 ÁREAS 
// ============================================================================
const AnalisisTopAreas = ({ prefijoId, datos, colorArea, colorCausa, mostrarTablas }) => {
    const topAreas = useMemo(() => {
        const conteo = datos.reduce((acc, curr) => {
            const k = (curr.servicio || 'Sin Dato').trim();
            acc[k] = (acc[k] || 0) + 1;
            return acc;
        }, {});
        return Object.entries(conteo).sort((a,b)=>b[1]-a[1]).slice(0,3).map(e=>e[0]);
    }, [datos]);

    if (datos.length === 0) return null;

    return (
        <>
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <SeccionGraficoTabla idCanvas={`${prefijoId}_areas_mayor_incidencia`} titulo="Áreas con Mayor Incidencia" datos={datos} campo="servicio" color={colorArea} mostrarTablas={mostrarTablas} />
                {topAreas[0] && (
                    <div className="ring-2 ring-offset-2 ring-slate-100 rounded-xl">
                        <SeccionGraficoTabla idCanvas={`${prefijoId}_analisis_area_1`} titulo={`#1 ANÁLISIS ÁREA: ${topAreas[0]}`} datos={datos.filter(d => d.servicio === topAreas[0])} campo="definicion" color={colorCausa} limite={7} mostrarTablas={mostrarTablas} />
                    </div>
                )}
            </div>
            {(topAreas[1] || topAreas[2]) && (
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    {topAreas[1] ? <SeccionGraficoTabla idCanvas={`${prefijoId}_analisis_area_2`} titulo={`#2 ANÁLISIS ÁREA: ${topAreas[1]}`} datos={datos.filter(d => d.servicio === topAreas[1])} campo="definicion" color={colorCausa} limite={5} mostrarTablas={mostrarTablas} /> : <div />}
                    {topAreas[2] ? <SeccionGraficoTabla idCanvas={`${prefijoId}_analisis_area_3`} titulo={`#3 ANÁLISIS ÁREA: ${topAreas[2]}`} datos={datos.filter(d => d.servicio === topAreas[2])} campo="definicion" color={colorCausa} limite={5} mostrarTablas={mostrarTablas} /> : <div />}
                </div>
            )}
        </>
    );
};

// ============================================================================
// 4. COMPONENTE: PIRÁMIDE POBLACIONAL 
// ============================================================================
const PiramidePoblacional = ({ idCanvas, datos, colorHombres = '#005C46', colorMujeres = '#D4C19C', mostrarTablas = true }) => {
    const { chartData, tableData, totalTotal } = useMemo(() => {
        const brackets = ['< 1 año', '1 a 4', '5 a 9', '10 a 14', '15 a 19', '20 a 29', '30 a 39', '40 a 49', '50 a 59', '> 60 años'];
        const hombres = Array(10).fill(0);
        const mujeres = Array(10).fill(0);

        datos.forEach(d => {
            let valorEdad = d.edad || d.Edad || d.EDAD;
            let valorSexo = d.sexo || d.Sexo || d.SEXO;

            if (valorEdad === undefined || valorSexo === undefined) return;

            let valStr = String(valorEdad).toUpperCase().trim();
            let index = -1;

            if (valStr.includes('<') || valStr.includes('MES') || valStr.includes('DIA') || valStr.includes('DÍA') || valStr.includes('MENOR') || valStr === '0') {
                index = 0;
            } else if (valStr.includes('>60') || valStr.includes('> 60') || valStr.includes('MAYOR')) {
                index = 9;
            } else {
                let match = valStr.match(/\d+/);
                if (match) {
                    let e = parseInt(match[0], 10);
                    if (e === 0) index = 0;
                    else if (e <= 4) index = 1;
                    else if (e <= 9) index = 2;
                    else if (e <= 14) index = 3;
                    else if (e <= 19) index = 4;
                    else if (e <= 29) index = 5;
                    else if (e <= 39) index = 6;
                    else if (e <= 49) index = 7;
                    else if (e <= 59) index = 8;
                    else index = 9; 
                }
            }

            if (index === -1) return; 
            
            let s = String(valorSexo).toUpperCase().trim();
            if (s === 'H' || s.includes('HOMBRE') || s === 'MASCULINO') {
                hombres[index]++;
            } else if (s === 'M' || s === 'F' || s.includes('MUJER') || s.includes('FEMENINO')) {
                mujeres[index]++;
            }
        });

        let tbl = brackets.map((rango, i) => ({
            rango,
            h: hombres[i],
            m: mujeres[i],
            t: hombres[i] + mujeres[i]
        })).filter(row => row.t > 0);

        tbl.sort((a, b) => b.t - a.t);
        const totalTotal = tbl.reduce((sum, row) => sum + row.t, 0);

        return {
            chartData: {
                labels: brackets,
                datasets: [
                    { label: 'Hombres', data: hombres.map(v => -v), backgroundColor: colorHombres, borderRadius: 4 }, 
                    { label: 'Mujeres', data: mujeres, backgroundColor: colorMujeres, borderRadius: 4 }
                ]
            },
            tableData: tbl,
            totalTotal
        };
    }, [datos, colorHombres, colorMujeres]);

    const options = {
        indexAxis: 'y',
        maintainAspectRatio: false,
        animation: { duration: 0 },
        scales: { 
            x: { stacked: true, ticks: { callback: v => Math.abs(v) }, grid: {display: false} }, 
            y: { stacked: true, grid: {display: false} } 
        },
        plugins: { 
            tooltip: { callbacks: { label: c => `${c.dataset.label}: ${Math.abs(c.raw)}` } },
            legend: { position: 'bottom' }
        }
    };

    return (
        <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden h-full flex flex-col transition-all duration-300">
            <div className="bg-slate-50 px-4 py-3 border-b border-slate-200 flex justify-between items-center shrink-0">
                <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide">Pirámide Poblacional</h3>
                <span className="text-xs font-bold bg-slate-200 text-slate-700 px-2 py-1 rounded-full">{totalTotal}</span>
            </div>
            
            <div className={`flex-1 grid grid-cols-1 ${mostrarTablas ? 'lg:grid-cols-5' : 'lg:grid-cols-1'} min-h-[300px]`}>
                <div className={`p-4 col-span-1 ${mostrarTablas ? 'lg:col-span-3 border-b lg:border-b-0 lg:border-r border-slate-100' : 'lg:col-span-1'} transition-all`}>
                    <div className="w-full h-full relative min-h-[250px]">
                        <Bar data={chartData} options={options} id={idCanvas} />
                    </div>
                </div>

                {mostrarTablas && (
                    <div className="bg-white col-span-1 lg:col-span-2 max-h-[300px] overflow-y-auto custom-scrollbar">
                        <table className="w-full text-xs text-left text-slate-600">
                            <thead className="text-[10px] text-slate-400 uppercase bg-slate-50 sticky top-0 shadow-sm z-10">
                                <tr>
                                    <th className="px-3 py-2">Edad</th>
                                    <th className="px-2 py-2 text-center text-blue-600">H</th>
                                    <th className="px-2 py-2 text-center text-pink-600">M</th>
                                    <th className="px-3 py-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-50">
                                {tableData.map((row, i) => (
                                    <tr key={i} className="hover:bg-slate-50 transition">
                                        <td className="px-3 py-2 font-medium truncate max-w-[100px]" title={row.rango}>
                                            <span className="text-[9px] text-slate-300 font-mono inline-block w-3 mr-1">{i+1}</span>
                                            {row.rango}
                                        </td>
                                        <td className="px-2 py-2 text-center font-bold text-slate-500">{row.h > 0 ? row.h : '-'}</td>
                                        <td className="px-2 py-2 text-center font-bold text-slate-500">{row.m > 0 ? row.m : '-'}</td>
                                        <td className="px-3 py-2 text-right font-black text-slate-700">{row.t}</td>
                                    </tr>
                                ))}
                                {tableData.length === 0 && (
                                    <tr>
                                        <td colSpan="4" className="px-3 py-4 text-center text-slate-400 italic">Sin datos</td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </div>
    );
};

// ============================================================================
// 5. COMPONENTE: ACORDEÓN DE PROCESOS 
// ============================================================================
const AcordeonProcesos = ({ prefijoId, datos, colorBase = "#005C46", titulo = "Análisis de Procesos Relacionados", mostrarTablas }) => {
    const [expandido, setExpandido] = useState(true); 

    const topProcesos = useMemo(() => {
        const conteo = datos.reduce((acc, curr) => {
            const k = (curr.proceso || 'Sin Dato').trim();
            acc[k] = (acc[k] || 0) + 1;
            return acc;
        }, {});
        return Object.entries(conteo).sort((a,b)=>b[1]-a[1]).slice(0,3).map(e=>e[0]);
    }, [datos]);

    return (
        <div className="mt-8 mb-6">
            <button 
                onClick={() => setExpandido(!expandido)} 
                className="w-full flex items-center justify-center gap-3 py-4 bg-slate-100 text-slate-700 border border-slate-200 rounded-xl font-black text-lg hover:bg-slate-200 transition-colors shadow-sm outline-none"
            >
                <Settings size={22} className="text-[#005C46]" /> 
                {titulo} 
                {expandido ? <ChevronUp size={22} className="text-[#005C46]"/> : <ChevronDown size={22} className="text-[#005C46]"/>}
            </button>
            
            {expandido && (
                <div className="mt-6 space-y-6 animate-in fade-in slide-in-from-top-4 duration-300">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <SeccionGraficoTabla idCanvas={`${prefijoId}_procesos_mayor_incidencia`} titulo="Procesos con Mayor Incidencia" datos={datos} campo="proceso" color={colorBase} limite={10} mostrarTablas={mostrarTablas} />
                        {topProcesos[0] && (
                            <div className="ring-2 ring-offset-2 ring-amber-200 rounded-xl">
                                <SeccionGraficoTabla idCanvas={`${prefijoId}_procesos_causas_1`} titulo={`#1 CAUSAS: ${topProcesos[0]}`} datos={datos.filter(d=>d.proceso===topProcesos[0])} campo="definicion" color="#D4C19C" limite={7} mostrarTablas={mostrarTablas} />
                            </div>
                        )}
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {topProcesos[1] && <SeccionGraficoTabla idCanvas={`${prefijoId}_procesos_causas_2`} titulo={`#2 CAUSAS: ${topProcesos[1]}`} datos={datos.filter(d=>d.proceso===topProcesos[1])} campo="definicion" color="#007A5E" limite={5} mostrarTablas={mostrarTablas} />}
                        {topProcesos[2] && <SeccionGraficoTabla idCanvas={`${prefijoId}_procesos_causas_3`} titulo={`#3 CAUSAS: ${topProcesos[2]}`} datos={datos.filter(d=>d.proceso===topProcesos[2])} campo="definicion" color="#007A5E" limite={5} mostrarTablas={mostrarTablas} />}
                    </div>
                </div>
            )}
        </div>
    );
};

// ============================================================================
// 6. APLICACIÓN PRINCIPAL (Layout Base)
// ============================================================================
function dashboardVencer() {
  // 1. Leer la URL para ver qué módulo abrir
  const queryParams = new URLSearchParams(window.location.search);
  const moduloInicial = queryParams.get('modulo') === 'productividad' ? 'productividad' : 'vencer';
  
  // 2. Leer la URL para saber si es Administrador
  const rolEnUrl = queryParams.get('rol');
  const esAdministrador = rolEnUrl === 'administrador'; 

  // 3. Estados
  const [moduloActual, setModuloActual] = useState(moduloInicial);
  const [datos, setDatos] = useState([]);
  const [cargando, setCargando] = useState(true);
  const [error, setError] = useState(null);
  
  const [anioSeleccionado, setAnioSeleccionado] = useState('todos');
  const [mesInicio, setMesInicio] = useState(0); 
  const [mesFin, setMesFin] = useState(11);      
  const [servicioSeleccionado, setServicioSeleccionado] = useState('todos');
  const [procesoSeleccionado, setProcesoSeleccionado] = useState('todos');
  
  const [pestanaActiva, setPestanaActiva] = useState('general');
  const [sidebarCollapsed, setSidebarCollapsed] = useState(false);
  const [mostrarTablas, setMostrarTablas] = useState(true);

  const [descargandoExcel, setDescargandoExcel] = useState(false);

  const generarExcelReporte = async () => {
      setDescargandoExcel(true);
      try {
          const workbook = new ExcelJS.Workbook();

          const attachChartImage = (ws, canvasId, startCol, startRow) => {
              const canvas = document.getElementById(canvasId);
              if (canvas) {
                  const imgData = canvas.toDataURL('image/png');
                  const base64Str = imgData.split(',')[1];
                  const imageId = workbook.addImage({ base64: base64Str, extension: 'png' });
                  ws.addImage(imageId, { tl: { col: startCol, row: startRow }, ext: { width: 450, height: 250 } });
              }
          };

          const addSection = (ws, title, obj, startRow, canvasIdParaFoto) => {
              ws.getCell(`A${startRow}`).value = title;
              ws.getCell(`A${startRow}`).font = { bold: true, size: 14, color: { argb: 'FF7A123A' } };
              ws.getCell(`A${startRow + 1}`).value = "Concepto";
              ws.getCell(`B${startRow + 1}`).value = "Total";
              ws.getCell(`A${startRow + 1}`).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF333333' } };
              ws.getCell(`A${startRow + 1}`).font = { color: { argb: 'FFFFFFFF' } };
              ws.getCell(`B${startRow + 1}`).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF333333' } };
              ws.getCell(`B${startRow + 1}`).font = { color: { argb: 'FFFFFFFF' } };

              let cr = startRow + 2;
              Object.entries(obj).sort((a, b) => b[1] - a[1]).forEach(([k, v]) => {
                  ws.getCell(`A${cr}`).value = k;
                  ws.getCell(`B${cr}`).value = v;
                  cr++;
              });

              if (canvasIdParaFoto) attachChartImage(ws, canvasIdParaFoto, 3, startRow);
              return Math.max(cr + 2, startRow + 15); 
          };

          const addSectionCategorias = (ws, title, obj, startRow, canvasIdParaFoto) => {
              ws.getCell(`A${startRow}`).value = title;
              ws.getCell(`A${startRow}`).font = { bold: true, size: 14, color: { argb: 'FF7A123A' } };
              
              const headers = ['A', 'B', 'C', 'D', 'E'];
              const cols = ["Categoría", "Adversos", "Cuasi", "Centinelas", "Total"];
              headers.forEach((h, i) => {
                  ws.getCell(`${h}${startRow + 1}`).value = cols[i];
                  ws.getCell(`${h}${startRow + 1}`).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF333333' } };
                  ws.getCell(`${h}${startRow + 1}`).font = { color: { argb: 'FFFFFFFF' } };
              });

              let cr = startRow + 2;
              Object.entries(obj).sort((a, b) => b[1].total - a[1].total).forEach(([k, v]) => {
                  ws.getCell(`A${cr}`).value = k;
                  ws.getCell(`B${cr}`).value = v.adversos;
                  ws.getCell(`C${cr}`).value = v.cuasi;
                  ws.getCell(`D${cr}`).value = v.centinela;
                  ws.getCell(`E${cr}`).value = v.total;
                  cr++;
              });

              if (canvasIdParaFoto) attachChartImage(ws, canvasIdParaFoto, 6, startRow);
              return Math.max(cr + 2, startRow + 15);
          };

          let s = {
              General: { Sexo: {}, Evento: {}, Turno: {}, Servicio: {}, Proceso: {}, Categoria: {} },
              Adverso: { Servicio: {}, Definicion: {}, Proceso: {} },
              Cuasi: { Servicio: {}, Definicion: {}, Proceso: {} },
              Centinela: { Servicio: {}, Definicion: {}, Proceso: {} }
          };

          datosFiltrados.forEach(r => {
              let ev = (r.evento || 'Sin Dato').trim();
              let sx = (r.sexo || 'Sin Dato').trim();
              let tu = (r.turno || 'Sin Dato').trim();
              let sv = (r.servicio || 'Sin Dato').trim();
              let def = (r.definicion || 'Sin Dato').trim();
              let pro = (r.proceso || 'Sin Dato').trim();
              let cat = (r.categoria || 'Sin Dato').trim();
              let evUpper = ev.toUpperCase();

              s.General.Sexo[sx] = (s.General.Sexo[sx] || 0) + 1;
              s.General.Evento[ev] = (s.General.Evento[ev] || 0) + 1;
              s.General.Turno[tu] = (s.General.Turno[tu] || 0) + 1;
              s.General.Servicio[sv] = (s.General.Servicio[sv] || 0) + 1;
              s.General.Proceso[pro] = (s.General.Proceso[pro] || 0) + 1;

              if (!s.General.Categoria[cat]) s.General.Categoria[cat] = { adversos: 0, cuasi: 0, centinela: 0, total: 0 };
              s.General.Categoria[cat].total++;
              if (evUpper.includes('ADVERSO')) s.General.Categoria[cat].adversos++;
              else if (evUpper.includes('CUASI')) s.General.Categoria[cat].cuasi++;
              else if (evUpper.includes('CENTINELA')) s.General.Categoria[cat].centinela++;

              if (evUpper.includes('ADVERSO')) {
                  s.Adverso.Servicio[sv] = (s.Adverso.Servicio[sv] || 0) + 1;
                  s.Adverso.Definicion[def] = (s.Adverso.Definicion[def] || 0) + 1;
                  s.Adverso.Proceso[pro] = (s.Adverso.Proceso[pro] || 0) + 1;
              } else if (evUpper.includes('CUASI')) {
                  s.Cuasi.Servicio[sv] = (s.Cuasi.Servicio[sv] || 0) + 1;
                  s.Cuasi.Definicion[def] = (s.Cuasi.Definicion[def] || 0) + 1;
                  s.Cuasi.Proceso[pro] = (s.Cuasi.Proceso[pro] || 0) + 1;
              } else if (evUpper.includes('CENTINELA')) {
                  s.Centinela.Servicio[sv] = (s.Centinela.Servicio[sv] || 0) + 1;
                  s.Centinela.Definicion[def] = (s.Centinela.Definicion[def] || 0) + 1;
                  s.Centinela.Proceso[pro] = (s.Centinela.Proceso[pro] || 0) + 1;
              }
          });

          // Hoja 1: Generales
          const wsG = workbook.addWorksheet('Generales');
          wsG.getColumn(1).width = 50;
          let rowGen = 1;
          rowGen = addSection(wsG, "SEXO", s.General.Sexo, rowGen, 'gen_sexo');
          rowGen = addSection(wsG, "EVENTOS", s.General.Evento, rowGen, 'gen_evento');
          rowGen = addSection(wsG, "TURNOS", s.General.Turno, rowGen, 'gen_turno');
          
          wsG.getCell(`A${rowGen}`).value = "PIRÁMIDE POBLACIONAL";
          wsG.getCell(`A${rowGen}`).font = { bold: true, size: 14 };
          attachChartImage(wsG, 'gen_piramide', 0, rowGen + 1);
          rowGen += 15; 
          
          rowGen = addSection(wsG, "SERVICIOS", s.General.Servicio, rowGen, 'gen_servicio');
          rowGen = addSectionCategorias(wsG, "CATEGORÍA QUE REPORTA", s.General.Categoria, rowGen, 'gen_categorias');

          // Hoja 2: Adversos
          const wsA = workbook.addWorksheet('Adversos');
          wsA.getColumn(1).width = 60;
          let rA = 1;
          rA = addSection(wsA, `TOP ÁREAS (Adversos)`, s.Adverso.Servicio, rA, 'adv_areas_mayor_incidencia');
          rA = addSection(wsA, `CAUSAS GLOBALES (Adversos)`, s.Adverso.Definicion, rA, 'adv_causas_globales');
          rA = addSection(wsA, `PROCESOS RELACIONADOS (Adversos)`, s.Adverso.Proceso, rA, 'adv_procesos_mayor_incidencia');

          // Hoja 3: Cuasifallas
          const wsC = workbook.addWorksheet('Cuasifallas');
          wsC.getColumn(1).width = 60;
          let rC = 1;
          rC = addSection(wsC, `TOP ÁREAS (Cuasifallas)`, s.Cuasi.Servicio, rC, 'cuasi_areas_mayor_incidencia');
          rC = addSection(wsC, `CAUSAS GLOBALES (Cuasifallas)`, s.Cuasi.Definicion, rC, 'cuasi_causas_globales');
          rC = addSection(wsC, `PROCESOS RELACIONADOS (Cuasifallas)`, s.Cuasi.Proceso, rC, 'cuasi_procesos_mayor_incidencia');

          // Hoja 4: Centinelas
          const wsCen = workbook.addWorksheet('Centinelas');
          wsCen.getColumn(1).width = 60;
          let rCen = 1;
          rCen = addSection(wsCen, `TOP ÁREAS (Centinelas)`, s.Centinela.Servicio, rCen, 'cen_areas_mayor_incidencia');
          rCen = addSection(wsCen, `CAUSAS GLOBALES (Centinelas)`, s.Centinela.Definicion, rCen, 'cen_causas_globales');
          rCen = addSection(wsCen, `PROCESOS RELACIONADOS (Centinelas)`, s.Centinela.Proceso, rCen, 'cen_procesos_mayor_incidencia');

          const buffer = await workbook.xlsx.writeBuffer();
          saveAs(new Blob([buffer]), `Reporte_VENCER_${new Date().toISOString().split('T')[0]}.xlsx`);

      } catch (error) {
          console.error(error);
          alert("Error al generar el Excel.");
      } finally {
          setDescargandoExcel(false);
      }
  };

useEffect(() => {
    // Solo cargamos la BD de VENCER si el usuario realmente está en ese módulo
    if (moduloActual === 'vencer') {
        // Asegúrate de que esta ruta llegue bien a tu PHP. 
        // A veces poner la ruta completa ayuda, ej: '/tu-carpeta-proyecto/api/api_vencer.php'
        axios.get('/api/api_vencer.php')
          .then(res => { 
              setDatos(Array.isArray(res.data) ? res.data : []); 
              setCargando(false); 
          })
          .catch(err => { 
              setError("Error de conexión con el servidor"); 
              setCargando(false); 
          });
    } else {
        // Si estamos en Productividad o Usuarios, apagamos la carga de Vencer
        setCargando(false); 
    }
  }, [moduloActual]);

  const aniosDisponibles = useMemo(() => [...new Set(datos.map(d => d.anio || (d.fecha_evento ? d.fecha_evento.split('-')[0] : null)).filter(a => a))].sort().reverse(), [datos]);
  
  const serviciosDisponibles = useMemo(() => {
      const servs = new Set(datos.map(d => (d.servicio || 'Sin Dato').trim()));
      return Array.from(servs).sort();
  }, [datos]);

  const procesosDisponibles = useMemo(() => {
      const procs = new Set(datos.map(d => (d.proceso || 'Sin Dato').trim()));
      return Array.from(procs).sort();
  }, [datos]);

  const datosFiltrados = useMemo(() => datos.filter(item => {
        if (!item.fecha_evento) return false;
        const [a, m] = item.fecha_evento.split('-');
        const mesIdx = parseInt(m) - 1;

        const pasaAnio = anioSeleccionado === 'todos' || a === anioSeleccionado;
        const pasaMes = mesIdx >= mesInicio && mesIdx <= mesFin;
        const pasaServicio = servicioSeleccionado === 'todos' || (item.servicio || 'Sin Dato').trim() === servicioSeleccionado;
        const pasaProceso = procesoSeleccionado === 'todos' || (item.proceso || 'Sin Dato').trim() === procesoSeleccionado;

        return pasaAnio && pasaMes && pasaServicio && pasaProceso;
  }), [datos, anioSeleccionado, mesInicio, mesFin, servicioSeleccionado, procesoSeleccionado]);

  const dGeneral = datosFiltrados;
  const dAdversos = datosFiltrados.filter(d => (d.evento||'').toUpperCase().includes('ADVERSO'));
  const dCuasi = datosFiltrados.filter(d => (d.evento||'').toUpperCase().includes('CUASI'));
  const dCentinela = datosFiltrados.filter(d => (d.evento||'').toUpperCase().includes('CENTINELA'));

  if (cargando) return <div className="h-screen flex flex-col items-center justify-center text-[#005C46] bg-slate-50 font-bold text-xl"><Activity className="animate-spin mb-4" size={40}/>Cargando Sistema VENCER...</div>;
  if (error) return <div className="h-screen flex items-center justify-center text-red-500 bg-slate-50 font-bold">{error}</div>;

  // EL INTERCEPTOR
  // Si la URL dice productividad, renderiza SOLO el componente rojo mate y se olvida de lo demás.
  if (moduloActual === 'productividad') {
      return <DashboardProductividad isAdmin={esAdministrador} />;
  }

  // Si no, dibuja toda la interfaz original de Vencer
  return (
    <div style={{ display: 'grid', gridTemplateColumns: sidebarCollapsed ? '80px 1fr' : '260px 1fr', height: '100vh', width: '100vw', overflow: 'hidden', backgroundColor: '#f8fafc' }} className="text-slate-800 font-sans">
      
      {/* --- COLUMNA 1: SIDEBAR --- */}
      <div style={{ backgroundColor: '#005C46', zIndex: 20, height: '100%' }}>
        <Sidebar 
          moduloActual={moduloActual}               
          setModuloActual={setModuloActual}         
          pestanaActiva={pestanaActiva} 
          setPestanaActiva={setPestanaActiva} 
          conteos={{ general: dGeneral.length, adversos: dAdversos.length, cuasi: dCuasi.length, centinela: dCentinela.length }}
          sidebarCollapsed={sidebarCollapsed}
          setSidebarCollapsed={setSidebarCollapsed}
          mostrarTablas={mostrarTablas}
          setMostrarTablas={setMostrarTablas}
          generarExcelReporte={generarExcelReporte} 
          descargandoExcel={descargandoExcel}
          hayDatos={datosFiltrados.length > 0}
        />
      </div>

      {/* --- COLUMNA 2: CONTENIDO DERECHO --- */}
      <div style={{ overflowY: 'auto', overflowX: 'hidden', minWidth: 0, height: '100%', position: 'relative', zIndex: 0 }}>
        
        {moduloActual === 'vencer' && (
          <>
            <nav className="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm min-h-[64px] py-2 flex-shrink-0">
              <div className="max-w-[1400px] mx-auto px-6 h-full flex flex-col md:flex-row items-center justify-between gap-2">
                  <div className="flex items-center gap-3">
                      <div className="flex items-center gap-2 text-[#005C46] font-bold"><Filter size={18}/> Filtros:</div>
                  </div>

                  <div className="flex items-center gap-3">
                      <div className="flex items-center gap-2 bg-slate-50 rounded-lg p-1.5 border border-slate-200 text-sm shadow-inner flex-wrap justify-center">
                          
                          {/* FILTRO AÑO */}
                          <span className="font-bold text-slate-500 text-[10px] uppercase ml-1">Año:</span>
                          <select className="bg-transparent font-bold text-[#005C46] outline-none cursor-pointer" value={anioSeleccionado} onChange={e=>setAnioSeleccionado(e.target.value)}>
                              <option value="todos">Todos</option>
                              {aniosDisponibles.map(a=><option key={a} value={a}>{a}</option>)}
                          </select>
                          
                          <div className="w-px h-5 bg-slate-300 mx-1"></div>
                          
                          {/* FILTROS MESES */}
                          <span className="font-bold text-slate-500 text-[10px] uppercase">De:</span>
                          <select className="bg-transparent font-bold text-[#005C46] outline-none cursor-pointer" value={mesInicio} onChange={e=>setMesInicio(Number(e.target.value))}>
                              {MESES.map((m, i) => <option key={i} value={i}>{m}</option>)}
                          </select>
                          <span className="text-slate-400 font-bold">-</span>
                          <span className="font-bold text-slate-500 text-[10px] uppercase">A:</span>
                          <select className="bg-transparent font-bold text-[#005C46] outline-none cursor-pointer" value={mesFin} onChange={e=>setMesFin(Number(e.target.value))}>
                              {MESES.map((m, i) => <option key={i} value={i}>{m}</option>)}
                          </select>

                          {/* FILTRO SERVICIO */}
                          <div className="w-px h-5 bg-slate-300 mx-1 hidden sm:block"></div>
                          <span className="font-bold text-slate-500 text-[10px] uppercase hidden sm:inline">Servicio:</span>
                          <select className="bg-transparent font-bold text-[#005C46] outline-none cursor-pointer max-w-[120px] lg:max-w-[150px] truncate hidden sm:inline" value={servicioSeleccionado} onChange={e=>setServicioSeleccionado(e.target.value)}>
                              <option value="todos">Todos los servicios</option>
                              {serviciosDisponibles.map(s=><option key={s} value={s}>{s}</option>)}
                          </select>

                          {/* FILTRO PROCESO */}
                          <div className="w-px h-5 bg-slate-300 mx-1 hidden sm:block"></div>
                          <span className="font-bold text-slate-500 text-[10px] uppercase hidden sm:inline">Proceso:</span>
                          <select className="bg-transparent font-bold text-[#005C46] outline-none cursor-pointer max-w-[120px] lg:max-w-[150px] truncate hidden sm:inline" value={procesoSeleccionado} onChange={e=>setProcesoSeleccionado(e.target.value)}>
                              <option value="todos">Todos los procesos</option>
                              {procesosDisponibles.map(p=><option key={p} value={p}>{p}</option>)}
                          </select>
                      </div>
                  </div>
              </div>
            </nav>

            <main className="max-w-[1400px] mx-auto px-8 pt-8 pb-16 w-full transition-all duration-300 relative">
              
              {/* ===================================================
                  PESTAÑA: PANORAMA GENERAL
              =================================================== */}
              <div className={pestanaActiva === 'general' ? 'block animate-in fade-in slide-in-from-bottom-4 duration-500' : 'absolute top-[-9999px] left-[-9999px] w-[1200px] invisible opacity-0'}>
                  <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                      <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 text-center">
                          <p className="text-xs font-bold text-slate-500 uppercase tracking-widest">Total Eventos</p>
                          <p className="text-4xl font-black text-[#005C46] mt-2">{dGeneral.length}</p>
                      </div>
                      <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 text-center">
                          <p className="text-xs font-bold text-slate-500 uppercase tracking-widest">Adversos</p>
                          <p className="text-4xl font-black text-red-600 mt-2">{dAdversos.length}</p>
                      </div>
                      <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 text-center">
                          <p className="text-xs font-bold text-slate-500 uppercase tracking-widest">Cuasifallas</p>
                          <p className="text-4xl font-black text-amber-500 mt-2">{dCuasi.length}</p>
                      </div>
                        <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 text-center">
                          <p className="text-xs font-bold text-slate-500 uppercase tracking-widest">Centinelas</p>
                          <p className="text-4xl font-black text-slate-800 mt-2">{dCentinela.length}</p>
                      </div>
                  </div>

                  <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                      <SeccionGraficoTabla idCanvas="gen_sexo" titulo="Distribución por Sexo" datos={dGeneral} campo="sexo" tipo="doughnut" color={['#005C46', '#D4C19C', '#003B2D']} mostrarTablas={mostrarTablas} />
                      <SeccionGraficoTabla idCanvas="gen_evento" titulo="Clasificación de Eventos" datos={dGeneral} campo="evento" color="#007A5E" mostrarTablas={mostrarTablas} />
                  </div>

                  <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                      <PiramidePoblacional idCanvas="gen_piramide" datos={dGeneral} mostrarTablas = {mostrarTablas} />
                      <SeccionGraficoTabla idCanvas="gen_turno" titulo="Turnos" datos={dGeneral} campo="turno" color="#D4C19C" mostrarTablas={mostrarTablas} />
                  </div>

                  <div className="mb-6">
                      <SeccionGraficoTabla idCanvas="gen_servicio" titulo="Top Servicios" datos={dGeneral} campo="servicio" color="#005C46" limite={15} mostrarTablas={mostrarTablas} />
                  </div>

                  <AnalisisCategorias idCanvas="gen_categorias" datos={dGeneral} mostrarTablas={mostrarTablas} />

                  <AcordeonProcesos prefijoId="gen" datos={dGeneral} colorBase="#005C46" titulo="Análisis de Procesos Relacionados (General)" mostrarTablas={mostrarTablas} />
              </div>

              {/* ===================================================
                  PESTAÑA: EVENTOS ADVERSOS
              =================================================== */}
              <div className={pestanaActiva === 'adversos' ? 'block animate-in fade-in slide-in-from-bottom-4 duration-500' : 'absolute top-[-9999px] left-[-9999px] w-[1200px] invisible opacity-0'}>
                  <div className="bg-red-50 border border-red-200 p-4 rounded-xl mb-6 flex gap-3 items-center text-red-800 shadow-sm">
                      <AlertOctagon /> <h2 className="font-bold text-xl uppercase tracking-wider">Análisis de Eventos Adversos</h2>
                  </div>
                  
                  {dAdversos.length === 0 ? <div className="text-center p-10 text-slate-400 font-bold">Sin registros de Eventos Adversos con estos filtros.</div> : (
                      <>
                          {servicioSeleccionado !== 'todos' && (
                              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                                  <PiramidePoblacional idCanvas="adv_piramide" datos={dAdversos} colorHombres="#ef4444" colorMujeres="#fca5a5" mostrarTablas = {mostrarTablas}/>
                                  <SeccionGraficoTabla idCanvas="adv_sexo" titulo={`Distribución por Sexo en ${servicioSeleccionado}`} datos={dAdversos} campo="sexo" tipo="doughnut" color={['#ef4444', '#fca5a5', '#b91c1c']} mostrarTablas={mostrarTablas} />
                              </div>
                          )}

                          <AnalisisTopAreas prefijoId="adv" datos={dAdversos} colorArea="#ef4444" colorCausa="#b91c1c" mostrarTablas={mostrarTablas} />
                          
                          <div className="mb-6">
                              <SeccionGraficoTabla idCanvas="adv_causas_globales" titulo="Causas Globales (Definición)" datos={dAdversos} campo="definicion" color="#991b1b" limite={10} mostrarTablas={mostrarTablas} />
                          </div>
                          <AcordeonProcesos prefijoId="adv" datos={dAdversos} colorBase="#ef4444" titulo="Procesos Relacionados (Adversos)" mostrarTablas={mostrarTablas} />
                      </>
                  )}
              </div>

              {/* ===================================================
                  PESTAÑA: CUASIFALLAS
              =================================================== */}
              <div className={pestanaActiva === 'cuasi' ? 'block animate-in fade-in slide-in-from-bottom-4 duration-500' : 'absolute top-[-9999px] left-[-9999px] w-[1200px] invisible opacity-0'}>
                  <div className="bg-amber-50 border border-amber-200 p-4 rounded-xl mb-6 flex gap-3 items-center text-amber-800 shadow-sm">
                      <ShieldAlert /> <h2 className="font-bold text-xl uppercase tracking-wider">Análisis de Cuasifallas</h2>
                  </div>
                  
                  {dCuasi.length === 0 ? <div className="text-center p-10 text-slate-400 font-bold">Sin registros de Cuasifallas con estos filtros.</div> : (
                      <>
                          {servicioSeleccionado !== 'todos' && (
                              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                                  <PiramidePoblacional idCanvas="cuasi_piramide" datos={dCuasi} colorHombres="#f59e0b" colorMujeres="#fcd34d" mostrarTablas = {mostrarTablas}/>
                                  <SeccionGraficoTabla idCanvas="cuasi_sexo" titulo={`Distribución por Sexo en ${servicioSeleccionado}`} datos={dCuasi} campo="sexo" tipo="doughnut" color={['#f59e0b', '#fcd34d', '#d97706']} mostrarTablas={mostrarTablas} />
                              </div>
                          )}

                          <AnalisisTopAreas prefijoId="cuasi" datos={dCuasi} colorArea="#f59e0b" colorCausa="#d97706" mostrarTablas={mostrarTablas} />
                          
                          <div className="mb-6">
                              <SeccionGraficoTabla idCanvas="cuasi_causas_globales" titulo="Causas Globales (Definición)" datos={dCuasi} campo="definicion" color="#b45309" limite={10} mostrarTablas={mostrarTablas} />
                          </div>
                          <AcordeonProcesos prefijoId="cuasi" datos={dCuasi} colorBase="#f59e0b" titulo="Procesos Relacionados (Cuasifallas)" mostrarTablas={mostrarTablas} />
                      </>
                  )}
              </div>

              {/* ===================================================
                  PESTAÑA: CENTINELAS
              =================================================== */}
              <div className={pestanaActiva === 'centinela' ? 'block animate-in fade-in slide-in-from-bottom-4 duration-500' : 'absolute top-[-9999px] left-[-9999px] w-[1200px] invisible opacity-0'}>
                  <div className="bg-slate-800 border border-slate-900 p-4 rounded-xl mb-6 flex gap-3 items-center text-white shadow-sm">
                      <Siren /> <h2 className="font-bold text-xl uppercase tracking-wider">Eventos Centinela (Críticos)</h2>
                  </div>
                  
                  {dCentinela.length === 0 ? (
                      <div className="flex flex-col items-center justify-center p-16 bg-white border-2 border-dashed border-slate-200 rounded-2xl text-slate-400">
                          <Siren size={48} className="mb-4 text-emerald-400"/>
                          <p className="font-bold text-lg text-slate-600">0 Eventos Centinela</p>
                          <p className="text-sm">No se encontraron registros críticos con los filtros seleccionados.</p>
                      </div>
                  ) : (
                      <>
                          <AnalisisTopAreas prefijoId="cen" datos={dCentinela} colorArea="#475569" colorCausa="#1e293b" mostrarTablas={mostrarTablas} />
                          <div className="mb-6">
                              <SeccionGraficoTabla idCanvas="cen_causas_globales" titulo="Causas Principales (Definición)" datos={dCentinela} campo="definicion" color="#0f172a" limite={10} mostrarTablas={mostrarTablas} />
                          </div>
                          <AcordeonProcesos prefijoId="cen" datos={dCentinela} colorBase="#475569" titulo="Procesos Relacionados (Centinelas)" mostrarTablas={mostrarTablas} />
                      </>
                  )}
              </div>

            </main>
          </>
        )}

      </div>
    </div>
  );
}

export default dashboardVencer;
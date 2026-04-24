import React, { useMemo, useEffect } from 'react';
import { Activity, Stethoscope, Users, CalendarCheck, Clock, MapPin } from 'lucide-react';
import { Doughnut, Bar } from 'react-chartjs-2';

// ==========================================
// SUB-COMPONENTE: Tabla de Datos
// ==========================================
const TablaDatos = ({ titulo1, titulo2, labels, data, dataPV, dataSub, tituloExtra, dataExtra, total = true }) => {
    if (!labels || !data) return null;
    const mostrarDesglose = dataPV && dataSub;
    const totalPV = mostrarDesglose ? dataPV.reduce((a, b) => a + b, 0) : 0;
    const totalSub = mostrarDesglose ? dataSub.reduce((a, b) => a + b, 0) : 0;
    const totalGeneral = data.reduce((a, b) => a + b, 0);

    return (
        <div className="mt-4 border-t border-slate-100 pt-4 animate-in fade-in slide-in-from-top-2 duration-300 h-full">
            <div className="max-h-[350px] overflow-y-auto pr-2 custom-scrollbar">
                <table className="w-full text-left text-sm text-slate-600">
                    <thead className="text-xs text-slate-400 uppercase bg-slate-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th className="py-2 px-3 font-bold rounded-l-lg">{titulo1}</th>
                            {dataExtra && <th className="py-2 px-3 font-bold">{tituloExtra}</th>}
                            {mostrarDesglose && <th className="py-2 px-3 font-bold text-center text-[#c2410c]/70">1ra Vez</th>}
                            {mostrarDesglose && <th className="py-2 px-3 font-bold text-center text-[#822626]/70">Subsec.</th>}
                            {mostrarDesglose && <th className="py-2 px-3 font-bold text-center text-slate-500">Índice</th>}
                            <th className="py-2 px-3 font-bold text-right rounded-r-lg">{titulo2}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {labels.map((label, index) => {
                            let indice = '0.00';
                            if (dataPV && dataPV[index] > 0) indice = (dataSub[index] / dataPV[index]).toFixed(2);
                            else if (dataSub && dataSub[index] > 0) indice = '∞'; 

                            return (
                                <tr key={index} className="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                                    <td className="py-2 px-3">
                                        {label.toString().replace('Dr. ', 'Lic. ')}
                                    </td>
                                    {dataExtra && <td className="py-2 px-3 text-xs font-bold text-slate-400">{dataExtra[index]}</td>}
                                    {mostrarDesglose && <td className="py-2 px-3 text-center text-[#c2410c] font-medium">{dataPV[index].toLocaleString()}</td>}
                                    {mostrarDesglose && <td className="py-2 px-3 text-center text-[#822626] font-medium">{dataSub[index].toLocaleString()}</td>}
                                    {mostrarDesglose && <td className="py-2 px-3 text-center text-slate-500 font-bold bg-slate-50/50">{indice}</td>}
                                    <td className="py-2 px-3 text-right font-black text-slate-700">{data[index].toLocaleString()}</td>
                                </tr>
                            );
                        })}
                    </tbody>
                    {total && (
                        <tfoot className="bg-slate-50 font-bold sticky bottom-0 z-10 shadow-sm">
                            <tr>
                                <td className="py-2 px-3 rounded-l-lg text-slate-500 uppercase tracking-widest text-xs">Total General</td>
                                {dataExtra && <td className="py-2 px-3"></td>}
                                {mostrarDesglose && <td className="py-2 px-3 text-center text-[#c2410c] font-black">{totalPV.toLocaleString()}</td>}
                                {mostrarDesglose && <td className="py-2 px-3 text-center text-[#822626] font-black">{totalSub.toLocaleString()}</td>}
                                {mostrarDesglose && <td className="py-2 px-3 text-center text-slate-600 font-black bg-slate-100/50">
                                    {totalPV > 0 ? (totalSub / totalPV).toFixed(2) : '0.00'}
                                </td>}
                                <td className="py-2 px-3 text-right rounded-r-lg text-slate-800 font-black">{totalGeneral.toLocaleString()}</td>
                            </tr>
                        </tfoot>
                    )}
                </table>
            </div>
        </div>
    );
};

const anchoDinamico = (cantidad) => cantidad > 15 ? `${cantidad * 40}px` : '100%';

// Criterios de filtrado para áreas paramédicas
const CRITERIOS_PARAMEDICOS = ['6300', '6600', '6900', 'NUTRICION', 'NUTRICIÓN', 'PSICOLOGIA', 'PSICOLOGÍA', 'TRABAJO SOCIAL', 'INHALOTERAPIA', 'FONIATRIA', 'REHABILITACION'];

// ==========================================
// COMPONENTE PRINCIPAL: TABLERO PARAMÉDICOS
// ==========================================
export default function TableroParamedicos({ 
    datos, 
    diccionarioMedicos = {}, 
    diccionarioCIE = {}, 
    diccionarioEspecialidades = {}, 
    mostrarTablas = false, 
    setExportData 
}) {

    // 1. FILTRO DE RAÍZ: Aislamos solo los datos de paramédicos
    const datosFiltrados = useMemo(() => {
        if (!datos || datos.length === 0) return [];
        return datos.filter(d => {
            const esp = String(d.especialidad || d.ESPECIALIDAD || '').toUpperCase();
            return CRITERIOS_PARAMEDICOS.some(c => esp.includes(c));
        });
    }, [datos]);

    // Dentro de TableroParamedicos.jsx
    useEffect(() => {
        if (datosFiltrados && datosFiltrados.length > 0) {
            console.log("Enviando datos de Paramédicos al padre...");
            setExportData(datosFiltrados);
        }
    }, [datosFiltrados, setExportData]);

    // 3. KPIs
    const kpis = useMemo(() => {
        let citados = 0; let primeraVez = 0;
        if (datosFiltrados.length === 0) return { total: 0, citados: 0, espontaneos: 0, primeraVez: 0, subsecuentes: 0 };

        datosFiltrados.forEach(d => {
            const citadoVal = String(d.citado || d.CITADO || '0').trim().toLowerCase().replace('.0', '');
            const pvVal = String(d.primera_vez || d.PRIMERA_VEZ || '0').trim().toLowerCase().replace('.0', '');

            if (citadoVal === '1' || citadoVal === 'citado') citados++;
            if (pvVal === '1' || pvVal === 'primera vez') primeraVez++;
        });

        return { 
            total: datosFiltrados.length, 
            citados, 
            espontaneos: datosFiltrados.length - citados, 
            primeraVez, 
            subsecuentes: datosFiltrados.length - primeraVez 
        };
    }, [datosFiltrados]);

    // 4. TURNOS
    const chartTurnos = useMemo(() => {
        if (datosFiltrados.length === 0) return { labels: [], datasets: [], dataPV: [], dataSub: [] };
        const conteo = datosFiltrados.reduce((acc, curr) => {
            const turno = curr.turno || curr.TURNO || 'Sin Asignar';
            if (!acc[turno]) acc[turno] = { total: 0, pv: 0, sub: 0 };
            acc[turno].total++;
            const pvVal = String(curr.primera_vez || curr.PRIMERA_VEZ || '0').trim().toLowerCase().replace('.0', '');
            if (pvVal === '1' || pvVal === 'primera vez') acc[turno].pv++; else acc[turno].sub++;
            return acc;
        }, {});
        const ordenados = Object.entries(conteo).sort((a, b) => b[1].total - a[1].total);
        return {
            labels: ordenados.map(item => item[0]),
            datasets: [{ data: ordenados.map(item => item[1].total), backgroundColor: ['#059669', '#10b981', '#34d399', '#475569', '#1e293b'], borderWidth: 0 }],
            dataPV: ordenados.map(item => item[1].pv),
            dataSub: ordenados.map(item => item[1].sub)
        };
    }, [datosFiltrados]);

    // 5. ESPECIALIDADES (Áreas Paramédicas)
    const chartEspecialidades = useMemo(() => {
        if (!datosFiltrados || datosFiltrados.length === 0) return { labels: [], datasets: [], dataPV: [], dataSub: [] };
        
        const conteo = datosFiltrados.reduce((acc, curr) => {
            let areaCruda = String(curr.especialidad || curr.ESPECIALIDAD || 'Sin Área').trim().toUpperCase();
            areaCruda = areaCruda.replace('COD:', '').replace('COD: ', '').replace('.0', '').trim();

            const respaldo = {
                '6300': 'TRABAJO SOCIAL',
                '6600': 'PSICOLOGIA',
                '6900': 'NUTRICIÓN Y DIETETICA'
            };

            let areaTraducida = areaCruda;
            if (diccionarioEspecialidades[areaCruda]?.nombre) {
                areaTraducida = String(diccionarioEspecialidades[areaCruda].nombre).toUpperCase();
            } else if (respaldo[areaCruda]) {
                areaTraducida = respaldo[areaCruda];
            }

            if (!acc[areaTraducida]) acc[areaTraducida] = { total: 0, pv: 0, sub: 0 };
            acc[areaTraducida].total++;
            
            const pvVal = String(curr.primera_vez || curr.PRIMERA_VEZ || '0').trim().toLowerCase().replace('.0', '');
            if (pvVal === '1' || pvVal === 'primera vez') acc[areaTraducida].pv++; else acc[areaTraducida].sub++;
            
            return acc;
        }, {});

        const ordenados = Object.entries(conteo).sort((a, b) => b[1].total - a[1].total);
        return {
            labels: ordenados.map(item => item[0]),
            datasets: [{ label: 'Consultas', data: ordenados.map(item => item[1].total), backgroundColor: '#059669', borderRadius: 4 }],
            dataPV: ordenados.map(item => item[1].pv),
            dataSub: ordenados.map(item => item[1].sub)
        };
    }, [datosFiltrados, diccionarioEspecialidades]);
    
    // 6. MÉDICOS / PERSONAL
    const chartMedicos = useMemo(() => {
        if (!datosFiltrados || datosFiltrados.length === 0) return { labels: [], datasets: [], dataPV: [], dataSub: [], dataExtra: [] };

        const conteo = datosFiltrados.reduce((acc, curr) => {
            const matricula = String(curr.matricula_medico || 'Sin Matrícula').trim().replace('.0', '');
            const nombreMedico = diccionarioMedicos[matricula] || `Matr. ${matricula}`;
            
            let areaCruda = String(curr.especialidad || curr.ESPECIALIDAD || 'Sin Área').trim().toUpperCase();
            areaCruda = areaCruda.replace('COD:', '').replace('COD: ', '').replace('.0', '').trim();
            
            const nombreEspecialidad = diccionarioEspecialidades[areaCruda]?.nombre 
                ? String(diccionarioEspecialidades[areaCruda].nombre).toUpperCase() 
                : areaCruda;

            if (!acc[nombreMedico]) {
                acc[nombreMedico] = { total: 0, pv: 0, sub: 0, especialidad: nombreEspecialidad };
            }
            acc[nombreMedico].total++;
            
            const pvVal = String(curr.primera_vez || curr.PRIMERA_VEZ || '0').trim().toLowerCase().replace('.0', '');
            if (pvVal === '1' || pvVal === 'primera vez') acc[nombreMedico].pv++; else acc[nombreMedico].sub++;
            
            return acc;
        }, {});
        
        const ordenados = Object.entries(conteo).sort((a, b) => b[1].total - a[1].total).slice(0, 20);

        return {
            labels: ordenados.map(item => item[0].replace('Dr. ', 'Lic. ')),
            datasets: [{ label: 'Consultas', data: ordenados.map(item => item[1].total), backgroundColor: '#822626', borderRadius: 4 }],
            dataPV: ordenados.map(item => item[1].pv),
            dataSub: ordenados.map(item => item[1].sub),
            dataExtra: ordenados.map(item => item[1].especialidad) 
        };
    }, [datosFiltrados, diccionarioMedicos, diccionarioEspecialidades]);

    // 7. DIAGNÓSTICOS
    const chartDiagnosticos = useMemo(() => {
        if (datosFiltrados.length === 0) return { labels: [], datasets: [], dataPV: [], dataSub: [] };
        const conteo = datosFiltrados.reduce((acc, curr) => {
            const codigoRaw = curr.diagnostico_principal || curr.DIAGNOSTICO_PRINCIPAL || 'Sin Diagnóstico';
            const codigoLimpio = String(codigoRaw).trim().toUpperCase();
            const nombreDiagnostico = diccionarioCIE[codigoLimpio] || codigoLimpio;

            if (!acc[nombreDiagnostico]) acc[nombreDiagnostico] = { total: 0, pv: 0, sub: 0 };
            acc[nombreDiagnostico].total++;
            const pvVal = String(curr.primera_vez || curr.PRIMERA_VEZ || '0').trim().toLowerCase().replace('.0', '');
            if (pvVal === '1' || pvVal === 'primera vez') acc[nombreDiagnostico].pv++; else acc[nombreDiagnostico].sub++;
            return acc;
        }, {});
        const ordenados = Object.entries(conteo).sort((a, b) => b[1].total - a[1].total).slice(0, 20);
        return {
            labels: ordenados.map(item => item[0]),
            datasets: [{ label: 'Frecuencia', data: ordenados.map(item => item[1].total), backgroundColor: '#047857', borderRadius: 4 }],
            dataPV: ordenados.map(item => item[1].pv),
            dataSub: ordenados.map(item => item[1].sub)
        };
    }, [datosFiltrados, diccionarioCIE]);

    // 8. CONSULTORIOS
    const chartConsultorios = useMemo(() => {
        if (!datosFiltrados || datosFiltrados.length === 0) return { labels: [], datasets: [], dataPV: [], dataSub: [] };

        const conteo = datosFiltrados.reduce((acc, curr) => {
            const cons = (curr.consultorio || curr.CONSULTORIO || "SIN ESPECIFICAR").toString().trim().toUpperCase();
            if (!acc[cons]) acc[cons] = { total: 0, pv: 0, sub: 0 };
            
            acc[cons].total++;
            const pvVal = String(curr.primera_vez || curr.PRIMERA_VEZ || '0').trim().toLowerCase().replace('.0', '');
            if (pvVal === '1' || pvVal === 'primera vez') acc[cons].pv++; else acc[cons].sub++;
            return acc;
        }, {});

        const ordenados = Object.entries(conteo).sort((a, b) => b[1].total - a[1].total);
        return {
            labels: ordenados.map(item => item[0]),
            datasets: [{ label: 'Consultas', data: ordenados.map(item => item[1].total), backgroundColor: '#10b981', borderRadius: 4 }],
            dataPV: ordenados.map(item => item[1].pv),
            dataSub: ordenados.map(item => item[1].sub)
        };
    }, [datosFiltrados]);

    const chartOptionsVertical = { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true }, x: { grid: { display: false } } } };

    return (
        <div id="seccion-paramedicos-completa" className="w-full animate-in fade-in duration-500">
            {/* 1. ENCABEZADO */}
            <div className="mb-8">
                <h2 className="text-3xl font-black text-slate-800 flex items-center gap-3">
                    <span className="text-emerald-600 bg-emerald-100 p-2 rounded-xl"><Stethoscope size={28} /></span>
                    Productividad Paramédica
                </h2>
            </div>

            {/* 2. KPIs (Se mantienen igual) */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 border-t-4 border-t-emerald-500">
                    <div className="flex items-center gap-3 text-slate-500 mb-2"><Users size={18}/><h3 className="text-xs font-bold uppercase tracking-widest">Total Consultas</h3></div>
                    <p className="text-4xl font-black text-emerald-600">{kpis.total.toLocaleString()}</p>
                </div>
                <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
                    <div className="flex items-center gap-3 text-slate-500 mb-2"><CalendarCheck size={18}/><h3 className="text-xs font-bold uppercase tracking-widest">Citados</h3></div>
                    <p className="text-4xl font-black text-slate-700">{kpis.citados.toLocaleString()}</p>
                    <div className="flex flex-col mt-5 pt-4 border-t border-slate-100"><span className="text-xs font-bold uppercase tracking-widest text-slate-500 mb-1">Espontáneos</span><p className="text-4xl font-black text-emerald-600">{kpis.espontaneos.toLocaleString()}</p></div>
                </div>
                <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
                    <div className="flex items-center gap-3 text-slate-500 mb-2"><Clock size={18}/><h3 className="text-xs font-bold uppercase tracking-widest">Primera Vez</h3></div>
                    <p className="text-4xl font-black text-[#c2410c]">{kpis.primeraVez.toLocaleString()}</p>
                    <div className="flex flex-col mt-5 pt-4 border-t border-slate-100"><span className="text-xs font-bold uppercase tracking-widest text-slate-500 mb-1">Subsecuentes</span><p className="text-4xl font-black text-emerald-600">{kpis.subsecuentes.toLocaleString()}</p></div>
                </div>
            </div>

            {/* 3. BLOQUE: TURNOS Y ÁREAS (Grid) */}
            <div className={`grid grid-cols-1 ${mostrarTablas ? 'lg:grid-cols-2' : ''} gap-6 mb-6 items-start`}>
                
                {/* GRÁFICA 1: TURNOS */}
                <div id="graficoP_1" className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col h-full min-h-[300px]">
                    <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Consultas por Turno</h3>
                    <div className="relative flex-1 min-h-[220px]">
                        <Doughnut data={chartTurnos} options={{ maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }} />
                    </div>
                    {mostrarTablas && <TablaDatos titulo1="Turno" titulo2="Consultas" labels={chartTurnos.labels} data={chartTurnos.datasets[0].data} dataPV={chartTurnos.dataPV} dataSub={chartTurnos.dataSub} />}
                </div>

                {/* GRÁFICA 2: ÁREAS (Versión Grid) */}
                <div id="graficoP_2" className={`bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col h-full min-h-[300px] ${!mostrarTablas ? 'hidden lg:flex' : ''}`}>
                    <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Distribución por Área Paramédica</h3>
                    <div className="relative flex-1 min-h-[220px]">
                        <Bar data={chartEspecialidades} options={chartOptionsVertical} />
                    </div>
                    {mostrarTablas && <TablaDatos titulo1="Área" titulo2="Consultas" labels={chartEspecialidades.labels} data={chartEspecialidades.datasets[0].data} dataPV={chartEspecialidades.dataPV} dataSub={chartEspecialidades.dataSub} />}
                </div>
            </div>

            <div className="flex flex-col gap-6">
                
                {/* GRÁFICA 3: ESPECIALIDADES (Versión Ancha) */}
                {/* Usamos un div contenedor con el ID para que siempre sea detectable */}
                <div id="graficoP_3" className={mostrarTablas ? 'hidden' : 'block'}>
                    <div className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col mb-6">
                        <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Distribución por Área Paramédica (Detalle)</h3>
                        <div className="relative w-full overflow-x-auto custom-scrollbar pb-4" style={{ height: '400px' }}>
                            <div style={{ minWidth: anchoDinamico(chartEspecialidades.labels.length), height: '100%' }}>
                                <Bar data={chartEspecialidades} options={chartOptionsVertical} />
                            </div>
                        </div>
                    </div>
                </div>

                {/* GRÁFICA 4: PERSONAL */}
                <div id="graficoP_4" className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col">
                    <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Top 20 Productividad por Personal</h3>
                    <div className={`flex-1 grid grid-cols-1 ${mostrarTablas ? 'lg:grid-cols-5 gap-6' : 'lg:grid-cols-1'}`}>
                        <div className={`relative overflow-x-auto custom-scrollbar pb-4 ${mostrarTablas ? 'lg:col-span-3' : 'lg:col-span-1'}`} style={{ height: '400px' }}>
                            <div style={{ minWidth: anchoDinamico(chartMedicos.labels.length), height: '100%' }}>
                                <Bar data={chartMedicos} options={chartOptionsVertical} />
                            </div>
                        </div>
                        {mostrarTablas && (
                            <div className="lg:col-span-2 h-[400px] overflow-hidden">
                                <TablaDatos 
                                    titulo1="Licenciado" 
                                    tituloExtra="Especialidad" 
                                    dataExtra={chartMedicos.dataExtra} 
                                    titulo2="Consultas" 
                                    labels={chartMedicos.labels} 
                                    data={chartMedicos.datasets[0].data} 
                                    dataPV={chartMedicos.dataPV} 
                                    dataSub={chartMedicos.dataSub} 
                                />
                            </div>
                        )}
                    </div>
                </div>

                {/* GRÁFICA 5: DIAGNÓSTICOS */}
                <div id="graficoP_5" className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col">
                    <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide mb-4 border-b border-slate-100 pb-2">Top 20 Diagnósticos Principales</h3>
                    <div className={`flex-1 grid grid-cols-1 ${mostrarTablas ? 'lg:grid-cols-5 gap-6' : 'lg:grid-cols-1'}`}>
                        <div className={`relative overflow-x-auto custom-scrollbar pb-4 ${mostrarTablas ? 'lg:col-span-3' : 'lg:col-span-1'}`} style={{ height: '400px' }}>
                            <div style={{ minWidth: anchoDinamico(chartDiagnosticos.labels.length), height: '100%' }}>
                                <Bar data={chartDiagnosticos} options={chartOptionsVertical} />
                            </div>
                        </div>
                        {mostrarTablas && <div className="lg:col-span-2 h-[400px] overflow-hidden"><TablaDatos titulo1="Diagnóstico" titulo2="Frecuencia" labels={chartDiagnosticos.labels} data={chartDiagnosticos.datasets[0].data} dataPV={chartDiagnosticos.dataPV} dataSub={chartDiagnosticos.dataSub} total={false} /></div>}
                    </div>
                </div>

                {/* GRÁFICA 6: CONSULTORIOS */}
                <div id="graficoP_6" className="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col mt-6">
                    <div className="flex justify-between items-center mb-4 border-b border-slate-100 pb-2">
                        <div className="flex items-center gap-3">
                            <div className="bg-emerald-50 p-2 rounded-lg text-emerald-600"><MapPin size={20} /></div>
                            <h3 className="font-bold text-slate-700 text-sm uppercase tracking-wide">Productividad por Consultorio</h3>
                        </div>
                    </div>
                    <div className={`flex-1 grid grid-cols-1 ${mostrarTablas ? 'lg:grid-cols-5 gap-6' : 'lg:grid-cols-1'}`}>
                        <div className={`relative overflow-x-auto custom-scrollbar pb-4 ${mostrarTablas ? 'lg:col-span-3' : 'lg:col-span-1'}`} style={{ height: '400px' }}>
                            <div style={{ width: `max(100%, ${chartConsultorios.labels.length * 50}px)`, height: '100%' }}>
                                <Bar data={chartConsultorios} options={chartOptionsVertical} />
                            </div>
                        </div>
                        {mostrarTablas && (
                            <div className="lg:col-span-2 h-[400px] overflow-hidden">
                                <TablaDatos titulo1="Consultorio" titulo2="Consultas" labels={chartConsultorios.labels} data={chartConsultorios.datasets[0].data} dataPV={chartConsultorios.dataPV} dataSub={chartConsultorios.dataSub} />
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
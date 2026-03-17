import React from 'react';
import { ShieldAlert, Activity, Siren, AlertOctagon, LayoutDashboard, ChevronLeft, ChevronRight, TableProperties, Download, BriefcaseMedical } from 'lucide-react';

export default function Sidebar({ 
    moduloActual, setModuloActual, 
    pestanaActiva, setPestanaActiva, 
    conteos, 
    sidebarCollapsed, setSidebarCollapsed, 
    mostrarTablas, setMostrarTablas, 
    generarExcelReporte, descargandoExcel, hayDatos 
}) {

    return (
        <aside className="h-full flex flex-col text-white transition-all duration-300 relative">
            {/* Logo / Encabezado */}
            <div className="p-4 flex items-center justify-between border-b border-white/10 shrink-0 min-h-[64px]">
                {!sidebarCollapsed && <h1 className="font-black text-xl tracking-wider text-emerald-50">Vencer</h1>}
                <button onClick={() => setSidebarCollapsed(!sidebarCollapsed)} className="p-1 hover:bg-white/10 rounded-lg transition-colors">
                    {sidebarCollapsed ? <ChevronRight size={24} /> : <ChevronLeft size={24} />}
                </button>
            </div>

            <div className="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-6">
                
                {/* 1. SECCIÓN DE MÓDULOS PRINCIPALES (El Semáforo) */}

                {/* 2. SUB-MENÚ DE VENCER (Solo se muestra si el semáforo está en Vencer) */}
                {moduloActual === 'vencer' && (
                    <div className="space-y-1 animate-in fade-in slide-in-from-left-2 duration-300">
                        {!sidebarCollapsed && <p className="px-3 text-[10px] font-bold text-emerald-200/60 uppercase tracking-widest mb-2">Vistas Vencer</p>}
                        
                        <button onClick={() => setPestanaActiva('general')} className={`w-full flex items-center justify-between p-3 rounded-xl transition-all ${pestanaActiva === 'general' ? 'bg-white/20 font-bold border border-white/30' : 'hover:bg-white/10 text-emerald-100/80 hover:text-white'}`} title="Panorama General">
                            <div className="flex items-center"><LayoutDashboard size={18} className={sidebarCollapsed ? "mx-auto" : "mr-3"}/> {!sidebarCollapsed && <span>General</span>}</div>
                            {!sidebarCollapsed && <span className="text-[10px] bg-[#003B2D] px-2 py-0.5 rounded-full">{conteos.general}</span>}
                        </button>
                        
                        <button onClick={() => setPestanaActiva('adversos')} className={`w-full flex items-center justify-between p-3 rounded-xl transition-all ${pestanaActiva === 'adversos' ? 'bg-red-500/80 font-bold border border-red-400' : 'hover:bg-white/10 text-emerald-100/80 hover:text-white'}`} title="Eventos Adversos">
                            <div className="flex items-center"><AlertOctagon size={18} className={sidebarCollapsed ? "mx-auto" : "mr-3"}/> {!sidebarCollapsed && <span>Adversos</span>}</div>
                            {!sidebarCollapsed && <span className="text-[10px] bg-red-900/50 px-2 py-0.5 rounded-full">{conteos.adversos}</span>}
                        </button>

                        <button onClick={() => setPestanaActiva('cuasi')} className={`w-full flex items-center justify-between p-3 rounded-xl transition-all ${pestanaActiva === 'cuasi' ? 'bg-amber-500/80 font-bold border border-amber-400' : 'hover:bg-white/10 text-emerald-100/80 hover:text-white'}`} title="Cuasifallas">
                            <div className="flex items-center"><Activity size={18} className={sidebarCollapsed ? "mx-auto" : "mr-3"}/> {!sidebarCollapsed && <span>Cuasifallas</span>}</div>
                            {!sidebarCollapsed && <span className="text-[10px] bg-amber-900/50 px-2 py-0.5 rounded-full">{conteos.cuasi}</span>}
                        </button>

                        <button onClick={() => setPestanaActiva('centinela')} className={`w-full flex items-center justify-between p-3 rounded-xl transition-all ${pestanaActiva === 'centinela' ? 'bg-slate-800 font-bold border border-slate-600' : 'hover:bg-white/10 text-emerald-100/80 hover:text-white'}`} title="Eventos Centinela">
                            <div className="flex items-center"><Siren size={18} className={sidebarCollapsed ? "mx-auto" : "mr-3"}/> {!sidebarCollapsed && <span>Centinelas</span>}</div>
                            {!sidebarCollapsed && <span className="text-[10px] bg-slate-900 px-2 py-0.5 rounded-full">{conteos.centinela}</span>}
                        </button>
                    </div>
                )}
            </div>

            {/* Acciones Inferiores (Descargar Excel, etc. Solo en Vencer por ahora) */}
            {moduloActual === 'vencer' && (
                <div className="p-3 border-t border-white/10 bg-[#004a38] shrink-0 space-y-2">
                    <button onClick={() => setMostrarTablas(!mostrarTablas)} className="w-full flex items-center p-3 rounded-xl hover:bg-white/10 transition-colors text-emerald-100 text-sm" title={mostrarTablas ? "Ocultar Tablas" : "Mostrar Tablas"}>
                        <TableProperties size={18} className={sidebarCollapsed ? "mx-auto" : "mr-3"} />
                        {!sidebarCollapsed && <span>{mostrarTablas ? "Ocultar Tablas" : "Mostrar Tablas"}</span>}
                    </button>

                    <button onClick={generarExcelReporte} disabled={!hayDatos || descargandoExcel} className={`w-full flex items-center p-3 rounded-xl transition-colors text-sm font-bold ${(!hayDatos || descargandoExcel) ? 'opacity-50 cursor-not-allowed bg-white/5 text-emerald-200' : 'bg-emerald-500 hover:bg-emerald-400 text-white shadow-lg'}`} title="Descargar Reporte">
                        <Download size={18} className={`${sidebarCollapsed ? "mx-auto" : "mr-3"} ${descargandoExcel ? 'animate-bounce' : ''}`} />
                        {!sidebarCollapsed && <span>{descargandoExcel ? "Generando..." : "Descargar Excel"}</span>}
                    </button>
                </div>
            )}
        </aside>
    );
}
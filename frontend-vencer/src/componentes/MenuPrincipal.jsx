import React from 'react';
import { BarChart2, Database, BookOpen } from 'lucide-react';

export default function MenuPrincipal({ setVistaActiva, isAdmin, setMensaje }) {
    return (
        <div className="bg-slate-50 min-h-screen flex flex-col items-center pt-20 px-8 animate-in fade-in duration-700">
            {/* Encabezado */}
            <div className="text-center mb-12">
                <h1 className="text-4xl font-black text-[#822626] mb-2 tracking-tight">
                    Módulo de Productividad
                </h1>
                <p className="text-slate-500 font-bold uppercase tracking-widest text-sm">
                    UMAE 48 - Panel Central
                </p>
            </div>

            {/* Contenedor de Tarjetas */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl w-full animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-150">
                
                {/* BOTÓN 1: TABLEROS (Visible para todos) */}
                <button 
                    onClick={() => setVistaActiva('dashboard')} 
                    className="bg-white p-8 rounded-2xl shadow-sm border border-red-100 hover:shadow-md hover:border-red-300 transition-all flex flex-col items-center text-center group"
                >
                    <div className="bg-red-50 p-4 rounded-full mb-4 group-hover:bg-[#822626] group-hover:text-white text-[#822626] transition-colors">
                        <BarChart2 size={40} />
                    </div>
                    <h2 className="text-xl font-black text-slate-800 mb-2">Tableros de Indicadores</h2>
                    <p className="text-slate-500 text-sm font-medium">
                        Ingresa para visualizar las gráficas de Consulta Externa, Hospitalización, Cirugía y más.
                    </p>
                </button>

                {/* VISTAS DE ADMINISTRADOR (Condicionales) */}
                {isAdmin && (
                    <>
                        {/* BOTÓN 2: ACTUALIZAR BD */}
                        <button 
                            onClick={() => { setVistaActiva('subir'); setMensaje(''); }} 
                            className="bg-white p-8 rounded-2xl shadow-sm border border-red-100 hover:shadow-md hover:border-red-300 transition-all flex flex-col items-center text-center group"
                        >
                            <div className="bg-red-50 p-4 rounded-full mb-4 group-hover:bg-[#822626] group-hover:text-white text-[#822626] transition-colors">
                                <Database size={40} />
                            </div>
                            <h2 className="text-xl font-black text-slate-800 mb-2">Actualizar Base de Datos</h2>
                            <p className="text-slate-500 text-sm font-medium">
                                Sube el CSV de productividad para actualizar la información de las bases de datos.
                            </p>
                        </button>

                        {/* BOTÓN 3: GESTIÓN DE CATÁLOGOS */}
                        <button 
                            onClick={() => setVistaActiva('catalogos')} 
                            className="bg-white p-8 rounded-2xl shadow-sm border border-red-100 hover:shadow-md hover:border-red-300 transition-all flex flex-col items-center text-center group relative overflow-hidden"
                        >
                            <div className="absolute top-4 right-4 bg-green-100 text-green-700 text-[10px] font-black px-2 py-1 rounded-full uppercase tracking-widest">
                                Nuevo
                            </div>
                            <div className="bg-red-50 p-4 rounded-full mb-4 group-hover:bg-[#822626] group-hover:text-white text-[#822626] transition-colors">
                                <BookOpen size={40} />
                            </div>
                            <h2 className="text-xl font-black text-slate-800 mb-2">Gestión de Catálogos</h2>
                            <p className="text-slate-500 text-sm font-medium">
                                Administra y edita los registros de Especialidades, Médicos, Consultorios y Diagnósticos.
                            </p>
                        </button>
                    </>
                )}
            </div>

            {/* Pie de página simple para el menú */}
            <div className="mt-20 text-slate-400 text-xs font-bold uppercase tracking-[0.3em]">
                Sistema de Gestión Hospitalaria v2.0
            </div>
        </div>
    );
}
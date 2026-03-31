import React, { useMemo } from 'react';
import { Activity, Stethoscope } from 'lucide-react';

export default function TableroParamedicos({ datos }) {
    
    // ==========================================
    // MOTOR DE DATOS: FILTRO PARAMÉDICO
    // OJO AQUÍ: Si PHP guardó NOMBRES en vez de claves, 
    // tendrás que cambiar '6300' por 'NUTRICION' (o el nombre que sea)
    // ==========================================
    const datosParamedicos = useMemo(() => {
        if (!datos || datos.length === 0) return [];

        const clavesPermitidas = ['6300', '6600', '6900'];

        return datos.filter(fila => {
            const claveEsp = String(fila.especialidad || fila.ESPECIALIDAD || '').trim().toUpperCase();
            return clavesPermitidas.includes(claveEsp);
        });
    }, [datos]);

    const totalConsultas = datosParamedicos.length;

    // ==========================================
    // INTERFAZ DE USUARIO INCRUSTADA
    // ==========================================
    return (
        <div className="w-full animate-in fade-in duration-500">
            {/* Encabezado del Módulo */}
            <div className="mb-8">
                <h2 className="text-3xl font-black text-slate-800 flex items-center gap-3">
                    <span className="text-emerald-600 bg-emerald-100 p-2 rounded-xl">
                        <Stethoscope size={28} />
                    </span>
                    Productividad Paramédica
                </h2>
                <p className="text-slate-500 font-medium mt-2">
                    Análisis de indicadores exclusivos para áreas paramédicas y de apoyo.
                </p>
            </div>

            {/* Zona de Tarjetas (KPIs) */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                
                <div className="bg-white p-6 rounded-xl shadow-sm border border-slate-200 border-t-4 border-t-emerald-500 relative overflow-hidden group">
                    <div className="absolute -right-6 -top-6 text-emerald-50 opacity-50 group-hover:scale-110 transition-transform duration-500">
                        <Activity size={120} />
                    </div>
                    
                    <div className="relative z-10">
                        <div className="flex items-center gap-3 text-slate-500 mb-2">
                            <h3 className="text-xs font-bold uppercase tracking-widest">Total de Consultas</h3>
                        </div>
                        <p className="text-4xl font-black text-slate-800">
                            {totalConsultas.toLocaleString()}
                        </p>
                        <p className="text-emerald-600 text-xs font-bold mt-3 bg-emerald-50 inline-block px-2 py-1 rounded">
                            Filtro: 6300, 6600, 6900
                        </p>
                    </div>
                </div>

                <div className="bg-slate-50/50 p-6 rounded-xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-center min-h-[160px]">
                    <p className="text-slate-400 font-bold">Espacio para gráfica</p>
                </div>
                
                <div className="bg-slate-50/50 p-6 rounded-xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-center min-h-[160px]">
                    <p className="text-slate-400 font-bold">Espacio para gráfica</p>
                </div>

            </div>
        </div>
    );
}
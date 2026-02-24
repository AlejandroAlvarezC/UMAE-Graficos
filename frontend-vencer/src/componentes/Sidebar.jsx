import React from 'react';
import { LayoutDashboard, AlertOctagon, ShieldAlert, Siren, LogOut, Menu, Table, BarChart } from 'lucide-react';

const Sidebar = ({ pestanaActiva, setPestanaActiva, conteos, sidebarCollapsed, setSidebarCollapsed, mostrarTablas, setMostrarTablas }) => {
  const menuOptions = [
    { id: 'general', label: 'General', icon: LayoutDashboard, count: conteos?.general },
    { id: 'adversos', label: 'Adversos', icon: AlertOctagon, count: conteos?.adversos },
    { id: 'cuasi', label: 'Cuasifallas', icon: ShieldAlert, count: conteos?.cuasi },
    { id: 'centinela', label: 'Centinelas', icon: Siren, count: conteos?.centinela }
  ];

  return (
    <aside className="h-full w-full flex flex-col bg-[#005C46] text-white overflow-hidden">
      
      {/* Título y Botón de Colapsar */}
      <div className={`h-16 flex items-center px-6 border-b border-[#004A38] shrink-0 ${sidebarCollapsed ? 'justify-center px-0' : 'justify-between'}`}>
        {!sidebarCollapsed && <span className="font-black text-2xl tracking-wider">VENCER</span>}
        <button 
          onClick={() => setSidebarCollapsed(!sidebarCollapsed)} 
          className="text-emerald-100 hover:text-white hover:bg-[#004A38] p-2 rounded-lg transition-colors outline-none"
          title={sidebarCollapsed ? "Expandir menú" : "Contraer menú"}
        >
          <Menu size={24} />
        </button>
      </div>

      {/* Opciones del menú */}
      <div className="flex-1 overflow-y-auto py-4">
        <ul className="list-none p-0 m-0 w-full flex flex-col">
          {menuOptions.map((opcion) => {
            const isActivo = pestanaActiva === opcion.id;
            return (
              <li key={opcion.id} className="w-full p-0 m-0 block">
                <button 
                  onClick={() => setPestanaActiva(opcion.id)}
                  style={{ borderRadius: 0 }}
                  className={`w-full flex items-center px-6 py-4 outline-none border-0 transition-colors ${
                    sidebarCollapsed ? 'justify-center' : 'justify-between'
                  } ${
                    isActivo 
                      ? 'bg-[#003B2D] border-l-4 border-white text-white' 
                      : 'bg-transparent border-l-4 border-transparent text-emerald-100 hover:bg-[#003B2D] hover:text-white'
                  }`}
                  title={sidebarCollapsed ? opcion.label : ""}
                >
                  <div className={`flex items-center gap-3 ${sidebarCollapsed ? 'justify-center' : ''}`}>
                    <opcion.icon size={22} className="shrink-0" />
                    {!sidebarCollapsed && <span className="font-bold text-base whitespace-nowrap">{opcion.label}</span>}
                  </div>
                  
                  {!sidebarCollapsed && opcion.count !== undefined && (
                    <span className={`text-xs px-2.5 py-1 rounded-full font-bold ${isActivo ? 'bg-white text-[#003B2D]' : 'bg-[#007A5E] text-white'}`}>
                      {opcion.count}
                    </span>
                  )}
                </button>
              </li>
            );
          })}
        </ul>
      </div>

      {/* Pie del menú: Botón de Tablas y Cerrar Sesión */}
      <div className="border-t border-[#004A38] shrink-0 w-full flex flex-col">
        {/* NUEVO: Botón para Ocultar/Mostrar Tablas */}
        <button 
          onClick={() => setMostrarTablas(!mostrarTablas)}
          style={{ borderRadius: 0 }}
          className={`w-full flex items-center px-6 py-4 bg-transparent hover:bg-[#003B2D] text-emerald-100 hover:text-white transition-colors outline-none border-0 ${sidebarCollapsed ? 'justify-center' : 'gap-3'}`}
          title={mostrarTablas ? "Ocultar Tablas" : "Mostrar Tablas"}
        >
          {mostrarTablas ? <BarChart size={22} className="shrink-0"/> : <Table size={22} className="shrink-0 text-amber-300"/>}
          {!sidebarCollapsed && <span className="text-sm font-bold whitespace-nowrap">{mostrarTablas ? "Solo ver gráficos" : "Mostrar tablas"}</span>}
        </button>

        <button 
          style={{ borderRadius: 0 }}
          className={`w-full flex items-center px-6 py-5 bg-[#004A38] hover:bg-red-700 text-emerald-100 hover:text-white transition-colors outline-none border-0 ${sidebarCollapsed ? 'justify-center' : 'gap-3'}`}
          title="Cerrar sesión"
        >
          <LogOut size={22} className="shrink-0" />
          {!sidebarCollapsed && <span className="text-base font-bold whitespace-nowrap">Cerrar sesión</span>}
        </button>
      </div>

    </aside>
  );
};

export default Sidebar;
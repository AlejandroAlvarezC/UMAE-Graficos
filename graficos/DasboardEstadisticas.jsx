import React, { useState } from 'react';
// Asumiendo que usas react-router-dom para llegar aquí
// import { useNavigate } from 'react-router-dom'; 

import SidebarNavegacion from './SidebarNavegacion';
import TopFiltros from './TopFiltros';
import ContenedorGraficos from './ContenedorGraficos';
import ContenedorTablas from './ContenedorTablas';

const DashboardEstadisticas = () => {
  // const navigate = useNavigate();

  // Estado para la pestaña activa en el Sidebar
  const [activeTab, setActiveTab] = useState('general');

  // Estado global de los filtros (Centro Arriba)
  const [filtros, setFiltros] = useState({
    anio: 'todos',
    mesInicio: '1',
    mesFin: '12',
    servicio: ''
  });

  // Estado para el panel derecho de tablas (Cerrado por defecto)
  const [isTablePanelOpen, setIsTablePanelOpen] = useState(false);

  return (
    <div className="d-flex" style={{ height: '100vh', backgroundColor: '#f8f9fa' }}>
      
      {/* 1. SIDEBAR IZQUIERDO (Navegación) */}
      <div className="bg-white border-end shadow-sm" style={{ width: '250px', zIndex: 10 }}>
        <SidebarNavegacion activeTab={activeTab} setActiveTab={setActiveTab} />
      </div>

      {/* CONTENEDOR PRINCIPAL (Centro y Derecha) */}
      <div className="d-flex flex-column flex-grow-1 overflow-hidden">
        
        {/* 2. FILTROS (Centro Arriba) */}
        <div className="bg-white border-bottom shadow-sm p-3 z-index-1">
          <TopFiltros 
            filtros={filtros} 
            setFiltros={setFiltros} 
            toggleTablas={() => setIsTablePanelOpen(!isTablePanelOpen)}
            isTablePanelOpen={isTablePanelOpen}
          />
        </div>

        {/* ÁREA DE DATOS (Gráficos y Tablas) */}
        <div className="d-flex flex-grow-1 overflow-hidden">
          
          {/* 3. GRÁFICOS (Centro) */}
          <div className="flex-grow-1 overflow-auto p-4">
            <ContenedorGraficos activeTab={activeTab} filtros={filtros} />
          </div>

          {/* 4. TABLAS (Lado Derecho - Colapsable) */}
          {/* Renderizado condicional: cerrado por defecto */}
          {isTablePanelOpen && (
            <div 
              className="bg-white border-start shadow-sm overflow-auto p-4 transition-all" 
              style={{ width: '400px', minWidth: '400px' }}
            >
              <div className="d-flex justify-content-between align-items-center mb-4">
                <h5 className="fw-bold m-0">Datos Tabulares</h5>
                <button 
                  className="btn-close" 
                  onClick={() => setIsTablePanelOpen(false)}
                ></button>
              </div>
              <ContenedorTablas activeTab={activeTab} filtros={filtros} />
            </div>
          )}
          
        </div>
      </div>
    </div>
  );
};

export default DashboardEstadisticas;
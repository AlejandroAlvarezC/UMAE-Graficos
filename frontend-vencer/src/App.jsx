import React from 'react';
// 1. Importamos tu nuevo componente 
import DashboardVencer from './componentes/dashboardVencer'; 
import AdministradorUsuarios from './componentes/AdministradorUsuarios';
// Importa aquí tu Dashboard de Productividad si lo tienes aparte

function App() {
  const queryParams = new URLSearchParams(window.location.search);
  const modulo = queryParams.get("modulo");

  // 2. El "Semáforo" de rutas
  if (modulo === "usuarios") {
    return <AdministradorUsuarios />;
  }
  
  // AQUÍ ESTÁ EL ARREGLO PARA VENCER:
  if (modulo === "vencer") {
    return <DashboardVencer />;
  }

  // 3. Ruta por defecto (Si no hay nada, o si es 'productividad')
  // Asegúrate de que no haya un "else" que siempre mande a productividad
  return <div className="p-10 text-center">Selecciona un módulo válido</div>;
}

export default App;
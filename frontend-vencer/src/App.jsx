import React from 'react';
// Importamos los componentes desde la carpeta componentes
import DashboardVencer from './componentes/dashboardVencer'; 
import DashboardProductividad from './componentes/dashboardProductividad';
import AdministradorUsuarios from './componentes/AdministradorUsuarios';
import ModuloIndicadores from './componentes/ModuloIndicadores';
import TableroCirugias from './componentes/TableroCirugias'; //

function App() {
  const queryParams = new URLSearchParams(window.location.search);
  const rol = queryParams.get("rol");
  const esAdmin = (rol === 'admin');
  const modulo = queryParams.get("modulo");



  // 1. Ruta para el Control de Usuarios
  if (modulo === "usuarios") {
    return <AdministradorUsuarios />;
  }
  
  // 2. Ruta para el módulo Vencer
  if (modulo === "vencer") {
    return <DashboardVencer />;
  }

  // 3. Ruta para el módulo de Productividad
    if (modulo === "productividad") {
    return <DashboardProductividad isAdmin={esAdmin} />;
  }

  // Indicadores
  if (modulo === 'indicadores') {
        return <ModuloIndicadores isAdmin={rol === 'admin'} />;
    }

 // RUTA para el módulo de Cirugías
  if (modulo === "cirugias") {
    return <TableroCirugias datos={datosCirugiasPrueba} />;
  }

  // 4. Ruta por defecto: 
  // Si no hay módulo en la URL o no coincide, cargamos Productividad por defecto
  // para que la página nunca se vea vacía.
  return <DashboardProductividad isAdmin={esAdmin}/>;
}

export default App;
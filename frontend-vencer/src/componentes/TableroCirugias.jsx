import React, { useMemo, useState, useEffect } from "react";
import Papa from "papaparse";
import {
  Scissors,
  Users,
  CalendarCheck,
  Activity,
  Hourglass,
  Stethoscope,
  ChartNoAxesColumn,
  DoorOpen,
  Timer,
  XCircle,
  FileQuestion,
  ClipboardCheck,
  FileText,
} from "lucide-react";
import { Doughnut, Bar } from "react-chartjs-2";
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
} from "chart.js";

ChartJS.register(
  ArcElement,
  Tooltip,
  Legend,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
);

const COLORS = {
  primary: "#991b1b",
  success: "#0f766e",
  info: "#2563eb",
  female: "#ec4899",
  danger: "#dc2626",
  warning: "#d97706",
  dark: "#0f172a",
  gray: "#64748b",
  male: "#3b82f6",
};

const AREA_FILTRO_TODAS = "TODAS";

const AREA_FILTER_OPTIONS = [
  { value: AREA_FILTRO_TODAS, label: "Todas las areas" },
  { value: "OBSTETRICIA", label: "Obstetricia" },
  { value: "CIRUGIA_PEDIATRICA", label: "Cirugia Pediatrica" },
  { value: "GINECOLOGIA", label: "Ginecologia" },
  { value: "ANESTESIOLOGIA", label: "Anestesiologia" },
  { value: "PEDIATRICA", label: "Pediatrica" },
];

const ESPECIALIDADES_POR_AREA = {
  OBSTETRICIA: ["OBSTETRICIA", "MATERNO FETAL", "MEDICINA MATERNO FETAL"],
  CIRUGIA_PEDIATRICA: [
    "CIRUGIA PEDIATRICA",
    "NEUROCIRUGIA",
    "NEUROCIRUGIA PEDIATRICA",
    "TRAUMATOLOGIA Y ORTOPEDIA",
    "UROLOGIA PEDIATRICA",
    "CIRUGIA CARDIO TORACICA",
    "CIRUGIA CARDIOTORACICA",
    "CIRUGIA PLASTICA Y RECONSTRUCTIVA",
    "CIRUGIA PLASTICA RECONSTRUCTIVA",
  ],
  GINECOLOGIA: [
    "GINECOLOGIA",
    "GINECOLOGIA ONCOLOGICA",
    "ONCOLOGIA GINECOLOGICA",
    "ONCOLOGIA QUIRURGICA",
    "BIOLOGIA DE LA REPRODUCCION",
    "BIOLOGIA REPRODUCCION",
    "BIOLOGIA DE LA REPRODUCCION HUMANA",
    "UROLOGIA GINECOLOGICA",
  ],
  ANESTESIOLOGIA: ["ANESTESIOLOGIA"],
  PEDIATRICA: [
    "PEDIATRICA",
    "PEDIATRIA",
    "OFTALMOLOGIA",
    "HEMATOLOGIA PEDIATRICA",
    "GASTROENTEROLOGIA PEDIATRICA",
  ],
};

const PALETA_CIE10 = [
  "#60a5fa",
  "#38bdf8",
  "#2dd4bf",
  "#93c5fd",
  "#67e8f9",
  "#5eead4",
  "#7dd3fc",
  "#a5b4fc",
  "#99f6e4",
  "#bfdbfe",
];

// ==================== UTILIDADES DE TEXTO ====================

const repararTextoRoto = (valor) => {
  if (valor === null || valor === undefined) return "";

  let texto = String(valor);

  for (let intento = 0; intento < 2; intento++) {
    if (!/[\u00c3\u00c2\u00e2]/.test(texto)) break;

    try {
      texto = decodeURIComponent(escape(texto));
    } catch {
      break;
    }
  }

  return texto
    .replace(/\u00ef\u00bf\u00bd/g, "")
    .replace(/\u00c3\u00af\u00c2\u00bf\u00c2\u00bd/g, "")
    .replace(/\u00fffd/g, "")
    .replace(/\u00c3\u201a/g, "")
    .replace(/\u00c2\u00bd/g, "")
    .replace(/\s+/g, " ")
    .trim();
};

const quitarAcentos = (valor) => {
  if (!valor) return "";

  return String(valor)
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "");
};

const ponerAcentosVisuales = (valor) => {
  if (!valor) return "";

  return valor
    .replace(/PEDITRICA/g, "PEDIATRICA")
    .replace(/PEDIATRCA/g, "PEDIATRICA")
    .replace(/PEDITRICOS/g, "PEDIATRICOS")
    .replace(/HEMATOLOGA/g, "HEMATOLOGIA")
    .replace(/CIRUGA/g, "CIRUGIA")
    .replace(/GINECOLOGA/g, "GINECOLOGIA")
    .replace(/GINECOLGICA/g, "GINECOLOGICA")
    .replace(/ONCOLOGA/g, "ONCOLOGIA")
    .replace(/ONCOLGICA/g, "ONCOLOGICA")
    .replace(/ANESTESIOLOGA/g, "ANESTESIOLOGIA")
    .replace(/CARDIOLOGA/g, "CARDIOLOGIA")
    .replace(/NEUMOLOGA/g, "NEUMOLOGIA")
    .replace(/TRAUMATOLOGA/g, "TRAUMATOLOGIA")
    .replace(/UROLOGA/g, "UROLOGIA")
    .replace(/OFTALMOLOGA/g, "OFTALMOLOGIA")
    .replace(/GASTROENTEROLOGA/g, "GASTROENTEROLOGIA")
    .replace(/CARDIOTORCICA/g, "CARDIOTORACICA")
    .replace(/PLSTICA/g, "PLASTICA")
    .replace(/QUIRRGICO/g, "QUIRURGICO")
    .replace(/QUIRRGICA/g, "QUIRURGICA")
    .replace(/GUIRURGICA/g, "QUIRURGICA")
    .replace(/CANCELACIN/g, "CANCELACION")
    .replace(/SUSPENSIN/g, "SUSPENSION")
    .replace(/PROGRAMACIN/g, "PROGRAMACION")
    .replace(/REALIZACIN/g, "REALIZACION")
    .replace(/INTERVENCIN/g, "INTERVENCION")
    .replace(/CONFIRMACIN/g, "CONFIRMACION")
    .replace(/PREPARACIN/g, "PREPARACION")
    .replace(/INDICACIN/g, "INDICACION")
    .replace(/DEFUNCIN/g, "DEFUNCION")
    .replace(/ADSCRIPCIN/g, "ADSCRIPCION")
    .replace(/DIAGNSTICO/g, "DIAGNOSTICO")
    .replace(/ANESTSICA/g, "ANESTESICA")
    .replace(/ANESTSICO/g, "ANESTESICO")
    .replace(/TERAPOUTICA/g, "TERAPEUTICA")
    .replace(/TERAPOTICA/g, "TERAPEUTICA")
    .replace(/TERAPUTICA/g, "TERAPEUTICA")
    .replace(/MODICA/g, "MEDICA")
    .replace(/MDICA/g, "MEDICA")
    .replace(/PATOLOGA/g, "PATOLOGIA")
    .replace(/\bPRESENT\b/g, "PRESENTO")
    .replace(/\bOPER\b/g, "OPERO")
    .replace(/CIRUGIA/g, "CIRUGÍA")
    .replace(/GINECOLOGIA/g, "GINECOLOGÍA")
    .replace(/PEDIATRICA/g, "PEDIÁTRICA")
    .replace(/PEDIATRICO/g, "PEDIÁTRICO")
    .replace(/ONCOLOGICA/g, "ONCOLÓGICA")
    .replace(/ONCOLOGIA/g, "ONCOLOGÍA")
    .replace(/TRAUMATOLOGIA/g, "TRAUMATOLOGÍA")
    .replace(/HEMATOLOGIA/g, "HEMATOLOGÍA")
    .replace(/PLASTICA/g, "PLÁSTICA")
    .replace(/UROLOGIA/g, "UROLOGÍA")
    .replace(/OFTALMOLOGIA/g, "OFTALMOLOGÍA")
    .replace(/GASTROENTEROLOGIA/g, "GASTROENTEROLOGÍA")
    .replace(/INDICACION/g, "INDICACIÓN")
    .replace(/TERAPEUTICA/g, "TERAPÉUTICA")
    .replace(/MEDICA/g, "MÉDICA")
    .replace(/QUIRURGICO/g, "QUIRÚRGICO")
    .replace(/QUIRURGICA/g, "QUIRÚRGICA")
    .replace(/PATOLOGIA/g, "PATOLOGÍA")
    .replace(/OPERO/g, "OPERÓ")
    .replace(/PRESENTO/g, "PRESENTÓ")
    .replace(/PREPARACION/g, "PREPARACIÓN")
    .replace(/CANCELACION/g, "CANCELACIÓN")
    .replace(/SUSPENSION/g, "SUSPENSIÓN")
    .replace(/DEFUNCION/g, "DEFUNCIÓN")
    .replace(/ADSCRIPCION/g, "ADSCRIPCIÓN")
    .replace(/PROGRAMACION/g, "PROGRAMACIÓN")
    .replace(/REALIZACION/g, "REALIZACIÓN")
    .replace(/INTERVENCION/g, "INTERVENCIÓN")
    .replace(/CONFIRMACION/g, "CONFIRMACIÓN")
    .replace(/DIAGNOSTICO/g, "DIAGNÓSTICO")
    .replace(/TERMINO/g, "TÉRMINO")
    .replace(/ANESTESICA/g, "ANESTÉSICA")
    .replace(/ANESTESICO/g, "ANESTÉSICO");
};

const normalizarValor = (valor) => {
  if (!valor) return "";

  const limpio = repararTextoRoto(valor)
    .toString()
    .trim()
    .toUpperCase()
    .replace(/\s+/g, " ");

  return ponerAcentosVisuales(limpio);
};

const normalizarParaComparar = (valor) => {
  return quitarAcentos(normalizarValor(valor));
};

const obtenerAreaEspecialidad = (especialidad) => {
  const especialidadNormalizada = normalizarParaComparar(especialidad);

  for (const [area, especialidades] of Object.entries(
    ESPECIALIDADES_POR_AREA,
  )) {
    const pertenece = especialidades.some((alias) => {
      return (
        especialidadNormalizada === alias ||
        (alias.length > 10 && especialidadNormalizada.includes(alias))
      );
    });

    if (pertenece) {
      return area;
    }
  }

  return null;
};

const obtenerEstadoConcertada = (valor) => {
  const limpio = quitarAcentos(repararTextoRoto(valor))
    .toUpperCase()
    .replace(/[^A-Z0-9]/g, "")
    .trim();

  if (!limpio) return "SIN DATO";

  if (
    limpio === "NO" ||
    limpio === "N" ||
    limpio === "0" ||
    limpio === "FALSE" ||
    limpio === "FALSO" ||
    limpio === "NOCONCERTADA" ||
    limpio.includes("NOCONCERTADA")
  ) {
    return "NO";
  }

  if (
    limpio === "SI" ||
    limpio === "S" ||
    limpio === "1" ||
    limpio === "TRUE" ||
    limpio === "VERDADERO" ||
    limpio === "CONCERTADA" ||
    limpio.includes("SICONCERTADA") ||
    limpio.includes("CONCERTADA")
  ) {
    return "SÍ";
  }

  return "SIN DATO";
};

const normalizarColumna = (valor) => {
  return quitarAcentos(
    ponerAcentosVisuales(repararTextoRoto(valor).toUpperCase()),
  )
    .toLowerCase()
    .replace(/tormino/g, "termino")
    .replace(/tirmino/g, "termino")
    .replace(/trmino/g, "termino")
    .replace(/termin/g, "termino")
    .replace(/programi/g, "programo")
    .replace(/programacin/g, "programacion")
    .replace(/programaciin/g, "programacion")
    .replace(/realizacin/g, "realizacion")
    .replace(/realizi/g, "realizo")
    .replace(/diagnstico/g, "diagnostico")
    .replace(/diagnistico/g, "diagnostico")
    .replace(/quirrgica/g, "quirurgica")
    .replace(/quirirgica/g, "quirurgica")
    .replace(/intervencin/g, "intervencion")
    .replace(/intervenciin/g, "intervencion")
    .replace(/confirmacin/g, "confirmacion")
    .replace(/confirmaciin/g, "confirmacion")
    .replace(/suspensin/g, "suspension")
    .replace(/suspensiin/g, "suspension")
    .replace(/cancelacin/g, "cancelacion")
    .replace(/cancelaciin/g, "cancelacion")
    .replace(/soliciti/g, "solicito")
    .replace(/ltimo/g, "ultimo")
    .replace(/mdico/g, "medico")
    .replace(/nico/g, "unico")
    .replace(/[^a-z0-9]/g, "")
    .trim();
};

const convertirNumero = (valor) => {
  if (valor === null || valor === undefined || valor === "") return null;

  const limpio = String(valor)
    .replace(",", ".")
    .replace(/[^\d.-]/g, "")
    .trim();

  if (!limpio) return null;

  const n = Number(limpio);

  return Number.isFinite(n) ? n : null;
};

const incrementarConteo = (conteo, valor, fallback = "SIN DATO") => {
  const llave = valor || fallback;
  conteo[llave] = (conteo[llave] || 0) + 1;
};

const obtenerTopOrdenado = (conteo, limite, fallback) => {
  const ordenados = Object.entries(conteo)
    .sort((a, b) => b[1] - a[1])
    .slice(0, limite);

  return ordenados.length ? ordenados : [[fallback, 0]];
};

const crearChartTop = (ordenados, color) => ({
  labels: ordenados.map((i) => i[0]),
  datasets: [
    {
      data: ordenados.map((i) => i[1]),
      backgroundColor: color,
      borderRadius: 8,
      normalized: true,
    },
  ],
});

// ==================== FECHAS ====================

const MESES_ES = {
  enero: 0,
  febrero: 1,
  marzo: 2,
  abril: 3,
  mayo: 4,
  junio: 5,
  julio: 6,
  agosto: 7,
  septiembre: 8,
  setiembre: 8,
  octubre: 9,
  noviembre: 10,
  diciembre: 11,
};

const convertirFecha = (valor) => {
  if (valor === null || valor === undefined || valor === "") return null;

  let textoOriginal = repararTextoRoto(String(valor))
    .trim()
    .replace(/\s+/g, " ");

  if (!textoOriginal) return null;

  const textoComparar = quitarAcentos(textoOriginal).toUpperCase().trim();

  if (
    textoComparar === "SIN FECHA" ||
    textoComparar === "S/F" ||
    textoComparar === "SF" ||
    textoComparar === "N/A" ||
    textoComparar === "NA" ||
    textoComparar === "NULL" ||
    textoComparar === "0" ||
    textoComparar === "-"
  ) {
    return null;
  }

  const texto = textoOriginal.toLowerCase().replace(/\s+/g, " ").trim();

  const posibleSerial = Number(texto.replace(",", "."));

  if (
    Number.isFinite(posibleSerial) &&
    posibleSerial > 20000 &&
    posibleSerial < 80000
  ) {
    const dias = Math.floor(posibleSerial);
    const fechaExcel = new Date(Math.round((dias - 25569) * 86400 * 1000));

    return new Date(
      fechaExcel.getUTCFullYear(),
      fechaExcel.getUTCMonth(),
      fechaExcel.getUTCDate(),
    );
  }

  const matchDMY = texto.match(
    /^(\d{1,2})[/\-.](\d{1,2})[/\-.](\d{2,4})(?:\s+.*)?$/,
  );

  if (matchDMY) {
    const dia = Number(matchDMY[1]);
    const mes = Number(matchDMY[2]) - 1;
    let anio = Number(matchDMY[3]);

    if (anio < 100) anio += 2000;

    const fecha = new Date(anio, mes, dia);

    if (
      !isNaN(fecha.getTime()) &&
      fecha.getFullYear() === anio &&
      fecha.getMonth() === mes &&
      fecha.getDate() === dia
    ) {
      return fecha;
    }
  }

  const matchYMD = texto.match(
    /^(\d{4})[/\-.](\d{1,2})[/\-.](\d{1,2})(?:\s+.*)?$/,
  );

  if (matchYMD) {
    const anio = Number(matchYMD[1]);
    const mes = Number(matchYMD[2]) - 1;
    const dia = Number(matchYMD[3]);

    const fecha = new Date(anio, mes, dia);

    if (
      !isNaN(fecha.getTime()) &&
      fecha.getFullYear() === anio &&
      fecha.getMonth() === mes &&
      fecha.getDate() === dia
    ) {
      return fecha;
    }
  }

  const matchTextoCompleto = texto.match(
    /(\d{1,2})\s+de\s+([a-z??????]+)\s+de\s+(\d{2,4})/i,
  );

  if (matchTextoCompleto) {
    const dia = Number(matchTextoCompleto[1]);
    const mes = MESES_ES[quitarAcentos(matchTextoCompleto[2])];
    let anio = Number(matchTextoCompleto[3]);

    if (anio < 100) anio += 2000;

    if (mes !== undefined) {
      const fecha = new Date(anio, mes, dia);

      if (
        !isNaN(fecha.getTime()) &&
        fecha.getFullYear() === anio &&
        fecha.getMonth() === mes &&
        fecha.getDate() === dia
      ) {
        return fecha;
      }
    }
  }

  const matchTextoSinAnio = texto.match(/(\d{1,2})\s+de\s+([a-z??????]+)/i);

  if (matchTextoSinAnio) {
    const hoy = new Date();
    const dia = Number(matchTextoSinAnio[1]);
    const mes = MESES_ES[quitarAcentos(matchTextoSinAnio[2])];

    if (mes !== undefined) {
      let anio = hoy.getFullYear();

      if (mes > hoy.getMonth()) {
        anio -= 1;
      }

      const fecha = new Date(anio, mes, dia);

      if (
        !isNaN(fecha.getTime()) &&
        fecha.getFullYear() === anio &&
        fecha.getMonth() === mes &&
        fecha.getDate() === dia
      ) {
        return fecha;
      }
    }
  }

  const fechaJS = new Date(textoOriginal);

  if (!isNaN(fechaJS.getTime())) {
    return new Date(
      fechaJS.getFullYear(),
      fechaJS.getMonth(),
      fechaJS.getDate(),
    );
  }

  return null;
};

const calcularDiasNaturalesDesde = (fecha) => {
  if (!fecha) return null;

  const inicio = new Date(
    fecha.getFullYear(),
    fecha.getMonth(),
    fecha.getDate(),
  );
  const hoy = new Date();
  const fin = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());

  const diferencia = fin - inicio;

  if (diferencia < 0) return 0;

  return Math.floor(diferencia / 86400000);
};

const esFechaPosteriorAHoy = (fecha) => {
  if (!fecha) return false;

  const fechaLimpia = new Date(
    fecha.getFullYear(),
    fecha.getMonth(),
    fecha.getDate(),
  );

  const hoy = new Date();
  const hoyLimpio = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());

  return fechaLimpia > hoyLimpio;
};

const formatearFecha = (fecha) => {
  if (!fecha) return "SIN FECHA";

  return fecha.toLocaleDateString("es-MX", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
};

// ==================== HORAS ====================

const convertirHoraAMinutos = (valor) => {
  if (valor === null || valor === undefined || valor === "") return null;

  const textoOriginal = String(valor).trim();

  if (!textoOriginal) return null;

  const numero = Number(textoOriginal.replace(",", "."));

  if (Number.isFinite(numero) && numero >= 0 && numero < 1) {
    return Math.round(numero * 24 * 60);
  }

  const fecha = new Date(textoOriginal);

  if (!isNaN(fecha.getTime())) {
    return fecha.getHours() * 60 + fecha.getMinutes();
  }

  const match = textoOriginal.match(/(\d{1,2}):(\d{2})(?::(\d{2}))?/);

  if (!match) return null;

  const horas = Number(match[1]);
  const minutos = Number(match[2]);

  if (!Number.isFinite(horas) || !Number.isFinite(minutos)) return null;
  if (horas < 0 || horas > 23 || minutos < 0 || minutos > 59) return null;

  return horas * 60 + minutos;
};

const calcularMinutos = (inicio, fin) => {
  const minInicio = convertirHoraAMinutos(inicio);
  const minFin = convertirHoraAMinutos(fin);

  if (minInicio === null || minFin === null) return 0;

  let diferencia = minFin - minInicio;

  if (diferencia < 0) {
    diferencia += 24 * 60;
  }

  if (diferencia <= 0 || diferencia > 24 * 60) return 0;

  return diferencia;
};

const formatearMinutos = (minutos) => {
  if (!minutos || minutos <= 0) return "0 min";
  if (minutos < 60) return `${Math.round(minutos)} min`;

  const h = Math.floor(minutos / 60);
  const m = Math.round(minutos % 60);

  return `${h}h ${m}min`;
};

const envolverLabel = (label, maxPorLinea = 34) => {
  if (!label) return "";

  const texto = String(label);
  const palabras = texto.split(" ");
  const lineas = [];
  let lineaActual = "";

  palabras.forEach((palabra) => {
    const posibleLinea = lineaActual ? `${lineaActual} ${palabra}` : palabra;

    if (posibleLinea.length > maxPorLinea) {
      if (lineaActual) {
        lineas.push(lineaActual);
      }

      lineaActual = palabra;
    } else {
      lineaActual = posibleLinea;
    }
  });

  if (lineaActual) {
    lineas.push(lineaActual);
  }

  return lineas;
};
// ==================== PROCESAMIENTO OPTIMIZADO ====================

const crearIndiceColumnas = (encabezados) => {
  const indice = {};

  encabezados.forEach((col) => {
    const limpia = normalizarColumna(col);

    if (limpia) {
      indice[limpia] = col;
    }
  });

  return indice;
};

const obtenerValorOptimizado = (fila, candidatos, indice) => {
  for (const candidato of candidatos) {
    const limpio = normalizarColumna(candidato);
    const real = indice[limpio];

    if (
      real &&
      fila[real] !== undefined &&
      fila[real] !== null &&
      fila[real] !== ""
    ) {
      return fila[real];
    }
  }

  for (const candidato of candidatos) {
    const limpio = normalizarColumna(candidato);
    const llaveEncontrada = Object.keys(indice).find((k) => k.includes(limpio));
    const real = indice[llaveEncontrada];

    if (
      real &&
      fila[real] !== undefined &&
      fila[real] !== null &&
      fila[real] !== ""
    ) {
      return fila[real];
    }
  }

  return "";
};

const obtenerValorPorColumnasQueIncluyen = (
  fila,
  indice,
  incluir = [],
  excluir = [],
) => {
  const incluirNorm = incluir.map((x) => normalizarColumna(x));
  const excluirNorm = excluir.map((x) => normalizarColumna(x));

  const llave = Object.keys(indice).find((k) => {
    const contieneIncluidos = incluirNorm.every((palabra) =>
      k.includes(palabra),
    );
    const contieneExcluidos = excluirNorm.some((palabra) =>
      k.includes(palabra),
    );

    return contieneIncluidos && !contieneExcluidos;
  });

  if (!llave) return "";

  const columnaReal = indice[llave];

  if (
    columnaReal &&
    fila[columnaReal] !== undefined &&
    fila[columnaReal] !== null &&
    fila[columnaReal] !== ""
  ) {
    return fila[columnaReal];
  }

  return "";
};

const pareceIdentificadorPaciente = (valor) => {
  if (!valor) return false;

  const texto = String(valor).trim();

  if (/^[A-Z]{4}\d{6}[A-Z0-9_]{4,}$/i.test(texto)) return true;
  if (/^\d{8,}$/.test(texto)) return true;
  if (texto.includes("_") && texto.length > 10) return true;

  return false;
};

const limpiarNombrePaciente = (valor) => {
  const texto = normalizarValor(valor);

  if (!texto) return "";
  if (pareceIdentificadorPaciente(texto)) return "";

  return texto;
};

const unirNombreCompleto = (...partes) => {
  return partes
    .map((p) => limpiarNombrePaciente(p))
    .filter(Boolean)
    .join(" ")
    .replace(/\s+/g, " ")
    .trim();
};

const obtenerNombrePaciente = (fila, indice) => {
  const nombresRaw =
    obtenerValorOptimizado(
      fila,
      [
        "Nombre (s)",
        "Nombre(s)",
        "Nombres",
        "Nombre",
        "Nombre paciente",
        "Nombres paciente",
      ],
      indice,
    ) ||
    obtenerValorPorColumnasQueIncluyen(
      fila,
      indice,
      ["nombre"],
      [
        "derechohabiente",
        "beneficiario",
        "paciente",
        "cirujano",
        "medico",
        "médico",
        "especialidad",
        "diagnostico",
        "cie",
        "nss",
        "curp",
        "id",
      ],
    );

  const apellidoPaternoRaw =
    obtenerValorOptimizado(
      fila,
      [
        "Apellido paterno",
        "Apellido Paterno",
        "Primer apellido",
        "Paterno",
        "Ap paterno",
        "Ap. paterno",
      ],
      indice,
    ) ||
    obtenerValorPorColumnasQueIncluyen(
      fila,
      indice,
      ["apellido", "paterno"],
      ["medico", "médico", "cirujano"],
    ) ||
    obtenerValorPorColumnasQueIncluyen(
      fila,
      indice,
      ["paterno"],
      ["medico", "médico", "cirujano"],
    );

  const apellidoMaternoRaw =
    obtenerValorOptimizado(
      fila,
      [
        "Apellido materno",
        "Apellido Materno",
        "Segundo apellido",
        "Materno",
        "Ap materno",
        "Ap. materno",
      ],
      indice,
    ) ||
    obtenerValorPorColumnasQueIncluyen(
      fila,
      indice,
      ["apellido", "materno"],
      ["medico", "médico", "cirujano"],
    ) ||
    obtenerValorPorColumnasQueIncluyen(
      fila,
      indice,
      ["materno"],
      ["medico", "médico", "cirujano"],
    );

  const nombreArmado = unirNombreCompleto(
    nombresRaw,
    apellidoPaternoRaw,
    apellidoMaternoRaw,
  );

  if (nombreArmado) {
    return nombreArmado;
  }

  const nombreCompletoRaw =
    obtenerValorOptimizado(
      fila,
      [
        "Nombre completo del paciente",
        "Nombre completo paciente",
        "Nombre completo",
        "Nombre del paciente",
        "Nombre paciente",
        "Nombre de paciente",
        "Paciente nombre completo",
        "Paciente nombre",
      ],
      indice,
    ) ||
    obtenerValorPorColumnasQueIncluyen(
      fila,
      indice,
      ["nombre", "paciente"],
      ["cirujano", "medico", "médico", "nss", "curp", "id"],
    );

  return limpiarNombrePaciente(nombreCompletoRaw) || "SIN NOMBRE";
};

const procesarFila = (fila, indice) => {
  const estatusRaw =
    normalizarValor(
      obtenerValorOptimizado(fila, ["Estatus Qx", "estatus qx"], indice),
    ) || "SIN ESTATUS";

  let estadoFinal = "OTRO";

  if (estatusRaw.includes("REALIZADA") || estatusRaw.includes("REALIZADO")) {
    estadoFinal = "REALIZADA";
  } else if (
    estatusRaw.includes("CANCELADA") ||
    estatusRaw.includes("CANCELADO")
  ) {
    estadoFinal = "CANCELADA";
  }

  const concertadaRaw = obtenerValorOptimizado(
    fila,
    [
      "Cirugía concertada",
      "Cirugia concertada",
      "cirugia concertada",
      "Cirugía Concertada",
      "CIRUGÍA CONCERTADA",
      "concertada",
      "Concertada",
      "CIRUGIA CONCERTADA",
      "Qx concertada",
      "QX concertada",
    ],
    indice,
  );

  const concertada = obtenerEstadoConcertada(concertadaRaw);

  const nombrePaciente = obtenerNombrePaciente(fila, indice);

  const identificadorPaciente =
    obtenerValorOptimizado(
      fila,
      [
        "Paciente",
        "NSS",
        "NSS paciente",
        "CURP",
        "Identificador",
        "ID paciente",
        "ID Paciente",
      ],
      indice,
    ) || "";

  const fechaSolicitudRaw =
    obtenerValorOptimizado(
      fila,
      [
        "Fecha de solicitud",
        "Fecha solicitud",
        "Fecha de Solicitud",
        "Fecha de la solicitud",
        "Fecha solicitud qx",
        "Fecha de solicitud qx",
        "Fecha de solicitud Qx",
        "Fecha solicitud cirugía",
        "Fecha de solicitud cirugía",
        "Fecha de solicitud de cirugía",
        "Fecha solicitud cirugia",
        "Fecha de solicitud de cirugia",
        "Fecha de captura solicitud",
        "Fecha captura solicitud",
        "Fecha de registro solicitud",
        "Fecha registro solicitud",
        "Fecha de registro",
        "Fecha registro",
        "Fecha de ingreso",
        "Fecha ingreso",
      ],
      indice,
    ) ||
    obtenerValorPorColumnasQueIncluyen(
      fila,
      indice,
      ["fecha", "solicitud"],
      ["program", "cancel", "realiz"],
    );

  const fechaCancelacionRaw =
    obtenerValorOptimizado(
      fila,
      [
        "Fecha de cancelación",
        "Fecha cancelación",
        "Fecha de cancelacion",
        "Fecha cancelacion",
        "Fecha cancelada",
        "Fecha de cirugía cancelada",
        "Fecha de cirugia cancelada",
        "Fecha suspensión",
        "Fecha de suspensión",
        "Fecha suspension",
        "Fecha de suspension",
      ],
      indice,
    ) ||
    obtenerValorPorColumnasQueIncluyen(
      fila,
      indice,
      ["fecha", "cancel"],
      ["program", "solicitud"],
    ) ||
    obtenerValorPorColumnasQueIncluyen(
      fila,
      indice,
      ["fecha", "suspension"],
      ["program", "solicitud"],
    );

  const fechaProgramacionRaw =
    obtenerValorOptimizado(
      fila,
      [
        "Fecha de programación",
        "Fecha programación",
        "Fecha programacion",
        "Fecha de programacion",
        "Fecha programada",
        "Fecha de cirugía programada",
        "Fecha de cirugia programada",
        "Fecha cirugía programada",
        "Fecha cirugia programada",
        "Fecha de Qx programada",
        "Fecha Qx programada",
        "Fecha de la Qx",
        "Fecha Qx",
        "Fecha qx",
        "Fecha de cirugía",
        "Fecha cirugía",
        "Fecha de cirugia",
        "Fecha cirugia",
      ],
      indice,
    ) ||
    obtenerValorPorColumnasQueIncluyen(
      fila,
      indice,
      ["fecha", "program"],
      ["solicitud", "cancel"],
    ) ||
    obtenerValorPorColumnasQueIncluyen(
      fila,
      indice,
      ["fecha", "qx"],
      ["solicitud", "cancel"],
    );

  const fechaSolicitud = convertirFecha(fechaSolicitudRaw);
  const fechaCancelacion = convertirFecha(fechaCancelacionRaw);
  const fechaProgramacion = convertirFecha(fechaProgramacionRaw);

  const reprogramacionRaw = normalizarValor(
    obtenerValorOptimizado(
      fila,
      [
        "Reprogramada",
        "Reprogramado",
        "Fue reprogramada",
        "Fue reprogramado",
        "Motivo de reprogramación",
        "Motivo de reprogramacion",
        "Causa de reprogramación",
        "Causa de reprogramacion",
        "Observaciones",
        "Observaci?n",
        "Observacion",
      ],
      indice,
    ),
  );

  return {
    edad: convertirNumero(obtenerValorOptimizado(fila, ["Edad"], indice)) || 0,

    sexo: normalizarValor(obtenerValorOptimizado(fila, ["Sexo"], indice)),

    estatusOriginal: estatusRaw,
    estatus: estadoFinal,

    especialidad:
      normalizarValor(obtenerValorOptimizado(fila, ["Especialidad"], indice)) ||
      "SIN ESPECIALIDAD",

    tipoSolicitud:
      normalizarValor(
        obtenerValorOptimizado(fila, ["Tipo de solicitud"], indice),
      ) || "NO REGISTRADO",

    concertada,
    nombrePaciente,
    identificadorPaciente,
    fechaSolicitudRaw,
    fechaSolicitud,
    fechaCancelacionRaw,
    fechaCancelacion,
    fechaProgramacionRaw,
    fechaProgramacion,
    reprogramacionRaw,

    motivoCancelacion:
      normalizarValor(
        obtenerValorOptimizado(
          fila,
          [
            "Motivo de cancelación",
            "motivo de cancelacion",
            "Motivo cancelacion",
            "motivo cancelación",
          ],
          indice,
        ) ||
          obtenerValorPorColumnasQueIncluyen(
            fila,
            indice,
            ["motivo", "cancel"],
            ["suspens"],
          ),
      ) || "NO REGISTRADO",

    ultimoMotivoSuspension:
      normalizarValor(
        obtenerValorOptimizado(
          fila,
          [
            "Último motivo de suspensión",
            "ultimo motivo de suspension",
            "Motivo de suspension",
            "motivo de suspensión",
            "suspension",
            "suspensión",
          ],
          indice,
        ) ||
          obtenerValorPorColumnasQueIncluyen(
            fila,
            indice,
            ["motivo", "suspens"],
            ["cancel"],
          ),
      ) || "SIN SUSPENSIÓN",

    nombreCirujano:
      normalizarValor(
        obtenerValorOptimizado(
          fila,
          ["Nombre del cirujano", "cirujano"],
          indice,
        ),
      ) || "SIN NOMBRE",

    sala:
      normalizarValor(
        obtenerValorOptimizado(
          fila,
          [
            "Sala donde se realizó la Qx",
            "sala donde se realizo la qx",
            "Sala donde se program? la Qx",
            "sala donde se programo la qx",
            "Sala",
          ],
          indice,
        ),
      ) || "SIN SALA",

    cie10:
      normalizarValor(
        obtenerValorOptimizado(
          fila,
          [
            "CIE10 Confirmación",
            "cie10 confirmacion",
            "CIE10 Diagnóstico nota post quirúrgica",
            "cie10 diagnostico nota post quirurgica",
            "CIE10 Diagnóstico solicitud",
            "cie10 diagnostico solicitud",
            "CIE10",
          ],
          indice,
        ),
      ) || "SIN CIE10",

    cie9:
      normalizarValor(
        obtenerValorOptimizado(
          fila,
          [
            "CIE9 Confirmación",
            "cie9 confirmacion",
            "CIE9 Intervención Qx",
            "cie9 intervencion qx",
            "CIE9 Solicitud",
            "cie9 solicitud",
            "CIE9",
          ],
          indice,
        ),
      ) || "SIN CIE9",

    entradaSala: obtenerValorOptimizado(
      fila,
      [
        "Hora de Entrada a sala",
        "Hora entrada sala",
        "Entrada a sala",
        "Entrada sala",
        "Hora de entrada",
        "Hora entrada",
      ],
      indice,
    ),

    inicioAnestesia: obtenerValorOptimizado(
      fila,
      [
        "Hora de Inicio de anestesia",
        "Hora inicio de anestesia",
        "Inicio de anestesia",
        "Inicio anestesia",
        "Hora de inicio anestesia",
        "Hora inicio anestesia",
      ],
      indice,
    ),

    inicioQx: obtenerValorOptimizado(
      fila,
      [
        "Hora de Inicio de Qx",
        "Hora inicio de Qx",
        "Inicio de Qx",
        "Inicio Qx",
        "Hora de inicio de cirugía",
        "Hora inicio de cirugía",
        "Inicio de cirugía",
        "Inicio cirugía",
        "Hora de inicio quirúrgico",
        "Inicio quirúrgico",
      ],
      indice,
    ),

    finQx: obtenerValorOptimizado(
      fila,
      [
        "Hora de Término de Qx",
        "Hora de Termino de Qx",
        "Hora de Tirmino de Qx",
        "Hora término Qx",
        "Hora termino Qx",
        "Hora tirmino Qx",
        "Término de Qx",
        "Termino de Qx",
        "Tirmino de Qx",
        "Fin de Qx",
        "Fin Qx",
        "Hora de fin de Qx",
        "Hora fin Qx",
        "Hora de término de cirugía",
        "Hora de termino de cirugia",
        "Hora de tirmino de cirugia",
        "Hora termino cirugia",
        "Término de cirugía",
        "Termino de cirugia",
        "Tirmino de cirugia",
        "Fin de cirugía",
        "Fin cirugia",
        "Hora de término quirúrgico",
        "Hora de termino quirurgico",
        "Hora de tirmino quirurgico",
        "Término quirúrgico",
        "Termino quirurgico",
        "Tirmino quirurgico",
      ],
      indice,
    ),

    finAnestesia: obtenerValorOptimizado(
      fila,
      [
        "Hora de Término de anestesia",
        "Hora de Termino de anestesia",
        "Hora de Tirmino de anestesia",
        "Hora término anestesia",
        "Hora termino anestesia",
        "Hora tirmino anestesia",
        "Término de anestesia",
        "Termino de anestesia",
        "Tirmino de anestesia",
        "Fin de anestesia",
        "Fin anestesia",
        "Hora de fin de anestesia",
        "Hora fin anestesia",
      ],
      indice,
    ),

    salidaSala: obtenerValorOptimizado(
      fila,
      [
        "Hora de Salida de sala",
        "Hora salida de sala",
        "Salida de sala",
        "Salida sala",
        "Hora de salida",
        "Hora salida",
      ],
      indice,
    ),

    diferimiento: convertirNumero(
      obtenerValorOptimizado(
        fila,
        [
          "Días de diferimiento a la realizaci?n",
          "dias de diferimiento a la realizacion",
          "Días de diferimiento a la programación",
          "dias de diferimiento a la programacion",
          "diferimiento",
        ],
        indice,
      ),
    ),
  };
};
// ==================== OPTIMIZACIÓN DE CARGA ====================

const TAMANO_LOTE_PROCESAMIENTO = 250;

const esperarTurnoNavegador = () => {
  return new Promise((resolve) => {
    if ("requestIdleCallback" in window) {
      window.requestIdleCallback(resolve, { timeout: 80 });
    } else {
      setTimeout(resolve, 0);
    }
  });
};

const procesarFilasPorLotes = async (filas, indice, onProgreso) => {
  const resultado = [];

  for (let i = 0; i < filas.length; i += TAMANO_LOTE_PROCESAMIENTO) {
    const lote = filas.slice(i, i + TAMANO_LOTE_PROCESAMIENTO);

    resultado.push(...lote.map((fila) => procesarFila(fila, indice)));

    if (onProgreso) {
      const porcentaje = Math.min(
        100,
        Math.round(((i + lote.length) / filas.length) * 100),
      );

      onProgreso(porcentaje);
    }

    await esperarTurnoNavegador();
  }

  return resultado;
};
// ==================== COMPONENTES ====================

const KpiCard = ({ titulo, valor, icono: Icono, color }) => {
  const estilos = {
    primary: "border-red-200 bg-red-50 text-red-700",
    success: "border-teal-200 bg-teal-50 text-teal-700",
    info: "border-blue-200 bg-blue-50 text-blue-700",
    danger: "border-red-200 bg-red-50 text-red-600",
    warning: "border-amber-200 bg-amber-50 text-amber-700",
  };

  const [border, bg, text] = estilos[color]?.split(" ") || ["", "", ""];

  return (
    <div className={`bg-white border ${border} rounded-2xl p-5 shadow-sm`}>
      <div className="flex justify-between gap-4">
        <div>
          <p className="text-xs uppercase font-bold text-slate-400">{titulo}</p>
          <h2 className="text-3xl font-black text-slate-900">{valor}</h2>
        </div>

        <div className={`p-3 rounded-2xl ${bg} h-fit`}>
          {React.createElement(Icono, { className: text, size: 24 })}
        </div>
      </div>
    </div>
  );
};

const Card = ({ titulo, icono: Icono, color, children }) => (
  <div className="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col shadow-sm">
    <div className="flex gap-3 mb-5 items-center">
      <div className="p-2 rounded-xl bg-slate-50">
        {React.createElement(Icono, { size: 18, style: { color } })}
      </div>

      <h3 className="font-bold text-slate-900">{titulo}</h3>
    </div>

    <div className="flex-1 min-h-[280px]">{children}</div>
  </div>
);

// ==================== COMPONENTE PRINCIPAL ====================

export default function TableroCirugias({ datos = [] }) {
  const [datosProcesados, setDatosProcesados] = useState([]);
  const [cargando, setCargando] = useState(true);
  const [progresoCarga, setProgresoCarga] = useState(0);
  const [error, setError] = useState(null);
  const [areaSeguimiento, setAreaSeguimiento] = useState(AREA_FILTRO_TODAS);
  const [limiteNoCanceladas, setLimiteNoCanceladas] = useState(100);
  const [limiteCanceladas, setLimiteCanceladas] = useState(100);

  useEffect(() => {
    let cancelado = false;

    const cargarYProcesar = async () => {
      try {
        setCargando(true);
        setError(null);
        setProgresoCarga(0);
        setDatosProcesados([]);

        if (datos && datos.length > 0) {
          const columnas = Object.keys(datos[0] || {});
          const indice = crearIndiceColumnas(columnas);

          const procesados = await procesarFilasPorLotes(
            datos,
            indice,
            (porcentaje) => {
              if (!cancelado) setProgresoCarga(porcentaje);
            },
          );

          if (!cancelado) {
            setDatosProcesados(procesados);
            setCargando(false);
          }

          return;
        }

        const respuesta = await fetch("/graficos/datos_cirugias_ene_mayo_2026.csv", {
          cache: "no-store",
        });

        if (!respuesta.ok) {
          throw new Error(
            `No se pudo cargar el CSV. Error HTTP ${respuesta.status}`,
          );
        }

        const buffer = await respuesta.arrayBuffer();

        let texto = new TextDecoder("windows-1252").decode(buffer);

        if (texto.charCodeAt(0) === 0xfeff) {
          texto = texto.slice(1);
        }

        if (cancelado) return;

        Papa.parse(texto, {
          header: true,
          skipEmptyLines: true,
          worker: true,
          complete: async (resultado) => {
            if (cancelado) return;

            if (resultado.errors?.length) {
              console.warn("Avisos de parseo CSV:", resultado.errors);
            }

            const filas = resultado.data || [];

            if (!filas.length) {
              setDatosProcesados([]);
              setCargando(false);
              return;
            }

            const columnas = Object.keys(filas[0] || {});
            const indice = crearIndiceColumnas(columnas);

            const procesados = await procesarFilasPorLotes(
              filas,
              indice,
              (porcentaje) => {
                if (!cancelado) setProgresoCarga(porcentaje);
              },
            );

            if (cancelado) return;

            setDatosProcesados(procesados);
            setCargando(false);
          },
          error: (err) => {
            if (cancelado) return;

            console.error(err);
            setError(err.message || "Error al procesar el CSV");
            setCargando(false);
          },
        });
      } catch (err) {
        if (cancelado) return;

        console.error(err);
        setError(err.message || "Error al cargar los datos");
        setCargando(false);
      }
    };

    cargarYProcesar();

    return () => {
      cancelado = true;
    };
  }, [datos]);

  const agregados = useMemo(() => {
    const gruposEdad = [
      { label: "MENOR DE 1 AÑO", min: 0, max: 0.999999 },
      { label: "1-2 AÑOS", min: 1, max: 2 },
      { label: "3-5 AÑOS", min: 3, max: 5 },
      { label: "6-11 AÑOS", min: 6, max: 11 },
      { label: "12-14 AÑOS", min: 12, max: 14 },
      { label: "15-17 AÑOS", min: 15, max: 17 },
      { label: "18-24 AÑOS", min: 18, max: 24 },
      { label: "25-34 AÑOS", min: 25, max: 34 },
      { label: "35-44 AÑOS", min: 35, max: 44 },
      { label: "45-54 AÑOS", min: 45, max: 54 },
      { label: "55-64 AÑOS", min: 55, max: 64 },
      { label: "65-74 AÑOS", min: 65, max: 74 },
      { label: "75+ AÑOS", min: 75, max: 200 },
    ];

    const kpisBase = { realizadas: 0, canceladas: 0, otras: 0 };
    const sumasTiempo = {
      qx: 0,
      anestesia: 0,
      preparacion: 0,
      recuperacion: 0,
      sala: 0,
      totalQx: 0,
      totalAnestesia: 0,
      totalPreparacion: 0,
      totalRecuperacion: 0,
      totalSala: 0,
    };
    const conteos = {
      estatus: {},
      tipoSolicitud: {},
      cirujanos: {},
      especialidades: {},
      salas: {},
      cie10: {},
      cie9: {},
      motivosCancelacion: {},
      motivosSuspension: {},
    };
    const sexo = { masc: 0, fem: 0, otro: 0 };
    const concertada = { si: 0, no: 0, sinDato: 0 };
    const diferimiento = { menos20: 0, mas20: 0 };
    const edadHombres = Array(gruposEdad.length).fill(0);
    const edadMujeres = Array(gruposEdad.length).fill(0);

    datosProcesados.forEach((d) => {
      const estatus = d.estatus;
      const sexoNorm = normalizarParaComparar(d.sexo);

      if (estatus === "REALIZADA") kpisBase.realizadas++;
      else if (estatus === "CANCELADA") kpisBase.canceladas++;
      else kpisBase.otras++;

      incrementarConteo(conteos.estatus, d.estatusOriginal, "SIN ESTATUS");

      if (sexoNorm === "M" || sexoNorm === "MASCULINO") sexo.masc++;
      else if (sexoNorm === "F" || sexoNorm === "FEMENINO") sexo.fem++;
      else sexo.otro++;

      const concertadaNorm = normalizarParaComparar(d.concertada);
      if (concertadaNorm === "SI") concertada.si++;
      else if (concertadaNorm === "NO") concertada.no++;
      else concertada.sinDato++;

      if (d.tipoSolicitud && d.tipoSolicitud !== "NO REGISTRADO") {
        incrementarConteo(conteos.tipoSolicitud, d.tipoSolicitud);
      }

      const excluirCirujano = [
        "SIN NOMBRE",
        "SIN DATO",
        "N/A NO APLICA",
        "NO APLICA",
        "NA",
        "N/A",
        "0",
      ];
      if (!excluirCirujano.includes(normalizarParaComparar(d.nombreCirujano))) {
        incrementarConteo(conteos.cirujanos, d.nombreCirujano);
      }

      if (
        !["SIN ESPECIALIDAD", "SIN DATO"].includes(
          normalizarParaComparar(d.especialidad),
        )
      ) {
        incrementarConteo(conteos.especialidades, d.especialidad);
      }

      if (!["SIN SALA", "SIN DATO"].includes(normalizarParaComparar(d.sala))) {
        incrementarConteo(conteos.salas, d.sala);
      }

      if (
        !["SIN CIE10", "SIN DATO"].includes(normalizarParaComparar(d.cie10))
      ) {
        incrementarConteo(conteos.cie10, d.cie10);
      }

      if (!["SIN CIE9", "SIN DATO"].includes(normalizarParaComparar(d.cie9))) {
        incrementarConteo(conteos.cie9, d.cie9);
      }

      const edad = Number(d.edad);
      if (Number.isFinite(edad)) {
        const indiceGrupo = gruposEdad.findIndex(
          (g) => edad >= g.min && edad <= g.max,
        );

        if (indiceGrupo !== -1) {
          if (sexoNorm === "M" || sexoNorm === "MASCULINO")
            edadHombres[indiceGrupo]++;
          else if (sexoNorm === "F" || sexoNorm === "FEMENINO")
            edadMujeres[indiceGrupo]++;
        }
      }

      if (estatus === "REALIZADA") {
        const dias = Number(d.diferimiento);

        if (!Number.isFinite(dias) || dias < 20) diferimiento.menos20++;
        else diferimiento.mas20++;

        if (d.entradaSala && d.salidaSala) {
          const m = calcularMinutos(d.entradaSala, d.salidaSala);
          if (m > 0) {
            sumasTiempo.sala += m;
            sumasTiempo.totalSala++;
          }
        }

        const minutosQx =
          d.inicioQx && (d.finQx || d.salidaSala)
            ? calcularMinutos(d.inicioQx, d.finQx || d.salidaSala)
            : 0;
        if (minutosQx > 0) {
          sumasTiempo.qx += minutosQx;
          sumasTiempo.totalQx++;
        }

        if (d.entradaSala && d.inicioQx) {
          const m = calcularMinutos(d.entradaSala, d.inicioQx);
          if (m > 0) {
            sumasTiempo.preparacion += m;
            sumasTiempo.totalPreparacion++;
          }
        }

        const minutosAnestesia =
          d.inicioAnestesia && (d.finAnestesia || d.salidaSala)
            ? calcularMinutos(d.inicioAnestesia, d.finAnestesia || d.salidaSala)
            : 0;
        if (minutosAnestesia > 0) {
          sumasTiempo.anestesia += minutosAnestesia;
          sumasTiempo.totalAnestesia++;
        }

        const inicioRecuperacion = d.finQx || d.finAnestesia;
        if (inicioRecuperacion && d.salidaSala) {
          const m = calcularMinutos(inicioRecuperacion, d.salidaSala);
          if (m > 0) {
            sumasTiempo.recuperacion += m;
            sumasTiempo.totalRecuperacion++;
          }
        }
      }

      if (estatus === "CANCELADA") {
        let motivo = normalizarValor(d.motivoCancelacion);

        if (!motivo || normalizarParaComparar(motivo) === "NO REGISTRADO") {
          motivo = "SIN MOTIVO REGISTRADO";
        }

        incrementarConteo(conteos.motivosCancelacion, motivo);
      }

      const motivoSuspension = normalizarValor(d.ultimoMotivoSuspension);
      const motivoSuspensionNorm = normalizarParaComparar(motivoSuspension);

      if (
        motivoSuspension &&
        !motivoSuspensionNorm.includes("SIN SUSPENSION") &&
        motivoSuspensionNorm !== "NO REGISTRADO"
      ) {
        incrementarConteo(conteos.motivosSuspension, motivoSuspension);
      }
    });

    const promedio = (suma, total) => (total ? suma / total : 0);

    return {
      gruposEdad,
      kpis: {
        total: datosProcesados.length,
        ...kpisBase,
        efectividad: datosProcesados.length
          ? ((kpisBase.realizadas / datosProcesados.length) * 100).toFixed(1)
          : 0,
      },
      tiempos: {
        qx: promedio(sumasTiempo.qx, sumasTiempo.totalQx),
        anestesia: promedio(sumasTiempo.anestesia, sumasTiempo.totalAnestesia),
        preparacion: promedio(
          sumasTiempo.preparacion,
          sumasTiempo.totalPreparacion,
        ),
        recuperacion: promedio(
          sumasTiempo.recuperacion,
          sumasTiempo.totalRecuperacion,
        ),
        sala: promedio(sumasTiempo.sala, sumasTiempo.totalSala),
      },
      conteos,
      sexo,
      concertada,
      diferimiento,
      edadHombres,
      edadMujeres,
    };
  }, [datosProcesados]);

  const { kpis, tiempos } = agregados;

  const baseChartOptions = useMemo(
    () => ({
      responsive: true,
      maintainAspectRatio: false,
      animation: false,
      resizeDelay: 250,
    }),
    [],
  );

  const doughnutOptions = useMemo(
    () => ({
      ...baseChartOptions,
      cutout: "72%",
      plugins: {
        legend: {
          position: "bottom",
          labels: {
            boxWidth: 12,
            font: {
              size: 11,
            },
          },
        },
        tooltip: {
          callbacks: {
            title: (items) => items?.[0]?.label || "",
          },
        },
      },
    }),
    [baseChartOptions],
  );

  const barOptions = useMemo(
    () => ({
      ...baseChartOptions,
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          callbacks: {
            title: (items) => items?.[0]?.label || "",
            label: (item) => `${item.raw} minutos`,
          },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0,
          },
        },
        x: {
          ticks: {
            autoSkip: false,
          },
        },
      },
    }),
    [baseChartOptions],
  );

  const diferimientoOptions = useMemo(
    () => ({
      ...baseChartOptions,
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          callbacks: {
            title: (items) => items?.[0]?.label || "",
            label: (item) => `${item.raw} cirugías realizadas`,
          },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0,
          },
        },
        x: {
          ticks: {
            autoSkip: false,
          },
        },
      },
    }),
    [baseChartOptions],
  );

  const horizontalOptions = useMemo(
    () => ({
      ...baseChartOptions,
      indexAxis: "y",
      layout: {
        padding: {
          left: 10,
          right: 20,
        },
      },
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          callbacks: {
            title: (items) => items?.[0]?.label || "",
          },
        },
      },
      scales: {
        x: {
          beginAtZero: true,
          ticks: {
            precision: 0,
          },
        },
        y: {
          ticks: {
            autoSkip: false,
            font: {
              size: 11,
            },
            padding: 8,
            callback: function (value) {
              return envolverLabel(this.getLabelForValue(value), 34);
            },
          },
        },
      },
    }),
    [baseChartOptions],
  );

  const chartEstatus = useMemo(() => {
    const labels = Object.keys(agregados.conteos.estatus).sort();

    return {
      labels,
      datasets: [
        {
          data: labels.map((l) => agregados.conteos.estatus[l]),
          backgroundColor: [
            COLORS.success,
            COLORS.danger,
            "#a855f7",
            "#06b6d4",
            "#f97316",
            "#14b8a6",
          ].slice(0, labels.length),
          normalized: true,
        },
      ],
    };
  }, [agregados.conteos.estatus]);

  const chartSexo = useMemo(() => {
    const { masc, fem, otro } = agregados.sexo;

    const labels =
      otro > 0
        ? ["MASCULINO", "FEMENINO", "SIN DATO"]
        : ["MASCULINO", "FEMENINO"];

    const data = otro > 0 ? [masc, fem, otro] : [masc, fem];

    const backgroundColor =
      otro > 0
        ? [COLORS.male, COLORS.female, COLORS.gray]
        : [COLORS.male, COLORS.female];

    return {
      labels,
      datasets: [
        {
          data,
          backgroundColor,
          normalized: true,
        },
      ],
    };
  }, [agregados.sexo]);

  const chartConcertada = useMemo(() => {
    const { si, no, sinDato } = agregados.concertada;

    const labels = sinDato > 0 ? ["SÍ", "NO", "SIN DATO"] : ["SÍ", "NO"];

    const data = sinDato > 0 ? [si, no, sinDato] : [si, no];

    const backgroundColor =
      sinDato > 0
        ? [COLORS.success, COLORS.danger, COLORS.gray]
        : [COLORS.success, COLORS.danger];

    return {
      labels,
      datasets: [
        {
          data,
          backgroundColor,
          normalized: true,
        },
      ],
    };
  }, [agregados.concertada]);

  const topTipoSolicitud = useMemo(() => {
    const conteo = agregados.conteos.tipoSolicitud;

    let ordenados = Object.entries(conteo)
      .sort((a, b) => b[1] - a[1])
      .slice(0, 8);

    if (ordenados.length === 0) {
      ordenados = [["SIN INFORMACIÓN", 0]];
    }

    const colores = ordenados.map(([label]) => {
      const l = normalizarParaComparar(label);

      if (l.includes("ELECTIV")) return COLORS.success;
      if (l.includes("URGENC")) return COLORS.danger;

      return COLORS.gray;
    });

    return {
      labels: ordenados.map((i) => i[0]),
      datasets: [
        {
          data: ordenados.map((i) => i[1]),
          backgroundColor: colores,
          borderRadius: 8,
          normalized: true,
        },
      ],
    };
  }, [agregados.conteos.tipoSolicitud]);

  const chartTiempos = useMemo(
    () => ({
      labels: ["PREPARACIÓN", "CIRUGÍA", "ANESTESIA", "RECUPERACIÓN"],
      datasets: [
        {
          label: "MINUTOS PROMEDIO",
          data: [
            Number(tiempos.preparacion.toFixed(1)),
            Number(tiempos.qx.toFixed(1)),
            Number(tiempos.anestesia.toFixed(1)),
            Number(tiempos.recuperacion.toFixed(1)),
          ],
          backgroundColor: [
            COLORS.warning,
            COLORS.success,
            COLORS.primary,
            COLORS.info,
          ],
          borderRadius: 8,
          normalized: true,
        },
      ],
    }),
    [tiempos],
  );

  const topCirujanos = useMemo(
    () =>
      crearChartTop(
        obtenerTopOrdenado(agregados.conteos.cirujanos, 10, "SIN INFORMACIÓN"),
        COLORS.info,
      ),
    [agregados.conteos.cirujanos],
  );

  const topEspecialidades = useMemo(
    () =>
      crearChartTop(
        obtenerTopOrdenado(
          agregados.conteos.especialidades,
          10,
          "SIN INFORMACIÓN",
        ),
        COLORS.primary,
      ),
    [agregados.conteos.especialidades],
  );

  const topSalas = useMemo(
    () =>
      crearChartTop(
        obtenerTopOrdenado(agregados.conteos.salas, 10, "SIN INFORMACIÓN"),
        COLORS.success,
      ),
    [agregados.conteos.salas],
  );

  const topCIE10 = useMemo(
    () =>
      crearChartTop(
        obtenerTopOrdenado(agregados.conteos.cie10, 10, "SIN INFORMACIÓN"),
        PALETA_CIE10,
      ),
    [agregados.conteos.cie10],
  );

  const topCIE9 = useMemo(
    () =>
      crearChartTop(
        obtenerTopOrdenado(agregados.conteos.cie9, 10, "SIN INFORMACIÓN"),
        COLORS.dark,
      ),
    [agregados.conteos.cie9],
  );

  const chartEdad = useMemo(() => {
    return {
      labels: agregados.gruposEdad.map((g) => g.label),
      datasets: [
        {
          label: "MASCULINO",
          data: agregados.edadHombres.map((v) => -v),
          backgroundColor: COLORS.male,
          borderColor: "#fff",
          borderWidth: 1,
          barPercentage: 0.9,
          categoryPercentage: 0.9,
          normalized: true,
        },
        {
          label: "FEMENINO",
          data: agregados.edadMujeres,
          backgroundColor: COLORS.female,
          borderColor: "#fff",
          borderWidth: 1,
          barPercentage: 0.9,
          categoryPercentage: 0.9,
          normalized: true,
        },
      ],
    };
  }, [agregados]);

  const piramideOptions = useMemo(
    () => ({
      ...baseChartOptions,
      indexAxis: "y",
      interaction: {
        mode: "index",
        intersect: false,
        axis: "y",
      },
      plugins: {
        legend: {
          display: true,
          position: "bottom",
        },
        tooltip: {
          enabled: true,
          mode: "index",
          intersect: false,
          callbacks: {
            title: (items) => items?.[0]?.label || "",
            label: (ctx) => {
              const valor = Math.abs(ctx.raw || 0);
              return `${ctx.dataset.label}: ${valor}`;
            },
            afterBody: (items) => {
              const total = items.reduce((acc, item) => {
                return acc + Math.abs(item.raw || 0);
              }, 0);

              return `TOTAL: ${total}`;
            },
          },
        },
      },
      scales: {
        x: {
          stacked: false,
          ticks: {
            callback: (value) => Math.abs(value),
            precision: 0,
          },
        },
        y: {
          stacked: false,
          grid: {
            display: false,
          },
          ticks: {
            autoSkip: false,
          },
        },
      },
    }),
    [baseChartOptions],
  );

  const chartDiferimiento = useMemo(() => {
    return {
      labels: ["< 20 DÍAS NATURALES", "≥ 20 DÍAS NATURALES"],
      datasets: [
        {
          label: "CIRUGÍAS REALIZADAS",
          data: [agregados.diferimiento.menos20, agregados.diferimiento.mas20],
          backgroundColor: [COLORS.success, COLORS.warning],
          borderRadius: 8,
          normalized: true,
        },
      ],
    };
  }, [agregados.diferimiento]);

  const registrosSinDiferimientoRealizado = useMemo(() => {
    const registros = datosProcesados
      .filter((d) => {
        const dias = Number(d.diferimiento);

        return d.estatus !== "REALIZADA" || !Number.isFinite(dias);
      })
      .map((d) => {
        let motivo = "NO REGISTRADO";

        if (d.reprogramacionRaw) {
          motivo = d.reprogramacionRaw;
        } else if (
          d.motivoCancelacion &&
          normalizarParaComparar(d.motivoCancelacion) !== "NO REGISTRADO"
        ) {
          motivo = d.motivoCancelacion;
        } else if (
          d.ultimoMotivoSuspension &&
          !normalizarParaComparar(d.ultimoMotivoSuspension).includes(
            "SIN SUSPENSION",
          )
        ) {
          motivo = d.ultimoMotivoSuspension;
        }

        return {
          paciente: d.nombrePaciente || "SIN NOMBRE",
          identificador: d.identificadorPaciente || "",
          especialidad: d.especialidad || "SIN ESPECIALIDAD",
          area: obtenerAreaEspecialidad(d.especialidad || "SIN ESPECIALIDAD"),
          estatus: d.estatusOriginal || d.estatus || "SIN ESTATUS",
          estatusNormalizado: d.estatus,
          motivo,

          cie10: d.cie10 || "SIN CIE10",
          cie9: d.cie9 || "SIN CIE9",

          fechaSolicitudTexto: formatearFecha(d.fechaSolicitud),
          fechaCancelacionTexto: formatearFecha(d.fechaCancelacion),
          fechaProgramacionTexto: formatearFecha(d.fechaProgramacion),
          fechaProgramacion: d.fechaProgramacion,
          diasNaturalesProgramacion: calcularDiasNaturalesDesde(
            d.fechaProgramacion,
          ),
        };
      })
      .sort((a, b) => {
        const diasA = a.diasNaturalesProgramacion ?? -1;
        const diasB = b.diasNaturalesProgramacion ?? -1;

        return diasB - diasA;
      });

    return registros;
  }, [datosProcesados]);

  const registrosNoCancelados = useMemo(() => {
    return registrosSinDiferimientoRealizado.filter((r) => {
      if (r.estatusNormalizado === "CANCELADA") return false;

      // Quita registros cuya fecha de programación es posterior al d?a de hoy.
      // Ejemplo: si hoy es 19/05/2026 y la cirugía está programada para 22/05/2026,
      // no debe aparecer porque todavía no está atrasada.
      if (r.fechaProgramacion && esFechaPosteriorAHoy(r.fechaProgramacion)) {
        return false;
      }

      return true;
    });
  }, [registrosSinDiferimientoRealizado]);

  const registrosCancelados = useMemo(() => {
    return registrosSinDiferimientoRealizado.filter((r) => {
      return r.estatusNormalizado === "CANCELADA";
    });
  }, [registrosSinDiferimientoRealizado]);

  const registrosNoCanceladosFiltrados = useMemo(() => {
    if (areaSeguimiento === AREA_FILTRO_TODAS) {
      return registrosNoCancelados;
    }

    return registrosNoCancelados.filter((r) => r.area === areaSeguimiento);
  }, [areaSeguimiento, registrosNoCancelados]);

  const registrosCanceladosFiltrados = useMemo(() => {
    if (areaSeguimiento === AREA_FILTRO_TODAS) {
      return registrosCancelados;
    }

    return registrosCancelados.filter((r) => r.area === areaSeguimiento);
  }, [areaSeguimiento, registrosCancelados]);

  const registrosNoCanceladosVisibles = useMemo(() => {
    return registrosNoCanceladosFiltrados.slice(0, limiteNoCanceladas);
  }, [registrosNoCanceladosFiltrados, limiteNoCanceladas]);

  const registrosCanceladosVisibles = useMemo(() => {
    return registrosCanceladosFiltrados.slice(0, limiteCanceladas);
  }, [registrosCanceladosFiltrados, limiteCanceladas]);

  const descargarExcelNoCanceladas = async () => {
    if (!registrosNoCanceladosFiltrados.length) {
      alert("No hay registros para descargar.");
      return;
    }

    const XLSX = await import("xlsx");

    const datosExcel = registrosNoCanceladosVisibles.map((r, index) => ({
      "#": index + 1,
      "Nombre completo": r.paciente,
      Identificador: r.identificador || "SIN IDENTIFICADOR",
      Especialidad: r.especialidad || "SIN ESPECIALIDAD",
      Estatus: r.estatus,
      "Reprogramación / Motivo": r.motivo,
      CIE10: r.cie10 || "SIN CIE10",
      CIE9: r.cie9 || "SIN CIE9",
      "Fecha solicitud": r.fechaSolicitudTexto,
      "Fecha cancelación": r.fechaCancelacionTexto,
      "Fecha programación": r.fechaProgramacionTexto,
      "Días naturales desde programación":
        r.diasNaturalesProgramacion === null
          ? "SIN FECHA"
          : r.diasNaturalesProgramacion,
    }));

    const hoja = XLSX.utils.json_to_sheet(datosExcel);

    hoja["!cols"] = [
      { wch: 6 },
      { wch: 36 },
      { wch: 18 },
      { wch: 24 },
      { wch: 24 },
      { wch: 40 },
      { wch: 28 },
      { wch: 28 },
      { wch: 18 },
      { wch: 18 },
      { wch: 20 },
      { wch: 32 },
    ];

    const libro = XLSX.utils.book_new();

    XLSX.utils.book_append_sheet(libro, hoja, "No canceladas");

    const fechaHoy = new Date()
      .toLocaleDateString("es-MX")
      .replaceAll("/", "-");

    XLSX.writeFile(libro, `cirugias_no_canceladas_${fechaHoy}.xlsx`);
  };

  const descargarExcelCanceladas = async () => {
    if (!registrosCanceladosFiltrados.length) {
      alert("No hay cirugías canceladas para descargar.");
      return;
    }

    const XLSX = await import("xlsx");

    const datosExcel = registrosCanceladosVisibles.map((r, index) => ({
      "#": index + 1,
      "Nombre completo": r.paciente,
      Identificador: r.identificador || "SIN IDENTIFICADOR",
      Especialidad: r.especialidad || "SIN ESPECIALIDAD",
      Estatus: r.estatus,
      "Motivo de cancelación": r.motivo,
      "Fecha solicitud": r.fechaSolicitudTexto,
      "Fecha cancelación": r.fechaCancelacionTexto,
      "Fecha programación": r.fechaProgramacionTexto,
    }));

    const hoja = XLSX.utils.json_to_sheet(datosExcel);

    hoja["!cols"] = [
      { wch: 6 },
      { wch: 36 },
      { wch: 18 },
      { wch: 24 },
      { wch: 24 },
      { wch: 45 },
      { wch: 18 },
      { wch: 18 },
      { wch: 20 },
    ];

    const libro = XLSX.utils.book_new();

    XLSX.utils.book_append_sheet(libro, hoja, "Canceladas");

    const fechaHoy = new Date()
      .toLocaleDateString("es-MX")
      .replaceAll("/", "-");

    XLSX.writeFile(libro, `cirugias_canceladas_${fechaHoy}.xlsx`);
  };

  const chartMotivosCancelacion = useMemo(() => {
    const ordenados = obtenerTopOrdenado(
      agregados.conteos.motivosCancelacion,
      10,
      "SIN CANCELACIONES",
    );

    return {
      labels: ordenados.map(([motivo, total]) => `${motivo} — ${total}`),
      datasets: [
        {
          data: ordenados.map(([, total]) => total),
          backgroundColor: COLORS.danger,
          borderRadius: 8,
          normalized: true,
        },
      ],
    };
  }, [agregados.conteos.motivosCancelacion]);

  const chartMotivosSuspension = useMemo(() => {
    const ordenados = obtenerTopOrdenado(
      agregados.conteos.motivosSuspension,
      10,
      "SIN SUSPENSIONES REGISTRADAS",
    );

    return {
      labels: ordenados.map(([motivo, total]) => `${motivo} — ${total}`),
      datasets: [
        {
          data: ordenados.map(([, total]) => total),
          backgroundColor: COLORS.warning,
          borderRadius: 8,
          normalized: true,
        },
      ],
    };
  }, [agregados.conteos.motivosSuspension]);

  if (cargando) {
    return (
      <div className="h-[500px] flex flex-col items-center justify-center gap-4">
        <Scissors className="animate-pulse text-red-200" size={52} />

        <div className="text-center">
          <p className="text-slate-500 font-semibold">
            Cargando datos del quirófano...
          </p>

          <p className="text-xs text-slate-400 mt-1">
            Procesando información {progresoCarga}%
          </p>
        </div>

        <div className="w-[260px] h-2 bg-slate-100 rounded-full overflow-hidden">
          <div
            className="h-full bg-red-700 transition-all duration-200"
            style={{ width: `${progresoCarga}%` }}
          />
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="h-[500px] flex items-center justify-center text-red-600">
        Error: {error}
      </div>
    );
  }

  if (!datosProcesados.length) {
    return (
      <div className="h-[500px] flex items-center justify-center">
        No hay datos disponibles
      </div>
    );
  }

  return (
    <div className="min-h-screen p-6 space-y-6 bg-gradient-to-br from-slate-50 via-white to-red-50">
      <h1 className="text-4xl font-black flex gap-4 items-center text-slate-900">
        <Scissors className="text-red-700" size={40} />
        Tablero de Cirugías
      </h1>

      <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-5">
        <KpiCard
          titulo="Total Cirugías"
          valor={kpis.total}
          icono={Activity}
          color="info"
        />
        <KpiCard
          titulo="Realizadas"
          valor={kpis.realizadas}
          icono={CalendarCheck}
          color="success"
        />
        <KpiCard
          titulo="Canceladas"
          valor={kpis.canceladas}
          icono={XCircle}
          color="danger"
        />
        <KpiCard
          titulo="Efectividad"
          valor={`${kpis.efectividad}%`}
          icono={ChartNoAxesColumn}
          color="primary"
        />
        <KpiCard
          titulo="Tiempo Sala"
          valor={formatearMinutos(tiempos.sala)}
          icono={Timer}
          color="warning"
        />
      </div>

      <div className="grid lg:grid-cols-3 gap-6">
        <Card
          titulo="Estatus Quirúrgico"
          icono={Activity}
          color={COLORS.success}
        >
          <div className="h-[260px]">
            <Doughnut data={chartEstatus} options={doughnutOptions} />
          </div>
        </Card>

        <Card
          titulo="Distribución por Sexo"
          icono={Users}
          color={COLORS.female}
        >
          <div className="h-[260px]">
            <Doughnut data={chartSexo} options={doughnutOptions} />
          </div>
        </Card>

        <Card
          titulo="Cirugía Concertada"
          icono={ClipboardCheck}
          color={COLORS.info}
        >
          <div className="h-[260px]">
            <Doughnut data={chartConcertada} options={doughnutOptions} />
          </div>
        </Card>
      </div>

      <div className="grid xl:grid-cols-2 gap-6">
        <Card titulo="Tipo de Solicitud" icono={FileText} color={COLORS.info}>
          <div className="h-[320px]">
            <Bar data={topTipoSolicitud} options={horizontalOptions} />
          </div>
        </Card>

        <Card
          titulo="Tiempos Promedio del Procedimiento (minutos)"
          icono={Hourglass}
          color={COLORS.primary}
        >
          <div className="h-[320px]">
            <Bar data={chartTiempos} options={barOptions} />
          </div>
        </Card>
      </div>

      <div className="grid xl:grid-cols-2 gap-6">
        <Card titulo="Top Cirujanos" icono={Users} color={COLORS.info}>
          <div className="h-[420px]">
            <Bar data={topCirujanos} options={horizontalOptions} />
          </div>
        </Card>

        <Card
          titulo="Cirugías por Especialidad"
          icono={Stethoscope}
          color={COLORS.primary}
        >
          <div className="h-[420px]">
            <Bar data={topEspecialidades} options={horizontalOptions} />
          </div>
        </Card>
      </div>

      <div className="grid xl:grid-cols-2 gap-6">
        <Card
          titulo="Pirámide Poblacional"
          icono={Users}
          color={COLORS.success}
        >
          <div className="h-[420px]">
            <Bar data={chartEdad} options={piramideOptions} />
          </div>
        </Card>

        <Card
          titulo="Días de Diferimiento (días naturales)"
          icono={Hourglass}
          color={COLORS.warning}
        >
          <div className="h-[320px]">
            <Bar data={chartDiferimiento} options={diferimientoOptions} />
          </div>
        </Card>
      </div>

      <div className="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <h3 className="font-bold text-lg mb-3 flex gap-2 items-center text-slate-900">
          <Hourglass size={20} className="text-amber-600" />
          Seguimiento de cirugías no completadas o sin diferimiento.
        </h3>

        <p className="text-xs text-slate-400 mb-4">
          Se separan las cirugías canceladas de las demás cirugías no
          completadas o registros sin días de diferimiento válido.
        </p>

        <div className="mb-5 flex flex-col md:flex-row md:items-end md:justify-between gap-3">
          <label className="flex flex-col gap-1 text-sm font-bold text-slate-700">
            Area
            <select
              value={areaSeguimiento}
              onChange={(event) => setAreaSeguimiento(event.target.value)}
              className="min-w-[260px] rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
            >
              {AREA_FILTER_OPTIONS.map((opcion) => (
                <option key={opcion.value} value={opcion.value}>
                  {opcion.label}
                </option>
              ))}
            </select>
          </label>

          <div className="text-xs text-slate-400 md:text-right">
            Mostrando {registrosNoCanceladosFiltrados.length} de{" "}
            {registrosNoCancelados.length} registros no cancelados.
          </div>
        </div>

        {/* ==================== NO CANCELADAS ==================== */}
        <div className="mb-8">
          <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-3">
            <div>
              <h4 className="font-black text-slate-900 mb-1 flex items-center gap-2">
                <span className="w-2 h-2 rounded-full bg-amber-500"></span>
                Cirugías no completadas o sin diferimiento, excepto canceladas
              </h4>

              <p className="text-xs text-slate-400">
                El conteo de días naturales se calcula desde la fecha de
                programación hasta hoy.
              </p>
            </div>

            <button
              type="button"
              onClick={descargarExcelNoCanceladas}
              className="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 active:scale-[0.98] transition disabled:opacity-50 disabled:cursor-not-allowed"
              disabled={registrosNoCanceladosFiltrados.length === 0}
            >
              Descargar Excel
            </button>
          </div>

          <div className="overflow-x-auto max-h-[460px] overflow-y-auto border border-slate-100 rounded-xl">
            <table className="min-w-full text-sm">
              <thead className="bg-slate-50 sticky top-0 z-10">
                <tr className="text-left text-slate-600">
                  <th className="px-4 py-3 font-bold">Nombre completo</th>
                  <th className="px-4 py-3 font-bold">Identificador</th>
                  <th className="px-4 py-3 font-bold">Especialidad</th>
                  <th className="px-4 py-3 font-bold">Estatus</th>
                  <th className="px-4 py-3 font-bold">
                    Reprogramación / Motivo
                  </th>
                  <th className="px-4 py-3 font-bold">CIE10</th>
                  <th className="px-4 py-3 font-bold">CIE9</th>
                  <th className="px-4 py-3 font-bold">Fecha solicitud</th>
                  <th className="px-4 py-3 font-bold">Fecha cancelación</th>
                  <th className="px-4 py-3 font-bold">Fecha programación</th>
                  <th className="px-4 py-3 font-bold text-center">
                    Días naturales desde programación
                  </th>
                </tr>
              </thead>

              <tbody>
                {registrosNoCanceladosFiltrados.length === 0 ? (
                  <tr>
                    <td
                      colSpan="11"
                      className="px-4 py-6 text-center text-slate-400"
                    >
                      No hay registros no cancelados pendientes o sin
                      diferimiento.
                    </td>
                  </tr>
                ) : (
                  registrosNoCanceladosFiltrados.map((r, index) => (
                    <tr
                      key={`${r.identificador || r.paciente}-no-cancelada-${index}`}
                      className="border-t border-slate-100 hover:bg-slate-50"
                    >
                      <td className="px-4 py-3 font-semibold text-slate-700 whitespace-nowrap">
                        {r.paciente}
                      </td>

                      <td className="px-4 py-3 text-slate-500 whitespace-nowrap">
                        {r.identificador || "SIN IDENTIFICADOR"}
                      </td>

                      <td className="px-4 py-3 text-slate-700 font-semibold whitespace-nowrap">
                        {r.especialidad}
                      </td>

                      <td className="px-4 py-3 whitespace-nowrap">
                        <span className="px-2 py-1 rounded-lg bg-amber-50 text-amber-700 text-xs font-bold">
                          {r.estatus}
                        </span>
                      </td>

                      <td className="px-4 py-3 text-slate-600 min-w-[260px]">
                        {r.motivo}
                      </td>

                      <td className="px-4 py-3 text-slate-600 min-w-[220px]">
                        {r.cie10 || "SIN CIE10"}
                      </td>

                      <td className="px-4 py-3 text-slate-600 min-w-[220px]">
                        {r.cie9 || "SIN CIE9"}
                      </td>

                      <td className="px-4 py-3 text-slate-600 whitespace-nowrap">
                        {r.fechaSolicitudTexto}
                      </td>

                      <td className="px-4 py-3 text-slate-600 whitespace-nowrap">
                        {r.fechaCancelacionTexto}
                      </td>

                      <td className="px-4 py-3 text-slate-600 whitespace-nowrap">
                        {r.fechaProgramacionTexto}
                      </td>

                      <td className="px-4 py-3 text-center font-bold text-slate-700 whitespace-nowrap">
                        {r.diasNaturalesProgramacion === null
                          ? "SIN FECHA"
                          : r.diasNaturalesProgramacion}
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>

        {/* ==================== CANCELADAS ==================== */}
        <div>
          <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-3">
            <div>
              <h4 className="font-black text-slate-900 mb-1 flex items-center gap-2">
                <span className="w-2 h-2 rounded-full bg-red-600"></span>
                Cirugías canceladas
              </h4>
            </div>

            <button
              type="button"
              onClick={descargarExcelCanceladas}
              className="px-4 py-2 rounded-xl bg-red-700 text-white text-sm font-bold hover:bg-red-800 active:scale-[0.98] transition disabled:opacity-50 disabled:cursor-not-allowed"
              disabled={registrosCanceladosFiltrados.length === 0}
            >
              Descargar Excel
            </button>
          </div>

          <div className="overflow-x-auto max-h-[460px] overflow-y-auto border border-slate-100 rounded-xl">
            <table className="min-w-full text-sm">
              <thead className="bg-slate-50 sticky top-0 z-10">
                <tr className="text-left text-slate-600">
                  <th className="px-4 py-3 font-bold">Nombre completo</th>
                  <th className="px-4 py-3 font-bold">Identificador</th>
                  <th className="px-4 py-3 font-bold">Especialidad</th>
                  <th className="px-4 py-3 font-bold">Estatus</th>
                  <th className="px-4 py-3 font-bold">Motivo de cancelación</th>
                  <th className="px-4 py-3 font-bold">Fecha solicitud</th>
                  <th className="px-4 py-3 font-bold">Fecha cancelación</th>
                  <th className="px-4 py-3 font-bold">Fecha programación</th>
                </tr>
              </thead>

              <tbody>
                {registrosCanceladosFiltrados.length === 0 ? (
                  <tr>
                    <td
                      colSpan="8"
                      className="px-4 py-6 text-center text-slate-400"
                    >
                      No hay cirugías canceladas registradas.
                    </td>
                  </tr>
                ) : (
                  registrosCanceladosFiltrados.map((r, index) => (
                    <tr
                      key={`${r.identificador || r.paciente}-cancelada-${index}`}
                      className="border-t border-slate-100 hover:bg-red-50"
                    >
                      <td className="px-4 py-3 font-semibold text-slate-700 whitespace-nowrap">
                        {r.paciente}
                      </td>

                      <td className="px-4 py-3 text-slate-500 whitespace-nowrap">
                        {r.identificador || "SIN IDENTIFICADOR"}
                      </td>

                      <td className="px-4 py-3 text-slate-700 font-semibold whitespace-nowrap">
                        {r.especialidad}
                      </td>

                      <td className="px-4 py-3 whitespace-nowrap">
                        <span className="px-2 py-1 rounded-lg bg-red-50 text-red-700 text-xs font-bold">
                          {r.estatus}
                        </span>
                      </td>

                      <td className="px-4 py-3 text-slate-600 min-w-[280px]">
                        {r.motivo}
                      </td>

                      <td className="px-4 py-3 text-slate-600 whitespace-nowrap">
                        {r.fechaSolicitudTexto}
                      </td>

                      <td className="px-4 py-3 text-slate-600 whitespace-nowrap">
                        {r.fechaCancelacionTexto}
                      </td>

                      <td className="px-4 py-3 text-slate-600 whitespace-nowrap">
                        {r.fechaProgramacionTexto}
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div className="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <h3 className="font-bold text-lg mb-3 flex gap-2 items-center text-slate-900">
          <FileQuestion size={20} className="text-red-700" />
          ¿Qué pasó con las cirugías que no se completaron?
        </h3>

        <div className="grid xl:grid-cols-2 gap-6">
          <div>
            <h4 className="font-semibold mb-2 text-slate-900">
              Motivos de Cancelación
            </h4>

            <div className="h-[480px]">
              <Bar data={chartMotivosCancelacion} options={horizontalOptions} />
            </div>
          </div>

          <div>
            <h4 className="font-semibold mb-2 text-slate-900">
              Último Motivo de Suspensión
            </h4>

            <div className="h-[480px]">
              <Bar data={chartMotivosSuspension} options={horizontalOptions} />
            </div>
          </div>
        </div>

        <p className="text-xs text-slate-400 mt-4 text-center">
          * Se muestran los motivos registrados en cirugías canceladas o con
          suspensión.
        </p>
      </div>

      <div className="grid xl:grid-cols-2 gap-6">
        <Card
          titulo="Top Diagnósticos CIE10"
          icono={Activity}
          color={COLORS.info}
        >
          <div className="h-[480px]">
            <Bar data={topCIE10} options={horizontalOptions} />
          </div>
        </Card>

        <Card
          titulo="Top Diagnósticos CIE9"
          icono={Activity}
          color={COLORS.dark}
        >
          <div className="h-[480px]">
            <Bar data={topCIE9} options={horizontalOptions} />
          </div>
        </Card>
      </div>

      <div className="grid grid-cols-1 gap-6">
        <Card
          titulo="Utilización de Salas"
          icono={DoorOpen}
          color={COLORS.success}
        >
          <div className="h-[350px]">
            <Bar data={topSalas} options={horizontalOptions} />
          </div>
        </Card>
      </div>
    </div>
  );
}

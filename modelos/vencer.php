<?php
// ARCHIVO: modelos/Vencer.php
require_once __DIR__ . '/conexion.php';

class Vencer {
    private $conexion;
    // Propiedades
    public $id, $folio, $evento, $ini_paciente, $seguridad_social, $edad, $sexo;
    public $diagnostico, $fecha_evento, $fecha_noti, $turno, $servicio, $categoria;
    public $proceso, $definicion, $descripcion, $estatus, $anio;

    public function __construct() {
        $this->conexion = new Conexion();
        
        // --- CORRECCIÓN 1: FORZAR UTF-8 ---
        $conn = $this->conexion->getConexion();
        
        // Detectamos si es MySQLi o PDO y aplicamos UTF-8
        if (method_exists($conn, 'set_charset')) {
            $conn->set_charset("utf8"); 
        } elseif (method_exists($conn, 'exec')) {
            $conn->exec("SET NAMES 'utf8'"); 
        }
    }

    // LISTAR
public static function listar() {
        $instancia = new self();
        $conexion = $instancia->conexion;

        // 1. Configurar caracteres
        try { $conexion->consultar("SET NAMES 'utf8mb4'"); } catch(Throwable $e){}

        // 2. Consulta SQL
        $sql = "SELECT * FROM vencer ORDER BY id DESC";
        $consulta = $conexion->consultar($sql);

        $result = [];

        if ($consulta) {
            foreach ($consulta as $fila) {
                // Convertimos a array por si el wrapper devuelve objetos
                $r = (array)$fila;

                // DETECCIÓN INTELIGENTE:
                // Si la fila tiene claves numéricas (0, 1, 2...), las convertimos a nombres.
                // Si ya tiene nombres ('folio', 'evento'...), la dejamos pasar.
                if (isset($r[0])) {
                    $result[] = [
                        'id'               => $r[0] ?? '',
                        'folio'            => $r[1] ?? '',
                        'evento'           => $r[2] ?? '',
                        'ini_paciente'     => $r[3] ?? '',
                        'seguridad_social' => $r[4] ?? '',
                        'edad'             => $r[5] ?? '',
                        'sexo'             => $r[6] ?? '',
                        'diagnostico'      => $r[7] ?? '',
                        'fecha_evento'     => $r[8] ?? '',
                        'fecha_noti'       => $r[9] ?? '',
                        'turno'            => $r[10] ?? '',
                        'servicio'         => $r[11] ?? '',
                        'categoria'        => $r[12] ?? '',
                        'proceso'          => $r[13] ?? '',
                        'definicion'       => $r[14] ?? '',
                        'descripcion'      => $r[15] ?? '',
                        'estatus'          => $r[16] ?? '',
                        'anio'             => $r[17] ?? ''
                    ];
                } else {
                    // Ya viene con nombres (ej: 'folio'), lo usamos directo
                    $result[] = $r;
                }
            }
        }

        return $result;
    }

    // CARGAR DESDE EXCEL
    public function cargarDesdeCSV($datos) {
        $this->folio            = trim($datos[0] ?? '');
        $this->evento           = trim($datos[1] ?? '');
        $this->ini_paciente     = trim($datos[2] ?? '');
        $this->seguridad_social = trim($datos[3] ?? '');
        
        // --- CORRECCIÓN 2: CLASIFICAR EDAD POR RANGOS ---
        $edadOriginal           = trim($datos[4] ?? '');
        $this->edad             = $this->clasificarEdad($edadOriginal); 

        $this->sexo             = trim($datos[5] ?? '');
        $this->diagnostico      = trim($datos[6] ?? '');
        $this->fecha_evento     = $this->normalizarFecha($datos[7] ?? '');
        $this->fecha_noti       = $this->normalizarFecha($datos[8] ?? '');
        $this->turno            = trim($datos[9] ?? '');
        $this->servicio         = trim($datos[10] ?? '');
        $this->categoria        = trim($datos[11] ?? '');
        $this->proceso          = trim($datos[12] ?? '');
        $this->definicion       = trim($datos[13] ?? '');
        $this->descripcion      = trim($datos[14] ?? '');
        $this->estatus          = trim($datos[15] ?? '');
        
        // --- CORRECCIÓN 3: EL EXTRACTOR DE AÑOS BLINDADO ---
        $anioBruto = trim($datos[16] ?? '');
        
        // 1. Le quitamos todas las comillas, espacios y letras que Excel le haya pegado.
        // Solo dejamos los números puros.
        $anioLimpio = preg_replace('/[^0-9]/', '', $anioBruto);
        $this->anio = (int)$anioLimpio;

        // 2. EL SALVAVIDAS: Si a pesar de todo el año es 0, o la columna venía vacía en el Excel...
        // ¡Lo sacamos automáticamente de la fecha del evento!
        if ($this->anio === 0 && !empty($this->fecha_evento)) {
            // Como tu función normalizarFecha ya lo dejó como YYYY-MM-DD, 
            // solo tomamos los primeros 4 caracteres.
            $this->anio = (int)substr($this->fecha_evento, 0, 4);
        }
    }

    // --- NUEVA LÓGICA DE RANGOS DE EDAD ---
    private function clasificarEdad($edad) {
        // Si viene vacío, se queda vacío
        if ($edad === '' || $edad === null) return '';

        // Intentamos convertir a número (quita texto como " años")
        $valor = intval($edad);

        if ($valor < 1) return "<1";
        if ($valor >= 1 && $valor <= 4)   return "1 a 4";
        if ($valor >= 5 && $valor <= 9)   return "5 a 9";
        if ($valor >= 10 && $valor <= 14) return "10 a 14";
        if ($valor >= 15 && $valor <= 19) return "15 a 19";
        if ($valor >= 20 && $valor <= 29) return "20 a 29";
        if ($valor >= 30 && $valor <= 39) return "30 a 39";
        if ($valor >= 40 && $valor <= 49) return "40 a 49";
        if ($valor >= 50 && $valor <= 59) return "50 a 59";
        if ($valor >= 60) return ">60";
        
        return $edad; // Por si acaso no cae en ningún rango (ej: negativo)
    }

    public function ingresar2() {
        if ($this->existeDuplicado()) {
            return $this->actualizar() ? 'actualizado' : 'colision_sin_cambios';
        } else {
            return $this->insertar() ? 'insertado' : 'error';
        }
    }

    private function existeDuplicado() {
        $sql = "SELECT COUNT(*) as total FROM vencer WHERE folio = " . $this->valorSQL($this->folio) . 
               " AND evento = " . $this->valorSQL($this->evento) . " AND anio = " . intval($this->anio);
        $res = $this->conexion->consultarUnaFila($sql);
        return ($res['total'] ?? 0) > 0;
    }

    private function insertar() {
        $campos = ['folio','evento','ini_paciente','seguridad_social','edad','sexo','diagnostico','fecha_evento','fecha_noti','turno','servicio','categoria','proceso','definicion','descripcion','estatus','anio'];
        $vals = [];
        foreach($campos as $c) $vals[] = $this->valorSQL($this->$c);
        $sql = "INSERT INTO vencer (".implode(',',$campos).") VALUES (".implode(',',$vals).")";
        return $this->conexion->actualizar($sql);
    }

    private function actualizar() {
        $sql = "UPDATE vencer SET ini_paciente = IF(ini_paciente IS NULL OR ini_paciente='', " . $this->valorSQL($this->ini_paciente) . ", ini_paciente) 
                WHERE folio = " . $this->valorSQL($this->folio) . " AND evento = " . $this->valorSQL($this->evento) . " AND anio = " . intval($this->anio);
        return $this->conexion->actualizar($sql);
    }

    public function valorSQL($v) {
        if ($v === null) return 'NULL';
        $c = $this->conexion->getConexion();
        if (method_exists($c, 'quote')) return $c->quote($v); 
        if (method_exists($c, 'real_escape_string')) return "'".$c->real_escape_string($v)."'"; 
        return "'".addslashes($v)."'";
    }

    private function normalizarFecha($f) {
        $f = trim($f);
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $f, $m)) return "$m[3]-$m[2]-$m[1]";
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $f, $m)) return "$m[3]-$m[2]-$m[1]";
        return $f;
    }

    public function eliminar() {
        $sql = "DELETE FROM vencer WHERE id = " . intval($this->id);
        return $this->conexion->actualizar($sql);
    }
}
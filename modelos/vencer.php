<?php
require_once 'conexion.php';

class Vencer
{
    private $conexion;

    public $id, $folio, $evento, $ini_paciente, $seguridad_social, $edad, $sexo;
    public $diagnostico, $fecha_evento, $fecha_noti, $turno, $servicio, $categoria;
    public $proceso, $definicion, $descripcion, $estatus, $anio;

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

   public static function listar()
{
    $conexion = new Conexion();
    $consulta = $conexion->consultar('SELECT * FROM vencer ORDER BY id DESC');
    $conexion->cerrar();

    $result = [];
    foreach ($consulta as $fila) {
        $result[] = [
            'id' => $fila[0],
            'folio' => $fila[1],
            'evento' => $fila[2],
            'ini_paciente' => $fila[3],
            'seguridad_social' => $fila[4],
            'edad' => $fila[5],
            'sexo' => $fila[6],
            'diagnostico' => $fila[7],
            'fecha_evento' => $fila[8],
            'fecha_noti' => $fila[9],
            'turno' => $fila[10],
            'servicio' => $fila[11],
            'categoria' => $fila[12],
            'proceso' => $fila[13],
            'definicion' => $fila[14],
            'descripcion' => $fila[15],
            'estatus' => $fila[16],
            'anio' => $fila[17]
        ];
    }
    return $result;
}


    public function cargarDesdeFormulario($data)
    {
        foreach ($data as $k => $v) {
            $this->$k = $v ?? null;
        }
    }

    public function valorSQL($valor)
    {
        if ($valor === null || trim($valor) === '') {
            return "NULL";
        } elseif (is_numeric($valor)) {
            return $valor;
        } else {
            return "'" . $this->conexion->getConexion()->real_escape_string($valor) . "'";
        }
    }

    public function folioEventoAnioExiste($folio, $evento, $anio)
    {
        $folio = $this->valorSQL($folio);
        $evento = $this->valorSQL($evento);
        $anio = intval($anio);
        $sql = "SELECT COUNT(*) as total FROM vencer WHERE folio = $folio AND evento = $evento AND anio = $anio";
        $res = $this->conexion->consultarUnaFila($sql);
        return $res['total'] > 0;
    }

    public function ingresar()
    {
        if ($this->folioEventoAnioExiste($this->folio, $this->evento, $this->anio)) {
            return $this->actualizarCamposVacios() ? 'actualizado' : 'colision_sin_cambios';
        } else {
            return $this->insertarNuevo() ? 'insertado' : 'error';
        }
    }

    public function actualizarCamposVacios()
    {
        $campos = [
            'ini_paciente', 'seguridad_social', 'edad', 'sexo',
            'diagnostico', 'fecha_evento', 'fecha_noti', 'turno',
            'servicio', 'categoria', 'proceso', 'definicion',
            'descripcion', 'estatus'
        ];

        $updates = [];

        foreach ($campos as $campo) {
            $valor = $this->valorSQL($this->$campo);
            if ($valor !== "NULL" && $valor != 0) {
                $updates[] = "$campo = IF($campo IS NULL OR $campo = '' OR $campo = 0, $valor, $campo)";
            }
        }

        if (empty($updates)) return false;

        $sql = "UPDATE vencer SET " . implode(', ', $updates) .
            " WHERE folio = '" . $this->folio . "' AND evento = '" . $this->evento . "' AND anio = " . intval($this->anio);

        $mysqli = $this->conexion->getConexion();
        $resultado = $mysqli->query($sql);

        return $resultado && $mysqli->affected_rows > 0;
    }

    public function insertarNuevo()
    {
        $campos = [];
        $valores = [];

        $atributos = [
            'folio', 'evento', 'ini_paciente', 'seguridad_social', 'edad', 'sexo',
            'diagnostico', 'fecha_evento', 'fecha_noti', 'turno', 'servicio',
            'categoria', 'proceso', 'definicion', 'descripcion', 'estatus', 'anio'
        ];

        foreach ($atributos as $campo) {
            $campos[] = $campo;
            $valores[] = $this->valorSQL($this->$campo);
        }

        $sql = "INSERT INTO vencer (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $valores) . ")";
        return $this->conexion->actualizar($sql);
    }

    public function eliminar()
    {
        $sql = "DELETE FROM vencer WHERE id = " . intval($this->id);
        return $this->conexion->actualizar($sql);
    }

    public static function obtenerPorId($id)
{
    $conexion = new Conexion();
    $sql = "SELECT * FROM vencer WHERE id = " . intval($id) . " LIMIT 1";
    $fila = $conexion->consultarUnaFila($sql);
    $conexion->cerrar();
    return $fila;
}

public function cargarDesdeFormulario2($data)
{
    $this->id = base64_decode($data['id']);

    $campos = [
        'folio', 'evento', 'ini_paciente', 'seguridad_social', 'edad', 'sexo',
        'diagnostico', 'fecha_evento', 'fecha_noti', 'turno', 'servicio',
        'categoria', 'proceso', 'definicion', 'descripcion', 'estatus', 'anio'
    ];

    foreach ($campos as $campo) {
        $this->$campo = $data[$campo] ?? null;
    }
}


public function editar()
{
    $mysqli = $this->conexion->getConexion();

    $campos = [
        'folio', 'evento', 'ini_paciente', 'seguridad_social', 'edad', 'sexo',
        'diagnostico', 'fecha_evento', 'fecha_noti', 'turno', 'servicio',
        'categoria', 'proceso', 'definicion', 'descripcion', 'estatus', 'anio'
    ];

    $sql = "UPDATE vencer SET ";

    foreach ($campos as $campo) {
        $valor = trim($this->$campo);
        if ($valor === '') {
            $sql .= "$campo = NULL, ";
        } else {
            $sql .= "$campo = '" . $mysqli->real_escape_string($valor) . "', ";
        }
    }

    $sql = rtrim($sql, ', ') . " WHERE id = " . intval($this->id);

    return $this->conexion->actualizar($sql);
}

public function cargarDesdeCSV($datos)
{
    $this->folio = trim($datos[0]);
    $this->evento = trim($datos[1]);
    $this->ini_paciente = trim($datos[2]);
    $this->seguridad_social = trim($datos[3]);
    $this->edad = $this->clasificarEdad($datos[4]);
    $this->sexo = trim($datos[5]);
    $this->diagnostico = trim($datos[6]);
    $this->fecha_evento = $this->normalizarFecha($datos[7]);
    $this->fecha_noti = $this->normalizarFecha($datos[8]);
    $this->turno = trim($datos[9]);
    $this->servicio = trim($datos[10]);
    $this->categoria = trim($datos[11]);
    $this->proceso = trim($datos[12]);
    $this->definicion = trim($datos[13]);
    $this->descripcion = trim($datos[14]);
    $this->estatus = trim($datos[15]);
    $this->anio = (int) $datos[16];
}


public function ingresar2()
{
    if ($this->claveCompuestaExiste($this->folio, $this->evento, $this->anio)) {
        return $this->actualizarCamposVacios2() ? 'actualizado' : 'colision_sin_cambios';
    } else {
        return $this->insertarNuevo2() ? 'insertado' : 'error';
    }
}

public function claveCompuestaExiste($folio, $evento, $anio)
{
    $folio = $this->valorSQL2($folio);
    $evento = $this->valorSQL2($evento);
    $anio = intval($anio);
    $sql = "SELECT COUNT(*) as total FROM vencer WHERE folio = $folio AND evento = $evento AND anio = $anio";
    $res = $this->conexion->consultarUnaFila($sql);
    return $res['total'] > 0;
}

public function actualizarCamposVacios2()
{
    // Paso 1: Obtener el registro actual en la BD para comparar
    $sqlSelect = "SELECT * FROM vencer WHERE folio = '" . $this->folio . "' AND evento = '" . $this->evento . "' AND anio = " . intval($this->anio);
    $registroActual = $this->conexion->consultarUnaFila($sqlSelect);

    if (!$registroActual) return false; // No existe registro, no hay qué actualizar

    // Paso 2: Verificar qué campos están vacíos y pueden ser actualizados
    $columnas = [
        'ini_paciente', 'seguridad_social', 'edad', 'sexo', 'diagnostico',
        'fecha_evento', 'fecha_noti', 'turno', 'servicio', 'categoria',
        'proceso', 'definicion', 'descripcion', 'estatus'
    ];

    $camposUpdate = [];

    foreach ($columnas as $campo) {
        $valorNuevo = $this->$campo;
        $valorBD = $registroActual[$campo];

        $estaVacio = is_null($valorBD) || $valorBD === '' || $valorBD === '0' || $valorBD === 0;

        if ($estaVacio && $valorNuevo !== null && $valorNuevo !== '' && $valorNuevo !== '0') {
            $camposUpdate[] = "$campo = " . $this->valorSQL2($valorNuevo);
        }
    }

    // Paso 3: Si no hay nada que actualizar, salimos
    if (empty($camposUpdate)) return false;

    // Paso 4: Ejecutar UPDATE solo con los campos necesarios
    $sql = "UPDATE vencer SET " . implode(', ', $camposUpdate) .
           " WHERE folio = '" . $this->folio . "' AND evento = '" . $this->evento . "' AND anio = " . intval($this->anio);

    $mysqli = $this->conexion->getConexion();
    $resultado = $mysqli->query($sql);

    return $resultado && $mysqli->affected_rows > 0;
}


public function insertarNuevo2()
{
    if (empty($this->folio) || empty($this->evento) || empty($this->anio)) {
    return false;
}
    $campos = ['folio', 'evento', 'ini_paciente', 'seguridad_social', 'edad', 'sexo', 'diagnostico', 'fecha_evento', 'fecha_noti', 'turno', 'servicio', 'categoria', 'proceso', 'definicion', 'descripcion', 'estatus', 'anio'];
    $valores = array_map(fn($c) => $this->valorSQL2($this->$c), $campos);

    $sql = "INSERT INTO vencer (" . implode(',', $campos) . ") VALUES (" . implode(',', $valores) . ")";
    return $this->conexion->actualizar($sql);
}

public function valorSQL2($valor)
{
    if ($valor === null || trim($valor) === '') return "NULL";
    elseif (is_numeric($valor)) return $valor;
    else return "'" . $this->conexion->getConexion()->real_escape_string($valor) . "'";
}

public function clasificarEdad($texto)
{
    $texto = strtolower(trim($texto));

    preg_match('/(\d+)\s*años?(?:\s*y\s*(\d+)\s*meses?)?/i', $texto, $matches);

    $anios = isset($matches[1]) ? (int)$matches[1] : 0;
    $meses = isset($matches[2]) ? (int)$matches[2] : 0;

    $edadDecimal = $anios + ($meses / 12);

    if ($edadDecimal < 1) return 'Menor a 1 año';
    elseif ($edadDecimal < 5) return '1 a 4';
    elseif ($edadDecimal < 10) return '5 a 9';
    elseif ($edadDecimal < 15) return '10 a 14';
    elseif ($edadDecimal < 20) return '15 a 19';
    elseif ($edadDecimal < 30) return '20 a 29';
    elseif ($edadDecimal < 40) return '30 a 39';
    elseif ($edadDecimal < 50) return '40 a 49';
    elseif ($edadDecimal < 60) return '50 a 59';
    else return '60 o más';
}

private function normalizarFecha($fecha)
{
    $fecha = trim($fecha);
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $fecha, $m)) {
        // dd/mm/yyyy -> yyyy-mm-dd
        return "{$m[3]}-{$m[2]}-{$m[1]}";
    } elseif (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $fecha, $m)) {
        // dd-mm-yyyy -> yyyy-mm-dd
        return "{$m[3]}-{$m[2]}-{$m[1]}";
    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        // ya está en formato correcto
        return $fecha;
    } else {
        return null; // inválido
    }
}

public function editarSinID()
{
    $mysqli = $this->conexion->getConexion();
    $campos = [];

    $columnas = [
        'ini_paciente', 'seguridad_social', 'edad', 'sexo',
        'diagnostico', 'fecha_evento', 'fecha_noti', 'turno',
        'servicio', 'categoria', 'proceso', 'definicion', 'descripcion', 'estatus'
    ];

    foreach ($columnas as $columna) {
        $valor = $this->{$columna};
        if ($valor !== null && $valor !== '' && $valor !== '0' && $valor !== 0) {
            $valorEsc = is_string($valor)
                ? "'" . mysqli_real_escape_string($mysqli, $valor) . "'"
                : intval($valor);
            $campos[] = "$columna = $valorEsc";
        }
    }

    if (empty($campos)) return false;

    $sql = "UPDATE vencer SET " . implode(", ", $campos) . "
            WHERE folio = '" . mysqli_real_escape_string($mysqli, $this->folio) . "'
              AND evento = '" . mysqli_real_escape_string($mysqli, $this->evento) . "'
              AND anio = " . intval($this->anio);

    return $this->conexion->actualizar($sql);
}

public static function obtenerPorClave($anio, $folio, $evento) {
    require_once 'conexion.php'; // Asegúrate de que esté si el archivo puede usarse de forma aislada
    $db = new Conexion(); // Se crea una instancia local
    $query = "SELECT * FROM vencer WHERE anio = ? AND folio = ? AND evento = ?";
    $stmt = $db->getConexion()->prepare($query);
    $stmt->bind_param('iss', $anio, $folio, $evento);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $registro = $resultado->fetch_assoc();
    $db->cerrar();

    if ($registro) {
        $v = new Vencer();
        foreach ($registro as $campo => $valor) {
            $v->$campo = $valor;
        }
        return $v;
    }
    return null;
}


public function difiereDe($otro) {
    foreach ($this as $campo => $valor) {
        // Ignora campos nulos o sin comparación
        if (!property_exists($otro, $campo)) continue;

        // Ignora campos que no se deberían comparar (como id)
        if (in_array($campo, ['id'])) continue;

        if (trim((string)$valor) !== trim((string)$otro->$campo)) {
            return true; // Al menos un campo es diferente
        }
    }
    return false;
}



}

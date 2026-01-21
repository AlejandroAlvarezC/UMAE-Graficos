<?php
require_once 'conexion.php';

class Especialidad_Ocasion
{
    private $conexion;
    public $id;
    public $clave;
    public $especialidad;
    public $descripcion;
    public $anio;
    public $ene_1era;

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

    public static function listar()
    {
        $conexion = new Conexion();
        $consulta = $conexion->consultar('SELECT * FROM especialidades');
        $conexion->cerrar();

        $result = [];
        foreach ($consulta as $fila) {
           $result[] = [
    'id' => $fila[0],
    'clave' => $fila[1],
    'especialidad' => $fila[2],
    'descripcion' => $fila[3],
    'ene_1era' => $fila[4],
    'ene_sub' => $fila[5],
    'feb_1era' => $fila[6],
    'feb_sub' => $fila[7],
    'mar_1era' => $fila[8],
    'mar_sub' => $fila[9],
    'abr_1era' => $fila[10],
    'abr_sub' => $fila[11],
    'may_1era' => $fila[12],
    'may_sub' => $fila[13],
    'jun_1era' => $fila[14],
    'jun_sub' => $fila[15],
    'jul_1era' => $fila[16],
    'jul_sub' => $fila[17],
    'ago_1era' => $fila[18],
    'ago_sub' => $fila[19],
    'sep_1era' => $fila[20],
    'sep_sub' => $fila[21],
    'oct_1era' => $fila[22],
    'oct_sub' => $fila[23],
    'nov_1era' => $fila[24],
    'nov_sub' => $fila[25],
    'dic_1era' => $fila[26],
    'dic_sub' => $fila[27],
    'anio' => $fila[28]
];
        }
        return $result;
    }

    public function cargarDesdeFormulario($data)
{
    $this->clave = $data['clave'];
    $this->especialidad = $data['especialidad'];
    $this->descripcion = $data['descripcion'];
    $this->anio = $data['anio'];
    $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    foreach ($meses as $m) {
        $this->{$m . '_1era'} = $data[$m . '_1era'] ?? null;
        $this->{$m . '_sub'} = $data[$m . '_sub'] ?? null;
    }
}


   public function cargarDesdeFormulario2($data)
{
    $this->id = base64_decode($data['id']);
    $this->clave = $data['clave'];
    $this->especialidad = $data['especialidad'];
    $this->descripcion = $data['descripcion'];
    $this->anio = $data['anio'];
    $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    foreach ($meses as $m) {
        $this->{$m . '_1era'} = $data[$m . '_1era'] ?? null;
        $this->{$m . '_sub'} = $data[$m . '_sub'] ?? null;
    }
}


    public function ingresar()
{
    if ($this->claveExiste($this->clave, $this->anio)) {
        return $this->actualizarCamposVacios();
    } else {
        return $this->insertarNuevo();
    }
}


    public function editar()
{
    $mysqli = $this->conexion->getConexion();

    $sql = "UPDATE especialidades SET 
         clave = '" . mysqli_real_escape_string($mysqli, $this->clave) . "',
        especialidad = '" . mysqli_real_escape_string($mysqli, $this->especialidad) . "',
        descripcion = '" . mysqli_real_escape_string($mysqli, $this->descripcion) . "', ";

    $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    foreach ($meses as $m) {
        $value1 = trim($this->{$m . '_1era'});
        $value2 = trim($this->{$m . '_sub'});

        $sql .= "{$m}_1era = " . ($value1 === '' ? "NULL" : intval($value1)) . ", ";
        $sql .= "{$m}_sub = " . ($value2 === '' ? "NULL" : intval($value2)) . ", ";
    }

    $sql .= "anio = " . intval($this->anio) . " WHERE id = " . intval($this->id);

    return $this->conexion->actualizar($sql);
}


  public function eliminar()
{
    $sql = "DELETE FROM especialidades WHERE id = " . intval($this->id);
    return $this->conexion->actualizar($sql);
}

public static function obtenerPorId($id)
{
    $conexion = new Conexion();
    $idEscapado = intval($id); // como es numérico, usamos intval
    $listado = $conexion->consultar("SELECT * FROM especialidades WHERE id = $idEscapado");
    $conexion->cerrar();
    return (!empty($listado) && isset($listado[0])) ? $listado[0] : null;
}


     public function consultar($sql)
    {
        return $this->conexion->consultar($sql);
    }

    public function actualizar($sql)
    {
        return $this->conexion->actualizar($sql);
    }

public function claveExiste($clave, $anio)
{
    $clave = $this->valorSQL($clave);
    $anio = $this->valorSQL($anio);
    $sql = "SELECT COUNT(*) as total FROM especialidades WHERE clave = $clave AND anio = $anio";
    $res = $this->conexion->consultarUnaFila($sql);
    return $res['total'] > 0;
}




    public function insertarNuevo()
    {
        $campos = ["clave", "especialidad", "descripcion"];
        $valores = [
            $this->valorSQL($this->clave),
            $this->valorSQL($this->especialidad),
            $this->valorSQL($this->descripcion)
        ];

        $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        foreach ($meses as $m) {
            $campos[] = "{$m}_1era";
            $valores[] = $this->valorSQL($this->{$m . '_1era'});
            $campos[] = "{$m}_sub";
            $valores[] = $this->valorSQL($this->{$m . '_sub'});
        }

        $campos[] = "anio";
        $valores[] = $this->valorSQL($this->anio);

        $sql = "INSERT INTO especialidades (" . implode(', ', $campos) . ")
        VALUES (" . implode(', ', $valores) . ")";


        return $this->conexion->actualizar($sql);
    }


    public function valorSQL($valor)
    {
        if ($valor === null || trim($valor) === '') {
            return "NULL"; // <-- devuelve el string SQL "NULL"
        } elseif (is_numeric($valor)) {
            return $valor; // número directo sin comillas
        } else {
            return "'" . $this->conexion->getConexion()->real_escape_string($valor) . "'";
        }
    }


  public function registroCompleto($clave, $anio)
{
    $mysqli = $this->conexion->getConexion();
    $sql = "SELECT * FROM especialidades WHERE clave = '" . mysqli_real_escape_string($mysqli, $clave) . "' AND anio = " . intval($anio);
    $resultado = $mysqli->query($sql);

    if ($fila = $resultado->fetch_assoc()) {
        foreach ($fila as $campo => $valor) {
            if ($campo == 'id' || $campo == 'clave') continue;
            if (is_null($valor) || $valor === '' || $valor === '0') {
                return false;
            }
        }
        return true;
    }
    return false;
}


   public function ingresar2()
{
    if ($this->claveExiste($this->clave, $this->anio)) {
        if ($this->registroCompleto($this->clave, $this->anio)) {
            return [
                'status' => 'info',
                'message' => "No se hizo ningún cambio. Ya existe un registro completo con esa clave y año."
            ];
        } else {
            $actualizado = $this->actualizarCamposVacios2();
            if ($actualizado) {
                return [
                    'status' => 'success',
                    'message' => "Se actualizaron correctamente los campos vacíos del registro existente."
                ];
            } else {
                return [
                    'status' => 'info',
                    'message' => "No se hizo ningún cambio. Los datos proporcionados ya estaban completos o no requerían actualización."
                ];
            }
        }
    } else {
        $this->insertarNuevo();
        return [
            'status' => 'success',
            'message' => "Registro ingresado correctamente."
        ];
    }
}


 public function actualizarCamposVacios2()
{
    $mysqli = $this->conexion->getConexion();
    $clave = mysqli_real_escape_string($mysqli, $this->clave);
    $anio = intval($this->anio);

    $sql = "SELECT * FROM especialidades WHERE clave = '$clave' AND anio = $anio";
    $resultado = $mysqli->query($sql);

    if (!$fila = $resultado->fetch_assoc()) {
        return false;
    }

    $camposActualizables = ['especialidad', 'descripcion'];
    $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    foreach ($meses as $m) {
        $camposActualizables[] = "{$m}_1era";
        $camposActualizables[] = "{$m}_sub";
    }

    $updates = [];
    foreach ($camposActualizables as $campo) {
        $valorActual = $fila[$campo];
        $valorNuevo = $this->{$campo};

        if ((is_null($valorActual) || $valorActual === '' || $valorActual === '0') &&
            $valorNuevo !== null && $valorNuevo !== '' && $valorNuevo !== 0
        ) {
            $updates[] = "$campo = '" . mysqli_real_escape_string($mysqli, $valorNuevo) . "'";
        }
    }

    if (empty($updates)) {
        return false;
    }

    $sqlUpdate = "UPDATE especialidades SET " . implode(', ', $updates) .
        " WHERE clave = '$clave' AND anio = $anio";

    return $this->conexion->actualizar($sqlUpdate);
}


public static function obtenerPorClaveYAnio($clave, $anio)
{
    $conexion = new Conexion();
    $claveEsc = $conexion->getConexion()->real_escape_string($clave);
    $anioInt = intval($anio);

    $resultado = $conexion->consultar("SELECT * FROM especialidades WHERE clave = '$claveEsc' AND anio = $anioInt");
    $conexion->cerrar();

    return (!empty($resultado) && isset($resultado[0])) ? $resultado[0] : null;
}



    public function cargarDesdeCSV($datos)
    {
        $this->clave = mb_convert_encoding($datos[0], 'UTF-8', 'auto');
        $this->especialidad = mb_convert_encoding($datos[1], 'UTF-8', 'auto');
        $this->descripcion = mb_convert_encoding($datos[2], 'UTF-8', 'auto');

        $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        $i = 3;
        foreach ($meses as $m) {
            $this->{$m . '_1era'} = (int)$datos[$i++];
            $this->{$m . '_sub'} = (int)$datos[$i++];
        }
        $this->anio = (int)$datos[$i];
    }


    public function vaciarTabla()
    {
        return $this->conexion->actualizar("TRUNCATE TABLE especialidades");
    }


    public static function contarEspecialidadOcasion()
    {
        $conexion = new conexion();

        $consulta = $conexion->consultarUnaFila('SELECT COUNT(*) AS total FROM especialidades');


        if (is_array($consulta) && isset($consulta['total'])) {
            return $consulta['total'];
        }

        return 0;
    }

    public function claveAnioExiste($clave, $anio)
{
    $clave = $this->valorSQL($clave);
    $anio = intval($anio);
    $sql = "SELECT COUNT(*) as total FROM especialidades WHERE clave = $clave AND anio = $anio";
    $res = $this->conexion->consultarUnaFila($sql);
    return $res['total'] > 0;
}

      public function actualizarCamposVacios()
    {
        $camposUpdate = [];

        // Estos 3 primeros campos
        foreach (['especialidad', 'descripcion', 'anio'] as $campo) {
            $nuevoValor = $this->valorSQL($this->$campo);
            if ($nuevoValor !== "NULL") {
                $camposUpdate[] = "$campo = IF($campo IS NULL OR $campo = '', $nuevoValor, $campo)";
            }
        }

        // Campos de los meses
        $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        foreach ($meses as $m) {
            foreach (['_1era', '_sub'] as $sufijo) {
                $campo = $m . $sufijo;
                $valor = $this->valorSQL($this->{$campo});
                if ($valor !== "NULL" && $valor != 0) {
                    $camposUpdate[] = "$campo = IF($campo IS NULL OR $campo = 0, $valor, $campo)";
                }
            }
        }

        if (empty($camposUpdate)) {
            echo "<p style='color:gray;'>Nada que actualizar.</p>";
            return false; // No hay nada útil
        }

      $sql = "UPDATE especialidades SET " . implode(", ", $camposUpdate) .
    " WHERE clave = '" . $this->clave . "' AND anio = " . intval($this->anio);

        echo "<pre>$sql</pre>"; // opcional: para debug
        return $this->conexion->actualizar($sql);
    }


public static function obtenerTotalesPorMes()
{
    $conexion = new Conexion();
    $consulta = $conexion->consultar("SELECT 
        anio,
        COALESCE(SUM(ene_1era + ene_sub), 0) AS enero,
        COALESCE(SUM(feb_1era + feb_sub), 0) AS febrero,
        COALESCE(SUM(mar_1era + mar_sub), 0) AS marzo,
        COALESCE(SUM(abr_1era + abr_sub), 0) AS abril,
        COALESCE(SUM(may_1era + may_sub), 0) AS mayo,
        COALESCE(SUM(jun_1era + jun_sub), 0) AS junio,
        COALESCE(SUM(jul_1era + jul_sub), 0) AS julio,
        COALESCE(SUM(ago_1era + ago_sub), 0) AS agosto,
        COALESCE(SUM(sep_1era + sep_sub), 0) AS septiembre,
        COALESCE(SUM(oct_1era + oct_sub), 0) AS octubre,
        COALESCE(SUM(nov_1era + nov_sub), 0) AS noviembre,
        COALESCE(SUM(dic_1era + dic_sub), 0) AS diciembre
    FROM especialidades
    GROUP BY anio
    ORDER BY anio");
    $conexion->cerrar();

    return $consulta;
}



}

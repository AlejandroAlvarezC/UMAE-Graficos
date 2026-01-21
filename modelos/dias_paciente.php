<?php
require_once 'conexion.php';

class Paciente
{
    private $conexion;
    public $id;
    public $clave;
    public $especialidad;
    public $division;
    public $anio;

    // Campos de meses
    public $enero;
    public $febrero;
    public $marzo;
    public $abril;
    public $mayo;
    public $junio;
    public $julio;
    public $agosto;
    public $septiembre;
    public $octubre;
    public $noviembre;
    public $diciembre;

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

    public static function listar()
    {
        $conexion = new Conexion();
        $consulta = $conexion->consultar('SELECT * FROM paciente');
        $conexion->cerrar();

        $result = [];
        foreach ($consulta as $fila) {
            $result[] = [
                'id' => $fila[0],
                'clave' => $fila[1],
                'especialidad' => $fila[2],
                'division' => $fila[3],
                'enero' => $fila[4],
                'febrero' => $fila[5],
                'marzo' => $fila[6],
                'abril' => $fila[7],
                'mayo' => $fila[8],
                'junio' => $fila[9],
                'julio' => $fila[10],
                'agosto' => $fila[11],
                'septiembre' => $fila[12],
                'octubre' => $fila[13],
                'noviembre' => $fila[14],
                'diciembre' => $fila[15],
                'anio' => $fila[16]
            ];
        }
        return $result;
    }

    public function cargarDesdeFormulario($data)
    {
        $this->clave = $data['clave'] ?? null;
        $this->especialidad = $data['especialidad'] ?? null;
        $this->division = $data['division'] ?? null;
        $this->anio = $data['anio'] ?? null;

        // Meses
        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        foreach ($meses as $mes) {
            $this->{$mes} = isset($data[$mes]) ? $data[$mes] : null;
        }
    }

    public function cargarDesdeFormulario2($data)
    {
        $this->id = base64_decode($data['id']);
        $this->clave = $data['clave'] ?? null;
        $this->especialidad = $data['especialidad'] ?? null;
        $this->division = $data['division'] ?? null;
        $this->anio = $data['anio'] ?? null;

        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        foreach ($meses as $mes) {
            $this->{$mes} = isset($data[$mes]) ? $data[$mes] : null;
        }
    }

    public function claveAnioExiste($clave, $anio)
    {
        $clave = $this->valorSQL($clave);
        $anio = intval($anio);
        $sql = "SELECT COUNT(*) as total FROM paciente WHERE clave = $clave AND anio = $anio";
        $res = $this->conexion->consultarUnaFila($sql);
        return $res['total'] > 0;
    }

    public function insertarNuevo()
    {
        $campos = ["clave", "especialidad", "division"];
        $valores = [
            $this->valorSQL($this->clave),
            $this->valorSQL($this->especialidad),
            $this->valorSQL($this->division)
        ];

        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        foreach ($meses as $mes) {
            $campos[] = $mes;
            $valores[] = $this->valorSQL($this->{$mes});
        }

        $campos[] = "anio";
        $valores[] = $this->valorSQL($this->anio);

        $sql = "INSERT INTO paciente (" . implode(', ', $campos) . ")
                VALUES (" . implode(', ', $valores) . ")";

        return $this->conexion->actualizar($sql);
    }



    public function actualizarCamposVacios()
    {
        $camposUpdate = [];

        // Campos básicos
        foreach (['clave', 'especialidad', 'division', 'anio'] as $campo) {
            $nuevoValor = $this->valorSQL($this->$campo);
            if ($nuevoValor !== "NULL") {
                $camposUpdate[] = "$campo = IF($campo IS NULL OR $campo = '', $nuevoValor, $campo)";
            }
        }

        // Meses
        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        foreach ($meses as $mes) {
            $valor = $this->valorSQL($this->{$mes});
            if ($valor !== "NULL" && $valor != 0) {
                $camposUpdate[] = "$mes = IF($mes IS NULL OR $mes = 0, $valor, $mes)";
            }
        }

        if (empty($camposUpdate)) {
            return false; // Nada que actualizar
        }

        $sql = "UPDATE paciente SET " . implode(", ", $camposUpdate) .
            " WHERE clave = '" . $this->clave . "' AND anio = " . intval($this->anio);

        // Ejecutar y verificar si se modificó alguna fila realmente
        $mysqli = $this->conexion->getConexion();
        $resultado = $mysqli->query($sql);

        return $resultado && $mysqli->affected_rows > 0;
    }




    public function ingresar()
    {
        if ($this->claveAnioExiste($this->clave, $this->anio)) {
            $actualizado = $this->actualizarCamposVacios();
            return $actualizado ? 'actualizado' : 'colision_sin_cambios';
        } else {
            return $this->insertarNuevo() ? 'insertado' : 'error';
        }
    }




    public function editar()
    {
        $mysqli = $this->conexion->getConexion();

        $sql = "UPDATE paciente SET 
            clave = '" . mysqli_real_escape_string($mysqli, $this->clave) . "',
            especialidad = '" . mysqli_real_escape_string($mysqli, $this->especialidad) . "',
            division = '" . mysqli_real_escape_string($mysqli, $this->division) . "', ";

        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        foreach ($meses as $mes) {
            $valor = trim($this->{$mes});
            $sql .= "$mes = " . ($valor === '' ? "NULL" : intval($valor)) . ", ";
        }

        $sql .= "anio = " . intval($this->anio) . " WHERE id = " . intval($this->id);

        return $this->conexion->actualizar($sql);
    }


    public function editarSinID()
    {
        $mysqli = $this->conexion->getConexion();

        $sql = "UPDATE paciente SET ";

        $campos = [];

        $campos[] = "clave = '" . mysqli_real_escape_string($mysqli, $this->clave) . "'";
        $campos[] = "especialidad = '" . mysqli_real_escape_string($mysqli, $this->especialidad) . "'";
        $campos[] = "division = '" . mysqli_real_escape_string($mysqli, $this->division) . "'";

        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        foreach ($meses as $mes) {
            $valor = trim($this->{$mes});
            $campos[] = "$mes = " . ($valor === '' ? "NULL" : intval($valor));
        }

        $campos[] = "anio = " . intval($this->anio);

        $sql .= implode(", ", $campos);

        $sql .= " WHERE clave = '" . mysqli_real_escape_string($mysqli, $this->clave) . "'
              AND anio = " . intval($this->anio) . "
              AND especialidad = '" . mysqli_real_escape_string($mysqli, $this->especialidad) . "'
              AND division = '" . mysqli_real_escape_string($mysqli, $this->division) . "'";

        return $this->conexion->actualizar($sql);
    }



    public function eliminar()
    {
        $sql = "DELETE FROM paciente WHERE id = " . intval($this->id);
        return $this->conexion->actualizar($sql);
    }

    public static function obtenerPorId($id)
    {
        $conexion = new Conexion();
        $idEscapado = intval($id);
        $listado = $conexion->consultar("SELECT * FROM paciente WHERE id = $idEscapado");
        $conexion->cerrar();
        return (!empty($listado) && isset($listado[0])) ? $listado[0] : null;
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

    public static function obtenerPorClaveYAnio($clave, $anio)
    {
        $conexion = new Conexion();
        $claveEsc = $conexion->getConexion()->real_escape_string($clave);
        $anioInt = intval($anio);

        $resultado = $conexion->consultar("SELECT * FROM paciente WHERE clave = '$claveEsc' AND anio = $anioInt");
        $conexion->cerrar();

        return (!empty($resultado) && isset($resultado[0])) ? $resultado[0] : null;
    }

    public function cargarDesdeCSV($datos)
    {
        // Asumiendo que el CSV tiene este orden:
        // clave, especialidad, division, enero, febrero, ..., diciembre, anio
        $this->clave = mb_convert_encoding($datos[0], 'UTF-8', 'auto');
        $this->especialidad = mb_convert_encoding($datos[1], 'UTF-8', 'auto');
        $this->division = mb_convert_encoding($datos[2], 'UTF-8', 'auto');

        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $i = 3;
        foreach ($meses as $mes) {
            $this->{$mes} = (int)$datos[$i++];
        }

        $this->anio = (int)$datos[$i];
    }



    public function vaciarTabla()
    {
        return $this->conexion->actualizar("TRUNCATE TABLE paciente");
    }

    public static function contar()
    {
        $conexion = new Conexion();
        $consulta = $conexion->consultarUnaFila('SELECT COUNT(*) AS total FROM paciente');
        if (is_array($consulta) && isset($consulta['total'])) {
            return $consulta['total'];
        }
        return 0;
    }

    public static function obtenerTotalesPorMes()
    {
        $conexion = new Conexion();
        $consulta = $conexion->consultar("SELECT 
            COALESCE(SUM(enero), 0) AS enero,
            COALESCE(SUM(febrero), 0) AS febrero,
            COALESCE(SUM(marzo), 0) AS marzo,
            COALESCE(SUM(abril), 0) AS abril,
            COALESCE(SUM(mayo), 0) AS mayo,
            COALESCE(SUM(junio), 0) AS junio,
            COALESCE(SUM(julio), 0) AS julio,
            COALESCE(SUM(agosto), 0) AS agosto,
            COALESCE(SUM(septiembre), 0) AS septiembre,
            COALESCE(SUM(octubre), 0) AS octubre,
            COALESCE(SUM(noviembre), 0) AS noviembre,
            COALESCE(SUM(diciembre), 0) AS diciembre
            FROM paciente");
        $conexion->cerrar();

        return $consulta[0];
    }
}
?>
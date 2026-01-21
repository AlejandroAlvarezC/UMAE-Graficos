<?php
require_once 'conexion.php';

class Manual
{
    public $id;
    public $normatividad;
    public $nombre;
    public $anio;
    public $entidad;
    public $servicio;
    public $fecha;
    public $archivo;
    public $direccion;
    private $conexion;

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

    public static function listar()
    {
        $conexion = new Conexion();
        $resultado = $conexion->consultar("SELECT * FROM manuales");
        $conexion->cerrar();
        return $resultado;
    }

    public static function obtenerPorId($id)
    {
        $conexion = new Conexion();
        $resultado = $conexion->consultar("SELECT * FROM manuales WHERE id = $id");
        $conexion->cerrar();
        return $resultado[0];
    }

    public function ingresar()
    {
        $sql = "INSERT INTO manuales (normatividad, nombre, anio, entidad, servicio, fecha, archivo, direccion)
                VALUES ('$this->normatividad', '$this->nombre', '$this->anio', '$this->entidad', '$this->servicio', '$this->fecha', '$this->archivo', '$this->direccion')";
        return $this->conexion->actualizar($sql);
    }

    public function editar()
    {
        $sql = "UPDATE manuales SET
                normatividad = '$this->normatividad',
                nombre = '$this->nombre',
                anio = '$this->anio',
                entidad = '$this->entidad',
                servicio = '$this->servicio',
                fecha = '$this->fecha',
                archivo = '$this->archivo',
                direccion = '$this->direccion'
                WHERE id = $this->id";
        return $this->conexion->actualizar($sql);
    }

    public function eliminar()
    {
        $sql = "DELETE FROM manuales WHERE id = $this->id";
        return $this->conexion->actualizar($sql);
    }

    public static function contarManuales()
    {
        $conexion = new Conexion();
        $consulta = $conexion->consultarUnaFila('SELECT COUNT(*) AS total FROM manuales');
        if (is_array($consulta) && isset($consulta['total'])) {
            return $consulta['total'];
        }
        return 0;
    }
}


?>
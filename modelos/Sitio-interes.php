<?php
require_once 'conexion.php';

class SitioInteres
{
    public $id;
    public $nombre;
    public $descripcion;
    public $imagen;
    public $ruta;
    private $conexion;

    public function __construct()
    {
        $this->conexion = new Conexion();
    }

    public static function listar()
    {
        $conexion = new Conexion();
        $resultado = $conexion->consultar("SELECT * FROM sitiosinteres");
        $conexion->cerrar();
        return $resultado;
    }

    public static function obtenerPorId($id)
    {
        $conexion = new Conexion();
        $resultado = $conexion->consultar("SELECT * FROM sitiosinteres WHERE id = $id");
        $conexion->cerrar();
        return $resultado[0];
    }

    public function ingresar()
    {
        $sql = "INSERT INTO sitiosinteres (nombre, descripcion, imagen, ruta) VALUES (
            '$this->nombre', '$this->descripcion', '$this->imagen', '$this->ruta'
        )";
        return $this->conexion->actualizar($sql);
    }

    public function editar()
    {
        $sql = "UPDATE sitiosinteres SET
            nombre = '$this->nombre',
            descripcion = '$this->descripcion',
            imagen = '$this->imagen',
            ruta = '$this->ruta'
            WHERE id = $this->id";
        return $this->conexion->actualizar($sql);
    }

    public function eliminar()
    {
        $sql = "DELETE FROM sitiosinteres WHERE id = $this->id";
        return $this->conexion->actualizar($sql);
    }
}
?>

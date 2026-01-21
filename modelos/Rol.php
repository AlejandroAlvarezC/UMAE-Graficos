<?php
require_once 'Conexion.php';

class Rol3 {
    public $id;
    public $num;
    public $nom;
    public $fech;
    public $cvv;
    public $user_id;
    private $conexion;

    public function __construct () {
        $this->id = 0;
        $this->num = '';
        $this->nom = '';
        $this->fech = '';
        $this->cvv = '';
        $this->user_id = 0;
        $this->conexion = new Conexion();
    }

    public static function listarPorUsuario($user_id) {
        $conexion = new Conexion ();
        $listado = $conexion->consultar("SELECT * FROM tarjet WHERE user_id = $user_id");
        $conexion->cerrar();
        return $listado;
    }

    public static function obtenerPorId ($id) {
        $conexion = new Conexion ();
        $listado = $conexion->consultar("SELECT * FROM tarjet WHERE Id = $id");
        $conexion->cerrar();
        return $listado[0];
    }

    public function ingresar () {
        $s = "INSERT INTO tarjet (Num, Nom, Fech, Cvv, user_id) VALUES ('$this->num', '$this->nom', '$this->fech', '$this->cvv', '$this->user_id')";
        $resultado = $this->conexion->actualizar($s);
        $this->conexion->cerrar();
        return $resultado;
    }

    public function eliminar () {
        $s = "DELETE FROM tarjet WHERE Id = $this->id";
        $resultado = $this->conexion->actualizar($s);
        $this->conexion->cerrar();
        return $resultado;
    }

    public function editar () {
        $s = "UPDATE tarjet SET Num = '$this->num', Nom = '$this->nom', Fech = '$this->fech', Cvv = '$this->cvv' WHERE Id = $this->id";
        $resultado = $this->conexion->actualizar($s);
        $this->conexion->cerrar();
        return $resultado;
    }
}
?>
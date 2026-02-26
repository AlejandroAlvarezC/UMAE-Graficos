<?php
class Conexion
{
    private $conexion;

    // =======================================================
    // MODO 1: MYSQLI (Para cuando usas $db = new Conexion())
    // =======================================================
    public function __construct()
    {
        $this->conexion = new mysqli('sql112.infinityfree.com', 'if0_41125231', 'DEtK59bqZzA', 'if0_41125231_vencer');
        $this->conexion->set_charset('utf8mb4'); // Fuerza acentos aquí
    }

    // =======================================================
    // MODO 2: PDO (Para cuando usas Conexion::conectar())
    // =======================================================
    public static function conectar() 
    {
        $opciones = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4", // Fuerza acentos aquí
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );
        
        $link = new PDO(
            "mysql:host=sql112.infinityfree.com;dbname=f0_41125231_vencer", 
            "if0_41125231", 
            "DEtK59bqZzA", 
            $opciones
        );
        return $link;
    }

    // =======================================================
    // TUS MÉTODOS ORIGINALES (Intactos)
    // =======================================================
    public function consultar($sql)
    {
        return $this->conexion->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function actualizar($sql)
    {
        return $this->conexion->query($sql);
    }
    
    public function consultarUnaFila($sql)
    {
        $resultado = $this->conexion->query($sql);
        return $resultado->fetch_assoc();
    }

    public function obtenerUltimoId()
    {
        return $this->conexion->insert_id;
    }

    public function getConexion()
    {
        return $this->conexion;
    }

    public function cerrar()
    {
        $this->conexion->close();
    }
}
?>
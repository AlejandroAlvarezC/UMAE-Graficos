<?php
class Conexion
{
    private $conexion;

    public function __construct()
    {
        // --- 1. CONFIGURACIÓN DE INFINITYFREE ---
        // Busca estos datos en tu panel -> "MySQL Databases"
        
        $host = 'sql112.infinityfree.com';  // MySQL Host Name 
        $user = 'if0_41125231';              // MySQL User Name
        $pass = 'DEtK59bqZzA';       // Tu contraseña del panel (MySQL Password)
        $db   = 'if0_41125231_vencer';        // MySQL Database Name 

        // --- 2. CREAR CONEXIÓN ---
        // Quitamos el puerto :3307 porque en la nube es estándar
        $this->conexion = new mysqli($host, $user, $pass, $db);

        // --- 3. VERIFICAR ERRORES (IMPORTANTE PARA LA NUBE) ---
        if ($this->conexion->connect_error) {
            die("Error fatal de conexión: " . $this->conexion->connect_error);
        }

        $this->conexion->set_charset('utf8');
    }

    // --- EL RESTO DE TUS FUNCIONES SIGUEN IGUAL ---

    public function consultar($sql)
    {
        return $this->conexion->query($sql)->fetch_all();
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

    public static function conectar()
    {
        $instance = new self();
        return $instance->getConexion();
    }
}
?>
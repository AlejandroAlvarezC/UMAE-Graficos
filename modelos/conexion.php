<?php
class Conexion
{
	private $conexion;

	public function __construct()
	{
		$this->conexion = new mysqli('localhost', 'root', '', 'base1');
		$this->conexion->set_charset('utf8');
	}

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

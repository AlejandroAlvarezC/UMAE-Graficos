<?php
require_once 'conexion.php';

class Personal {
    public $id;
    public $nombre;
    public $apaterno;
    public $amaterno;
    public $area;
    public $puesto;
    public $telefono;
    public $extension;
    public $correo;
    public $foto;
    public $jefe_id;
    private $conexion;

    public function __construct() {
        $this->id = 0;
        $this->nombre = '';
        $this->apaterno = '';
        $this->amaterno = '';
        $this->area = '';
        $this->puesto = '';
        $this->telefono = '';
        $this->extension = '';
        $this->correo = '';
        $this->foto = '';
        $this->jefe_id = null;
        $this->conexion = new Conexion();
    }

    public static function listar() {
        $conexion = new Conexion();
        $resultado = $conexion->consultar('SELECT * FROM personal');
        $conexion->cerrar();
        return $resultado;
    }

    public static function obtenerPorId($id) {
        $conexion = new Conexion();
        $resultado = $conexion->consultar("SELECT * FROM personal WHERE id = $id");
        $conexion->cerrar();
        return $resultado ? $resultado[0] : null;
    }

    public function ingresar() {
         $conn = $this->conexion->getConexion();

    $stmt = $conn->prepare("INSERT INTO personal (nombre, apaterno, amaterno, area, puesto, telefono, extension, correo, foto, jefe_id)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Si jefe_id no es numérico, lo pasamos como null
    $jefe_id = is_numeric($this->jefe_id) ? $this->jefe_id : null;

    $stmt->bind_param("sssssssssi", 
        $this->nombre,
        $this->apaterno,
        $this->amaterno,
        $this->area,
        $this->puesto,
        $this->telefono,
        $this->extension,
        $this->correo,
        $this->foto,
        $jefe_id
    );

    $resultado = $stmt->execute();
    $stmt->close();
    $this->conexion->cerrar();
    return $resultado;
    }

    public function editar() {
            $conn = $this->conexion->getConexion();

    $stmt = $conn->prepare("UPDATE personal SET 
        nombre = ?,
        apaterno = ?,
        amaterno = ?, 
        area = ?, 
        puesto = ?, 
        telefono = ?, 
        extension = ?, 
        correo = ?, 
        foto = ?, 
        jefe_id = ? 
        WHERE id = ?");

    // Manejo de jefe_id (puede ser NULL)
    $jefe_id = is_numeric($this->jefe_id) ? $this->jefe_id : null;

    $stmt->bind_param("sssssssssii",
        $this->nombre,
        $this->apaterno,
        $this->amaterno,
        $this->area,
        $this->puesto,
        $this->telefono,
        $this->extension,
        $this->correo,
        $this->foto,
        $jefe_id,
        $this->id
    );

    $resultado = $stmt->execute();
    $stmt->close();
    $this->conexion->cerrar();
    return $resultado;
    }

    public function eliminar() {
        $sql = "DELETE FROM personal WHERE id = $this->id";
        $resultado = $this->conexion->actualizar($sql);
        $this->conexion->cerrar();
        return $resultado;
    }

public static function obtenerJerarquia() {
    $conexion = new Conexion();
    $conn = $conexion->getConexion();
    $stmt = $conn->prepare("SELECT id, nombre, apaterno, amaterno, area, puesto, correo, telefono, extension, foto, jefe_id FROM personal"); // ← Aquí estaba el error
    $stmt->execute();
    $resultado = $stmt->get_result();
    $datos = $resultado->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conexion->cerrar();
    return $datos;
}

}
?>
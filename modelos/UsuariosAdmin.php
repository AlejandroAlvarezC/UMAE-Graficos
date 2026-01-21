<?php require_once 'conexion.php';

class Usuarios {
	public $id;
	public $names;
	public $email;
	public $pasword;
	private $conexion;
    

	public function __construct () {
		$this->id = 0;
		$this->names = '';
		$this->email ='';
		$this->pasword ='';
		$this->conexion = new Conexion();
	}

	public static function listar () {
		$conexion = new Conexion ();
		$listado = $conexion->consultar('SELECT * FROM admi');
		$conexion->cerrar();
		return $listado;
	}

	public static function contarUsuarios() {
		$conexion = new conexion();
	
		$consulta = $conexion->consultarUnaFila('SELECT COUNT(*) AS total FROM admi');
		
		
		if (is_array($consulta) && isset($consulta['total'])) {
			return $consulta['total']; 
		}
	
		return 0;
	}

	public static function obtenerPorId ($id) {
		$conexion = new Conexion ();
		$listado = $conexion->consultar("SELECT * FROM admi WHERE Id = $id");
		$conexion->cerrar();
		return $listado[0];
	}

	public function ingresar () {
		$s = "INSERT INTO admi (Names,Email,Pasword) VALUES ('$this->names'".",'$this->email'".",'$this->pasword')";
	
		$resultado = $this->conexion->actualizar($s);
		$this->conexion->cerrar();
		return $resultado;
	}

	public function eliminar () {
		$s = "DELETE FROM admi WHERE Id = $this->id";
		$resultado = $this->conexion->actualizar($s);
		$this->conexion->cerrar();
		return $resultado;
	}

	public function editar () {
		$s = "UPDATE admi SET Names = '$this->names'".",Email = '$this->email'".",Pasword= '$this->pasword' WHERE Id = $this->id";
	
		$resultado = $this->conexion->actualizar($s);
		$this->conexion->cerrar();
		return $resultado;
	}

	public static function obtenerUsuarioActual($userId) {
		$conexion = new Conexion();
		$db = $conexion->getConexion();  
		$sql = "SELECT * FROM admi WHERE Id = ?";
		$stmt = $db->prepare($sql);
		$stmt->bind_param("i", $userId);
		$stmt->execute();
		$result = $stmt->get_result();
		$usuario = null;
	
		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$usuario = new Usuarios();  
			$usuario->email = $row['Email'];
			$usuario->pasword = $row['Pasword'];
		}
	
		$stmt->close();
		$conexion->cerrar();
		return $usuario;
	}

	public static function cambiarPassword($userId, $currentPassword, $newPassword) {
		$db = new Conexion();
	
	
		$sql = "SELECT Pasword FROM admi WHERE Id = $userId";
		$user = $db->consultarUnaFila($sql);
		if (password_verify($currentPassword, $user['Pasword'])) {
		
			$hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
			$updateSql = "UPDATE admi SET Pasword = '$hashedPassword' WHERE Id = $userId";
			return $db->actualizar($updateSql);
		} else {
			return false; 
		}
	}
}
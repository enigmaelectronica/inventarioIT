<?php
// conexion.php - Conexión segura a MySQL usando Singleton Pattern

class ConexionBD {
    // Configuración para XAMPP (modificar según tu entorno)
    private $host = "localhost";
    private $usuario = "root";
    private $contrasena = "";
    private $nombre_bd = "enigmatool";
    private $charset = "utf8mb4";

    private static $instancia = null;
    private $conexion;

    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->nombre_bd};charset={$this->charset}";
            
            $this->conexion = new PDO($dsn, $this->usuario, $this->contrasena);
            
            // Configurar PDO para mejor seguridad
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
        } catch(PDOException $e) {
            error_log("Error de conexión: " . $e->getMessage());
            die("Error crítico: No se pudo conectar a la base de datos");
        }
    }

    public static function obtenerInstancia() {
        if(!self::$instancia) {
            self::$instancia = new ConexionBD();
        }
        return self::$instancia->conexion;
    }

    // Prevenir clonación y serialización
    private function __clone() {}
    public function __wakeup() {}
}

// Uso en otros archivos:
// $db = ConexionBD::obtenerInstancia();
// $stmt = $db->prepare("SELECT * FROM equipos");
// $stmt->execute();
?>

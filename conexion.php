<?php
// conexion.php - Maneja la conexión a la base de datos MySQL

class Database {
    private static $instance = null;
    private $connection;
    
    // Configuración de la base de datos (modificar según tu entorno)
    private $host = "localhost";
    private $usuario = "root";
    private $contrasena = "";
    private $nombre_db = "enigmatool";
    private $charset = "utf8mb4";

    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->nombre_db};charset={$this->charset}";
            $this->connection = new PDO($dsn, $this->usuario, $this->contrasena);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $e) {
            error_log("Error de conexión: " . $e->getMessage());
            die("Error al conectar con la base de datos. Por favor intenta más tarde.");
        }
    }

    public static function getInstance() {
        if(!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }

    // Evita la clonación del objeto
    private function __clone() { }
}

// Uso básico en otros archivos:
// $db = Database::getInstance();
// $stmt = $db->prepare("SELECT * FROM tabla");
// $stmt->execute();
// $resultados = $stmt->fetchAll();
?>

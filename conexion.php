<?php
class ConexionBD {
    private static $instancia = null;
    private $conexion;

    // Configuración para XAMPP (modifica según tu entorno)
    private $host = "localhost";
    private $usuario = "root";
    private $contrasena = "";
    private $nombre_bd = "enigmatool";
    private $charset = "utf8mb4";

    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->nombre_bd};charset={$this->charset}";
            $this->conexion = new PDO($dsn, $this->usuario, $this->contrasena);
            
            // Configuración segura de PDO
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
        } catch (PDOException $e) {
            error_log("Error de conexión: " . $e->getMessage());
            die("Error crítico: No se pudo conectar a la base de datos");
        }
    }

    public static function obtenerInstancia() {
        if (!self::$instancia) {
            self::$instancia = new ConexionBD();
        }
        return self::$instancia->conexion;
    }

    // Prevenir clonación y serialización
    private function __clone() {}
    public function __wakeup() {}
}

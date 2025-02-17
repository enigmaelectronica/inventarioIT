<?php
class ConexionBD {
    private static $instancia = null;
    private $conexion;

    private function __construct() {
        try {
            $this->conexion = new PDO(
                'mysql:host=localhost;dbname=enigmatool;charset=utf8mb4',
                'root',
                '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            error_log("[Error BD] " . $e->getMessage());
            die("Error crítico: Contacte al administrador del sistema");
        }
    }

    public static function obtenerInstancia() {
        if (!self::$instancia) {
            self::$instancia = new self();
        }
        return self::$instancia->conexion;
    }

    private function __clone() {}
    public function __wakeup() {}
}

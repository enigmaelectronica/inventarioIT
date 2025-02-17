<?php
// auth.php - Maneja la autenticación de usuarios

session_start();
header('Content-Type: application/json');

require_once 'conexion.php';

// Configuración de seguridad
const MAX_INTENTOS = 5;
const BLOQUEO_TIEMPO = 300; // 5 minutos en segundos

try {
    $db = Database::getInstance();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos de entrada
    if (empty($data['usuario']) || empty($data['contrasena'])) {
        throw new Exception('Usuario y contraseña son requeridos');
    }
    
    $usuario = trim($data['usuario']);
    $contrasena = $data['contrasena'];
    
    // Prevenir fuerza bruta
    if (isset($_SESSION['intentos']) && $_SESSION['intentos'] >= MAX_INTENTOS) {
        if (time() - $_SESSION['ultimo_intento'] < BLOQUEO_TIEMPO) {
            throw new Exception('Demasiados intentos. Intente nuevamente en 5 minutos');
        } else {
            unset($_SESSION['intentos']);
            unset($_SESSION['ultimo_intento']);
        }
    }
    
    // Buscar usuario en la base de datos
    $stmt = $db->prepare("SELECT id, contrasena FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($contrasena, $user['contrasena'])) {
        $_SESSION['intentos'] = ($_SESSION['intentos'] ?? 0) + 1;
        $_SESSION['ultimo_intento'] = time();
        throw new Exception('Credenciales inválidas');
    }
    
    // Autenticación exitosa
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['last_activity'] = time();
    
    // Regenerar ID de sesión para prevenir fixation
    session_regenerate_id(true);
    
    echo json_encode([
        'success' => true,
        'message' => 'Autenticación exitosa'
    ]);
    
} catch (Exception $e) {
    error_log('Error de autenticación: ' . $e->getMessage());
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

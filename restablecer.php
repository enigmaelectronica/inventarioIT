<?php
require_once 'conexion.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $token = $data['token'];
    $nuevaContrasena = $data['nueva_contrasena'];

    // Validar token
    $db = ConexionBD::obtenerInstancia();
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE token_recuperacion = ? AND expiracion_token > NOW()");
    $stmt->execute([$token]);
    
    $usuario = $stmt->fetch();
    if (!$usuario) {
        throw new Exception("Token inválido o expirado");
    }

    // Validar contraseña
    if (strlen($nuevaContrasena) < 8) {
        throw new Exception("La contraseña debe tener al menos 8 caracteres");
    }

    // Actualizar contraseña
    $hash = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE usuarios SET contrasena = ?, token_recuperacion = NULL, expiracion_token = NULL WHERE id = ?");
    $stmt->execute([$hash, $usuario['id']]);

    echo json_encode([
        'success' => true,
        'mensaje' => 'Contraseña actualizada exitosamente'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'mensaje' => $e->getMessage()
    ]);
}

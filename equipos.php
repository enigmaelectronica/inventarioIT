<?php
// equipos.php - API para gestión de equipos
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

try {
    $db = Database::getInstance();
    
    // Verificar autenticación
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Acceso no autorizado', 401);
    }

    $metodo = $_SERVER['REQUEST_METHOD'];
    $datos = array_merge($_REQUEST, json_decode(file_get_contents('php://input'), true) ?? []);

    switch ($metodo) {
        case 'GET':
            handleGet($db, $datos);
            break;
            
        case 'POST':
            handlePost($db, $datos);
            break;
            
        case 'PUT':
            handlePut($db, $datos);
            break;
            
        case 'DELETE':
            handleDelete($db, $datos);
            break;
            
        default:
            throw new Exception('Método no permitido', 405);
    }
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// =============================================
// Funciones de manejo de operaciones
// =============================================

function handleGet($db, $datos) {
    $query = "SELECT * FROM equipos";
    $params = [];
    
    // Filtros
    if (!empty($datos['busqueda'])) {
        $query .= " WHERE numero_serie LIKE :busqueda OR modelo LIKE :busqueda";
        $params[':busqueda'] = "%{$datos['busqueda']}%";
    }
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear campos numéricos
    foreach ($equipos as &$equipo) {
        $equipo['costo'] = (float)$equipo['costo'];
        $equipo['fotos'] = json_decode($equipo['fotos'], true);
    }
    
    echo json_encode(['success' => true, 'data' => $equipos]);
}

function handlePost($db, $datos) {
    validarDatosEquipo($datos);
    
    // Manejar subida de archivos
    $fotos = subirArchivos($_FILES['fotos'] ?? [], 'imagen');
    $pdf = subirArchivos($_FILES['pdf'] ?? [], 'pdf');
    
    $query = "INSERT INTO equipos (
        tipo, marca, modelo, numero_serie, numero_express,
        numero_telefono, imei, costo, fotos, receptor_id
    ) VALUES (
        :tipo, :marca, :modelo, :numero_serie, :numero_express,
        :numero_telefono, :imei, :costo, :fotos, :receptor_id
    )";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':tipo' => $datos['tipo'],
        ':marca' => $datos['marca'],
        ':modelo' => $datos['modelo'],
        ':numero_serie' => $datos['numero_serie'],
        ':numero_express' => $datos['numero_express'] ?? null,
        ':numero_telefono' => $datos['numero_telefono'] ?? null,
        ':imei' => $datos['imei'] ?? null,
        ':costo' => $datos['costo'],
        ':fotos' => json_encode($fotos),
        ':receptor_id' => $datos['receptor_id']
    ]);
    
    echo json_encode([
        'success' => true,
        'id' => $db->lastInsertId()
    ]);
}

function handlePut($db, $datos) {
    if (empty($datos['id'])) {
        throw new Exception('ID de equipo requerido', 400);
    }
    
    validarDatosEquipo($datos);
    
    $query = "UPDATE equipos SET
        tipo = :tipo,
        marca = :marca,
        modelo = :modelo,
        numero_serie = :numero_serie,
        numero_express = :numero_express,
        numero_telefono = :numero_telefono,
        imei = :imei,
        costo = :costo,
        receptor_id = :receptor_id
        WHERE id = :id";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':id' => $datos['id'],
        ':tipo' => $datos['tipo'],
        ':marca' => $datos['marca'],
        ':modelo' => $datos['modelo'],
        ':numero_serie' => $datos['numero_serie'],
        ':numero_express' => $datos['numero_express'] ?? null,
        ':numero_telefono' => $datos['numero_telefono'] ?? null,
        ':imei' => $datos['imei'] ?? null,
        ':costo' => $datos['costo'],
        ':receptor_id' => $datos['receptor_id']
    ]);
    
    echo json_encode(['success' => true]);
}

function handleDelete($db, $datos) {
    if (empty($datos['id'])) {
        throw new Exception('ID de equipo requerido', 400);
    }
    
    // Eliminar equipo y archivos asociados
    $equipo = obtenerEquipo($db, $datos['id']);
    eliminarArchivos($equipo['fotos']);
    
    $stmt = $db->prepare("DELETE FROM equipos WHERE id = :id");
    $stmt->execute([':id' => $datos['id']]);
    
    echo json_encode(['success' => true]);
}

// =============================================
// Funciones auxiliares
// =============================================

function validarDatosEquipo($datos) {
    $camposRequeridos = [
        'tipo' => ['computadora', 'celular', 'impresora'],
        'marca' => 50,
        'modelo' => 50,
        'numero_serie' => 50,
        'costo' => 'numeric',
        'receptor_id' => 'numeric'
    ];
    
    foreach ($camposRequeridos as $campo => $validacion) {
        if (empty($datos[$campo])) {
            throw new Exception("Campo requerido: $campo", 400);
        }
        
        if (is_array($validacion) && !in_array($datos[$campo], $validacion)) {
            throw new Exception("Valor inválido para $campo", 400);
        }
        
        if (is_int($validacion) && strlen($datos[$campo]) > $validacion) {
            throw new Exception("$campo excede longitud máxima", 400);
        }
        
        if ($validacion === 'numeric' && !is_numeric($datos[$campo])) {
            throw new Exception("$campo debe ser numérico", 400);
        }
    }
}

function subirArchivos($archivos, $tipo) {
    $rutas = [];
    $maxArchivos = ($tipo === 'imagen') ? 4 : 1;
    $maxSize = ($tipo === 'imagen') ? 1024 * 1024 : 5 * 1024 * 1024;
    $extensiones = ($tipo === 'imagen') ? ['jpg', 'jpeg', 'png', 'bmp'] : ['pdf'];
    
    if (count($archivos['name']) > $maxArchivos) {
        throw new Exception("Máximo $maxArchivos archivos permitidos");
    }
    
    for ($i = 0; $i < count($archivos['name']); $i++) {
        if ($archivos['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        
        $nombre = basename($archivos['name'][$i]);
        $tmp = $archivos['tmp_name'][$i];
        $size = $archivos['size'][$i];
        $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $extensiones)) {
            throw new Exception("Tipo de archivo no permitido: $ext");
        }
        
        if ($size > $maxSize) {
            throw new Exception("Archivo excede tamaño máximo: " . ($maxSize/1024/1024) . "MB");
        }
        
        $nombreUnico = uniqid() . "_" . $nombre;
        $ruta = "uploads/" . $nombreUnico;
        
        if (!move_uploaded_file($tmp, $ruta)) {
            throw new Exception("Error al subir archivo");
        }
        
        $rutas[] = $ruta;
    }
    
    return $rutas;
}

function obtenerEquipo($db, $id) {
    $stmt = $db->prepare("SELECT * FROM equipos WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $equipo = $stmt->fetch();
    
    if (!$equipo) {
        throw new Exception('Equipo no encontrado', 404);
    }
    
    return $equipo;
}

function eliminarArchivos($rutas) {
    if (is_string($rutas)) {
        $rutas = json_decode($rutas, true);
    }
    
    foreach ($rutas as $ruta) {
        if (file_exists($ruta)) {
            unlink($ruta);
        }
    }
}

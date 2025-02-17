<?php
// receptores.php - API para gestión de receptores
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
    $query = "SELECT * FROM receptores";
    $params = [];
    
    // Filtros
    if (!empty($datos['busqueda'])) {
        $query .= " WHERE nombre_completo LIKE :busqueda OR departamento LIKE :busqueda";
        $params[':busqueda'] = "%{$datos['busqueda']}%";
    }
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    $receptores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Decodificar URLs de archivos
    foreach ($receptores as &$receptor) {
        $receptor['foto'] = base64_encode($receptor['foto']);
        $receptor['pdf_aceptacion'] = base64_encode($receptor['pdf_aceptacion']);
    }
    
    echo json_encode(['success' => true, 'data' => $receptores]);
}

function handlePost($db, $datos) {
    validarDatosReceptor($datos);
    
    // Manejar subida de archivos
    $foto = subirArchivo($_FILES['foto'] ?? null, 'imagen');
    $pdf = subirArchivo($_FILES['pdf'] ?? null, 'pdf');

    $query = "INSERT INTO receptores (
        nombre_completo, 
        departamento, 
        cargo, 
        foto, 
        pdf_aceptacion
    ) VALUES (
        :nombre_completo, 
        :departamento, 
        :cargo, 
        :foto, 
        :pdf_aceptacion
    )";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':nombre_completo' => $datos['nombre_completo'],
        ':departamento' => $datos['departamento'],
        ':cargo' => $datos['cargo'],
        ':foto' => $foto,
        ':pdf_aceptacion' => $pdf
    ]);
    
    echo json_encode([
        'success' => true,
        'id' => $db->lastInsertId()
    ]);
}

function handlePut($db, $datos) {
    if (empty($datos['id'])) {
        throw new Exception('ID de receptor requerido', 400);
    }
    
    validarDatosReceptor($datos);
    
    // Obtener receptor existente
    $receptor = obtenerReceptor($db, $datos['id']);
    
    // Manejar actualización de archivos
    $foto = $receptor['foto'];
    if (!empty($_FILES['foto'])) {
        eliminarArchivo($foto);
        $foto = subirArchivo($_FILES['foto'], 'imagen');
    }
    
    $pdf = $receptor['pdf_aceptacion'];
    if (!empty($_FILES['pdf'])) {
        eliminarArchivo($pdf);
        $pdf = subirArchivo($_FILES['pdf'], 'pdf');
    }

    $query = "UPDATE receptores SET
        nombre_completo = :nombre_completo,
        departamento = :departamento,
        cargo = :cargo,
        foto = :foto,
        pdf_aceptacion = :pdf_aceptacion
        WHERE id = :id";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':id' => $datos['id'],
        ':nombre_completo' => $datos['nombre_completo'],
        ':departamento' => $datos['departamento'],
        ':cargo' => $datos['cargo'],
        ':foto' => $foto,
        ':pdf_aceptacion' => $pdf
    ]);
    
    echo json_encode(['success' => true]);
}

function handleDelete($db, $datos) {
    if (empty($datos['id'])) {
        throw new Exception('ID de receptor requerido', 400);
    }
    
    $receptor = obtenerReceptor($db, $datos['id']);
    
    // Eliminar archivos asociados
    eliminarArchivo($receptor['foto']);
    eliminarArchivo($receptor['pdf_aceptacion']);
    
    $stmt = $db->prepare("DELETE FROM receptores WHERE id = :id");
    $stmt->execute([':id' => $datos['id']]);
    
    echo json_encode(['success' => true]);
}

// =============================================
// Funciones auxiliares
// =============================================

function validarDatosReceptor($datos) {
    $camposRequeridos = [
        'nombre_completo' => 100,
        'departamento' => 50,
        'cargo' => 50
    ];
    
    foreach ($camposRequeridos as $campo => $longitud) {
        if (empty($datos[$campo])) {
            throw new Exception("Campo requerido: $campo", 400);
        }
        
        if (strlen($datos[$campo]) > $longitud) {
            throw new Exception("$campo excede longitud máxima de $longitud caracteres", 400);
        }
    }
}

function subirArchivo($archivo, $tipo) {
    if (!$archivo || $archivo['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $extensiones = [
        'imagen' => ['jpg', 'jpeg', 'png', 'bmp'],
        'pdf' => ['pdf']
    ];
    
    $maxSize = [
        'imagen' => 2 * 1024 * 1024, // 2MB
        'pdf' => 5 * 1024 * 1024 // 5MB
    ];
    
    $nombre = basename($archivo['name']);
    $tmp = $archivo['tmp_name'];
    $size = $archivo['size'];
    $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $extensiones[$tipo])) {
        throw new Exception("Tipo de archivo no permitido para $tipo: $ext");
    }
    
    if ($size > $maxSize[$tipo]) {
        throw new Exception("Archivo excede tamaño máximo: " . ($maxSize[$tipo]/1024/1024) . "MB");
    }
    
    $nombreUnico = uniqid() . "_" . $nombre;
    $ruta = "uploads/" . $nombreUnico;
    
    if (!move_uploaded_file($tmp, $ruta)) {
        throw new Exception("Error al subir archivo");
    }
    
    return $ruta;
}

function obtenerReceptor($db, $id) {
    $stmt = $db->prepare("SELECT * FROM receptores WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $receptor = $stmt->fetch();
    
    if (!$receptor) {
        throw new Exception('Receptor no encontrado', 404);
    }
    
    return $receptor;
}

function eliminarArchivo($ruta) {
    if ($ruta && file_exists($ruta)) {
        unlink($ruta);
    }
}

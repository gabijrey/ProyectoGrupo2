<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['nombre']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

require __DIR__ . '/../conexion_pdo.php';

$nombre = $_SESSION['nombre'];

try {
    // 1. Localizar usuario y rol (eliminamos el id que no existe)
    $stmt = $_conexion->prepare("SELECT rol FROM usuario WHERE nombre = :n");
    $stmt->execute(['n' => $nombre]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
        exit();
    }
    // 2. Comprobar que tiene una suscripción activa usando nombre_usuario
    $stmt = $_conexion->prepare("
        SELECT id FROM suscripcion
        WHERE nombre_usuario = :nombre AND estado = 1 AND fecha_cancelacion IS NULL
        LIMIT 1
    ");
    $stmt->execute(['nombre' => $nombre]);
    $sus = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sus) {
        echo json_encode(['success' => false, 'error' => 'No tienes una suscripción activa que cancelar']);
        exit();
    }
    $_conexion->beginTransaction();
    // 3. Marcar la suscripción como cancelada
    $upd = $_conexion->prepare("
        UPDATE suscripcion
        SET estado = 0, fecha_cancelacion = CURDATE()
        WHERE nombre_usuario = :nombre AND estado = 1
    ");
    $upd->execute(['nombre' => $nombre]);
    // 4. Devolver el rol del usuario a normal (0) usando el nombre
    $upd2 = $_conexion->prepare("UPDATE usuario SET rol = 0 WHERE nombre = :nombre");
    $upd2->execute(['nombre' => $nombre]);
    $_conexion->commit();
    $_SESSION['rol'] = 0;
    echo json_encode([
        'success'  => true,
        'mensaje'  => 'Suscripción cancelada correctamente',
        'rol'      => 0,
    ]);
} catch (PDOException $e) {
    if ($_conexion->inTransaction()) $_conexion->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
}

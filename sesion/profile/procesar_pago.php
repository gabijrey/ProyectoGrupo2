<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['nombre']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

require __DIR__ . '/../conexion_pdo.php';

$data = json_decode(file_get_contents('php://input'), true) ?: [];
$plan         = $data['plan']         ?? '';
$metodo_pago  = $data['metodo_pago']  ?? '';

// Validar plan
$planes_validos = [
    'premium' => ['rol' => 1, 'precio' => 15.99, 'nombre' => 'Premium'],
    'autor'   => ['rol' => 2, 'precio' => 18.99, 'nombre' => 'Autor'],
];
if (!isset($planes_validos[$plan])) {
    echo json_encode(['success' => false, 'error' => 'Plan no válido']);
    exit();
}

// Validar método de pago
$metodos_validos = ['paypal', 'tarjeta', 'bizum', 'applepay'];
if (!in_array($metodo_pago, $metodos_validos, true)) {
    echo json_encode(['success' => false, 'error' => 'Método de pago no válido']);
    exit();
}

// Validaciones adicionales por método (todas simuladas — sin pasarela real)
if ($metodo_pago === 'tarjeta') {
    $numero = preg_replace('/\s+/', '', $data['numero'] ?? '');
    $cvv    = $data['cvv']    ?? '';
    $cad    = $data['cad']    ?? '';
    $titular= trim($data['titular'] ?? '');
    if (!preg_match('/^\d{13,19}$/', $numero) || !preg_match('/^\d{3,4}$/', $cvv)
        || !preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $cad) || $titular === '') {
        echo json_encode(['success' => false, 'error' => 'Datos de tarjeta inválidos']);
        exit();
    }
}
if ($metodo_pago === 'bizum') {
    $telefono = preg_replace('/\s+/', '', $data['telefono'] ?? '');
    if (!preg_match('/^(\+?34)?\d{9}$/', $telefono)) {
        echo json_encode(['success' => false, 'error' => 'Número de móvil de Bizum inválido']);
        exit();
    }
}

$nombre_usuario = $_SESSION['nombre'];
$rol_nuevo      = $planes_validos[$plan]['rol'];

try {
    // Localizar usuario
    $stmt = $_conexion->prepare("SELECT nombre, rol FROM usuario WHERE nombre = :n");
    $stmt->execute(['n' => $nombre_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$usuario) {
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
        exit();
    }
    $nombre_usuario = $usuario['nombre'];

    $_conexion->beginTransaction();

    // Cancelar suscripciones activas previas (si existen)
    $upd = $_conexion->prepare("
        UPDATE suscripcion
        SET estado = 0, fecha_cancelacion = CURDATE()
        WHERE nombre_usuario = :usuario AND estado = 1
    ");
    $upd->execute(['usuario' => $nombre_usuario]);

    // Crear nueva suscripción activa
    $ins = $_conexion->prepare("
        INSERT INTO suscripcion (nombre_usuario, metodo_pago, estado, fecha_inicio, fecha_cancelacion)
        VALUES (:usuario, :metodo, 1, CURDATE(), NULL)
    ");
    $ins->execute(['usuario' => $nombre_usuario, 'metodo' => $metodo_pago]);

    // Actualizar el rol del usuario
    $upd2 = $_conexion->prepare("UPDATE usuario SET rol = :rol WHERE nombre = :usuario");
    $upd2->execute(['rol' => $rol_nuevo, 'usuario' => $nombre_usuario]);

    $_conexion->commit();

    // Refrescar la sesión
    $_SESSION['rol'] = $rol_nuevo;

    echo json_encode([
        'success'      => true,
        'plan'         => $plan,
        'plan_nombre'  => $planes_validos[$plan]['nombre'],
        'precio'       => $planes_validos[$plan]['precio'],
        'metodo_pago'  => $metodo_pago,
        'rol'          => $rol_nuevo,
        'redirect'     => '../sesion/user.php?usuario=' . urlencode($nombre_usuario) . '&suscripcion=activada',
    ]);
} catch (PDOException $e) {
    if ($_conexion->inTransaction()) $_conexion->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
}

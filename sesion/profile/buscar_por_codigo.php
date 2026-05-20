<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['nombre'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

require __DIR__ . '/../conexion_pdo.php';
require __DIR__ . '/../premium_helper.php';

if (!esPremium($_conexion, $_SESSION['nombre'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Funcionalidad solo disponible para usuarios premium']);
    exit();
}

$codigo = trim($_GET['codigo'] ?? '');
// Solo dígitos: códigos EAN/UPC son numéricos (8, 12 o 13 dígitos)
if (!preg_match('/^\d{6,14}$/', $codigo)) {
    echo json_encode(['success' => false, 'error' => 'Código no válido']);
    exit();
}

try {
    $stmt = $_conexion->prepare("
        SELECT id, titulo, portada, tipo, genero, nombre_editorial, anno_lanzamiento
        FROM obra
        WHERE codigodebarras = :codigo
        LIMIT 1
    ");
    $stmt->execute(['codigo' => $codigo]);
    $obra = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$obra) {
        echo json_encode(['success' => false, 'error' => 'No hay ninguna obra con ese código en la web', 'codigo' => $codigo]);
        exit();
    }

    echo json_encode(['success' => true, 'obra' => $obra]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
}

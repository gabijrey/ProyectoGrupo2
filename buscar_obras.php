<?php
require "sesion/conexion_pdo.php";

header('Content-Type: application/json');

if (!isset($_GET['q'])) {
    echo json_encode([]);
    exit;
}

$query = trim($_GET['q']);

if (strlen($query) < 1) {
    echo json_encode([]);
    exit;
}

try {
    $consulta = "SELECT id, titulo, portada, tipo FROM obra WHERE titulo LIKE :query LIMIT 8";
    $stmt = $_conexion->prepare($consulta);
    $stmt->execute(['query' => '%' . $query . '%']);
    $resultados = $stmt->fetchAll();
    
    echo json_encode($resultados);
} catch (PDOException $e) {
    echo json_encode([]);
}

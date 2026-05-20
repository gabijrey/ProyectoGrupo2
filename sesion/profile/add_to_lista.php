<?php
session_start();
header("Content-Type: application/json");

if(!isset($_SESSION["nombre"]) || $_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "error" => "No autorizado"
    ]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if(!isset($data["id_obra"]) || !isset($data["id_lista"])) {
    echo json_encode([
        'success' => false,
        'error' => "Faltan datos"
    ]);
    exit();
}

$id_obra = (int)$data["id_obra"];
$id_lista = (int)$data["id_lista"];

require '../conexion_pdo.php';

try {
    //Comprobar que la lista es del usuario
    $consulta = "SELECT id FROM lista WHERE id = :id_lista AND nombre_usuario = :usuario";
    $check = $_conexion->prepare($consulta);
    $check->execute([
        'id_lista' => $id_lista,
        'usuario' => $_SESSION["nombre"]
    ]);

    if(!$check->fetch()) {
        echo json_encode([
            'success' => false,
            'error' => 'La lista no te pertenece'
        ]);
        exit();
    }
    //Insertar en la tabla
    $ins = $_conexion->prepare("INSERT IGNORE INTO lista_obra (id_lista, id_obra) VALUES (:id_lista, :id_obra)");

    $ins->execute([
        "id_lista" => $id_lista,
        "id_obra" => $id_obra
    ]);

    //Si es una nueva fila
    if($ins->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "La obra ya estaba en esta lista"
        ]);
    }     
}
catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error en la base de datos'
    ]);
}
?>
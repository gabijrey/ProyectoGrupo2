<?php
session_start();
header('Content-Type: application/json');

//Si no hay sesion bloqueamos el acceso (estructura util a futuro)
if(!isset($_SESSION["nombre"]) || $_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        'success' => false, 
        'error' => 'No autorizado'
    ]);
    exit();
}

//Adquirir datos de la obra en formato JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id_obra'])) {
    echo json_encode([
        'success' => false, 
        'error' => 'Error al obtener la obra'
    ]);
    exit();
}

$id_obra = (int)$data['id_obra'];
$usuario = $_SESSION['nombre'];

//Conexion
require __DIR__ . '/../conexion_pdo.php';

try {
    //Comprobamos si es favorito o no
    $stmt = $_conexion->prepare("SELECT * FROM favorito WHERE id_obra = :id_obra AND nombre_usuario = :usuario");
    $stmt->execute([
        'id_obra' => $id_obra,
        'usuario' => $usuario
    ]);

    $existe = $stmt->fetch();

    if($existe) {
        //Si existe lo quitamos de favoritos
        $del = $_conexion->prepare("DELETE FROM favorito WHERE id_obra = :id_obra AND nombre_usuario = :usuario");
        $del->execute([
            'id_obra' => $id_obra,
            'usuario' => $usuario
        ]);
        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        //Si no existe lo añadimos
        $ins = $_conexion->prepare("INSERT INTO favorito (id_obra, nombre_usuario) VALUES (:id_obra, :usuario)");
        $ins->execute([
            'id_obra' => $id_obra,
            'usuario' => $usuario
        ]);
        echo json_encode(['success' => true, 'action' => 'added']);
    }
}
catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
}
?>
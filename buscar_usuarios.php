<?php
require "sesion/conexion_pdo.php";

header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

try {
    // 1. Obtener el total de usuarios que coinciden con la búsqueda para la paginación
    $sql_count = "SELECT COUNT(*) FROM usuario WHERE nombre LIKE :query";
    $stmt_count = $_conexion->prepare($sql_count);
    $stmt_count->execute(['query' => '%' . $query . '%']);
    $total_users = $stmt_count->fetchColumn();
    $total_pages = ceil($total_users / $limit);

    // 2. Obtener los usuarios de la página actual con su contador de reseñas
    $sql_users = "SELECT u.nombre, u.img_perfil, COUNT(r.id) as total_resenas 
                  FROM usuario u 
                  LEFT JOIN resena r ON u.nombre = r.nombre_usuario 
                  WHERE u.nombre LIKE :query 
                  GROUP BY u.nombre, u.img_perfil 
                  ORDER BY u.nombre ASC 
                  LIMIT :limit OFFSET :offset";
    
    $stmt_users = $_conexion->prepare($sql_users);
    $stmt_users->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
    $stmt_users->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt_users->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt_users->execute();
    
    $users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'users' => $users,
        'total_pages' => $total_pages,
        'current_page' => $page,
        'total_users' => $total_users
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Error en la base de datos',
        'details' => $e->getMessage()
    ]);
}
?>

<?php
/**
 * Helper para comprobar si el usuario actual es premium.
 *
 * Convención de roles:
 *   0 = Usuario normal
 *   1 = Premium
 *   2 = Autor (también con beneficios premium)
 *   3 = Administrador
 *
 * Además, se considera premium si tiene una suscripción con estado = 1
 * y sin fecha_cancelacion en la tabla `suscripcion`.
 */
function esPremium(PDO $conexion, string $nombre_usuario): bool {
    try {
        $stmt = $conexion->prepare("SELECT rol FROM usuario WHERE nombre = :nombre");
        $stmt->execute(['nombre' => $nombre_usuario]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$usuario) return false;

        // Rol elevado → premium
        if ((int)$usuario['rol'] > 0) return true;

        // O bien suscripción activa
        $stmt = $conexion->prepare("
            SELECT 1 FROM suscripcion
            WHERE nombre_usuario = :nombre AND estado = 1 AND fecha_cancelacion IS NULL
            LIMIT 1
        ");
        $stmt->execute(['nombre' => $nombre_usuario]);
        return (bool)$stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

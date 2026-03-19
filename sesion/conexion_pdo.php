<?php
$_servidor = "localhost";
$_bd = "comiclook";
$_usuario = "admin";
$_contrasena = "admin";

try {
    $_conexion = new PDO(
        "mysql:host=$_servidor;dbname=$_bd;charset=utf8mb4",
        $_usuario,
        $_contrasena
    );
    $_conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $_conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die();
}

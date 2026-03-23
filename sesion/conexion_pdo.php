<?php
//Datos en local
// $_servidor = "localhost";
// $_bd = "comiclook";
// $_usuario = "admin";
// $_contrasena = "admin";

//Datos para el servidor de hosting
$_servidor = 'sql201.infinityfree.com';
$_usuario = 'if0_41430615';
$_contrasena= '95MvF92CEM5U1AY';
$_bd   = 'if0_41430615_comiclook';

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

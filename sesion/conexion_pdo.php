<?php
/*
Tuve que hacer la conexion local en el puerto 3307 ya que en mi ordenador ya estaba ocupado el puerto por defecto (3306) por MySQL. Pero con esta estructura debería de funcionar en local en todos los ordenadores
*/
try {
    // Comprobamos si estamos en entorno local
    if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
        // Datos en local
        $_servidor = "localhost";
        $_bd = "comiclook";
        $_usuario = "root";
        $_contrasena = "";
        
        try {
            // Probamos con el puerto 3307
            $dns = "mysql:host=$_servidor;port=3307;dbname=$_bd;charset=utf8mb4";
            $_conexion = new PDO($dns, $_usuario, $_contrasena);
        } catch (PDOException $e) {
            // Sino buscara el 3306
            $dns = "mysql:host=$_servidor;port=3306;dbname=$_bd;charset=utf8mb4";
            $_conexion = new PDO($dns, $_usuario, $_contrasena);
        }
    } else {
        // Datos en el Hosting
        $_servidor = 'sql201.infinityfree.com';
        $_usuario = 'if0_41430615';
        $_contrasena = '95MvF92CEM5U1AY';
        $_bd = 'if0_41430615_comiclook';
        $dns = "mysql:host=$_servidor;dbname=$_bd;charset=utf8mb4";
        
        $_conexion = new PDO($dns, $_usuario, $_contrasena);
    }
    $_conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $_conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>
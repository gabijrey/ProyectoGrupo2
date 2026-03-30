<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <script src="../Js/FormularioLogin.js"></script>
    <?php
    require "conexion_pdo.php";
    if(isset($_GET["usuarioexistente"])) $err_usuario = "El usuario ya existe";
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $tmp_usuario = $_POST["usuario"];
        $tmp_contrasena = $_POST["contrasena"];

        if($tmp_usuario == "") $err_usuario = "Inserta un usuario";
        else $usuario = $tmp_usuario;
        if($tmp_contrasena == "") $err_contrasena = "Inserta una contrasena";
        else $contrasena = $tmp_contrasena;

        if(isset($contrasena) && isset($usuario)){
            $consulta = $conexion->prepare("SELECT contrasena, rol FROM usuario WHERE nombre = ?");
            $consulta->bind_param("s", $usuario);
            $consulta->execute();
            $resultado = $consulta->get_result();

            if($resultado->num_rows === 0){
                $err_usuario = "El usuario no existe";
            } else {
                $info_usuario = $resultado->fetch_assoc();
                $verificado = password_verify($contrasena, $info_usuario["contrasena"]);
                
                if(!$verificado){
                    $err_contrasena = "La constraseña no coincide";
                } else {
                    $_SESSION["usuario"] = $usuario;
                    $_SESSION["rol"] = $info_usuario["rol"];
                    header("location: ../index.php");
                    exit();
                }
            }
        }
    }
    ?>
</head>
<body>
    <form action="" method="POST" id="Formulario">

        <label for="usuario">Nombre</label>
        <input type="text" id="usuario" name="nombre">
        <?php if(isset($err_usuario)) echo "<p class='FE'>$err_usuario</p>" ;?>
        <hr>

        <label for="contrasena" >Contraseña</label>
        <input type="password" id="password" name="contrasena">
        <?php if(isset($err_contrasena)) echo "<p class='FE'>$err_contrasena</p>" ;?>
        <hr>

        <input type="button" value="Borrar" id="BotonReseteo">
        <input type="submit" value="Enviar" id="BotonEnviar">
    </form>
    <span>¿No tienes cuenta?</span>
    <a href="createUser.php">Registrarse</a>
    <footer>
        Copyright
    </footer>
</body>
</html>
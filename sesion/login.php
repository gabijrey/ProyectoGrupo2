<?php
    session_start();
    require "conexion_pdo.php";
    if(isset($_GET["usuarioexistente"])) $err_usuario = "El usuario ya existe";
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $tmp_usuario = $_POST["usuario"];
        $tmp_contrasena = $_POST["contrasena"];

        if($tmp_usuario == "") $err_usuario = "Inserta un usuario";
        else $usuario = htmlspecialchars($tmp_usuario);
        if($tmp_contrasena == "") $err_contrasena = "Inserta una contrasena";
        else $contrasena = $tmp_contrasena;

        //Si ambos campos estan, sigue la ejecucion
        if(isset($contrasena) && isset($usuario)){
            try {
            // Buscamos al usuario por su nombre (igual que haces en la validación de registro)
            $sql = "SELECT nombre, email, contrasena, rol FROM usuario WHERE nombre = ?";
            $consulta = $_conexion->prepare($sql);
            $consulta->execute([$usuario]);
            
            // Obtenemos el resultado
            $info_usuario = $consulta->fetch(PDO::FETCH_ASSOC);

            if(!$info_usuario){
                $err_usuario = "El usuario no existe";
            } else {
                // Verificamos si la contraseña coincide con el hash de la BD
                if(password_verify($contrasena, $info_usuario["contrasena"])){
                    
                    
                    //Guardar datos del usuario una vez validados
                    $_SESSION["nombre"] = $info_usuario["nombre"];
                    $_SESSION["email"] = $info_usuario["email"];
                    $_SESSION["rol"] = $info_usuario["rol"];

                    // Redirigir al index
                    header("location: ../index.php");
                    exit();
                    
                } else {
                    $err_contrasena = "La contraseña no coincide";
                }
            }
            } catch (PDOException $e) {
                $err_general = "Error en la base de datos: " . $e->getMessage();
            }
        }
    }
    ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | ComicLook</title>
    <script src="../Js/FormularioLogin.js"></script>
    <!-- <script src="../Js/EventosTeclado.js"></script> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="icon" href="../comiclook_icon.ico">
</head>

<body class="bg-dark text-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="text-center mb-5 mt-2">
                    <img src="../logos/logoLight.webp" alt="ComicLook Logo" class="img-fluid" style="max-height: 80px;">
                </div>
                <h3 class="text-center">Accede a tu cuenta</h3>

                <form action="" method="POST" id="Formulario">
                    <div class="mb-3">
                        <label class="form-label" for="nombre">Nombre</label>
                        <input class="form-control" type="text" id="usuario" name="usuario">
                        <?php if(isset($err_usuario)) echo "<p class='FE text-danger'>$err_usuario</p>" ;?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="contrasena">Contraseña</label>
                        <input class="form-control" type="password" id="contrasena" name="contrasena">
                         <?php if(isset($err_contrasena)) echo "<p class='FE text-danger'>$err_contrasena</p>" ;?>
                    </div>
                <div class="d-flex justify-content-between gap-2 mb-3">
                        <input class="btn btn-outline-secondary w-100" type="reset" value="Borrar" id="BotonReseteo">

                        <input class="btn btn-danger w-100" type="submit" value="Iniciar Sesión" id="BotonEnviar">
                    </div>
                </form>
                    <div class="row justify-content-between">
                        <span class="text-center mt-4 mb-3">¿No tienes cuenta? <a class="link-danger"  href="createUser.php">Regístrate</a></span>
                    </div>
                
                <footer class="row justify-content-center">
                    <span class="text-center mt-4 mb-3">Copyright &copy; <?php echo date("Y"); ?> ComicLook</span>
                </footer>
            </div>
        </div>
    </div>

</body>

</html>
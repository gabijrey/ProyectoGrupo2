<?php
session_start();
require "conexion_pdo.php";
if (isset($_GET["usuarioexistente"]))
    $err_usuario = "El usuario ya existe";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tmp_usuario = $_POST["usuario"];
    $tmp_contrasena = $_POST["contrasena"];

    if ($tmp_usuario == "")
        $err_usuario = "Inserta un usuario";
    else
        $usuario = htmlspecialchars($tmp_usuario);
    if ($tmp_contrasena == "")
        $err_contrasena = "Inserta una contrasena";
    else
        $contrasena = $tmp_contrasena;

    //Si ambos campos estan, sigue la ejecucion
    if (isset($contrasena) && isset($usuario)) {
        try {
            // Buscamos al usuario por su nombre (igual que haces en la validación de registro)
            $sql = "SELECT nombre, email, contrasena, rol FROM usuario WHERE nombre = ?";
            $consulta = $_conexion->prepare($sql);
            $consulta->execute([$usuario]);

            // Obtenemos el resultado
            $info_usuario = $consulta->fetch(PDO::FETCH_ASSOC);

            if (!$info_usuario) {
                $err_usuario = "El usuario no existe";
            } else {
                // Verificamos si la contraseña coincide con el hash de la BD
                if (password_verify($contrasena, $info_usuario["contrasena"])) {


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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body class="bg-dark text-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="text-center mb-5 mt-2">
                    <img src="../logos/logoLight.webp" alt="ComicLook Logo" class="img-fluid" style="max-height: 80px;">
                </div>
                <h3 class="text-center">Accede a tu cuenta</h3>

                <?php if (isset($_GET['recuperar'])): ?>
                    <?php if ($_GET['recuperar'] === 'enviado'): ?>
                        <div class="alert alert-success py-2 text-center" style="border-radius: 8px; font-size: 0.9em;">
                            ✉️ Se ha enviado un correo con las instrucciones de recuperación.
                        </div>
                    <?php elseif ($_GET['recuperar'] === 'email_no_encontrado'): ?>
                        <div class="alert alert-danger py-2 text-center" style="border-radius: 8px; font-size: 0.9em;">
                            No se encontró ninguna cuenta con ese correo.
                        </div>
                    <?php elseif ($_GET['recuperar'] === 'error' || $_GET['recuperar'] === 'error_mail'): ?>
                        <div class="alert alert-danger py-2 text-center" style="border-radius: 8px; font-size: 0.9em;">
                            Ocurrió un error al enviar el correo. Inténtalo de nuevo.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <form action="" method="POST" id="Formulario">
                    <div class="mb-3">
                        <label class="form-label" for="nombre">Nombre</label>
                        <input class="form-control" type="text" id="usuario" name="usuario">
                        <?php if (isset($err_usuario))
                            echo "<p class='FE'>$err_usuario</p>"; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="contrasena">Contraseña</label>
                        <input class="form-control" type="password" id="contrasena" name="contrasena">
                        <?php if (isset($err_contrasena))
                            echo "<p class='FE'>$err_contrasena</p>"; ?>
                    </div>

                </form>
                <div class="d-flex justify-content-between gap-2 mb-3">
                    <input class="btn btn-outline-secondary w-100" type="reset" value="Borrar" id="BotonReseteo">

                    <input class="btn btn-danger w-100" type="submit" value="Iniciar Sesión" id="BotonEnviar">
                </div>
                <div class="d-flex flex-column text-center mt-3 gap-2 mb-3">

                    <span>¿No tienes cuenta? <a class="link-danger text-decoration-none"
                            href="createUser.php">Regístrate</a></span>
                    <span class="text-muted text-decoration-underline" style="cursor: pointer; font-size: 0.95em;"
                        data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">¿Has
                        olvidado tu contraseña?</span>
                </div>


                <footer class="row justify-content-center">
                    <span class="text-center mt-4 mb-3">Copyright &copy; <?php echo date("Y"); ?> ComicLook</span>
                </footer>
            </div>
        </div>
    </div>

    <!-- Modal Recuperar Contraseña -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-light shadow" style="border: 1px solid #444; border-radius: 12px;">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">Recuperar Contraseña</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <p class="text-muted mb-4" style="font-size: 0.95em;">Introduzca tu correo para recibir las
                        instrucciones de recuperación.</p>
                    <form action="enviarRecuperacion.php" method="POST">
                        <div class="mb-4">
                            <label for="correoRecuperacion" class="form-label ms-1">Correo electrónico</label>
                            <input type="email" class="form-control bg-dark text-light py-2"
                                style="border-color: #555; border-radius: 8px;" id="correoRecuperacion"
                                name="correo_recuperacion" placeholder="ejemplo@correo.com" required>
                        </div>
                        <div class="d-grid mt-2">
                            <button type="submit" class="btn btn-danger py-2" style="border-radius: 8px;">Enviar
                                correo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
</body>

</html>
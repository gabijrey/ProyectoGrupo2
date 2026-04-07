<?php
session_start();
require "conexion_pdo.php";

// Misma clave secreta que en enviarRecuperacion.php
define('SECRET_KEY', 'ComicLook_SecretKey_2026_!@#');

$token_valido = false;
$email_usuario = '';
$mensaje_exito = '';
$mensaje_error = '';

// Verificar el token del enlace
if (isset($_GET['token'])) {
    $token_raw = base64_decode($_GET['token']);
    $partes = explode('|', $token_raw);

    if (count($partes) === 3) {
        $email_token = $partes[0];
        $expiracion = $partes[1];
        $firma_recibida = $partes[2];

        // Verificar la firma HMAC
        $datos = $email_token . '|' . $expiracion;
        $firma_esperada = hash_hmac('sha256', $datos, SECRET_KEY);

        if (hash_equals($firma_esperada, $firma_recibida)) {
            // Verificar que no ha expirado
            if (time() <= $expiracion) {
                $token_valido = true;
                $email_usuario = $email_token;
            } else {
                $mensaje_error = "El enlace ha expirado. Solicita uno nuevo desde el inicio de sesión.";
            }
        } else {
            $mensaje_error = "El enlace no es válido.";
        }
    } else {
        $mensaje_error = "El enlace no es válido.";
    }
}

// Procesar el formulario de nueva contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['token'])) {
    $token_raw = base64_decode($_POST['token']);
    $partes = explode('|', $token_raw);

    if (count($partes) === 3) {
        $email_token = $partes[0];
        $expiracion = $partes[1];
        $firma_recibida = $partes[2];

        $datos = $email_token . '|' . $expiracion;
        $firma_esperada = hash_hmac('sha256', $datos, SECRET_KEY);

        if (hash_equals($firma_esperada, $firma_recibida) && time() <= $expiracion) {
            $nueva_pass = $_POST['nueva_contrasena'];
            $confirmar_pass = $_POST['confirmar_contrasena'];

            // Validar que las contraseñas coincidan
            if ($nueva_pass !== $confirmar_pass) {
                $mensaje_error = "Las contraseñas no coinciden.";
                $token_valido = true;
                $email_usuario = $email_token;
            }
            // Validar requisitos de la contraseña
            else {
                $patron = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).*$/';
                if (!preg_match($patron, $nueva_pass)) {
                    $mensaje_error = "La contraseña debe contener una mayúscula, una minúscula, un número y un carácter especial.";
                    $token_valido = true;
                    $email_usuario = $email_token;
                } else {
                    // Actualizar la contraseña en la base de datos
                    try {
                        $hash = password_hash($nueva_pass, PASSWORD_DEFAULT);
                        $sql = "UPDATE usuario SET contrasena = ? WHERE email = ?";
                        $consulta = $_conexion->prepare($sql);
                        $consulta->execute([$hash, $email_token]);

                        if ($consulta->rowCount() > 0) {
                            $mensaje_exito = "¡Contraseña actualizada correctamente! Ya puedes iniciar sesión.";
                            $token_valido = false; // Ocultar el formulario
                        } else {
                            $mensaje_error = "No se encontró la cuenta asociada a este correo.";
                        }
                    } catch (PDOException $e) {
                        $mensaje_error = "Error al actualizar la contraseña. Inténtalo de nuevo.";
                        $token_valido = true;
                        $email_usuario = $email_token;
                    }
                }
            }
        } else {
            $mensaje_error = "El enlace ha expirado o no es válido. Solicita uno nuevo.";
        }
    } else {
        $mensaje_error = "Token no válido.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña | ComicLook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body class="bg-dark text-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="text-center mb-5 mt-2">
                    <img src="../logos/logoLight.webp" alt="ComicLook Logo" class="img-fluid"
                        style="max-height: 80px;">
                </div>
                <h3 class="text-center mb-4">Restablecer Contraseña</h3>

                <?php if ($mensaje_exito): ?>
                    <!-- Mensaje de éxito -->
                    <div class="text-center">
                        <div class="mb-4 p-4" style="background-color: #1a3a2a; border: 1px solid #2d6a4f; border-radius: 12px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#2d6a4f" viewBox="0 0 16 16" class="mb-3">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                            </svg>
                            <p class="text-light mb-0"><?php echo $mensaje_exito; ?></p>
                        </div>
                        <a href="login.php" class="btn btn-danger w-100 py-2" style="border-radius: 8px;">Ir a Iniciar Sesión</a>
                    </div>

                <?php elseif ($token_valido): ?>
                    <!-- Formulario de nueva contraseña -->
                    <?php if ($mensaje_error): ?>
                        <div class="alert alert-danger py-2" style="border-radius: 8px; font-size: 0.9em;">
                            <?php echo $mensaje_error; ?>
                        </div>
                    <?php endif; ?>

                    <p class="text-muted text-center mb-4" style="font-size: 0.95em;">
                        Introduce tu nueva contraseña para la cuenta asociada a
                        <strong class="text-light"><?php echo htmlspecialchars($email_usuario); ?></strong>
                    </p>

                    <form action="recuperarPass.php" method="POST">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? $_POST['token'] ?? ''); ?>">

                        <div class="mb-3">
                            <label for="nueva_contrasena" class="form-label ms-1">Nueva contraseña</label>
                            <input type="password" class="form-control bg-dark text-light py-2"
                                style="border-color: #555; border-radius: 8px;"
                                id="nueva_contrasena" name="nueva_contrasena" required>
                            <small class="text-muted">Debe contener una mayúscula, una minúscula, un número y un carácter especial</small>
                        </div>

                        <div class="mb-4">
                            <label for="confirmar_contrasena" class="form-label ms-1">Confirmar contraseña</label>
                            <input type="password" class="form-control bg-dark text-light py-2"
                                style="border-color: #555; border-radius: 8px;"
                                id="confirmar_contrasena" name="confirmar_contrasena" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger py-2" style="border-radius: 8px;">
                                Cambiar contraseña
                            </button>
                        </div>
                    </form>

                <?php else: ?>
                    <!-- Token inválido o no proporcionado -->
                    <div class="text-center">
                        <div class="mb-4 p-4" style="background-color: #3a1a1a; border: 1px solid #6a2d2d; border-radius: 12px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#dc3545" viewBox="0 0 16 16" class="mb-3">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
                            </svg>
                            <p class="text-light mb-0">
                                <?php echo $mensaje_error ?: "No se ha proporcionado un enlace válido de recuperación."; ?>
                            </p>
                        </div>
                        <a href="login.php" class="btn btn-outline-secondary w-100 py-2" style="border-radius: 8px;">
                            Volver al inicio de sesión
                        </a>
                    </div>
                <?php endif; ?>

                <footer class="row justify-content-center">
                    <span class="text-center mt-4 mb-3">Copyright &copy; <?php echo date("Y"); ?> ComicLook</span>
                </footer>
            </div>
        </div>
    </div>
</body>

</html>

<?php
session_start();
require "conexion_pdo.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PhpMailer/Exception.php';
require 'PhpMailer/PHPMailer.php';
require 'PhpMailer/SMTP.php';

// Clave secreta para firmar los tokens (cámbiala por algo único de tu proyecto)
define('SECRET_KEY', 'ComicLook_SecretKey_2026_!@#');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars(trim($_POST["correo_recuperacion"]));

    // Verificar que el email existe en la base de datos
    try {
        $sql = "SELECT nombre, email FROM usuario WHERE email = ?";
        $consulta = $_conexion->prepare($sql);
        $consulta->execute([$email]);
        $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            // Redirigir con error
            header("location: login.php?recuperar=email_no_encontrado");
            exit();
        }

        // Generar token firmado (válido por 1 hora)
        $expiracion = time() + 3600; // 1 hora
        $datos = $email . '|' . $expiracion;
        $firma = hash_hmac('sha256', $datos, SECRET_KEY);
        $token = base64_encode($datos . '|' . $firma);

        // Construir el enlace de recuperación
        // Detectar si es local o en hosting
        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $dominio = $_SERVER['HTTP_HOST'];
        $enlace = $protocolo . '://' . $dominio . '/sesion/recuperarPass.php?token=' . urlencode($token);

        // Enviar el correo con PHPMailer
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'comiclook.info@gmail.com';
        $mail->Password = 'mfjktuyyfcstohvr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('comiclook.info@gmail.com', 'ComicLook');
        $mail->addAddress($email);
        $mail->Subject = 'Recuperación de contraseña - ComicLook';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: #1a1a2e; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;'>
                    <h1 style='color: #e94560; margin: 0; font-size: 24px;'>ComicLook</h1>
                </div>
                <div style='background-color: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px;'>
                    <h2 style='color: #2c3e50; margin-top: 0;'>Recupera tu contraseña</h2>
                    <p>Hola <strong>" . htmlspecialchars($usuario['nombre']) . "</strong>,</p>
                    <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en ComicLook.</p>
                    <p>Haz clic en el siguiente botón para crear una nueva contraseña:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . $enlace . "' style='background-color: #e94560; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;'>Restablecer contraseña</a>
                    </div>
                    <p style='color: #7f8c8d; font-size: 0.9em;'>Este enlace expirará en <strong>1 hora</strong>.</p>
                    <p style='color: #7f8c8d; font-size: 0.9em;'>Si no has solicitado este cambio, ignora este correo y tu contraseña seguirá siendo la misma.</p>
                    <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
                    <p style='font-size: 0.8em; color: #7f8c8d;'>Atentamente,<br>El equipo de ComicLook</p>
                </div>
            </div>
        ";
        $mail->AltBody = "Hola " . $usuario['nombre'] . ", recupera tu contraseña en este enlace: " . $enlace;
        $mail->send();

        // Redirigir con mensaje de éxito
        header("location: login.php?recuperar=enviado");
        exit();

    } catch (PDOException $e) {
        header("location: login.php?recuperar=error");
        exit();
    } catch (Exception $e) {
        header("location: login.php?recuperar=error_mail");
        exit();
    }
} else {
    header("location: login.php");
    exit();
}
?>
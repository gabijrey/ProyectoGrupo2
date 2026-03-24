<?php
//Mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    
    

//Archivos requeridos
require "conexion_pdo.php";
// require 'vendor/autoload.php'; 

//Clases a usar
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PhpMailer/Exception.php';
require 'PhpMailer/PHPMailer.php';
require 'PhpMailer/SMTP.php';

//Validar los datos
if($_SERVER["REQUEST_METHOD"] == "POST"){

    //Booleano para indicar que todo se valido correctamente
    $validado = true;
    //Variables temporales a validar
    $tmp_nombre = $_POST["nombre"];
    $tmp_email = $_POST["email"];
    $tmp_contrasena = $_POST["contrasena"];
   // $tmp_nacionalidad = $_POST["nacionalidad"];

    //Validar nombre
    $tmp_nombre = htmlspecialchars($tmp_nombre); //Eliminar caracteres especiales
    if(strlen($tmp_nombre) < 5) {
     $err_nombre = "El nombre debe tener más de 5 caracteres, vuelva a intentarlo.";
     $validado = false;
    } 
    else{
        $nombre = $tmp_nombre;
        try{
            $consulta = $_conexion->prepare("SELECT * FROM usuario WHERE nombre = ?");
            //$res = $_conexion->prepare($consulta);
            $consulta->execute([$tmp_nombre]);
            if ($consulta->rowCount() === 0) $nombre = $tmp_nombre;
            else {
                $err_nombre = "El usuario ya existe, pruebe a iniciar sesión. ";
                $validado = false;
            } 
        }catch(PDOException $e) {
            $e->getMessage();
        }
    }

    //Validar mail
    $tmp_email = htmlspecialchars($tmp_email);
    if (filter_var($tmp_email, FILTER_VALIDATE_EMAIL)) {
        //Confirmar que el usuario no existe en la base de datos
        try{
            $consulta = $_conexion->prepare("SELECT * FROM usuario WHERE email = ?");
            $consulta->execute([$tmp_email]);
            //$res = $_conexion->prepare($consulta);
            if ($consulta->rowCount() == 0) $email = $tmp_email;
            else {
              $err_mail = "El correo ya existe, pruebe a iniciar sesión. ";
                $validado = false;
            } 
        }catch(PDOException $e) {
            $e->getMessage();
        }
    }else {
        $err_mail = "Formato de mail no aceptado, vuelva a intentarlo.";
        $validado = false;
    }
	
    //Validar la contraseña
    $tmp_contrasena = htmlspecialchars($tmp_contrasena);
    $patron = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).*$/';
    if (preg_match($patron, $tmp_contrasena)) {
        $contrasena = password_hash($tmp_contrasena, PASSWORD_DEFAULT);
    } else {
       $err_contrasena = "La contraseña no cumple los requisitos, vuelve a intentarlo.";
        $validado = false; //no se ha validado todo
    }
    
    //Insertar los datos en la base de datos
    if($validado && isset($nombre) && isset($email) && isset($contrasena)) {
        try {
        //Consulta a insertar
        $_conexion->beginTransaction();
        //Formato de fecha_registro
        $fecha_registro = date("Y-m-d");
        //Consulta sql
        $sql = "INSERT INTO usuario (nombre, email, contrasena, fecha_registro, rol) 
                VALUES (:nombre, :email, :contrasena, :fecha_registro, :rol)";
        $consulta = $_conexion->prepare($sql);
        //Bindeo de parametros
        /*Lo he cambiado a bindValue porque para rol no funcionaba bindParam
        ya que no permite pasar valores literales, habria que crear una variable inicial
        pero por facilidad he visto mejor usar bindValue
        */
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindValue(':email', $email, PDO::PARAM_STR);
        $consulta->bindValue(':contrasena', $contrasena, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_registro', $fecha_registro, PDO::PARAM_STR);
        $consulta->bindValue(':rol', 0, PDO::PARAM_INT); //Se pone como usuario normal al principio, luego en la base de datos se cambia
        // Ejecutamos la consulta ya armada
        $consulta->execute();
        // Confirmamos la transacción
        $_conexion->commit();
        
        //Enviar un mail al usuario si se ha realizado correctamente el registro
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; //Hay que usar el mail del servidor si es que hay
        $mail->SMTPAuth = true;
        $mail->Username = 'comiclook.info@gmail.com';
        $mail->Password = 'mfjktuyyfcstohvr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->isHTML(true); // Habilitar formato HTML
        $mail->CharSet = 'UTF-8'; 
        $mail->setFrom('comiclook.info@gmail.com', 'ComicLook');
        $mail->addAddress($email);
        $mail->Subject = '¡Bienvenido a ComicLook, ' . $nombre . '!';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <h2 style='color: #2c3e50;'>¡Hola, " . $nombre . "!</h2>
                <p>Estamos muy emocionados de darte la bienvenida a <strong>ComicLook</strong>.</p>
                <p>Tu cuenta ha sido creada exitosamente con los siguientes datos:</p>
                <ul>
                    <li><strong>Usuario:</strong> " . $email . "</li>
                </ul>
                <p>A partir de ahora, podrás acceder a nuestra plataforma y disfrutar de todo el contenido que tenemos preparado para ti.</p>
                <br>
                <a href='http://comiclook.com' style='background-color: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir a mi cuenta</a>
                <br><br>
                <p>Si no te has registrado en nuestro sitio, por favor ignora este correo.</p>
                <hr>
                <p style='font-size: 0.8em; color: #7f8c8d;'>Atentamente,<br>El equipo de ComicLook</p>
            </div>
        ";
        //Texto alternativo para clientes que no soportan HTML
        $mail->AltBody = "¡Hola " . $nombre . "! Bienvenido a ComicLook. Tu cuenta ha sido creada con el correo " . $email . ".";
        //Enviar el correo
        $mail->send();
        //Por el momento se enviará a iniciar sesion por temas del rol
        header("location: login.php");
    } catch(PDOException $e) {
        if ($_conexion->inTransaction()) {
            $_conexion->rollBack();
        }
        $err_registro = "No se ha podido registrar al usuario";
    }
   }
   
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <script src="../Js/js.js"></script>
</head>
<body>
    <form action="" method="POST" id="Formulario">
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre">
        <?php 
        if(isset($err_nombre)) echo "<p class='FE'>$err_nombre</p>" ;
        else echo "<p class='FE'></p>";
        ?>
        <hr>
        <label for="email" >Email</label>
        <input type="text" id="apellidos" name="email">
        <?php 
        if(isset($err_mail)) echo "<p class='FE'>$err_mail</p>" ;
        else echo "<p class='FE'></p>";
        ?>
        <hr>
        <label for="contrasena" >Contraseña</label>
        <input type="password" id="password" name="contrasena">
        <?php 
        if(isset($err_contrasena)) echo "<p class='FE'>$err_contrasena</p>" ;
        else echo "<p class='FE'></p>";
        ?>
        <hr>
        <input type="reset" value="BotonReseteo" id="BotonReseteo">
        <input type="submit" value="BotonEnviar" id="BotonEnviar">
    </form>
    <span>¿Tienes una cuenta?</span>
    <a href="login.php">Iniciar sesión</a>
    <footer>
        Copyright
    </footer>
</body>
</html>
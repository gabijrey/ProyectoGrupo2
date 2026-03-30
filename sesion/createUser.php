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
        $bio = "";
        $img_perfil = "imagen/perfiles/default.png";
        //Consulta sql
        $sql = "INSERT INTO usuario (nombre, email, contrasena, fecha_registro, rol, bio, img_perfil) 
                VALUES (:nombre, :email, :contrasena, :fecha_registro, :rol, :bio, :img_perfil)";
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
        $consulta->bindValue(':bio', $bio, PDO::PARAM_STR);
        $consulta->bindValue(':img_perfil', $img_perfil, PDO::PARAM_STR);
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
                    <li><strong>Usuario:</strong> " . $nombre . "</li>
                </ul>
                <p>A partir de ahora, podrás acceder a nuestra plataforma y disfrutar de todo el contenido que tenemos preparado para ti.</p>
                <br>
                <a href='https://comiclook-tfg.infinityfreeapp.com/' style='background-color: #901735; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir a mi cuenta</a>
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
    <title>Registrarse | ComicLook</title>
    <script src="../Js/FormularioRegistro.js"></script>
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
                <h3 class="text-center">Formulario de registro</h3>
                <form action="" method="POST" id="Formulario">
                    <div class="mb-3">
                        <label class="form-label" for="nombre">Nombre</label>
                        <input class="form-control" type="text" id="nombre" name="nombre">
                         <?php 
                            if(isset($err_nombre)) echo "<p class='FE'>$err_nombre</p>" ;
                            else echo "<p class='FE'></p>";
                            ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control" type="text" id="email" name="email">
                        <?php 
                        if(isset($err_mail)) echo "<p class='FE'>$err_mail</p>" ;
                        else echo "<p class='FE'></p>";
                        ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="contrasena">Contraseña</label>
                        <input class="form-control" type="password" id="contrasena" name="contrasena">
                        <small>Debe contener una mayúscula, una minúscula, un número y un carácter especial</small>
                        <?php 
                    	if(isset($err_contrasena)) echo "<p class='FE'>$err_contrasena</p>" ;
                    	else echo "<p class='FE'></p>";
                    	?>
                    </div>
                    <div class="d-flex justify-content-between gap-2 mb-3">
                        <input class="btn btn-outline-secondary w-100" type="reset" value="Borrar" id="BotonReseteo">

                        <input class="btn btn-danger w-100" type="submit" value="Registrarse" id="BotonEnviar">
                    </div>

                </form>
                <div class="row justify-content-between">
                    <span class="text-center mt-4 mb-3">¿Tienes una cuenta? <a class="link-danger" href="login.php">Iniciar sesión</a></span>
                </div>
                <footer class="row justify-content-center">
                    <span class="text-center mt-4 mb-3">Copyright &copy;</span>
                </footer>
            </div>
        </div>
    </div>
</body>
</html>
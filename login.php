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
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | ComicLook</title>
    <script src="../Js/FormularioLogin.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>
        .font-comic { font-family: 'Bangers', cursive; }
        /* Simplificamos el fondo a un color neutro de Tailwind */
        body {
            background-color: #171717; /* neutral-900 aproximado */
            background-image: radial-gradient(#333 1px, transparent 1px);
            background-size: 20px 20px;
        }
        .FE {
            color: #9f1239; /* rose-800 para los errores también */
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4 bg-neutral-900">
    
    <div class="w-full max-w-md bg-yellow-500 border-4 border-black p-8 shadow-[10px_10px_0_0_black]">
        
        <div class="flex justify-center mb-6">
            <img src="../logos/logoLight.webp" alt="ComicLook Logo" class="h-16 drop-shadow-[4px_4px_0_black]">
        </div>

        <h3 class="font-comic text-3xl text-center text-black uppercase tracking-tight mb-8 italic">
            ¡Accede a tu cuenta!
        </h3>

        <form action="../index.php" method="POST" id="Formulario" class="space-y-6">
            
            <div>
                <label for="usuario" class="block text-black font-bold uppercase text-sm mb-1">Nombre:</label>
                <input type="text" id="usuario" name="usuario" class="w-full border-4 border-black p-3 focus:outline-none focus:bg-white font-bold text-black" placeholder="Nombre">
                <?php if(isset($err_usuario)) echo "<p>$err_usuario</p>" ;?>
            </div>

            <div class="mb-3">
                <label for="contrasena" class="block text-black font-bold uppercase text-sm mb-1">Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena" 
                    class="w-full border-4 border-black p-3 focus:outline-none focus:bg-white font-bold text-black" placeholder="********">
                <?php if(isset($err_contrasena)) echo "<p >$err_contrasena</p>" ;?>
            </div>

            <div class="flex gap-4 pt-4">

                <input type="submit" value="Iniciar Sesión" id="BotonEnviar" 
                    class="flex-1 bg-rose-800 text-white font-bold py-3 border-4 border-black shadow-[4px_4px_0_0_black] cursor-pointer hover:bg-rose-900 transition-all active:translate-y-1 active:shadow-none">
            </div>
            
        </form>

        <div class="mt-8 text-center">
            <span class="text-black font-bold text-sm uppercase">
                ¿No tienes cuenta? 
                <a href="createUser.php" class="text-rose-800 underline hover:text-rose-950">Regístrate</a>
            </span>
        </div>
        
        <footer class="mt-4 text-center border-t-2 border-black pt-4">
            <span class="text-xs text-black font-bold uppercase">
                Copyright &copy; <?php echo date("Y"); ?> ComicLook
            </span>
        </footer>

    </div>

</body>
</html>
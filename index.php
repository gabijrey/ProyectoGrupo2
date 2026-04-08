<?php
//Iniciar la sesion
session_start();

//Comprobar si existe la variable de sesión que crearemos en el login
if (!isset($_SESSION['nombre'])) {
    // Lo mandamos de vuelta al login
    header("Location: sesion/login.php");
    exit();
}

//Si llega aquí, es que está logueado
$nombre = $_SESSION['nombre'];
$email = $_SESSION['email'];

require "sesion/conexion_pdo.php";
// Función rápida para evitar repetir código en los 3 bloques
function obtenerNovedad($conexion, $tipo) {
    $consulta = "SELECT * FROM obra WHERE tipo = :tipo ORDER BY id DESC LIMIT 1";
    $stmt = $conexion->prepare($consulta);
    $stmt->execute(['tipo' => $tipo]);
    return $stmt->fetch();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comiclook | Inicio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>
        .font-comic { font-family: 'Bangers', cursive; }
        body {
            background-color: #171717; /* neutral-900 */
            background-image: radial-gradient(#333 1px, transparent 1px);
            background-size: 20px 20px;
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #18181b; }
        ::-webkit-scrollbar-thumb { background: #9f1239; border-radius: 3px; }
    </style>
</head>
<body class="text-white flex flex-col min-h-screen">

    <nav class="bg-neutral-900 p-6 grid grid-cols-1 md:grid-cols-3 items-center border-b-8 border-black sticky top-0 z-50">
        <div class="flex justify-start">
            <img src="logos/logoLight.webp" class="h-10 w-auto drop-shadow-[4px_4px_0_black]" alt="Logo">
        </div>
        <div class="flex justify-center items-center gap-8 font-comic text-xl">
            <a href="index.php" class="text-rose-800 hover:text-white transition-colors uppercase tracking-widest">Inicio</a>
            <a href="catalogo.php?tipo=0" class="hover:text-rose-800 transition-colors uppercase tracking-widest">Comics</a>
            <a href="catalogo.php?tipo=1" class="hover:text-rose-800 transition-colors uppercase tracking-widest">Mangas</a>
            <a href="catalogo.php?tipo=2" class="hover:text-rose-800 transition-colors uppercase tracking-widest">Libros</a>
        </div>
        <div class="flex justify-end items-center gap-4">
            <span class="text-sm font-bold bg-yellow-500 text-black px-2 border-2 border-black hidden md:block">
                HOLA, <?= strtoupper($_SESSION['nombre']) ?>
            </span>
            <a class="bg-rose-800 text-white px-4 py-2 border-4 border-black font-bold hover:bg-rose-900 transition-all shadow-[4px_4px_0_0_black] active:shadow-none active:translate-y-1 text-xs" href="user.php?usuario=<?= $_SESSION['nombre'] ?>">MI CUENTA</a>
            <a class="bg-black text-white px-4 py-2 border-4 border-black font-bold hover:bg-neutral-800 transition-all text-xs" href="sesion/logout.php">LOGOUT</a>
        </div>
    </nav>

    <main class="flex-grow p-10 flex flex-col items-center">
        <h1 class="font-comic text-6xl md:text-8xl mb-16 text-rose-800 uppercase tracking-tighter drop-shadow-[6px_6px_0_black] italic">
            Novedades <span class="text-white">de la semana</span>
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-16 w-full max-w-6xl">
            <?php 
            $tipos = [0 => 'Último Cómic', 1 => 'Último Manga', 2 => 'Último Libro'];
            foreach($tipos as $num => $nombre_tipo){
                $obra = obtenerNovedad($_conexion, $num);
            ?>
            <div class="flex flex-col items-center group">
                <h2 class="font-comic text-2xl mb-4 text-yellow-500 uppercase bg-black px-4 py-1 rotate-[-2deg] border-2 border-white shadow-[4px_4px_0_0_black]">
                    <?= $nombre_tipo ?>
                </h2>
                
                <div class="w-full aspect-[2/3] bg-white border-4 border-black overflow-hidden shadow-[12px_12px_0_0_black] transition-all group-hover:translate-x-1 group-hover:translate-y-1 group-hover:shadow-none">
                    <?php if ($obra){ ?>
                        <a href="obra.php?id=<?= $obra['id'] ?>">
                            <img class="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500" src="<?= $obra['portada'] ?>" alt="<?= $obra['titulo'] ?>">
                        </a>
                    <?php }else{ ?>
                        <div class="flex items-center justify-center h-full text-black font-comic text-2xl italic">PRÓXIMAMENTE...</div>
                    <?php }; ?>
                </div>

                <div class="mt-8 bg-white border-4 border-black p-4 w-full shadow-[6px_6px_0_0_black]">
                    <p class="text-black text-xl font-bold uppercase truncate"><?= $obra['titulo'] ?? '???' ?></p>
                    <a class="inline-block w-full text-center bg-rose-800 text-white font-bold py-2 border-4 border-black mt-3 hover:bg-rose-900 transition-all" href="obra.php?id=<?= $obra['id'] ?>">
                        VER MÁS
                    </a>
                </div>
            </div>
            <?php }; ?>
        </div>
    </main>

    <footer class="bg-black w-full border-t-8 border-rose-800 py-10 px-6 text-center">
        <div class="inline-block bg-yellow-500 p-2 border-4 border-black shadow-[6px_6px_0_0_white]">
            <p class="text-sm text-black font-black uppercase tracking-widest">© 2026 TFG DAW - PROYECTO COMICLOOK </p>
            <p></p>
        </div>
    </footer>

    <div id="event-widget" class="fixed bottom-6 right-6 z-50 max-w-xs bg-white border-4 border-black shadow-[10px_10px_0_0_#9f1239] overflow-hidden transform transition-all hover:scale-105">
        <div class="bg-black px-4 py-2 text-[12px] font-comic text-yellow-500 uppercase tracking-widest flex justify-between items-center">
            <span>¡AVISO ESPECIAL!</span>
            <button onclick="document.getElementById('event-widget').remove()" class="text-white hover:text-rose-500 font-bold">X</button>
        </div>
        <div class="p-4 flex gap-4">
            <div class="flex-shrink-0 bg-rose-800 border-2 border-black p-2 text-center flex flex-col justify-center min-w-[70px] shadow-[3px_3px_0_0_black]">
                <?php
                    $fecha_evento = new DateTime('2026-10-08'); 
                    $hoy = new DateTime();
                    $diferencia = $hoy->diff($fecha_evento);
                    $dias_restantes = $diferencia->format('%a');
                ?>
                <span class="text-3xl font-comic text-white leading-none"><?= $dias_restantes ?></span>
                <span class="text-[10px] font-bold uppercase text-yellow-400">Días</span>
            </div>
            <div>
                <h4 class="font-black text-black text-sm uppercase">📍 Comic Con Málaga</h4>
                <p class="text-[10px] text-neutral-700 font-bold mt-1 leading-tight">¡TE ESPERAMOS EN EL <span class="bg-yellow-400 px-1">STAND B-12</span> CON TU MEJOR COSPLAY!</p>
                <a href="https://sandiegocomicconmalaga.com/" target="_blank" class="inline-block mt-2 text-[11px] text-rose-800 font-black hover:underline italic uppercase">¡VER INFO! →</a>
            </div>
        </div>
    </div>
</body>
</html>
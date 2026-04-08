<?php
session_start();
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
    <link rel="icon" href="comiclook_icon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-zinc-900 text-white flex flex-col min-h-screen">
    <nav class="bg-zinc-900 p-6 grid grid-cols-3 items-center border-b-4 border-zinc-950 sticky top-0 z-50">
        <div class="flex justify-start">
            <img src="logos/logoLight.webp" class="h-10 w-auto" alt="Logo">
        </div>
        <div class="flex justify-center items-center gap-6">
            <a href="index.php" class="hover:text-rose-500 transition-colors">Inicio</a>
            <a href="catalogo.php?tipo=0" class="hover:text-rose-500 transition-colors">Comics</a>
            <a href="catalogo.php?tipo=1" class="hover:text-rose-500 transition-colors">Mangas</a>
            <a href="catalogo.php?tipo=2" class="hover:text-rose-500 transition-colors">Libros</a>
        </div>
        <div class="flex justify-end items-center gap-3">
            <?php if(!isset($_SESSION['nombre'])){ ?>
                <a class="bg-rose-800 text-white px-4 py-2 hover:bg-rose-900 transition-colors rounded-lg text-sm" href="sesion/login.php">Iniciar sesión</a>
                <a class="border border-rose-800 text-white px-4 py-2 hover:bg-rose-800 transition-colors rounded-lg text-sm" href="sesion/createUser.php">Registrarse</a>
            <?php }else{ ?>
                <span class="text-sm">Hola, @<?= $_SESSION['nombre'] ?></span>
                <a class="bg-rose-800 text-white px-4 py-2 hover:bg-rose-900 transition-colors rounded-lg text-sm" href="user.php?usuario=<?= $_SESSION['nombre'] ?>">Mi cuenta</a>
                <a class="border border-rose-800 text-white px-4 py-2 hover:bg-rose-800 transition-colors rounded-lg text-sm" href="sesion/logout.php">Logout</a>
            <?php } ?>
        </div>
    </nav>
    <main class="flex-grow p-10 flex flex-col items-center">
        <h1 class="text-5xl font-bold mb-12">Novedades de la semana</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 w-full max-w-6xl">
            <?php 
            $tipos = [0 => 'Último Cómic', 1 => 'Último Manga', 2 => 'Último Libro'];
            foreach($tipos as $num => $nombre){
                $obra = obtenerNovedad($_conexion, $num);
            ?>
            <div class="flex flex-col items-center">
                <h2 class="text-xl mb-4 text-rose-500 font-semibold"><?= $nombre ?></h2>
                <div class="w-full aspect-[2/3] bg-zinc-800 rounded-lg overflow-hidden shadow-2xl border-2 border-zinc-700 hover:border-rose-800 transition-all transform hover:-translate-y-2">
                    <?php if ($obra){ ?>
                        <a href="obra.php?id=<?= $obra['id'] ?>">
                            <img class="w-full h-full object-cover" src="<?= $obra['portada'] ?>" alt="<?= $obra['titulo'] ?>">
                        </a>
                    <?php }else{ ?>
                        <div class="flex items-center justify-center h-full text-zinc-500 italic">No hay datos</div>
                    <?php }; ?>
                </div>
                <p class="mt-4 text-lg font-medium"><?= $obra['titulo'] ?? 'Próximamente' ?></p>
                <a class="bg-rose-800 text-white px-4 py-2 hover:bg-rose-900 transition-colors rounded-lg text-sm mt-4" href="obra.php?id=<?= $obra['id'] ?>">Más info</a>
            </div>
            <?php }; ?>
        </div>
    </main>
    <footer class="bg-zinc-950 w-full border-t-4 border-black py-8 px-6 text-center">
        <div class="inline-block bg-zinc-800 p-1 border-2 border-black shadow-[4px_4px_0_0_rgba(0,0,0,1)]">
            <p class="text-sm text-zinc-400 px-4 py-2">
                © 2026 TFG DAW - Proyecto Comiclook 
            </p>
        </div>
    </footer>
<!--Eventos de la web-->
    <div id="event-widget" class="fixed bottom-6 right-6 z- max-w-xs bg-zinc-800 border-2 border-rose-800 rounded-2xl shadow-[0_0_20px_rgba(159,18,57,0.3)] overflow-hidden transform transition-transform hover:scale-105">
        <div class="bg-rose-800 px-4 py-1 text-[10px] font-bold uppercase tracking-widest flex justify-between items-center">
            <span>Evento Destacado</span>
            <button onclick="document.getElementById('event-widget').remove()" class="hover:text-black">✕</button>
        </div>
        <div class="p-4 flex gap-4">
            <div class="flex-shrink-0 bg-zinc-900 rounded-lg p-2 text-center flex flex-col justify-center min-w-[60px] border border-zinc-700">
                <?php
                    $fecha_evento = new DateTime('2026-10-8'); // Fecha supuesta Comic Con
                    $hoy = new DateTime();
                    $diferencia = $hoy->diff($fecha_evento);
                    $dias_restantes = $diferencia->format('%a');
                ?>
                <span class="text-2xl font-black text-rose-500"><?= $dias_restantes ?></span>
                <span class="text-[10px] uppercase text-zinc-400">Días</span>
            </div>
            <div>
                <h4 class="font-bold text-sm">📍 Comic Con Málaga</h4>
                <p class="text-xs text-zinc-400 mt-1">¡Prepara tu cosplay! Te esperamos en el **Stand B-12**.</p>
                <a href="https://sandiegocomicconmalaga.com/" target="_blank" class="inline-block mt-2 text-[10px] text-rose-500 font-bold hover:underline italic">Ver agenda del evento →</a>
            </div>
        </div>
    </div>
</body>
</html>
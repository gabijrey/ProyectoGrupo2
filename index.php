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
    
    <link rel="icon" href="comiclook_icon.ico">
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
         /* Animaciones para los Héroes */
        @keyframes writingShake {
            0% { transform: translateX(0) scale(1.1); }
            25% { transform: translateX(-5px) scale(1.1) rotate(-5deg); }
            75% { transform: translateX(5px) scale(1.1) rotate(5deg); }
            100% { transform: translateX(0) scale(1.1); }
        }

        @keyframes punchJump {
            0% { transform: scale(1); }
            50% { transform: scale(1.4) rotate(10deg); }
            100% { transform: scale(1.2); }
        }

        .hero-active-writing {
            animation: writingShake 0.2s infinite;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .hero-active-punch {
            animation: punchJump 0.2s ease-out forwards;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .hero-container {
            transition: opacity 0.2s ease, visibility 0.2s ease;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            /* Centrado vertical perfecto sin usar transform */
            top: 50%;
            margin-top: -72px; /* Mitad de la altura (144px / 2) */
        }
    </style>
</head>
<body class="text-white flex flex-col min-h-screen">
    <!-- navbar y sidebar -->
    <?php include 'assets/navbar.php'?>
    <?php include 'assets/sidebar.php'?>

    <main class="flex-grow p-10 flex flex-col items-center">
         <!-- Buscador con Héroes -->
        <div class="relative w-full max-w-2xl mb-12 z-40">
            <!-- Héroe Izquierda (Escribiendo) -->
            <div id="hero-writing" class="hero-container absolute -left-36 w-36 h-36 z-50">
                <img src="imagen/barraBusqueda/HeroeEscribiendo.png" alt="Escribiendo" class="w-full h-full object-contain">
            </div>

            <!-- Héroe Derecha (Borrando) -->
            <div id="hero-punch" class="hero-container absolute -right-36 w-36 h-36 z-50">
                <img src="imagen/barraBusqueda/HeroePegando.png" alt="Pegando" class="w-full h-full object-contain">
            </div>

            <div class="relative flex items-center">
                <input type="text" id="buscador-obras" placeholder="BUSCAR LIBROS, CÓMICS, MANGAS..." autocomplete="off"
                    class="w-full bg-white border-4 border-black text-black font-bold py-4 pl-6 pr-14 text-xl outline-none shadow-[6px_6px_0_0_black] focus:translate-y-1 focus:translate-x-1 focus:shadow-none transition-all placeholder:text-neutral-500 font-comic tracking-wider">
                <div class="absolute right-4 text-black pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            
            <!-- Contenedor Resultados -->
            <div id="resultados-busqueda" class="absolute top-full left-0 w-full mt-4 bg-white border-4 border-black shadow-[8px_8px_0_0_black] hidden flex-col max-h-[400px] overflow-y-auto">
                <!-- Los resultados se inyectarán aquí por JS -->
            </div>
        </div>
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
                        <a href="webcontent/obra.php?id=<?= $obra['id'] ?>">
                            <img class="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500" src="<?= $obra['portada'] ?>" alt="<?= $obra['titulo'] ?>">
                        </a>
                    <?php }else{ ?>
                        <div class="flex items-center justify-center h-full text-black font-comic text-2xl italic">PRÓXIMAMENTE...</div>
                    <?php }; ?>
                </div>

                <div class="mt-8 bg-white border-4 border-black p-4 w-full shadow-[6px_6px_0_0_black]">
                    <p class="text-black text-xl font-bold uppercase truncate"><?= $obra['titulo'] ?? '???' ?></p>
                    <a class="inline-block w-full text-center bg-rose-800 text-white font-bold py-2 border-4 border-black mt-3 hover:bg-rose-900 transition-all" href="webcontent/obra.php?id=<?= $obra['id'] ?>">
                        VER MÁS
                    </a>
                </div>
            </div>
            <?php }; ?>
        </div>
    </main>

    <!-- Footer -->
    <?php include "assets/footer.php"?>

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
    <!-- Script funcion de busqueda -->
    <script src="Js/FuncionBusqueda.js"></script>
</body>
</html>
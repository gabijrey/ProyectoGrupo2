<?php
session_start();
require '../sesion/conexion_pdo.php';
// Si quieres que solo puedan verlo los usuarios logueados, descomenta estas dos líneas:
// if (!isset($_SESSION['nombre'])) { header("Location: ../sesion/login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comiclook | Eventos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="../comiclook_icon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>
        .font-comic { font-family: 'Bangers', cursive; }
        body {
            background-color: #171717; /* Fondo oscuro base */
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #18181b; }
        ::-webkit-scrollbar-thumb { background: #9f1239; border-radius: 3px; }
        
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp 0.5s ease both; }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
    </style>
</head>
<body class="bg-zinc-900 text-white flex flex-col min-h-screen">
<?php include '../assets/navbar.php'; ?>
<?php include '../assets/sidebar.php'; ?>
<!-- CABECERA: Igual que en obra.php (fondo rojo con trama de puntos) -->
<section class="relative bg-rose-800 border-b-8 border-black p-10 flex flex-col items-center justify-center min-h-[350px] overflow-hidden">
    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(#000 2px, transparent 2px); background-size: 16px 16px;"></div>
    
    <div class="relative z-10 text-center fade-up mt-8">
        <h1 class="font-comic text-6xl md:text-8xl uppercase drop-shadow-[4px_4px_0_black] italic leading-none mb-4">
            ¡VEN A <span class="text-yellow-500">VERNOS!</span>
        </h1>
        <p class="font-bold uppercase tracking-widest text-white text-sm md:text-base max-w-2xl mx-auto bg-black p-3 border-4 border-black shadow-[6px_6px_0_0_black]">
            Descubre dónde estaremos próximamente. ¡Pásate por nuestro stand, llévate regalitos exclusivos y prueba la app en directo!
        </p>
    </div>
</section>
<!-- CONTENIDO PRINCIPAL: Tarjetas de los eventos -->
<main class="flex-grow p-6 md:p-12 max-w-6xl mx-auto w-full">
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
        
        <!-- EVENTO 1: FreakCon Málaga -->
        <article class="bg-neutral-900 border-4 border-black shadow-[10px_10px_0_0_black] flex flex-col relative fade-up delay-1 group">
            <!-- Insignia flotante -->
            <div class="absolute -top-4 -right-4 bg-yellow-500 text-black font-comic px-4 py-1 border-4 border-black text-xl uppercase transform rotate-3 shadow-[4px_4px_0_0_black] z-10">
                ¡Confirmado!
            </div>
            
            <!-- Cabecera de la tarjeta (sustituto de imagen) -->
            <div class="h-48 bg-purple-900 border-b-4 border-black relative overflow-hidden flex items-center justify-center">
                <div class="absolute inset-0 opacity-30" style="background-image: repeating-linear-gradient(45deg, #000 0, #000 4px, transparent 4px, transparent 12px);"></div>
                <a href="https://freakcon.es/" target="_blank" class="relative z-10 block w-full group-hover:scale-110 transition-transform px-6 text-center">
                    <img src="../imagen/eventos/freakcon_logo.png" alt="FreakCon Logo" class="h-16 md:h-20 w-auto mx-auto object-contain drop-shadow-[4px_4px_0_black]">
                </a>
            </div>
            
            <div class="p-6 flex flex-col flex-grow">
                <h3 class="font-comic text-3xl uppercase text-yellow-500 mb-2">FreakCon Málaga 2026</h3>
                <div class="flex items-center gap-2 text-zinc-400 font-bold text-xs uppercase tracking-widest mb-4">
                    <span>📅 22-24 Mayo</span>
                    <span>·</span>
                    <span>📍 Torremolinos Congress Centre</span>
                </div>
                
                <p class="text-zinc-300 text-sm leading-relaxed mb-6 flex-grow">
                    El festival internacional de manga, cómic, series de TV y videojuegos de Málaga. Estaremos en la zona de creadores independientes enseñando las nuevas funcionalidades de ComicLook. ¡Ven a escanear cómics con nosotros!
                </p>
                
                <a href="https://freakcon.es/" target="_blank" class="text-center bg-white text-black font-comic text-xl uppercase tracking-widest py-3 border-4 border-black shadow-[4px_4px_0_0_black] hover:bg-zinc-200 active:translate-x-1 active:translate-y-1 active:shadow-none transition-all">
                    Más Información
                </a>
            </div>
        </article>
        <!-- EVENTO 2: Comic-Con Málaga -->
        <article class="bg-neutral-900 border-4 border-black shadow-[10px_10px_0_0_black] flex flex-col relative fade-up delay-2 group">
            <!-- Insignia flotante -->
            <div class="absolute -top-4 -right-4 bg-rose-600 text-white font-comic px-4 py-1 border-4 border-black text-xl uppercase transform -rotate-2 shadow-[4px_4px_0_0_black] z-10">
                Próximamente
            </div>
            
            <!-- Cabecera de la tarjeta (sustituto de imagen) -->
            <div class="h-48 bg-blue-900 border-b-4 border-black relative overflow-hidden flex items-center justify-center">
                <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle, #000 2px, transparent 2px); background-size: 12px 12px;"></div>
                <a href="https://sandiegocomicconmalaga.com/" target="_blank" class="relative z-10 block w-full group-hover:scale-110 transition-transform px-6 text-center">
                    <img src="../imagen/eventos/comiccon_logo.png" alt="Comic-Con Logo" class="h-20 md:h-28 w-auto mx-auto object-contain drop-shadow-[4px_4px_0_black]">
                </a>
            </div>
            
            <div class="p-6 flex flex-col flex-grow">
                <h3 class="font-comic text-3xl uppercase text-rose-500 mb-2">Comic-Con Málaga</h3>
                <div class="flex items-center gap-2 text-zinc-400 font-bold text-xs uppercase tracking-widest mb-4">
                    <span>📅 Por definir</span>
                    <span>·</span>
                    <span>📍 FYCMA (Palacio de Ferias)</span>
                </div>
                
                <p class="text-zinc-300 text-sm leading-relaxed mb-6 flex-grow">
                    La convención de cómics por excelencia. Llevaremos merchandising exclusivo para los primeros usuarios que nos enseñen su perfil Premium en la app. Habrá sorteos y muchas sorpresas para la comunidad lectora.
                </p>
                
                <a href="https://sandiegocomicconmalaga.com/" target="_blank" class="text-center bg-white text-black font-comic text-xl uppercase tracking-widest py-3 border-4 border-black shadow-[4px_4px_0_0_black] hover:bg-zinc-200 active:translate-x-1 active:translate-y-1 active:shadow-none transition-all">
                    Más Información
                </a>
            </div>
        </article>
    </div>
</main>
<?php include '../assets/footer.php'; ?>
</body>
</html>
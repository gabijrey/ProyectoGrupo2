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
    <title>Comiclook | Sobre Nosotros</title>
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
<!-- CABECERA HERO (Estilo eventos) -->
<section class="relative bg-rose-800 border-b-8 border-black p-10 flex flex-col items-center justify-center min-h-[350px] overflow-hidden">
    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(#000 2px, transparent 2px); background-size: 16px 16px;"></div>
    
    <div class="relative z-10 text-center fade-up mt-8">
        <h1 class="font-comic text-6xl md:text-8xl uppercase drop-shadow-[4px_4px_0_black] italic leading-none mb-4">
            SOBRE <span class="text-yellow-500">NOSOTROS</span>
        </h1>
        <p class="font-bold uppercase tracking-widest text-white text-sm md:text-base max-w-2xl mx-auto bg-black p-3 border-4 border-black shadow-[6px_6px_0_0_black]">
            Conoce la historia detrás de ComicLook y a los responsables de que ahora tengas todas tus lecturas organizadas en un solo lugar.
        </p>
    </div>
</section>
<!-- CONTENIDO PRINCIPAL: HISTORIA Y EQUIPO -->
<main class="flex-grow p-6 md:p-12 max-w-5xl mx-auto w-full">
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center mb-16 fade-up delay-1">
        
        <!-- Bloque de Texto -->
        <div class="space-y-6">
            <h2 class="font-comic text-5xl uppercase text-rose-500 mb-2">El Origen del Proyecto</h2>
            <div class="w-16 h-2 bg-yellow-500 mb-6"></div>
            
            <p class="text-zinc-300 leading-relaxed">
                ComicLook nace de las cabezas de cuatro estudiantes malagueños de un grado superior de Desarrollo de Aplicaciones Web con la intención de brindar a los lectores un lugar en el que sentirse como en casa junto a una comunidad apasionada del mundo del cómic.
            </p>
            <p class="text-zinc-300 leading-relaxed">
                Nosotros al ser parte de este mundillo, nos dimos cuenta de que hacía falta una plataforma moderna, visual y adaptada a nuestras necesidades donde no solo pudieramos catalogar lo que leíamos, sino también compartir opiniones, comparar nuestros gustos y descubrir nuevas joyas ocultas en el mundo de la literatura.
            </p>
            <p class="text-zinc-300 leading-relaxed">
                Lo que empezó como un reto académico fue tomando forma, línea de código tras línea de código, hasta convertirse en la red social literaria que estás viendo ahora mismo. Haciendo de este un lugar tanto para lectores que les apasiona este mundo, como autores en ciernes que quieren que sus obras impacten en la vida de la gente y tengan el reconocimiento que merece.
            </p>
        </div>
        <!-- Tarjeta "Comic" Ilustrativa -->
        <div class="bg-yellow-500 border-8 border-black shadow-[12px_12px_0_0_black] p-8 transform rotate-2 hover:rotate-0 transition-transform relative">
            <div class="absolute -top-4 -left-4 bg-white text-black font-comic px-4 py-1 border-4 border-black text-xl uppercase transform -rotate-6 shadow-[4px_4px_0_0_black] z-10">
                ¡BOOM!
            </div>
            <div class="bg-black text-white p-6 border-4 border-black font-bold uppercase text-center text-sm tracking-widest leading-loose">
                "Nuestra misión es conectar a lectores, dar voz a los autores independientes y crear el catálogo literario visual más popular de la red."
            </div>
        </div>
    </div>
    <!-- SECCIÓN NUESTRA VISIÓN -->
    <div class="bg-neutral-900 border-4 border-black shadow-[10px_10px_0_0_black] p-8 md:p-12 fade-up delay-2">
        <h2 class="font-comic text-4xl uppercase text-white mb-6 text-center">Nuestra Visión</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <div class="text-center">
                <div class="w-16 h-16 bg-rose-800 rounded-full border-4 border-black mx-auto mb-4 flex items-center justify-center text-white font-comic text-3xl shadow-[4px_4px_0_0_black]">1</div>
                <h3 class="font-comic text-2xl uppercase text-yellow-500 mb-2">Comunidad</h3>
                <p class="text-zinc-400 text-sm">Queremos que todo fanático de la lectura tenga un rincón donde sus opiniones cuenten y sean escuchadas.</p>
            </div>
            
            <div class="text-center">
                <div class="w-16 h-16 bg-rose-800 rounded-full border-4 border-black mx-auto mb-4 flex items-center justify-center text-white font-comic text-3xl shadow-[4px_4px_0_0_black]">2</div>
                <h3 class="font-comic text-2xl uppercase text-yellow-500 mb-2">Innovación</h3>
                <p class="text-zinc-400 text-sm">Desde un escáner de códigos de barras hasta comparadores dinámicos. Siempre estamos tratando de mejorar incorporando nuevas tecnologías.</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-rose-800 rounded-full border-4 border-black mx-auto mb-4 flex items-center justify-center text-white font-comic text-3xl shadow-[4px_4px_0_0_black]">3</div>
                <h3 class="font-comic text-2xl uppercase text-yellow-500 mb-2">Independencia</h3>
                <p class="text-zinc-400 text-sm">Creemos en el talento local. Queremos ser el trampolín para los autores que merecen ser leídos y reconocidos y que de otra manera les resultaría más díficil acceder a su público objetivo.</p>
            </div>
        </div>
    </div>
</main>
<?php include '../assets/footer.php'; ?>
</body>
</html>
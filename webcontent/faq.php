<?php
session_start();
require ("../sesion/conexion_pdo.php");
// Opcional: require '../sesion/conexion_pdo.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comiclook | Preguntas Frecuentes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="../comiclook_icon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>
        .font-comic { font-family: 'Bangers', cursive; }
        body {
            background-color: #171717;
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
        .delay-3 { animation-delay: 0.3s; }
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
            PREGUNTAS <span class="text-yellow-500">FRECUENTES</span>
        </h1>
        <p class="font-bold uppercase tracking-widest text-white text-sm md:text-base max-w-2xl mx-auto bg-black p-3 border-4 border-black shadow-[6px_6px_0_0_black]">
            Resolvemos todas tus dudas existenciales sobre ComicLook. Si no encuentras lo que buscas, ¡escríbenos más abajo!
        </p>
    </div>
</section>
<!-- CONTENIDO PRINCIPAL: PREGUNTAS -->
<main class="flex-grow p-6 md:p-12 max-w-4xl mx-auto w-full">
    <div class="space-y-8">
        
        <!-- Pregunta 1 -->
        <article class="bg-neutral-900 border-4 border-black shadow-[8px_8px_0_0_black] p-6 md:p-8 fade-up delay-1">
            <h3 class="font-comic text-3xl uppercase text-rose-500 mb-3 flex items-start gap-3">
                <span class="text-yellow-500">1.</span> ¿Es obligatorio ser Premium para crear reseñas?
            </h3>
            <p class="text-zinc-300 text-sm md:text-base leading-relaxed pl-8">
                ¡Para nada! Crear reseñas, listas personalizadas y marcar tus obras favoritas es totalmente gratuito para siempre. El plan Premium es una forma de apoyar el proyecto y obtener ventajas exclusivas como el Escáner de códigos de barras, mayor peso en la nota global y una insignia de usuario verificado.
            </p>
        </article>
        <!-- Pregunta 2 -->
        <article class="bg-neutral-900 border-4 border-black shadow-[8px_8px_0_0_black] p-6 md:p-8 fade-up delay-2">
            <h3 class="font-comic text-3xl uppercase text-rose-500 mb-3 flex items-start gap-3">
                <span class="text-yellow-500">2.</span> ¿Cómo funciona el comparador de obras?
            </h3>
            <p class="text-zinc-300 text-sm md:text-base leading-relaxed pl-8">
                El comparador te permite enfrentar frente a frente dos obras distintas (cómics, libros o mangas) para analizar cuál tiene mejor puntuación, qué autores participan, la editorial y ver de un vistazo qué opinan otros usuarios de la plataforma antes de decidir cuál leer.
            </p>
        </article>
        <!-- Pregunta 3 -->
        <article class="bg-neutral-900 border-4 border-black shadow-[8px_8px_0_0_black] p-6 md:p-8 fade-up delay-3">
            <h3 class="font-comic text-3xl uppercase text-rose-500 mb-3 flex items-start gap-3">
                <span class="text-yellow-500">3.</span> ¿Puedo sugerir un cómic que no está en el catálogo?
            </h3>
            <p class="text-zinc-300 text-sm md:text-base leading-relaxed pl-8">
                ¡Sí! Si buscas una obra y no aparece en la plataforma, puedes utilizar nuestro sistema de contacto. Nuestro equipo de moderación la revisará y la añadirá a la base de datos oficial en la mayor brevedad posible para que puedas añadirla a tu colección.
            </p>
        </article>
        <!-- Pregunta 4 -->
        <article class="bg-neutral-900 border-4 border-black shadow-[8px_8px_0_0_black] p-6 md:p-8 fade-up delay-3">
            <h3 class="font-comic text-3xl uppercase text-rose-500 mb-3 flex items-start gap-3">
                <span class="text-yellow-500">4.</span> ¿Sois una tienda online de cómics?
            </h3>
            <p class="text-zinc-300 text-sm md:text-base leading-relaxed pl-8">
                No, no comerciamos con libros, cómics o mangas de ningún tipo. El objetivo de ComicLook es ofrecer un espacio donde compartir opiniones sobre las lecturas de los usuarios y cuáles son las novedades más recientes del mundillo.
            </p>
        </article>
        <!-- Pregunta 5 -->
        <article class="bg-neutral-900 border-4 border-black shadow-[8px_8px_0_0_black] p-6 md:p-8 fade-up delay-3">
            <h3 class="font-comic text-3xl uppercase text-rose-500 mb-3 flex items-start gap-3">
                <span class="text-yellow-500">5.</span> ¿Asistís a algún evento del mundo del cómic próximamente?
            </h3>
            <p class="text-zinc-300 text-sm md:text-base leading-relaxed pl-8">
                ¡Sí! Tenemos confirmada la asistencia a la Freakcon de Málaga y tenemos expectativa de más adelante asistir a la ComicCon Así nos podéis conocer en persona y probar nuestras funcionalidades exclusivas en directo.
            </p>
        </article>
        <!-- Pregunta 6-->
        <article class="bg-neutral-900 border-4 border-black shadow-[8px_8px_0_0_black] p-6 md:p-8 fade-up delay-3">
            <h3 class="font-comic text-3xl uppercase text-rose-500 mb-3 flex items-start gap-3">
                <span class="text-yellow-500">6.</span> Si soy escritor, ¿tengo algún beneficio por usar vuestra web?
            </h3>
            <p class="text-zinc-300 text-sm md:text-base leading-relaxed pl-8">
                ¡Por supuesto! Disponéis de una suscripción exclusiva con ventajas únicas para poder daros a conocer y que alcancéis el máximo de público lector posible. Tendréis prioridad para la visibilidad de vuestro contenido.
            </p>
        </article>
    </div>
    <!-- SECCIÓN CONTACTO (El email lo pones tú en el href) -->
    <div class="mt-16 bg-yellow-500 border-8 border-black shadow-[12px_12px_0_0_black] p-8 md:p-12 text-center fade-up relative overflow-hidden">
        <!-- Textura del fondo -->
        <div class="absolute inset-0 opacity-10" style="background-image: repeating-linear-gradient(45deg, #000 0, #000 4px, transparent 4px, transparent 12px);"></div>
        
        <div class="relative z-10 flex flex-col items-center">
            <h2 class="font-comic text-5xl uppercase text-black italic drop-shadow-[2px_2px_0_white] mb-4">¿AÚN TIENES DUDAS?</h2>
            <p class="text-black font-bold uppercase tracking-widest text-sm mb-8 max-w-lg">
                Nuestro equipo de soporte está listo para ayudarte con lo que necesites. Escríbenos y te responderemos volando.
            </p>
            
            <!-- Sustituye tu-email@aqui.com por tu correo real -->
            <a href="mailto:comiclook.info@gmail.com" class="bg-black text-white font-comic text-2xl uppercase tracking-widest px-8 py-4 border-4 border-white shadow-[6px_6px_0_0_white] hover:bg-neutral-800 hover:text-yellow-500 active:translate-x-1 active:translate-y-1 active:shadow-none transition-all flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Contáctanos por Email
            </a>
            <!-- Redes sociales -->
            <div class="mt-8 flex gap-6 items-center justify-center">
                <a href="https://instagram.com/somoscomiclook" target="_blank" class="text-black hover:text-white transition-all transform hover:scale-125">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                </a>
                <a href="https://x.com/somoscomiclook" target="_blank" class="text-black hover:text-white transition-all transform hover:scale-125">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4l11.733 16h4.267l-11.733-16zM4 20l6.768-6.768m2.46-2.46L20 4"></path></svg>
                </a>
            </div>
        </div>
    </div>
</main>
<?php include '../assets/footer.php'; ?>
</body>
</html>
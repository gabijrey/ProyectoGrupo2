<?php
session_start();

if (!isset($_SESSION['nombre'])) {
    header("Location: ../sesion/login.php");
    exit();
}

require __DIR__ . "/../sesion/conexion_pdo.php";
require __DIR__ . "/../sesion/premium_helper.php";

$es_premium = esPremium($_conexion, $_SESSION['nombre']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comiclook | Escáner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="../comiclook_icon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>
        .font-comic { font-family: 'Bangers', cursive; }
        body {
            background-color: #171717;
            background-image: radial-gradient(#333 1px, transparent 1px);
            background-size: 20px 20px;
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #18181b; }
        ::-webkit-scrollbar-thumb { background: #9f1239; border-radius: 3px; }

        /* Marco del lector */
        #scanner-video-wrapper {
            position: relative;
            background: #000;
            border: 4px solid #000;
            box-shadow: 8px 8px 0 0 #000;
            aspect-ratio: 4/3;
            overflow: hidden;
        }
        #scanner-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        /* Overlay con líneas de mira tipo cómic */
        .scanner-overlay {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            pointer-events: none;
        }
        .scanner-frame {
            width: 70%; height: 35%;
            border: 4px solid #fbbf24;
            box-shadow: 0 0 0 9999px rgba(0,0,0,0.4);
            position: relative;
        }
        .scanner-frame::before, .scanner-frame::after {
            content: '';
            position: absolute;
            width: 28px; height: 28px;
            border: 6px solid #9f1239;
        }
        .scanner-frame::before { top: -6px; left: -6px; border-right: 0; border-bottom: 0; }
        .scanner-frame::after  { bottom: -6px; right: -6px; border-left: 0; border-top: 0; }

        @keyframes scanline {
            0%   { transform: translateY(0); opacity: 1; }
            50%  { opacity: 0.6; }
            100% { transform: translateY(100%); opacity: 1; }
        }
        .scanline {
            position: absolute; left: 0; right: 0; top: 0;
            height: 4px; background: #ef4444;
            box-shadow: 0 0 8px #ef4444;
            animation: scanline 1.6s ease-in-out infinite alternate;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp 0.5s ease both; }
    </style>
</head>
<body class="text-white flex flex-col min-h-screen">

<?php include '../assets/navbar.php'?>
<?php include '../assets/sidebar.php'?>

<main class="flex-grow p-6 md:p-10 max-w-5xl mx-auto w-full">

    <!-- Cabecera -->
    <div class="text-center mb-8 fade-up">
        <h1 class="font-comic text-5xl md:text-7xl text-white uppercase drop-shadow-[6px_6px_0_black] italic">
            ESCÁNER <span class="text-rose-800">DE CÓMICS</span>
        </h1>
        <p class="mt-3 font-bold uppercase tracking-widest text-zinc-400 text-sm">
            Apunta con tu cámara al código de barras
        </p>
    </div>

    <?php if (!$es_premium): ?>
        <!-- Vista para usuario NO premium -->
        <div class="bg-neutral-900 border-4 border-rose-800 shadow-[10px_10px_0_0_#9f1239] p-8 md:p-12 text-center fade-up">
            <div class="inline-block bg-yellow-500 text-black font-comic text-5xl md:text-6xl uppercase tracking-widest border-4 border-black shadow-[6px_6px_0_0_black] px-6 py-3 rotate-[-2deg] mb-6">
                ¡Solo Premium!
            </div>
            <p class="text-zinc-200 text-lg max-w-2xl mx-auto mb-2 font-bold">
                El escáner de códigos de barras está reservado para suscriptores Premium de ComicLook.
            </p>
            <p class="text-zinc-400 text-sm max-w-2xl mx-auto mb-6">
                Hazte Premium para escanear cualquier cómic, manga o libro físico con tu móvil y abrir su ficha al instante.
            </p>
            <a href="../assets/pricing.php" class="inline-block bg-rose-800 text-white font-comic text-2xl uppercase tracking-widest px-6 py-3 border-4 border-black shadow-[6px_6px_0_0_black] hover:bg-rose-900 active:translate-x-1 active:translate-y-1 active:shadow-none transition-all">
                Ver planes Premium →
            </a>
        </div>
    <?php else: ?>

    <!-- Vista escáner para usuario premium -->
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6 fade-up">

        <!-- Cámara -->
        <div>
            <div id="scanner-video-wrapper">
                <video id="scanner-video" playsinline muted></video>
                <div class="scanner-overlay">
                    <div class="scanner-frame">
                        <div class="scanline"></div>
                    </div>
                </div>
            </div>

            <!-- Controles -->
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <button id="btn-iniciar" type="button" class="bg-rose-800 text-white font-comic text-lg uppercase tracking-widest px-5 py-2 border-4 border-black shadow-[5px_5px_0_0_black] hover:bg-rose-900 active:translate-x-1 active:translate-y-1 active:shadow-none transition-all">
                    ▶ Iniciar cámara
                </button>
                <button id="btn-detener" type="button" disabled class="bg-black text-white font-comic text-lg uppercase tracking-widest px-5 py-2 border-4 border-zinc-700 shadow-[5px_5px_0_0_#52525b] hover:bg-neutral-900 disabled:opacity-50 disabled:cursor-not-allowed active:translate-x-1 active:translate-y-1 active:shadow-none transition-all">
                    ■ Detener
                </button>
                <select id="select-camara" class="bg-black border-4 border-zinc-700 text-white font-bold px-3 py-2 text-xs uppercase tracking-widest focus:border-yellow-500 focus:outline-none"></select>
            </div>

            <!-- Entrada manual de respaldo -->
            <details class="mt-4 bg-neutral-900 border-4 border-zinc-700 p-4">
                <summary class="cursor-pointer font-comic text-base uppercase tracking-widest text-yellow-500">
                    ¿No funciona la cámara? Introduce el código manualmente
                </summary>
                <form id="form-manual" class="mt-3 flex gap-2">
                    <input id="codigo-manual" type="text" inputmode="numeric" pattern="\d{6,14}" placeholder="Ej: 9788491736387" class="flex-1 bg-black border-4 border-zinc-700 focus:border-rose-700 focus:outline-none px-4 py-2 text-white font-comic tracking-wider">
                    <button type="submit" class="bg-yellow-500 text-black font-comic text-lg uppercase tracking-widest px-4 py-2 border-4 border-black shadow-[4px_4px_0_0_black] hover:bg-yellow-400 active:translate-x-1 active:translate-y-1 active:shadow-none transition-all">
                        Buscar
                    </button>
                </form>
            </details>
        </div>

        <!-- Estado / resultado -->
        <aside class="bg-neutral-900 border-4 border-black shadow-[8px_8px_0_0_black] p-5 flex flex-col gap-3">
            <h2 class="font-comic text-xl uppercase tracking-widest text-yellow-500 border-b-4 border-black pb-2">
                Estado
            </h2>
            <div id="status-msg" class="text-sm text-zinc-300 font-bold uppercase tracking-widest">
                Pulsa "Iniciar cámara" para empezar.
            </div>
            <div id="ultimo-codigo" class="hidden bg-black border-2 border-zinc-700 p-3">
                <p class="text-[10px] uppercase tracking-widest text-zinc-500">Último código leído</p>
                <p id="ultimo-codigo-valor" class="font-comic text-2xl text-yellow-500 break-all"></p>
            </div>
            <div id="resultado-obra" class="hidden bg-black border-4 border-rose-800 p-3 flex gap-3">
                <!-- Se rellena por JS -->
            </div>
            <p class="text-[11px] text-zinc-500 leading-relaxed mt-auto">
                Asegúrate de tener buena luz y mantener el código dentro del marco amarillo. La cámara requiere HTTPS o localhost.
            </p>
        </aside>
    </div>

    <!-- ZXing-JS desde CDN para decodificar EAN/UPC desde la cámara -->
    <script src="https://unpkg.com/@zxing/library@0.21.3/umd/index.min.js"></script>
    <script src="../Js/FuncionesEscaner.js"></script>

    <?php endif; ?>

</main>

<?php include "../assets/footer.php"?>

</body>
</html>

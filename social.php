<?php
session_start();
if (!isset($_SESSION['nombre'])) {
    header("Location: ../sesion/login.php");
    exit();
}

require "../sesion/conexion_pdo.php";

// Consulta para obtener el podio de los 3 usuarios con más reseñas
$sql_podio = "SELECT u.nombre, u.img_perfil, u.rol, COUNT(r.id) as total_resenas 
              FROM usuario u 
              LEFT JOIN resena r ON u.nombre = r.nombre_usuario 
              GROUP BY u.nombre, u.img_perfil 
              ORDER BY total_resenas DESC 
              LIMIT 3";
$stmt_podio = $_conexion->prepare($sql_podio);
$stmt_podio->execute();
$podio = $stmt_podio->fetchAll();

// Reorganizar para el orden visual del podio: 2º, 1º, 3º
$visual_podio = [];
if (isset($podio[1])) $visual_podio[] = $podio[1]; // 2º puesto
if (isset($podio[0])) $visual_podio[] = $podio[0]; // 1º puesto
if (isset($podio[2])) $visual_podio[] = $podio[2]; // 3º puesto

// Consulta para obtener las 10 reseñas más recientes
$sql_recientes = "SELECT r.*, o.titulo, o.portada, u.img_perfil, u.rol 
                  FROM resena r 
                  JOIN obra o ON r.id_obra = o.id 
                  JOIN usuario u ON r.nombre_usuario = u.nombre 
                  ORDER BY r.fecha_public DESC, r.id DESC 
                  LIMIT 10";
$stmt_recientes = $_conexion->prepare($sql_recientes);
$stmt_recientes->execute();
$resenas_recientes = $stmt_recientes->fetchAll();

// Consulta para obtener los 12 últimos usuarios registrados
$sql_nuevos_users = "SELECT nombre, img_perfil, fecha_registro, rol 
                     FROM usuario 
                     ORDER BY fecha_registro DESC, nombre DESC 
                     LIMIT 12";
$stmt_nuevos = $_conexion->prepare($sql_nuevos_users);
$stmt_nuevos->execute();
$usuarios_nuevos = $stmt_nuevos->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comiclook | Social</title>
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
        .podium-1 { height: 250px; }
        .podium-2 { height: 180px; }
        .podium-3 { height: 130px; }
    </style>
</head>
<body class="text-white flex flex-col min-h-screen">
    <?php include '../assets/navbar.php'?>
    <?php include '../assets/sidebar.php'?>

    <main class="flex-grow p-4 md:p-10 flex flex-col items-center">
        <h1 class="font-comic text-6xl md:text-8xl mb-16 text-rose-800 uppercase tracking-tighter drop-shadow-[6px_6px_0_black] italic text-center">
            Comunidad <span class="text-white">ComicLook</span>
        </h1>

        <div class="w-full max-w-[1850px] grid grid-cols-1 xl:grid-cols-[380px_1fr_380px] gap-16 items-start">
            
            <!-- LATERAL IZQUIERDO: RESEÑAS RECIENTES -->
            <aside class="hidden xl:flex flex-col gap-8">
                <h3 class="font-comic text-4xl text-yellow-500 uppercase bg-black px-6 py-2 border-4 border-white shadow-[6px_6px_0_0_black] rotate-[-2deg] text-center mb-6">
                    Reseñas Recientes
                </h3>
                
                <div class="space-y-8">
                    <?php foreach ($resenas_recientes as $r): ?>
                    <a href="obra.php?id=<?= $r['id_obra'] ?>" class="block bg-white border-4 border-black p-4 shadow-[8px_8px_0_0_black] hover:translate-x-2 hover:-translate-y-2 transition-transform cursor-pointer group">
                        <div class="flex items-center gap-3 mb-3">
                            <img src="../<?= $r['img_perfil'] ?>" class="w-12 h-12 rounded-full border-2 border-black object-cover">
                            <span class="font-comic text-black text-lg uppercase truncate group-hover:text-rose-800 transition-colors flex items-center gap-1">
                                <?= $r['nombre_usuario'] ?>
                                <?php if(isset($r['rol']) && in_array($r['rol'], [1, 2])): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 ml-1 animate-pulse <?= $r['rol'] == 1 ? 'text-yellow-400' : 'text-cyan-400' ?> drop-shadow-[1px_1px_0_black]" title="<?= $r['rol'] == 1 ? 'Premium' : 'Autor' ?>">
                                        <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                                    </svg>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="flex gap-4">
                            <img src="../<?= $r['portada'] ?>" class="w-20 h-28 object-cover border-4 border-black shrink-0">
                            <div class="min-w-0">
                                <h4 class="font-black text-black text-xs uppercase leading-tight mb-1"><?= $r['titulo'] ?></h4>
                                <div class="bg-rose-800 text-white text-[10px] font-black px-2 py-0.5 inline-block border-2 border-black">★ <?= $r['puntuacion'] ?>/5</div>
                                <p class="text-xs text-neutral-600 italic line-clamp-4 leading-tight mt-2">"<?= $r['comentario'] ?>"</p>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Banner Publicitario Comic (Movido aquí) -->
                <div class="mt-12 bg-yellow-400 border-4 border-black p-6 rotate-[-1deg] shadow-[10px_10px_0_0_black]">
                    <p class="font-black text-black uppercase text-center text-base mb-2">¿Quieres aparecer aquí?</p>
                    <p class="font-comic text-rose-800 text-center text-3xl leading-none uppercase">¡Sube tu primera reseña ahora!</p>
                </div>
            </aside>

            <!-- COLUMNA CENTRAL -->
            <div class="flex flex-col items-center w-full">
                <h2 class="font-comic text-4xl mb-12 text-yellow-500 uppercase bg-black px-6 py-2 border-4 border-white shadow-[6px_6px_0_0_black] rotate-[-1deg]">
                    Top Reseñadores
                </h2>

                <!-- Podio -->
                <div class="flex items-end justify-center gap-4 md:gap-8 w-full max-w-4xl mt-10">
                    <?php foreach ($visual_podio as $index => $user): 
                        $rank = ($user === $podio[0]) ? 1 : (($user === $podio[1]) ? 2 : 3);
                        $height_class = "podium-" . $rank;
                        $color_class = ($rank == 1) ? "bg-yellow-500" : (($rank == 2) ? "bg-neutral-400" : "bg-orange-700");
                    ?>
                    <div class="flex flex-col items-center w-32 md:w-48 transition-transform hover:scale-105">
                        <a href="../sesion/perfilUsuario.php?usuario=<?= urlencode($user['nombre']) ?>" class="relative mb-4 cursor-pointer group">
                            <div class="w-20 h-20 md:w-32 md:h-32 rounded-full border-4 border-black overflow-hidden shadow-[4px_4px_0_0_black] bg-white flex items-center justify-center group-hover:shadow-none transition-all">
                                <?php if (!empty($user['img_perfil'])): ?>
                                    <img src="../<?= $user['img_perfil'] ?>" alt="<?= $user['nombre'] ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <span class="font-comic text-4xl md:text-6xl text-rose-800"><?= strtoupper(substr($user['nombre'], 0, 1)) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="absolute -top-4 -right-4 bg-rose-800 text-white font-comic text-2xl w-10 h-10 flex items-center justify-center border-4 border-black rotate-12">#<?= $rank ?></div>
                        </a>
                        <div class="<?= $height_class ?> <?= $color_class ?> w-full border-x-4 border-t-4 border-black shadow-[8px_0_0_0_black] flex flex-col items-center justify-start pt-4 px-2 text-center">
                            <a href="../sesion/perfilUsuario.php?usuario=<?= urlencode($user['nombre']) ?>" class="font-comic text-xl md:text-2xl text-black truncate w-full uppercase hover:text-rose-800 transition-colors cursor-pointer flex justify-center items-center gap-1">
                                <?= $user['nombre'] ?>
                                <?php if(isset($user['rol']) && in_array($user['rol'], [1, 2])): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 ml-1 animate-pulse <?= $user['rol'] == 1 ? 'text-yellow-400' : 'text-cyan-400' ?> drop-shadow-[1px_1px_0_black]" title="<?= $user['rol'] == 1 ? 'Premium' : 'Autor' ?>">
                                        <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                                    </svg>
                                <?php endif; ?>
                            </a>
                            <p class="font-bold text-black text-xs md:text-sm mt-1"><?= $user['total_resenas'] ?> RESEÑAS</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Personaje Profundo con Bocadillo -->
                <div class="mt-20 w-full max-w-4xl flex items-center justify-center relative">
                    <div class="relative flex items-center justify-center">
                        <img src="../imagen/Social/bocadillo.png" alt="Bocadillo" class="w-64 md:w-[400px] rotate-[-90deg]">
                        <p class="absolute inset-0 flex items-center justify-center text-black font-comic text-sm md:text-xl uppercase italic text-center px-16 md:px-32 leading-tight transform -translate-x-4 md:-translate-x-8">
                            ¡Gracias a nuestra comunidad por compartir sus opiniones sobre el noveno arte!
                        </p>
                    </div>
                    <div class="shrink-0 -ml-10">
                        <img src="../imagen/Social/Profundo.png" alt="Profundo" class="w-48 md:w-64 drop-shadow-[8px_8px_0_black]">
                    </div>
                </div>

                <!-- BUSCADOR DE USUARIOS -->
                <section class="w-full mt-24 mb-20">
                    <h2 class="font-comic text-4xl mb-12 text-rose-800 uppercase bg-white px-6 py-2 border-4 border-black shadow-[6px_6px_0_0_black] inline-block rotate-[1deg]">
                        Buscador de Usuarios
                    </h2>
                    <div class="bg-neutral-900 border-4 border-black p-8 shadow-[12px_12px_0_0_black] relative overflow-hidden">
                        <div class="absolute top-0 right-0 bg-yellow-500 text-black font-black px-4 py-1 border-l-4 border-b-4 border-black uppercase rotate-3">Comunidad</div>
                        <div class="flex flex-col md:flex-row gap-6 mb-8 items-end text-black">
                            <div class="flex-grow w-full">
                                <label class="block font-comic text-xl mb-2 uppercase text-neutral-400">¿A quién buscas?</label>
                                <input type="text" id="user-search-input" class="w-full bg-white border-4 border-black p-4 text-black font-bold text-xl focus:outline-none focus:bg-yellow-400 transition-colors placeholder:text-neutral-400" placeholder="Escribe un nombre...">
                            </div>
                            <div class="shrink-0 w-full md:w-32">
                                <label class="block font-comic text-xl mb-2 uppercase text-neutral-400">Ver</label>
                                <select id="user-limit-select" class="w-full bg-white border-4 border-black p-4 text-black font-bold text-xl appearance-none cursor-pointer focus:bg-rose-800 focus:text-white transition-colors">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                        </div>
                        <div id="user-results-container" class="grid grid-cols-1 md:grid-cols-2 gap-6 min-h-[200px]"></div>
                        <div id="user-pagination" class="mt-12 flex justify-center items-center gap-4"></div>
                    </div>
                </section>
            </div>

            <!-- LATERAL DERECHO: NUEVOS USUARIOS -->
            <aside class="hidden xl:flex flex-col gap-8">
                <h3 class="font-comic text-4xl text-rose-800 uppercase bg-white px-6 py-2 border-4 border-black shadow-[6px_6px_0_0_black] rotate-[2deg] text-center mb-6">
                    Nuevos Héroes
                </h3>
                
                <div class="grid grid-cols-1 gap-6">
                    <?php foreach ($usuarios_nuevos as $un): ?>
                    <a href="../sesion/perfilUsuario.php?usuario=<?= urlencode($un['nombre']) ?>" class="bg-neutral-800 border-4 border-black p-6 flex items-center gap-6 hover:bg-yellow-500 hover:text-black transition-all group shadow-[8px_8px_0_0_black] hover:-translate-y-2">
                        <div class="relative shrink-0">
                            <img src="../<?= $un['img_perfil'] ?>" class="w-20 h-20 rounded-full border-4 border-white object-cover group-hover:border-black transition-colors">
                            <div class="absolute -bottom-1 -right-1 bg-rose-800 w-6 h-6 rounded-full border-2 border-black flex items-center justify-center">
                                <span class="text-[10px] text-white">⚡</span>
                            </div>
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-comic text-2xl uppercase truncate text-white group-hover:text-black leading-none mb-2 flex items-center gap-1">
                                <?= $un['nombre'] ?>
                                <?php if(isset($un['rol']) && in_array($un['rol'], [1, 2])): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 ml-1 animate-pulse <?= $un['rol'] == 1 ? 'text-yellow-400' : 'text-cyan-400' ?> drop-shadow-[1px_1px_0_black]" title="<?= $un['rol'] == 1 ? 'Premium' : 'Autor' ?>">
                                        <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                                    </svg>
                                <?php endif; ?>
                            </h4>
                            <p class="text-xs font-bold text-neutral-400 group-hover:text-black uppercase tracking-tighter">Héroe nº <?= rand(100, 999) ?></p>
                            <p class="text-[10px] font-black text-rose-800 group-hover:text-black uppercase mt-1">Unido: <?= $un['fecha_registro'] ?></p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Nuevo Banner para Usuarios -->
                <div class="mt-12 bg-rose-800 border-4 border-black p-6 rotate-[1deg] shadow-[10px_10px_0_0_black] text-white">
                    <p class="font-black text-white uppercase text-center text-base mb-2">¿Te gustaría ser un héroe más?</p>
                    <p class="font-comic text-yellow-500 text-center text-3xl leading-none uppercase">¡Completa tu perfil y destaca!</p>
                </div>
            </aside>

        </div>
    </main>

    <?php include "../assets/footer.php"?>
    
    <script src="../Js/FuncionBusqueda.js"></script>
    <script src="../Js/BusquedaUsuarios.js"></script>
</body>
</html>

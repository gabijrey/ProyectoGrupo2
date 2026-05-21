<?php
session_start();
if (!isset($_SESSION['nombre'])) {
    header("Location: login.php");
    exit();
}

require "conexion_pdo.php";

// Obtener el nombre del usuario a visualizar
if (!isset($_GET['usuario'])) {
    header("Location: ../webcontent/social.php");
    exit();
}

$nombre_perfil = $_GET['usuario'];

// Obtener los datos del usuario
$sql_user = "SELECT * FROM usuario WHERE nombre = ?";
$stmt_user = $_conexion->prepare($sql_user);
$stmt_user->execute([$nombre_perfil]);
$user_perfil = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$user_perfil) {
    die("Usuario no encontrado.");
}

// Obtener las reseñas del usuario
$sql_resenas = "SELECT r.*, o.titulo, o.portada 
                FROM resena r 
                JOIN obra o ON r.id_obra = o.id 
                WHERE r.nombre_usuario = ? 
                ORDER BY r.fecha_public DESC";
$stmt_resenas = $_conexion->prepare($sql_resenas);
$stmt_resenas->execute([$nombre_perfil]);
$resenas = $stmt_resenas->fetchAll(PDO::FETCH_ASSOC);


//Obtener las listas públicas del usuario
$sql_listas = "SELECT * FROM lista WHERE nombre_usuario = ? AND privacidad = 0 ORDER BY fecha_creacion DESC";
$stmt_listas = $_conexion->prepare($sql_listas);
$stmt_listas->execute([$nombre_perfil]);
$listas_publicas = $stmt_listas->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?= htmlspecialchars($nombre_perfil) ?> | ComicLook</title>
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
    </style>
</head>
<body class="text-white min-h-screen">
    <div class="flex flex-col md:flex-row min-h-screen">
        <?php include '../assets/sidebar_dashboard.php'; ?>

        <main class="flex-1 p-6 md:p-12 overflow-y-auto">
            <!-- Cabecera de Perfil -->
        <div class="flex flex-col md:flex-row gap-12 items-start">
            
            <!-- Columna Izquierda: Foto y Datos Básicos -->
            <div class="w-full md:w-80 flex flex-col items-center shrink-0">
                <div class="w-64 h-64 bg-white border-8 border-black shadow-[12px_12px_0_0_#9f1239] overflow-hidden flex items-center justify-center mb-8 rotate-[-2deg]">
                    <?php if (!empty($user_perfil['img_perfil'])): ?>
                        <img src="../<?= htmlspecialchars($user_perfil['img_perfil']) ?>" 
                             alt="<?= htmlspecialchars($user_perfil['nombre']) ?>" 
                             class="w-full h-full object-cover">
                    <?php else: ?>
                        <span class="font-comic text-8xl text-rose-800">
                            <?= strtoupper(substr($user_perfil['nombre'], 0, 1)) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="bg-yellow-500 text-black border-4 border-black p-4 w-full shadow-[6px_6px_0_0_black] rotate-[1deg] text-center">
                    <h2 class="font-comic text-4xl uppercase tracking-tighter mb-2 flex justify-center items-center gap-2">
                        <?= htmlspecialchars($user_perfil['nombre']) ?>
                        <?php if(isset($user_perfil['rol']) && in_array($user_perfil['rol'], [1, 2])): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 ml-2 animate-pulse <?= $user_perfil['rol'] == 1 ? 'text-yellow-600' : 'text-cyan-400' ?> drop-shadow-[2px_2px_0_black]" title="<?= $user_perfil['rol'] == 1 ? 'Premium' : 'Autor' ?>">
                                <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                            </svg>
                        <?php endif; ?>
                    </h2>
                    <p class="font-bold text-xs uppercase opacity-80 italic">Miembro desde: <?= $user_perfil['fecha_registro'] ?></p>
                    <div class="mt-4 bg-black text-white px-3 py-1 inline-block border-2 border-white text-xs font-bold uppercase tracking-widest">
                        <?= count($resenas) ?> Reseñas publicadas
                    </div>
                </div>

                <?php if ($_SESSION['nombre'] === $nombre_perfil): ?>
                    <a href="user.php" class="mt-4 w-full bg-black text-yellow-500 font-comic text-2xl uppercase py-2 border-4 border-black shadow-[4px_4px_0_0_#eab308] text-center hover:bg-neutral-800 transition-all active:translate-y-1 active:shadow-none mb-4">
                        ✎ Editar Perfil
                    </a>
                <?php endif; ?>

                <a href="../webcontent/social.php" class="mt-4 w-full bg-rose-800 text-white font-bold uppercase py-3 border-4 border-black shadow-[4px_4px_0_0_black] text-center hover:bg-rose-900 transition-all active:translate-y-1 active:shadow-none">
                    ← Volver a Social
                </a>
            </div>

            <!-- Columna Derecha: Biografía y Reseñas -->
            <div class="flex-1 space-y-12">
                
                <!-- Biografía -->
                <div class="bg-neutral-900 border-8 border-black p-8 shadow-[12px_12px_0_0_black]">
                    <h3 class="font-comic text-4xl text-yellow-500 uppercase italic mb-6 drop-shadow-[2px_2px_0_black]">Biografía</h3>
                    <div class="bg-white text-black p-6 border-4 border-black italic font-bold leading-relaxed min-h-[150px]">
                        <?php if (!empty($user_perfil['bio'])): ?>
                            <?= nl2br(htmlspecialchars($user_perfil['bio'])) ?>
                        <?php else: ?>
                            ESTE USUARIO PREFIERE MANTENER EL MISTERIO... (AÚN NO TIENE BIOGRAFÍA)
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Reseñas -->
                <div>
                    <h3 class="font-comic text-4xl text-rose-800 uppercase italic mb-8 drop-shadow-[4px_4px_0_black]">Últimas Reseñas</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <?php if (empty($resenas)): ?>
                            <div class="col-span-full bg-black text-white p-8 border-4 border-white shadow-[10px_10px_0_0_#9f1239] italic uppercase font-bold text-center">
                                ¡SILENCIO EN LA SALA! ESTE USUARIO AÚN NO HA RESEÑADO NADA.
                            </div>
                        <?php else: ?>
                            <?php foreach ($resenas as $resena): ?>
                                <a href="../webcontent/obra.php?id=<?= $resena['id_obra'] ?>" class="bg-white text-black border-4 border-black p-4 flex gap-4 shadow-[8px_8px_0_0_black] hover:scale-105 transition-transform group">
                                    <img src="../<?= htmlspecialchars($resena['portada']) ?>" 
                                         alt="<?= htmlspecialchars($resena['titulo']) ?>" 
                                         class="w-20 h-32 object-cover border-4 border-black group-hover:rotate-3 transition-transform">
                                    <div class="flex flex-col justify-between">
                                        <div>
                                            <h4 class="font-black text-lg uppercase leading-none mb-2 group-hover:text-rose-800"><?= htmlspecialchars($resena['titulo']) ?></h4>
                                            <div class="bg-yellow-500 text-black font-bold px-2 py-1 inline-block border-2 border-black text-xs italic mb-2">
                                                PUNTUACIÓN: <?= $resena['puntuacion'] ?>/5
                                            </div>
                                        </div>
                                        <p class="text-[10px] font-bold text-neutral-600 uppercase leading-tight italic line-clamp-4">
                                            "<?= htmlspecialchars($resena['comentario']) ?>"
                                        </p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <br>
                    <!-- Listas publicas -->
                     <h3 class="font-comic text-4xl text-rose-800 uppercase italic mb-8 drop-shadow-[4px_4px_0_black]">Listas de <?= htmlspecialchars($nombre_perfil)?></h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <?php if(empty($listas_publicas)): ?>
                            <div class="col-span-full bg-black text-white p-8 border-4 border-white shadow-[10px_10px_0_0_#9f1239] italic uppercase font-bold text-center">
                                ESTE USUARIO NO TIENE NINGUNA LISTA CREADA
                            </div>
                        <?php else: ?>
                       <?php foreach($listas_publicas as $lista_p):  ?>
                        <a href="ver_lista.php?id=<?= $lista_p['id'] ?>" class="bg-white text-black border-4 border-black p-6 flex flex-col justify-between shadow-[8px_8px_0_0_black] hover:scale-105 transition-transform group min-h-[180px]">
                            <div>
                                <div class="flex justify-between items-start mb-3">
                                    <h4 class="font-black text-xl uppercase leading-tight group-hover:text-rose-800 transition-colors">
                                        <?= htmlspecialchars($lista_p['titulo']) ?>
                                    </h4>
                                    <!-- Icono Planeta -->
                                    <svg class="w-6 h-6 text-blue-500 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="black" stroke-width="1.5">
                                        <circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path><path d="M2 12h20"></path>
                                    </svg>
                                </div>
                                
                                <div class="bg-yellow-500 text-black font-bold px-2 py-0.5 inline-block border-2 border-black text-[10px] italic mb-4">
                                    CREADA EL <?= date('d/m/Y', strtotime($lista_p['fecha_creacion'])) ?>
                                </div>
                                
                                <p class="text-[11px] font-bold text-neutral-600 uppercase leading-tight italic line-clamp-3">
                                    "<?= htmlspecialchars($lista_p['descripcion']) ?: 'Sin descripción...' ?>"
                                </p>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <span class="bg-black text-white px-4 py-1 border-2 border-black text-xs font-bold uppercase tracking-widest group-hover:bg-rose-800 transition-colors">
                                    Ver →
                                </span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </div>
    </main>
</div>
<script src="../Js/FuncionBusqueda.js"></script>
</body>
</html>

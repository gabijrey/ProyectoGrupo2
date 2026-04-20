<?php
session_start();

if (!isset($_SESSION['nombre'])) {
    header("Location: ../sesion/login.php");
    exit();
}
//Si llega aquí, es que está logueado
require __DIR__ . "/../sesion/conexion_pdo.php";
//Comprobar que se ha pasado un ID válido por GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../index.php");
    exit();
}
//Obtener el ID de la obra y cargar sus datos
$id_obra = (int)$_GET['id'];
try {
    $stmt = $_conexion->prepare("SELECT * FROM obra WHERE id = :id");
    $stmt->execute(['id' => $id_obra]);
    $obra = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die();
}
//Si no se encuentra la obra, redirigir al inicio
if (!$obra) {
    header("Location: ../index.php");
    exit();
}
//Guardar reseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resena_texto'])) {
    $comentario = trim($_POST['resena_texto']);
    $puntuacion = (int)($_POST['puntuacion'] ?? 5);
    if (isset($_SESSION['nombre']) && $comentario !== "") {
        try {
            //Hacemos la insercion de los datos correspondientes a reseña
                $ins = $_conexion->prepare(
                    "INSERT INTO resena (fecha_public, puntuacion, nombre_usuario, comentario, id_obra)
                    VALUES (NOW(), :puntuacion, :nombre_usuario, :comentario, :id_obra)"
                );
                $ins->execute([
                    'puntuacion' => $puntuacion,
                    'nombre_usuario' => $_SESSION["nombre"],
                    'comentario' => $comentario,
                    'id_obra'    => $id_obra
                ]);
                header("Location: obra.php?id=$id_obra&resena=subida");
                exit();
        } catch (PDOException $e) {
            die("Error en la inserción ". $e->getMessage());
        }
    }
}

// Obtener estadísticas globales (total y media)
$total_resenas = 0;
$media_puntuacion = 0;
try {
    $stmt_stats = $_conexion->prepare("
        SELECT COUNT(*) AS total, AVG(puntuacion) AS media
        FROM resena
        WHERE id_obra = :id
    ");
    $stmt_stats->execute(['id' => $id_obra]);
    $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    $total_resenas    = (int)($stats['total'] ?? 0);
    $media_puntuacion = $total_resenas > 0 ? round((float)$stats['media'], 1) : 0;
} catch (PDOException $e) {}

// Obtener las últimas 3 reseñas para mostrar
$resenas = [];
try {
    $stmt2 = $_conexion->prepare("
        SELECT r.fecha_public, r.puntuacion, r.comentario, r.nombre_usuario AS usuario
        FROM resena r
        WHERE r.id_obra = :id
        ORDER BY r.fecha_public DESC
        LIMIT 3
    ");
    $stmt2->execute(['id' => $id_obra]);
    $resenas = $stmt2->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

$tipos = [0 => 'Cómic', 1 => 'Manga', 2 => 'Libro'];
$tipo_label = $tipos[$obra['tipo']] ?? 'Obra';
$titulo = htmlspecialchars($obra['titulo'] ?? '');
$descripcion = htmlspecialchars($obra['descripcion'] ?? '');
$genero = htmlspecialchars($obra['genero'] ?? '');
$editorial = htmlspecialchars($obra['nombre_editorial'] ?? '');
$anno = htmlspecialchars($obra['anno_lanzamiento'] ?? '');
$portada = htmlspecialchars($obra['portada'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comiclook | <?= $titulo ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="../comiclook_icon.ico">
    <script src="../Js/FormularioRegistro.js"></script>
    <script src="../Js/ValidacionObra.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>
        .font-comic { font-family: 'Bangers', cursive; }
        body {
            background-color: #171717; /* neutral-900 */
            background-image: radial-gradient(#333 1px, transparent 1px);
            background-size: 20px 20px;
        }
        .font-display { font-family: 'Playfair Display', serif; }
        .hero-bg {
            position: absolute; inset: 0;
            background-size: cover; background-position: center;
            filter: blur(28px) brightness(0.18) saturate(1.4);
            transform: scale(1.1);
        }
        .star-rating { display: flex; flex-direction: row-reverse; gap: 4px; }
        .star-rating input { display: none; }
        .star-rating label { cursor: pointer; font-size: 1.6rem; color: #52525b; transition: color 0.15s; }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label { color: #ebb624; }
        .resena-card { transition: border-color 0.2s, transform 0.2s; }
        .resena-card:hover { border-color: #9f1239; transform: translateY(-2px); }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up  { animation: fadeUp 0.6s ease both; }
        .delay-1  { animation-delay: 0.1s; }
        .delay-2  { animation-delay: 0.25s; }
        .delay-3  { animation-delay: 0.4s; }
        .delay-4  { animation-delay: 0.55s; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #18181b; }
        ::-webkit-scrollbar-thumb { background: #9f1239; border-radius: 3px; }
    </style>
</head>
<body class="bg-zinc-900 text-white flex flex-col min-h-screen">

<?php include '../assets/navbar.php'?>
<?php include '../assets/sidebar.php'?>

<section class="relative bg-rose-800 border-b-8 border-black p-10 flex justify-center min-h-[400px]">
    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(#000 2px, transparent 2px); background-size: 16px 16px;"></div>
    <div class="relative z-10 w-full max-w-6xl flex flex-col md:flex-row gap-10 items-center md:items-end">
        <div class="flex-shrink-0 fade-up z-20">
            <div class="bg-white p-3 border-4 border-black shadow-[12px_12px_0_0_black]">
                <?php if ($portada){ ?>
                    <img src="../<?= $portada ?>" alt="<?= $titulo ?>"
                        class="w-48 md:w-64 h-auto border-4 border-black object-cover">
                <?php }else{?>
                    <div class="w-48 md:w-64 h-80 border-4 border-black bg-zinc-200 flex items-center justify-center text-black font-comic text-2xl uppercase text-center p-4">
                        Próximamente...
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="flex flex-col gap-4 fade-up delay-1 bg-neutral-900 border-4 border-black p-6 md:p-8 shadow-[8px_8px_0_0_black] w-full">
            <div class="text-xs font-bold flex flex-wrap items-center gap-2 uppercase tracking-widest border-b-4 border-black pb-3">
                <a href="../index.php" class="hover:text-rose-600 transition-colors">Inicio</a>
                <span class="text-rose-800 font-comic text-lg leading-none">></span>
                <?php
                if($obra["tipo"] == 0) {
                $linkObra = "comics.php";
                } 
                elseif ($obra["tipo"] == 1) {
                   $linkObra = "mangas.php"; 
                }
                else {
                    $linkObra = "libros.php";
                }
                ?>
                <a href="<?= $linkObra ?>" class="hover:text-rose-600 transition-colors"><?= $tipo_label ?>s</a>
                <span class="text-rose-800 font-comic text-lg leading-none">></span>
                <span class="text-zinc-500"><?= $titulo ?></span>
            </div>
            <div class="flex flex-wrap gap-3 mt-2">
                <span class="bg-yellow-500 text-black border-2 border-black font-comic px-4 py-1 uppercase tracking-widest shadow-[3px_3px_0_0_black]"><?= $tipo_label ?></span>
                <?php if ($genero){ ?>
                    <span class="bg-rose-800 text-white border-2 border-black font-comic px-4 py-1 uppercase tracking-widest shadow-[3px_3px_0_0_black]"><?= $genero ?></span>
                <?php } ?>
            </div>
            <h1 class="font-comic text-5xl md:text-7xl uppercase drop-shadow-[3px_3px_0_black] italic mt-2 leading-none">
                <?= $titulo ?>
            </h1>
            <div class="flex flex-wrap gap-6 text-sm text-black font-bold mt-4 bg-yellow-400 border-4 border-black p-4 shadow-[4px_4px_0_0_black]">
                <?php if ($editorial){ ?>
                    <span class="flex flex-col"><span class="font-comic text-rose-800 tracking-wider text-lg leading-none">Editorial</span><span class="uppercase"><?= $editorial ?></span></span>
                <?php } ?>
                <?php if ($anno){ ?>
                    <span class="flex flex-col"><span class="font-comic text-rose-800 tracking-wider text-lg leading-none">Año</span><span class="uppercase"><?= $anno ?></span></span>
                <?php } ?>
                <?php if ($total_resenas > 0){ ?>
                    <span class="flex flex-col border-l-4 border-black pl-4 ml-2"><span class="font-comic text-black tracking-wider text-lg leading-none">Reseñas</span><span class="text-rose-800 text-xl font-comic"><?= $total_resenas ?></span></span>
                    <span class="flex flex-col">
                        <span class="font-comic text-black tracking-wider text-lg leading-none">Media</span>
                        <span class="flex items-center gap-1">
                            <span class="font-comic text-2xl leading-none text-rose-800"><?= number_format($media_puntuacion, 1) ?></span>
                            <span class="text-black text-lg drop-shadow-[1px_1px_0_white]">★</span>
                        </span>
                    </span>
                <?php } ?>
            </div>
        </div>
    </div>
</section>
<!-- CUERPO -->
<main class="flex-grow max-w-6xl mx-auto w-full px-6 py-12 grid grid-cols-1 lg:grid-cols-3 gap-10">
    <!-- Columna izquierda -->
    <div class="lg:col-span-2 flex flex-col gap-10">
        <?php if ($descripcion){ ?>
        <!-- Sinopsis -->
        <section class="fade-up delay-2">
            <h2 class="font-comic text-3xl text-white uppercase tracking-widest mb-1 rotate-[-1deg] inline-block bg-rose-800 px-4 py-1 border-4 border-black shadow-[4px_4px_0_0_black]">
                Sinopsis
            </h2>
            <div class="mt-5 bg-neutral-900 border-4 border-black shadow-[6px_6px_0_0_black] p-6">
                <p class="text-zinc-300 leading-relaxed text-[15px]"><?= nl2br($descripcion) ?></p>
            </div>
        </section>
        <?php } ?>
        <!-- Ficha técnica -->
        <section class="fade-up delay-2">
            <h2 class="font-comic text-3xl text-black uppercase tracking-widest mb-1 rotate-[1deg] inline-block bg-yellow-500 px-4 py-1 border-4 border-black shadow-[4px_4px_0_0_black]">
                Ficha técnica
            </h2>
            <div class="mt-5 bg-neutral-900 border-4 border-black shadow-[6px_6px_0_0_black] p-6">
                <dl class="grid grid-cols-2 sm:grid-cols-3 gap-6 text-sm">
                    <?php
                    $ficha = ['Título' => $titulo, 'Tipo' => $tipo_label, 'Género' => $genero, 'Editorial' => $editorial, 'Año' => $anno];
                    if ($total_resenas > 0) $ficha['Puntuación'] = number_format($media_puntuacion, 1) . ' / 5 ★ (' . $total_resenas . ' reseñas)';
                    foreach ($ficha as $label => $valor){
                        if (!$valor) continue;
                    ?>
                    <div class="border-l-4 border-rose-800 pl-3">
                        <dt class="font-comic text-[11px] uppercase tracking-widest text-zinc-500 mb-0.5"><?= $label ?></dt>
                        <dd class="text-white font-bold text-base"><?= $valor ?></dd>
                    </div>
                    <?php } ?>
                </dl>
            </div>
        </section>
        <!-- Reseñas -->
        <section class="fade-up delay-3">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
                <h2 class="font-comic text-3xl text-white uppercase tracking-widest rotate-[-1deg] inline-block bg-black px-4 py-1 border-4 border-white shadow-[4px_4px_0_0_white]">
                    Reseñas
                </h2>
                <?php if ($total_resenas > 0){ ?>
                <div class="flex items-center gap-3">
                    <?php if ($total_resenas > 3){ ?>
                        <span class="font-comic text-xs uppercase tracking-widest text-zinc-400 bg-neutral-800 border-2 border-zinc-700 px-3 py-1">
                            Últimas 3 de <?= $total_resenas ?>
                        </span>
                    <?php }else{ ?>
                        <span class="font-comic text-xs uppercase tracking-widest text-zinc-400 bg-neutral-800 border-2 border-zinc-700 px-3 py-1">
                            <?= $total_resenas ?> reseña<?= $total_resenas > 1 ? 's' : '' ?>
                        </span>
                    <?php } ?>
                    <div class="flex items-center gap-1 bg-yellow-500 border-4 border-black shadow-[3px_3px_0_0_black] px-3 py-1">
                        <?php
                        $media_entera = round($media_puntuacion);
                        for ($i = 1; $i <= 5; $i++){
                        ?>
                            <span class="text-base <?= $i <= $media_entera ? 'text-black' : 'text-yellow-700' ?>">★</span>
                        <?php } ?>
                        <span class="font-comic text-lg text-black ml-1 leading-none"><?= number_format($media_puntuacion, 1) ?></span>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php if (empty($resenas)){ ?>
                <div class="bg-neutral-900 border-4 border-black shadow-[6px_6px_0_0_black] p-8 text-center">
                    <p class="font-comic text-2xl text-zinc-500 uppercase tracking-widest">¡Aún sin reseñas!</p>
                    <p class="text-zinc-600 text-sm mt-2">Sé el primero en opinar sobre esta obra.</p>
                </div>
            <?php }else{ ?>
                <div class="flex flex-col gap-5">
                <?php foreach ($resenas as $resena) { 
                    $estrellas = (int)($resena['puntuacion'] ?? 0); ?>
                    <article class="resena-card bg-neutral-900 border-4 border-black shadow-[6px_6px_0_0_black] p-5 transition-all hover:translate-x-1 hover:translate-y-1 hover:shadow-none">
                        <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <div class="w-9 h-9 bg-rose-800 border-2 border-black flex items-center justify-center text-sm font-comic text-white shadow-[2px_2px_0_0_black]">
                                    <?php
                                    try {
                                        $stmt = $_conexion->prepare("SELECT img_perfil FROM usuario WHERE nombre = :nombre");
                                        $stmt->execute(['nombre' => $resena['usuario']]);
                                        $img_perfil = $stmt->fetch(PDO::FETCH_ASSOC);
                                    } catch (PDOException $e) {
                                        die();
                                    }
                                    ?>
                                    <img src="../<?= $img_perfil['img_perfil'] ?? 'default-avatar.png' ?>" alt="Perfil" class="object-cover">
                                </div>
                                <span class="font-comic text-base uppercase tracking-widest text-white">@<?= htmlspecialchars($resena['usuario'] ?? 'user' . $resena['id_usuario']) ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="bg-black border-2 border-zinc-700 px-2 py-0.5 flex items-center gap-0.5">
                                    <span class="text-rose-500 text-sm"><?= str_repeat('★', $estrellas) ?></span><span class="text-zinc-700 text-sm"><?= str_repeat('★', 5 - $estrellas) ?></span>
                                </div>
                                <span class="font-comic text-xs text-zinc-500 uppercase tracking-wider"><?= date('d/m/Y', strtotime($resena['fecha_public'])) ?></span>
                            </div>
                        </div>
                        <p class="text-zinc-300 text-sm leading-relaxed border-l-4 border-rose-800 pl-4"><?= nl2br(htmlspecialchars($resena['comentario'])) ?></p>
                    </article>
                <?php } ?>
                </div>
            <?php } ?>
        </section>
    </div>
    <!-- Columna derecha: formulario -->
    <aside class="lg:col-span-1 fade-up delay-4">
        <div class="sticky top-24 flex flex-col gap-0">
            <div class="bg-rose-800 border-4 border-black px-5 py-3 shadow-[0_0_0_0_black]">
                <h3 class="font-comic text-2xl text-white uppercase tracking-widest">¡Tu reseña!</h3>
            </div>
            <div class="bg-neutral-900 border-4 border-t-0 border-black shadow-[8px_8px_0_0_black] p-6 flex flex-col gap-5">
                <?php if (isset($_GET['resena']) && $_GET['resena'] === 'subida'){ ?>
                <div class="bg-yellow-500 border-4 border-black shadow-[4px_4px_0_0_black] p-3 text-center">
                    <p class="font-comic text-black uppercase tracking-widest text-lg">¡Reseña publicada!</p>
                </div>
                <?php } ?>
                <form method="POST" action="obra.php?id=<?= $id_obra ?>" class="flex flex-col gap-5">
                    <div>
                        <label class="font-comic text-xs uppercase tracking-widest text-zinc-400 mb-2 block">Puntuación</label>
                        <div class="star-rating justify-start">
                            <?php for ($i = 5; $i >= 1; $i--){ ?>
                                <input type="radio" name="puntuacion" id="star<?= $i ?>" value="<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?>>
                                <label for="star<?= $i ?>" title="<?= $i ?> estrellas">★</label>
                            <?php } ?>
                        </div>
                    </div>
                    <div>
                        <label for="resena_texto" class="font-comic text-xs uppercase tracking-widest text-zinc-400 mb-2 block">Tu opinión</label>
                        <textarea
                            id="resena_texto" name="resena_texto" rows="7"
                            placeholder="Comparte qué te ha parecido esta obra..."
                            required
                            class="w-full bg-black border-4 border-zinc-700 focus:border-rose-700 focus:outline-none px-4 py-3 text-sm text-zinc-200 placeholder-zinc-600 resize-none transition-colors leading-relaxed"
                        ></textarea>
                    </div>
                    <p class="font-comic text-xs uppercase tracking-widest text-zinc-500">
                        Publicando como <span class="text-yellow-500">@<?= htmlspecialchars($_SESSION['nombre']) ?></span>
                    </p>
                    <button type="submit"
                            class="w-full bg-rose-800 text-white font-comic text-xl uppercase tracking-widest py-3 border-4 border-black shadow-[6px_6px_0_0_black] hover:bg-rose-900 active:shadow-none active:translate-x-1 active:translate-y-1 transition-all">
                        ¡Publicar!
                    </button>
                </form>
                <a href="javascript:history.back()"
                class="text-center font-comic text-sm uppercase tracking-widest text-zinc-500 hover:text-white transition-colors py-2 border-2 border-zinc-700 hover:border-white">
                    ← Volver
                </a>
            </div>
        </div>
    </aside>
</main>
    
<!-- Footer -->
<?php include "../assets/footer.php"?>

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
                $dias_restantes = $hoy->diff($fecha_evento)->format('%a');
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
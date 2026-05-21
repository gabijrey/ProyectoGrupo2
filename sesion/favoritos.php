<?php
session_start();

if(!isset($_SESSION["nombre"])) {
    header("Location: login.php");
    exit();
}

$nombre = $_SESSION["nombre"];
require "conexion_pdo.php";

//Consulta para unir favoritos con obras
$stmtFavs = $_conexion->prepare("
    SELECT o.*, f.nombre_usuario
    FROM favorito f
    JOIN obra o ON f.id_obra = o.id
    WHERE f.nombre_usuario = :usuario
    ORDER BY o.titulo ASC
");

$stmtFavs->execute(['usuario' => $nombre]);
$obras_favs = $stmtFavs->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comiclook | Mis Favoritos</title>
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
        .comic-card { transition: transform 0.2s, box-shadow 0.2s; }
        .comic-card:hover { 
            transform: translateY(-5px) translateX(-5px); 
            box-shadow: 12px 12px 0 0 #e11d48; /* rose-600 */
        }
    </style>
</head>
<body class="text-white flex flex-col min-h-screen">
    
    <!-- Navbar y Sidebar -->
    <?php include '../assets/navbar.php'?>
    <?php include '../assets/sidebar.php'?>
    <main class="flex-grow p-10 flex flex-col items-center">
        <!-- Título -->
        <h1 class="font-comic text-6xl md:text-8xl mb-12 text-white uppercase drop-shadow-[6px_6px_0_black] italic">
            MIS <span class="text-rose-800">FAVORITOS</span>
        </h1>
        <!-- Galería de obras -->
        <?php if (empty($obras_favs)) { ?>
            <div class="bg-neutral-900 border-4 border-black p-10 shadow-[8px_8px_0_0_black] text-center max-w-xl">
                <p class="font-comic text-3xl text-zinc-500 uppercase tracking-widest">¡Tu estantería está vacía!</p>
                <p class="text-zinc-400 mt-4">Navega por las secciones y dale al corazón en las obras que más te gusten.</p>
                <a href="../index.php" class="inline-block mt-6 bg-yellow-500 text-black font-comic text-xl uppercase px-6 py-2 border-4 border-black shadow-[4px_4px_0_0_black] hover:scale-105 transition-transform">Ir a descubrir</a>
            </div>
        <?php } else { ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8 w-full max-w-7xl">
                <?php foreach ($obras_favs as $obra) { 
                    // Determinar la etiqueta según el tipo (0=Cómic, 1=Manga, 2=Libro)
                    $etiqueta = "Obra";
                    $color_etiqueta = "bg-neutral-800";
                    if ($obra['tipo'] == 0) { $etiqueta = "Cómic"; $color_etiqueta = "bg-blue-600"; }
                    elseif ($obra['tipo'] == 1) { $etiqueta = "Manga"; $color_etiqueta = "bg-rose-800"; }
                    elseif ($obra['tipo'] == 2) { $etiqueta = "Libro"; $color_etiqueta = "bg-yellow-600"; }
                ?>
                    <a href="../webcontent/obra.php?id=<?= $obra['id'] ?>" class="group">
                        <div class="comic-card bg-white border-4 border-black shadow-[8px_8px_0_0_black] relative aspect-[2/3] overflow-hidden">
                            <!-- Píldora de tipo -->
                            <div class="absolute top-2 right-2 z-10 <?= $color_etiqueta ?> text-white font-comic px-3 py-1 text-sm border-2 border-black shadow-[2px_2px_0_0_black] uppercase">
                                <?= $etiqueta ?>
                            </div>
                            <img src="../<?= htmlspecialchars($obra['portada']) ?>" alt="<?= htmlspecialchars($obra['titulo']) ?>" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-300">
                        </div>
                        <h3 class="font-comic text-2xl uppercase mt-4 text-center tracking-wide group-hover:text-rose-500 transition-colors">
                            <?= htmlspecialchars($obra['titulo']) ?>
                        </h3>
                    </a>
                <?php } ?>
            </div>
        <?php } ?>
    </main>
    <!-- Footer -->
    <?php include '../assets/footer.php'?>
</body>
</html>
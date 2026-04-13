<?php
//Iniciar la sesion
session_start();

if (!isset($_SESSION['nombre'])) {
    header("Location: sesion/login.php");
    exit();
}
$nombre = $_SESSION['nombre'];
$email = $_SESSION['email'];

require "sesion/conexion_pdo.php";

$stmtLibrosTop = $_conexion->prepare("
    SELECT o.*, COALESCE(AVG(r.puntuacion), 0) AS media
    FROM obra o
    LEFT JOIN resena r ON o.id = r.id_obra
    WHERE o.tipo = 2
    GROUP BY o.id
    ORDER BY media DESC, o.id DESC
    LIMIT 5
");
$stmtLibrosTop->execute();
$LibrosTop = $stmtLibrosTop->fetchAll(PDO::FETCH_ASSOC);

$stmtCategorias = $_conexion->prepare("
    SELECT DISTINCT genero 
    FROM obra 
    WHERE tipo = 2 AND genero IS NOT NULL AND genero != ''
");
$stmtCategorias->execute();
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_COLUMN);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comiclook | Libros</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
    </style>
</head>
<body class="text-white flex flex-col min-h-screen">

    <nav class="bg-neutral-900 p-6 grid grid-cols-1 md:grid-cols-3 items-center border-b-8 border-black sticky top-0 z-50">
        <div class="flex justify-start">
            <img src="logos/logoLight.webp" class="h-10 w-auto drop-shadow-[4px_4px_0_black]" alt="Logo">
        </div>
        <div class="flex justify-center items-center gap-8 font-comic text-xl">
            <a href="index.php" class="hover:text-rose-800 transition-colors uppercase tracking-widest">Inicio</a>
            <a href="comics.php" class="hover:text-rose-800 transition-colors uppercase tracking-widest">Comics</a>
            <a href="Libros.php" class="text-rose-800 hover:text-white transition-colors uppercase tracking-widest">Libros</a>
            <a href="libros.php" class="hover:text-rose-800 transition-colors uppercase tracking-widest">Libros</a>
        </div>
        <div class="flex justify-end items-center gap-4">
            <span class="text-sm font-bold bg-yellow-500 text-black px-2 border-2 border-black hidden md:block">
                HOLA, <?= strtoupper($_SESSION['nombre']) ?>
            </span>
            <a class="bg-rose-800 text-white px-4 py-2 border-4 border-black font-bold hover:bg-rose-900 transition-all shadow-[4px_4px_0_0_black] active:shadow-none active:translate-y-1 text-xs" href="user.php?usuario=<?= $_SESSION['nombre'] ?>">MI CUENTA</a>
            <a class="bg-black text-white px-4 py-2 border-4 border-black font-bold hover:bg-neutral-800 transition-all text-xs" href="sesion/logout.php">LOGOUT</a>
        </div>
    </nav>
    <main class="flex-grow p-10 flex flex-col items-center">
        <h1 class="font-comic text-6xl md:text-8xl mb-12 text-white uppercase drop-shadow-[6px_6px_0_black] italic">
            Libros
        </h1>
        <section class="fade-up delay-4 mt-8 w-full max-w-7xl">
            <?php if (!empty($LibrosTop)){ ?>
            <div class="mb-16">
                <h2 class="font-comic text-3xl text-black uppercase tracking-widest mb-6 rotate-[-1deg] inline-block bg-yellow-500 px-4 py-1 border-4 border-black shadow-[4px_4px_0_0_black]">
                    Mejores valorados
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-8">
                    <?php foreach ($LibrosTop as $libro){ ?>
                        <div class="flex flex-col items-center group">
                            <div class="w-full aspect-[2/3] bg-white border-4 border-black overflow-hidden shadow-[8px_8px_0_0_black] transition-all group-hover:translate-x-1 group-hover:translate-y-1 group-hover:shadow-none">
                                <a href="obra.php?id=<?= $libro['id'] ?>">
                                    <img class="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500" src="imagen/<?= htmlspecialchars($libro['portada']) ?>" alt="<?= htmlspecialchars($libro['titulo']) ?>">
                                </a>
                            </div>
                            <div class="mt-4 bg-white border-4 border-black p-3 w-full shadow-[4px_4px_0_0_black]">
                                <p class="text-black text-sm font-bold uppercase truncate text-center mb-1" title="<?= htmlspecialchars($libro['titulo']) ?>"><?= htmlspecialchars($libro['titulo']) ?></p>
                                <p class="text-center font-comic text-rose-800 text-xl leading-none">
                                    <?= number_format($libro['media'], 1) ?> <span class="text-yellow-500 drop-shadow-[1px_1px_0_black]">★</span>
                                </p>
                                <a class="block w-full text-center bg-rose-800 text-white font-bold py-1 border-2 border-black mt-2 hover:bg-rose-900 transition-all text-xs" href="obra.php?id=<?= $libro['id'] ?>">
                                    VER MÁS
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>

            <?php foreach ($categorias as $categoria){ 
                $stmtLibros = $_conexion->prepare("
                    SELECT * FROM obra 
                    WHERE tipo IN (2) AND genero = :genero 
                    ORDER BY id DESC 
                    LIMIT 5
                ");
                $stmtLibros->execute(['genero' => $categoria]);
                $LibrosCategorias = $stmtLibros->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($LibrosCategorias) > 0){
            ?>
            <div class="mb-16">
                <h2 class="font-comic text-3xl text-white uppercase tracking-widest mb-6 rotate-[1deg] inline-block bg-rose-800 px-4 py-1 border-4 border-black shadow-[4px_4px_0_0_black]">
                Novedades en <?= htmlspecialchars($categoria) ?>
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-8">
                    <?php foreach ($LibrosCategorias as $libro){ ?>
                        <div class="flex flex-col items-center group">
                            <div class="w-full aspect-[2/3] bg-white border-4 border-black overflow-hidden shadow-[8px_8px_0_0_black] transition-all group-hover:translate-x-1 group-hover:translate-y-1 group-hover:shadow-none">
                                <a href="obra.php?id=<?= $libro['id'] ?>">
                                    <img class="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500" src="imagen/<?= htmlspecialchars($libro['portada']) ?>" alt="<?= htmlspecialchars($libro['titulo']) ?>">
                                </a>
                            </div>
                            <div class="mt-4 bg-white border-4 border-black p-3 w-full shadow-[4px_4px_0_0_black]">
                                <p class="text-black text-sm font-bold uppercase truncate text-center mb-1" title="<?= htmlspecialchars($libro['titulo']) ?>"><?= htmlspecialchars($libro['titulo']) ?></p>
                                <p class="text-center font-comic text-rose-800 text-xl leading-none">
                                    <?= number_format($libro['media'], 1) ?> <span class="text-yellow-500 drop-shadow-[1px_1px_0_black]">★</span>
                                </p>
                                <a class="block w-full text-center bg-rose-800 text-white font-bold py-1 border-2 border-black mt-2 hover:bg-rose-900 transition-all text-xs" href="obra.php?id=<?= $libro['id'] ?>">
                                    VER MÁS
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php 
                }
            }
            ?>

        </section>
    </main>

    <footer class="bg-black w-full border-t-8 border-rose-800 py-10 px-6 text-center">
        <div class="inline-block bg-yellow-500 p-2 border-4 border-black shadow-[6px_6px_0_0_white]">
            <p class="text-sm text-black font-black uppercase tracking-widest">© 2026 TFG DAW - PROYECTO COMICLOOK </p>
        </div>
    </footer>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['nombre'])) {
    header("Location: login.php");
    exit();
}
if (!isset($_GET['id'])) {
    header("Location: listas.php");
    exit();
}
$id_lista = (int)$_GET['id'];
$nombre = $_SESSION['nombre'];
require "conexion_pdo.php";
// 1. Obtener la información de la lista
$stmtLista = $_conexion->prepare("SELECT * FROM lista WHERE id = :id");
$stmtLista->execute(['id' => $id_lista]);
$info_lista = $stmtLista->fetch(PDO::FETCH_ASSOC);
// Si la lista no existe sale a menu de listas
if (!$info_lista) {
    header("Location: listas.php");
    exit();
}
// Lógica de privacidad: Si es privada y no es tuya, no puedes verla
if ($info_lista['privacidad'] == 1 && $info_lista['nombre_usuario'] !== $nombre) {
    die("<h1 style='color:red; font-family:sans-serif; text-align:center; margin-top:50px;'>Esta lista es privada</h1>");
}
// 2. Obtener las obras que están dentro de esta lista
$stmtObras = $_conexion->prepare("
    SELECT o.* 
    FROM lista_obra lo
    JOIN obra o ON lo.id_obra = o.id
    WHERE lo.id_lista = :id_lista
    ORDER BY o.titulo ASC
");
$stmtObras->execute(['id_lista' => $id_lista]);
$obras_lista = $stmtObras->fetchAll(PDO::FETCH_ASSOC);

//Eliminar obra de la lista
if(isset($_GET["eliminar_obra"])) {
    $id_borrar = (int)$_GET["eliminar_obra"];
    //Verificar quien es el dueño de la lista por si acaso
    $consultaUser = "SELECT nombre_usuario FROM lista WHERE id = :id_lista";
    $stmtCheck = $_conexion->prepare($consultaUser);
    $stmtCheck->execute([
        'id_lista' => $id_lista
    ]);
    $propietario = $stmtCheck->fetchColumn();
    if($propietario === $nombre) {
        $consulta_delete = "DELETE FROM lista_obra WHERE id_lista = :id_lista AND id_obra = :id_obra";
        $stmtDel = $_conexion->prepare($consulta_delete);
        $stmtDel->execute([
            "id_lista" => $id_lista,
            "id_obra" => $id_borrar
        ]);

        header("Location: ver_lista.php?id=$id_lista");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comiclook | <?= htmlspecialchars($info_lista['titulo']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../Js/FuncionesLista.js"></script>
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
<body class="text-white flex flex-col min-h-screen">
    
    <?php include '../assets/navbar.php'?>
    <?php include '../assets/sidebar.php'?>
    <main class="flex-grow p-10 flex flex-col items-center max-w-7xl mx-auto w-full">
        
        <!-- Cabecera de la lista -->
        <div class="bg-neutral-900 border-4 border-black p-8 shadow-[8px_8px_0_0_black] w-full mb-12 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden">
            <!-- Patrón de fondo estilo cómic -->
            <div class="absolute inset-0 opacity-10" style="background-image: repeating-linear-gradient(45deg, #000 0, #000 2px, transparent 2px, transparent 8px);"></div>
            
            <div class="relative z-10">
                <a href="listas.php" class="text-yellow-500 font-bold uppercase tracking-widest text-sm hover:text-yellow-400 transition-colors mb-2 inline-block">← Volver a Mis Listas</a>
                <h1 class="font-comic text-5xl md:text-6xl text-white uppercase drop-shadow-[3px_3px_0_black] leading-none mb-2">
                    <?= htmlspecialchars($info_lista['titulo']) ?>
                </h1>
                <p class="text-zinc-400 font-bold uppercase tracking-widest text-sm">
                    Creada por <span class="text-rose-500"><?= htmlspecialchars($info_lista['nombre_usuario']) ?></span> el <?= date('d/m/Y', strtotime($info_lista['fecha_creacion'])) ?>
                </p>
                <?php if (!empty($info_lista['descripcion'])) { ?>
                    <p class="text-zinc-300 mt-4 max-w-3xl leading-relaxed border-l-4 border-yellow-500 pl-4"><?= htmlspecialchars($info_lista['descripcion']) ?></p>
                <?php } ?>
            </div>
            
            <!-- Badge de Privacidad -->
            <div class="relative z-10 flex items-center gap-2 bg-black border-2 border-zinc-700 px-4 py-2 font-bold uppercase text-sm tracking-widest">
                <?php if($info_lista['privacidad'] == 1) { ?>
                    <svg class="w-5 h-5 text-rose-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <span class="text-rose-600">Privada</span>
                <?php } else { ?>
                    <svg class="w-5 h-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path><path d="M2 12h20"></path></svg>
                    <span class="text-blue-500">Pública</span>
                <?php } ?>
            </div>
        </div>
        <!-- Galería de obras -->
        <?php if (empty($obras_lista)) { ?>
            <div class="bg-neutral-900 border-4 border-black p-10 text-center shadow-[8px_8px_0_0_black] max-w-2xl w-full">
                <p class="font-comic text-3xl text-zinc-500 uppercase tracking-widest">Lista vacía</p>
                <p class="text-zinc-400 font-bold mt-2">Navega por las fichas de las obras y añádelas a esta lista.</p>
                <a href="../index.php" class="inline-block mt-6 bg-yellow-500 text-black font-comic text-xl uppercase px-6 py-2 border-4 border-black shadow-[4px_4px_0_0_black] hover:scale-105 transition-transform">Ir al catálogo</a>
            </div>
        <?php } else { ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8 w-full">
                <?php foreach ($obras_lista as $obra) { 
                    $etiqueta = "Obra";
                    $color_etiqueta = "bg-neutral-800";
                    if ($obra['tipo'] == 0) { $etiqueta = "Cómic"; $color_etiqueta = "bg-blue-600"; }
                    elseif ($obra['tipo'] == 1) { $etiqueta = "Manga"; $color_etiqueta = "bg-rose-800"; }
                    elseif ($obra['tipo'] == 2) { $etiqueta = "Libro"; $color_etiqueta = "bg-yellow-600"; }
                ?>
                    <a href="../webcontent/obra.php?id=<?= $obra['id'] ?>" class="group relative block">
                        <?php if ($info_lista['nombre_usuario'] === $nombre) { ?>
                        <!-- Botón de eliminar (asoma por la izquierda al hacer hover) -->
                        <button onclick="eliminarDeLista(event, <?= $id_lista ?>, <?= $obra['id'] ?>)" 
                                class="absolute top-4 left-0 opacity-0 group-hover:-left-5 group-hover:opacity-100 group-hover:-translate-y-2 group-hover:-translate-x-2 bg-rose-800 text-white p-2 border-2 border-black shadow-[2px_2px_0_0_black] transition-all duration-300 hover:bg-rose-900 z-[-1] group-hover:z-10" 
                                title="Quitar de la lista">
                            <!-- Icono Cubo de Basura -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </button>
                        <?php } ?>
                        <div class="bg-white border-4 border-black shadow-[8px_8px_0_0_black] relative aspect-[2/3] overflow-hidden transition-all duration-300 group-hover:-translate-y-2 group-hover:-translate-x-2 group-hover:shadow-[12px_12px_0_0_#9f1239]">
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
    <?php include '../assets/footer.php'?>
</body>
</html>
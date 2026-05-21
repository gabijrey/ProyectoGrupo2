<?php
$pagina_actual = basename($_SERVER['PHP_SELF']);
$path = ($pagina_actual == 'index.php') ? "" : "../";

 // Comprobar si el usuario es premium para mostrar enlaces exclusivos
            $es_premium_sb = false;
            if (isset($_SESSION['nombre']) && isset($_conexion)) {
                $helper_path = __DIR__ . '/../sesion/premium_helper.php';
                if (file_exists($helper_path)) {
                    require_once $helper_path;
                    $es_premium_sb = esPremium($_conexion, $_SESSION['nombre']);
                }
            }
?>
<nav class="bg-neutral-900 p-4 md:p-6 flex flex-col md:grid md:grid-cols-[1fr_2fr_1fr] items-center gap-4 md:gap-0 border-b-8 border-black sticky top-0 z-50 overflow-hidden">
    
    <!-- Fila superior en móvil (Logo + Botones de cuenta móvil) -->
    <div class="w-full flex justify-between items-center md:w-auto">
        <div class="flex items-center">
            <button onclick="toggleSidebar()" class="mr-4 md:mr-6 text-white hover:text-rose-800 transition-all transform hover:scale-110 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 md:h-10 md:w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 6h16M4 12h10M4 18h16" />
                </svg>
            </button>
            <a href="<?= $path ?>index.php" style="cursor: pointer;">
                <img src="<?= $path ?>logos/logoLight.webp" class="h-8 md:h-10 w-auto drop-shadow-[2px_2px_0_black] md:drop-shadow-[4px_4px_0_black]" alt="Logo">
            </a>
        </div>
        
        <!-- Botones de cuenta (Solo visibles en móvil) -->
        <div class="flex md:hidden items-center gap-2">
            <a class="bg-rose-800 text-white px-2 py-1 border-2 border-black font-bold text-[10px]" href="<?= $path ?>sesion/perfilUsuario.php?usuario=<?= $_SESSION['nombre'] ?>">CUENTA</a>
            <a class="bg-black text-white px-2 py-1 border-2 border-black font-bold text-[10px]" href="<?= $path ?>sesion/logout.php">SALIR</a>
        </div>
    </div>
    <!-- Enlaces del centro (Con scroll horizontal oculto en móvil) -->
    <div class="flex w-full md:w-auto overflow-x-auto md:overflow-visible md:justify-center items-center gap-6 md:gap-8 font-comic text-lg md:text-xl pb-2 md:pb-0" style="scrollbar-width: none;">
        <a href="<?= $path ?>index.php" class="whitespace-nowrap <?= ($pagina_actual == 'index.php') ? 'text-rose-800' : 'hover:text-rose-800' ?> hover:text-white-800 transition-colors uppercase tracking-widest">Inicio</a>
        <a href="<?= $path ?>webcontent/comics.php" class="whitespace-nowrap <?= ($pagina_actual == 'comics.php') ? 'text-rose-800' : 'hover:text-rose-800' ?> hover:text-white-800 transition-colors uppercase tracking-widest">Comics</a>
        <a href="<?= $path ?>webcontent/mangas.php" class="whitespace-nowrap <?= ($pagina_actual == 'mangas.php') ? 'text-rose-800' : 'hover:text-rose-800' ?> hover:text-white-800 transition-colors uppercase tracking-widest">Mangas</a>
        <a href="<?= $path ?>webcontent/libros.php" class="whitespace-nowrap <?= ($pagina_actual == 'libros.php') ? 'text-rose-800' : 'hover:text-rose-800' ?> hover:text-white-800 transition-colors uppercase tracking-widest">Libros</a>
        <?php if ($es_premium_sb): ?>
        <a href="<?= $path ?>webcontent/comparar.php" class="whitespace-nowrap <?= ($pagina_actual == 'comparar.php') ? 'text-yellow-500' : 'hover:text-rose-800' ?> hover:text-yellow-500 transition-colors uppercase tracking-widest">Comparar</a>
        <?php endif; ?>
        <a href="<?= $path ?>webcontent/social.php" class="whitespace-nowrap <?= ($pagina_actual == 'social.php') ? 'text-rose-800' : 'hover:text-rose-800' ?> hover:text-white-800 transition-colors uppercase tracking-widest">Social</a>
    </div>
    <!-- Botones de cuenta (Solo visibles en ordenador) -->
    <div class="hidden md:flex justify-end items-center gap-4">
        <span class="text-sm font-bold bg-yellow-500 text-black px-2 border-2 border-black flex items-center">
            HOLA, <?= strtoupper($_SESSION['nombre']) ?>
            <?php if(isset($_SESSION['rol']) && in_array($_SESSION['rol'], [1, 2])): ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 ml-1 animate-pulse <?= $_SESSION['rol'] == 1 ? 'text-yellow-600' : 'text-cyan-400' ?> drop-shadow-[1px_1px_0_black]" title="<?= $_SESSION['rol'] == 1 ? 'Premium' : 'Autor' ?>">
                    <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                </svg>
            <?php endif; ?>
        </span>
        <a class="bg-rose-800 text-white px-4 py-2 border-4 border-black font-bold hover:bg-rose-900 transition-all shadow-[4px_4px_0_0_black] active:shadow-none active:translate-y-1 text-xs" href="<?= $path ?>sesion/perfilUsuario.php?usuario=<?= $_SESSION['nombre'] ?>">MI CUENTA</a>
        <a class="bg-black text-white px-4 py-2 border-4 border-black font-bold hover:bg-neutral-800 transition-all text-xs" href="<?= $path ?>sesion/logout.php">LOGOUT</a>
    </div>
</nav>
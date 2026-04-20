<?php
$pagina_actual = basename($_SERVER['PHP_SELF']);
$path = ($pagina_actual == 'index.php') ? "" : "../";
?>
<nav class="bg-neutral-900 p-6 grid grid-cols-1 md:grid-cols-3 items-center border-b-8 border-black sticky top-0 z-50">
        <div class="flex justify-start">
            <button onclick="toggleSidebar()" class="mr-6 text-white hover:text-rose-800 transition-all transform hover:scale-110 active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 6h16M4 12h10M4 18h16" />
    </svg>
</button>
            <a href="<?= $path ?>index.php" style="cursor: pointer;"><img src="<?= $path ?>logos/logoLight.webp" class="h-10 w-auto drop-shadow-[4px_4px_0_black]" alt="Logo"></a>
        </div>
        <div class="flex justify-center items-center gap-8 font-comic text-xl">
            <a href="<?= $path ?>index.php" class="<?= ($pagina_actual == 'index.php') ? 'text-rose-800' : 'hover:text-rose-800' ?> hover:text-white-800 transition-colors uppercase tracking-widest">Inicio</a>
            <a href="<?= $path ?>webcontent/comics.php" class="<?= ($pagina_actual == 'comics.php') ? 'text-rose-800' : 'hover:text-rose-800' ?> hover:text-white-800 transition-colors uppercase tracking-widest">Comics</a>
            <a href="<?= $path ?>webcontent/mangas.php" class="<?= ($pagina_actual == 'mangas.php') ? 'text-rose-800' : 'hover:text-rose-800' ?> hover:text-white-800 transition-colors uppercase tracking-widest">Mangas</a>
            <a href="<?= $path ?>webcontent/libros.php" class="<?= ($pagina_actual == 'libros.php') ? 'text-rose-800' : 'hover:text-rose-800' ?> hover:text-white-800 transition-colors uppercase tracking-widest">Libros</a>
        </div>
        <div class="flex justify-end items-center gap-4">
            <span class="text-sm font-bold bg-yellow-500 text-black px-2 border-2 border-black hidden md:block">
                HOLA, <?= strtoupper($_SESSION['nombre']) ?>
            </span>
            <a class="bg-rose-800 text-white px-4 py-2 border-4 border-black font-bold hover:bg-rose-900 transition-all shadow-[4px_4px_0_0_black] active:shadow-none active:translate-y-1 text-xs" href="<?= $path ?>sesion/user.php?usuario=<?= $_SESSION['nombre'] ?>">MI CUENTA</a>
            <a class="bg-black text-white px-4 py-2 border-4 border-black font-bold hover:bg-neutral-800 transition-all text-xs" href="<?= $path ?>sesion/logout.php">LOGOUT</a>
        </div>
    </nav>
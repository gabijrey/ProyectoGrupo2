<?php
$pagina_actual_dash = basename($_SERVER['PHP_SELF']);
// Determinar si estamos en la carpeta 'sesion' o en la raíz
$is_in_sesion = (strpos($_SERVER['PHP_SELF'], '/sesion/') !== false);
$prefix = $is_in_sesion ? "../" : "";
$sesion_prefix = $is_in_sesion ? "" : "sesion/";
?>
<aside class="w-full md:w-64 bg-neutral-900 border-b-8 md:border-b-0 md:border-r-8 border-black p-6 flex flex-col justify-between shrink-0">
    <div>
        <div class="mb-10 text-center md:text-left">
            <a href="<?= $prefix ?>index.php">
                <img src="<?= $prefix ?>logos/logoLight.webp" class="h-10 w-auto inline-block drop-shadow-[4px_4px_0_black]" alt="ComicLook">
            </a>
        </div>
        
        <nav class="space-y-4">
            <!-- Link a Perfil Público (NUEVO) -->
            <a href="<?= $prefix ?>perfilUsuario.php?usuario=<?= $_SESSION['nombre'] ?>" 
               class="flex items-center gap-3 <?= ($pagina_actual_dash == 'perfilUsuario.php') ? 'bg-yellow-500 text-black' : 'text-white hover:bg-rose-800' ?> font-bold uppercase px-4 py-3 border-4 border-black shadow-[4px_4px_0_0_black] hover:scale-105 hover:-rotate-2 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Mi Perfil
            </a>

            <!-- Link a Ajustes de Cuenta (User.php) -->
            <a href="<?= $sesion_prefix ?>user.php" 
               class="flex items-center gap-3 <?= ($pagina_actual_dash == 'user.php') ? 'bg-yellow-500 text-black' : 'text-white hover:bg-rose-800' ?> font-bold uppercase px-4 py-3 border-4 border-black shadow-[4px_4px_0_0_black] hover:scale-105 hover:-rotate-2 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.1a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                Ajustes
            </a>

            <a href="<?= $sesion_prefix ?>favoritos.php" class="flex items-center gap-3 text-white font-bold uppercase px-4 py-3 border-4 border-transparent hover:border-black hover:bg-rose-800 transition-all hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                Favoritos
            </a>
            <a href="<?= $sesion_prefix ?>listas.php" class="flex items-center gap-3 text-white font-bold uppercase px-4 py-3 border-4 border-transparent hover:border-black hover:bg-rose-800 transition-all hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                Mis listas
            </a>
            <a href="<?= $prefix ?>index.php" class="flex items-center gap-3 text-white font-bold uppercase px-4 py-3 border-4 border-transparent hover:border-black hover:bg-neutral-800 transition-all hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Volver a Inicio
            </a>
        </nav>
    </div>

    <div class="mt-10 border-t-4 border-black pt-6">
        <a href="<?= $sesion_prefix ?>logout.php" class="flex items-center gap-3 text-rose-800 font-bold uppercase hover:text-rose-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
            Cerrar Sesión
        </a>
    </div>
</aside>

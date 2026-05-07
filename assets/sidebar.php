<!-- Fondo oscuro-->
<div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/60 z-[60] hidden backdrop-blur-sm transition-opacity duration-300"></div>
<!-- Panel lateral -->
<div id="sidebarPanel" class="fixed top-0 left-0 h-full w-72 bg-neutral-900 border-r-8 border-black z-[70] transform -translate-x-full transition-transform duration-300 ease-in-out flex flex-col p-8 shadow-[10px_0_0_0_rgba(0,0,0,0.3)]">
    
    <!-- Cabecera del menu -->
    <div class="flex justify-between items-center mb-12 border-b-4 border-black pb-4">
        <h2 class="font-comic text-4xl text-yellow-500 uppercase italic tracking-tighter drop-shadow-[3px_3px_0_black]">
            Menú
        </h2>
        <button onclick="toggleSidebar()" class="text-white hover:text-rose-800 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <!-- Enlaces principales -->
    <nav class="flex flex-col gap-8">
        <?php
            $pagina_actual_sb = basename($_SERVER['PHP_SELF']);
            $path_sb = ($pagina_actual_sb == 'index.php') ? "" : "../";

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
        <a href="<?= $path_sb ?>webcontent/comparar.php" class="font-comic text-2xl text-white hover:text-rose-800 transition-all uppercase tracking-widest flex items-center gap-3 group">
            <span class="w-2 h-2 bg-rose-800 group-hover:w-4 transition-all"></span>
            Comparar obras
        </a>
        <?php if ($es_premium_sb): ?>
            <a href="<?= $path_sb ?>webcontent/escaner.php" class="font-comic text-2xl text-yellow-500 hover:text-rose-800 transition-all uppercase tracking-widest flex items-center gap-3 group">
                <span class="w-2 h-2 bg-yellow-500 group-hover:w-4 transition-all"></span>
                Escáner ★
            </a>
        <?php endif; ?>
        <a href="<?= $path_sb ?>sesion/favoritos.php" class="font-comic text-2xl text-white hover:text-rose-800 transition-all uppercase tracking-widest flex items-center gap-3 group">
            <span class="w-2 h-2 bg-rose-800 group-hover:w-4 transition-all"></span>
            Mis Favoritos
        </a>
        <a href="<?= $path_sb ?>sesion/listas.php" class="font-comic text-2xl text-white hover:text-rose-800 transition-all uppercase tracking-widest flex items-center gap-3 group">
            <span class="w-2 h-2 bg-rose-800 group-hover:w-4 transition-all"></span>
            Mis Listas
        </a>
        <a href="pricing.php" class="font-comic text-2xl text-white hover:text-rose-800 transition-all uppercase tracking-widest flex items-center gap-3 group">
            <span class="w-2 h-2 bg-rose-800 group-hover:w-4 transition-all"></span>
            Planes y precios
        </a>
        <a href="eventos.php" class="font-comic text-2xl text-white hover:text-rose-800 transition-all uppercase tracking-widest flex items-center gap-3 group">
            <span class="w-2 h-2 bg-rose-800 group-hover:w-4 transition-all"></span>
            Eventos
        </a>
        <a href="faq.php" class="font-comic text-2xl text-white hover:text-rose-800 transition-all uppercase tracking-widest flex items-center gap-3 group">
            <span class="w-2 h-2 bg-rose-800 group-hover:w-4 transition-all"></span>
            Preguntas frecuentes
        </a>
        <a href="nosotros.php" class="font-comic text-2xl text-white hover:text-rose-800 transition-all uppercase tracking-widest flex items-center gap-3 group">
            <span class="w-2 h-2 bg-rose-800 group-hover:w-4 transition-all"></span>
            Sobre nosotros
        </a>
    </nav>
    <!-- Redes Sociales -->
    <div class="mt-auto pt-8 border-t-4 border-black flex flex-col gap-4">
        <span class="font-comic text-sm text-zinc-500 uppercase tracking-widest">¡Síguenos!</span>
        <div class="flex gap-6">
            <!-- Instagram -->
            <a href="https://instagram.com/somoscomiclook" target="_blank" class="text-zinc-500 hover:text-rose-800 transition-all transform hover:scale-125">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
            </a>
            <!-- Twitter -->
            <a href="https://x.com/somoscomiclook" target="_blank" class="text-zinc-500 hover:text-rose-800 transition-all transform hover:scale-125">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4l11.733 16h4.267l-11.733-16zM4 20l6.768-6.768m2.46-2.46L20 4"></path></svg>
            </a>
        </div>
    </div>
</div>
<!-- JS para el boton -->
<script>
    function toggleSidebar() {
        const panel = document.getElementById('sidebarPanel');
        const overlay = document.getElementById('sidebarOverlay');
        
        panel.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }
</script>
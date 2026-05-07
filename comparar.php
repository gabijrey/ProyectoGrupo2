<?php
session_start();

if (!isset($_SESSION['nombre'])) {
    header("Location: ../sesion/login.php");
    exit();
}

require __DIR__ . "/../sesion/conexion_pdo.php";

$nombre = $_SESSION['nombre'];
$tipos_label = [0 => 'Cómic', 1 => 'Manga', 2 => 'Libro', 3 => 'Cómic'];
$tipos_color = [0 => 'bg-blue-600', 1 => 'bg-rose-800', 2 => 'bg-yellow-600', 3 => 'bg-blue-600'];

function cargarObraCompleta($conexion, $id_obra, $usuario) {
    $stmt = $conexion->prepare("SELECT * FROM obra WHERE id = :id");
    $stmt->execute(['id' => $id_obra]);
    $obra = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$obra) return null;

    // Estadísticas (total reseñas + media)
    try {
        $stmt = $conexion->prepare("SELECT COUNT(*) AS total, AVG(puntuacion) AS media FROM resena WHERE id_obra = :id");
        $stmt->execute(['id' => $id_obra]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        $obra['total_resenas'] = (int)($stats['total'] ?? 0);
        $obra['media_puntuacion'] = $obra['total_resenas'] > 0 ? round((float)$stats['media'], 1) : 0;
    } catch (PDOException $e) {
        $obra['total_resenas'] = 0;
        $obra['media_puntuacion'] = 0;
    }

    // Autores
    $obra['autores'] = [];
    try {
        $stmt = $conexion->prepare("
            SELECT a.nombre, a.apellidos
            FROM obra_autor oa
            JOIN autor a ON oa.id_autor = a.id
            WHERE oa.id_obra = :id
        ");
        $stmt->execute(['id' => $id_obra]);
        $obra['autores'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {}

    // ¿Es favorito del usuario?
    $obra['es_favorito'] = false;
    try {
        $stmt = $conexion->prepare("SELECT 1 FROM favorito WHERE id_obra = :id AND nombre_usuario = :u");
        $stmt->execute(['id' => $id_obra, 'u' => $usuario]);
        $obra['es_favorito'] = (bool)$stmt->fetch();
    } catch (PDOException $e) {}

    // ¿En cuántas de mis listas está?
    $obra['num_listas'] = 0;
    $obra['nombres_listas'] = [];
    try {
        $stmt = $conexion->prepare("
            SELECT l.titulo
            FROM lista_obra lo
            JOIN lista l ON lo.id_lista = l.id
            WHERE lo.id_obra = :id AND l.nombre_usuario = :u
        ");
        $stmt->execute(['id' => $id_obra, 'u' => $usuario]);
        $obra['nombres_listas'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $obra['num_listas'] = count($obra['nombres_listas']);
    } catch (PDOException $e) {}

    return $obra;
}

$id1 = (isset($_GET['id1']) && is_numeric($_GET['id1'])) ? (int)$_GET['id1'] : null;
$id2 = (isset($_GET['id2']) && is_numeric($_GET['id2'])) ? (int)$_GET['id2'] : null;

$obra1 = $id1 ? cargarObraCompleta($_conexion, $id1, $nombre) : null;
$obra2 = $id2 ? cargarObraCompleta($_conexion, $id2, $nombre) : null;

// Cargar mis favoritos para el quick-pick
$mis_favoritos = [];
try {
    $stmt = $_conexion->prepare("
        SELECT o.id, o.titulo, o.portada, o.tipo
        FROM favorito f
        JOIN obra o ON f.id_obra = o.id
        WHERE f.nombre_usuario = :u
        ORDER BY o.titulo ASC
    ");
    $stmt->execute(['u' => $nombre]);
    $mis_favoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// Cargar mis listas con sus obras
$listas_agrupadas = [];
try {
    $stmt = $_conexion->prepare("
        SELECT l.id AS id_lista, l.titulo AS titulo_lista,
               o.id AS id_obra, o.titulo, o.portada, o.tipo
        FROM lista l
        LEFT JOIN lista_obra lo ON lo.id_lista = l.id
        LEFT JOIN obra o ON o.id = lo.id_obra
        WHERE l.nombre_usuario = :u
        ORDER BY l.fecha_creacion DESC, o.titulo ASC
    ");
    $stmt->execute(['u' => $nombre]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $key = $row['id_lista'];
        if (!isset($listas_agrupadas[$key])) {
            $listas_agrupadas[$key] = [
                'titulo' => $row['titulo_lista'],
                'obras' => [],
            ];
        }
        if (!empty($row['id_obra'])) {
            $listas_agrupadas[$key]['obras'][] = [
                'id' => $row['id_obra'],
                'titulo' => $row['titulo'],
                'portada' => $row['portada'],
                'tipo' => $row['tipo'],
            ];
        }
    }
} catch (PDOException $e) {}

// Helper para construir querystring conservando el otro id
function urlConObra($slot, $id_obra, $id1_actual, $id2_actual) {
    $params = [];
    if ($slot === 1) {
        $params['id1'] = $id_obra;
        if ($id2_actual) $params['id2'] = $id2_actual;
    } else {
        if ($id1_actual) $params['id1'] = $id1_actual;
        $params['id2'] = $id_obra;
    }
    return 'comparar.php?' . http_build_query($params);
}

function urlSinObra($slot, $id1_actual, $id2_actual) {
    $params = [];
    if ($slot === 1 && $id2_actual) $params['id2'] = $id2_actual;
    if ($slot === 2 && $id1_actual) $params['id1'] = $id1_actual;
    return 'comparar.php' . (empty($params) ? '' : '?' . http_build_query($params));
}

// Determinar "ganador" de una métrica numérica para destacarla
function ganador($v1, $v2) {
    if ($v1 === null || $v2 === null) return 0;
    if ($v1 > $v2) return 1;
    if ($v2 > $v1) return 2;
    return 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comiclook | Comparador</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="../comiclook_icon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bangers&display=swap" rel="stylesheet">
    <style>
        .font-comic { font-family: 'Bangers', cursive; }
        body {
            background-color: #171717;
            background-image: radial-gradient(#333 1px, transparent 1px);
            background-size: 20px 20px;
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #18181b; }
        ::-webkit-scrollbar-thumb { background: #9f1239; border-radius: 3px; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp 0.5s ease both; }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .winner-glow {
            position: relative;
        }
        .winner-glow::after {
            content: '★ MEJOR';
            position: absolute;
            top: -10px;
            right: -10px;
            background: #fbbf24;
            color: #000;
            font-family: 'Bangers', cursive;
            font-size: 0.7rem;
            padding: 2px 8px;
            border: 2px solid #000;
            box-shadow: 2px 2px 0 0 #000;
            transform: rotate(8deg);
        }
        .tab-btn.active {
            background: #9f1239;
            color: #fff;
        }
    </style>
</head>
<body class="text-white flex flex-col min-h-screen">

<?php include '../assets/navbar.php'?>
<?php include '../assets/sidebar.php'?>

<main class="flex-grow p-6 md:p-10 max-w-7xl mx-auto w-full">

    <!-- Cabecera -->
    <div class="text-center mb-10 fade-up">
        <h1 class="font-comic text-6xl md:text-7xl text-white uppercase drop-shadow-[6px_6px_0_black] italic">
            COMPARADOR <span class="text-rose-800">DE OBRAS</span>
        </h1>
        <p class="mt-4 font-bold uppercase tracking-widest text-zinc-400 text-sm">
            Elige dos obras y enfréntalas cara a cara
        </p>
    </div>

    <!-- SELECTORES -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-10 relative">

        <?php
        $obras = [1 => $obra1, 2 => $obra2];
        foreach ($obras as $slot => $obra):
            $tipo_label = $obra ? ($tipos_label[$obra['tipo']] ?? 'Obra') : '';
            $color_tipo = $obra ? ($tipos_color[$obra['tipo']] ?? 'bg-neutral-800') : '';
        ?>
        <div class="fade-up <?= $slot === 2 ? 'delay-1' : '' ?>">
            <!-- Etiqueta de slot -->
            <div class="flex items-center justify-between mb-3">
                <span class="font-comic text-2xl uppercase bg-black px-4 py-1 border-4 border-<?= $slot === 1 ? 'rose-800' : 'yellow-500' ?> text-<?= $slot === 1 ? 'rose-500' : 'yellow-500' ?> shadow-[4px_4px_0_0_black]">
                    Obra #<?= $slot ?>
                </span>
                <?php if ($obra): ?>
                    <a href="<?= urlSinObra($slot, $id1, $id2) ?>" class="text-xs font-bold uppercase tracking-widest text-zinc-400 hover:text-rose-500 transition-colors border-2 border-zinc-700 hover:border-rose-500 px-3 py-1">
                        ✕ Cambiar
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($obra): ?>
                <!-- Vista previa de la obra elegida -->
                <div class="bg-neutral-900 border-4 border-black shadow-[8px_8px_0_0_black] p-5 flex gap-5 items-center">
                    <div class="flex-shrink-0 bg-white p-2 border-4 border-black shadow-[4px_4px_0_0_black]">
                        <?php if (!empty($obra['portada'])): ?>
                            <img src="../<?= htmlspecialchars($obra['portada']) ?>" alt="<?= htmlspecialchars($obra['titulo']) ?>" class="w-28 h-40 object-cover border-2 border-black">
                        <?php else: ?>
                            <div class="w-28 h-40 bg-zinc-200 flex items-center justify-center text-black font-comic text-xs uppercase text-center p-2">Próximamente</div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="<?= $color_tipo ?> text-white font-comic px-2 py-0.5 text-xs border-2 border-black uppercase shadow-[2px_2px_0_0_black] inline-block mb-2"><?= $tipo_label ?></span>
                        <h3 class="font-comic text-2xl md:text-3xl text-white uppercase leading-tight truncate" title="<?= htmlspecialchars($obra['titulo']) ?>">
                            <?= htmlspecialchars($obra['titulo']) ?>
                        </h3>
                        <p class="text-xs text-zinc-400 mt-1 font-bold uppercase tracking-widest"><?= htmlspecialchars($obra['nombre_editorial'] ?? '—') ?> · <?= htmlspecialchars($obra['anno_lanzamiento'] ?? '—') ?></p>
                        <a href="obra.php?id=<?= $obra['id'] ?>" class="inline-block mt-3 bg-rose-800 text-white text-xs font-bold uppercase tracking-widest px-3 py-1 border-2 border-black shadow-[3px_3px_0_0_black] hover:bg-rose-900 transition-colors">
                            Ver ficha →
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Selector vacío -->
                <div class="bg-neutral-900 border-4 border-dashed border-zinc-700 p-5">
                    <!-- Tabs -->
                    <div class="flex gap-1 border-b-4 border-black mb-4">
                        <button type="button" data-slot="<?= $slot ?>" data-tab="buscar" class="tab-btn active flex-1 font-comic text-sm uppercase tracking-widest px-3 py-2 bg-neutral-800 text-zinc-300 border-2 border-black border-b-0 transition-colors">
                            Buscar
                        </button>
                        <button type="button" data-slot="<?= $slot ?>" data-tab="favoritos" class="tab-btn flex-1 font-comic text-sm uppercase tracking-widest px-3 py-2 bg-neutral-800 text-zinc-300 border-2 border-black border-b-0 transition-colors">
                            ♥ Favs (<?= count($mis_favoritos) ?>)
                        </button>
                        <button type="button" data-slot="<?= $slot ?>" data-tab="listas" class="tab-btn flex-1 font-comic text-sm uppercase tracking-widest px-3 py-2 bg-neutral-800 text-zinc-300 border-2 border-black border-b-0 transition-colors">
                            ☰ Listas (<?= count($listas_agrupadas) ?>)
                        </button>
                    </div>

                    <!-- Tab: Buscar -->
                    <div data-slot="<?= $slot ?>" data-panel="buscar" class="tab-panel">
                        <input type="text"
                               data-buscador="<?= $slot ?>"
                               placeholder="ESCRIBE UN TÍTULO..."
                               autocomplete="off"
                               class="w-full bg-black border-4 border-zinc-700 focus:border-rose-700 focus:outline-none px-4 py-3 text-white font-comic tracking-wider transition-colors">
                        <p class="text-xs text-zinc-500 mt-3 font-bold uppercase tracking-widest">
                            Busca entre todas las obras de la web.
                        </p>
                    </div>

                    <!-- Tab: Favoritos -->
                    <div data-slot="<?= $slot ?>" data-panel="favoritos" class="tab-panel hidden">
                        <?php if (empty($mis_favoritos)): ?>
                            <div class="text-center py-6">
                                <p class="font-comic text-xl text-zinc-500 uppercase">Sin favoritos</p>
                                <p class="text-zinc-400 text-xs mt-1">Marca obras con el corazón para que aparezcan aquí.</p>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 max-h-[280px] overflow-y-auto pr-1">
                                <?php foreach ($mis_favoritos as $fav): ?>
                                    <?php $color_pill = $tipos_color[$fav['tipo']] ?? 'bg-neutral-800'; ?>
                                    <a href="<?= urlConObra($slot, $fav['id'], $id1, $id2) ?>" class="group block bg-white border-2 border-black shadow-[3px_3px_0_0_black] hover:shadow-[5px_5px_0_0_#9f1239] hover:-translate-y-0.5 transition-all">
                                        <div class="aspect-[2/3] overflow-hidden relative">
                                            <span class="absolute top-1 right-1 z-10 <?= $color_pill ?> text-white font-comic px-1.5 py-0.5 text-[9px] border border-black uppercase">
                                                <?= $tipos_label[$fav['tipo']] ?? '' ?>
                                            </span>
                                            <?php if (!empty($fav['portada'])): ?>
                                                <img src="../<?= htmlspecialchars($fav['portada']) ?>" alt="<?= htmlspecialchars($fav['titulo']) ?>" class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all">
                                            <?php else: ?>
                                                <div class="w-full h-full bg-zinc-300 flex items-center justify-center text-black font-comic text-[10px] uppercase p-1 text-center">Próximamente</div>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-black text-[10px] font-bold uppercase truncate p-1.5 text-center" title="<?= htmlspecialchars($fav['titulo']) ?>"><?= htmlspecialchars($fav['titulo']) ?></p>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab: Listas -->
                    <div data-slot="<?= $slot ?>" data-panel="listas" class="tab-panel hidden">
                        <?php if (empty($listas_agrupadas)): ?>
                            <div class="text-center py-6">
                                <p class="font-comic text-xl text-zinc-500 uppercase">Sin listas</p>
                                <p class="text-zinc-400 text-xs mt-1">Crea listas desde la sección "Mis listas" para agrupar tus lecturas.</p>
                            </div>
                        <?php else: ?>
                            <div class="max-h-[320px] overflow-y-auto pr-1 flex flex-col gap-3">
                                <?php foreach ($listas_agrupadas as $lista): ?>
                                    <details class="bg-black/40 border-2 border-zinc-700 group" <?= count($listas_agrupadas) === 1 ? 'open' : '' ?>>
                                        <summary class="cursor-pointer px-3 py-2 font-comic text-sm uppercase tracking-widest text-yellow-500 flex justify-between items-center hover:bg-zinc-900 transition-colors">
                                            <span class="truncate"><?= htmlspecialchars($lista['titulo']) ?></span>
                                            <span class="text-[10px] text-zinc-400">(<?= count($lista['obras']) ?>)</span>
                                        </summary>
                                        <?php if (empty($lista['obras'])): ?>
                                            <p class="text-xs text-zinc-500 italic px-3 py-2 border-t-2 border-zinc-800">Lista vacía.</p>
                                        <?php else: ?>
                                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 p-2 border-t-2 border-zinc-800">
                                                <?php foreach ($lista['obras'] as $ol): ?>
                                                    <?php $color_pill = $tipos_color[$ol['tipo']] ?? 'bg-neutral-800'; ?>
                                                    <a href="<?= urlConObra($slot, $ol['id'], $id1, $id2) ?>" class="group block bg-white border-2 border-black hover:shadow-[3px_3px_0_0_#fbbf24] transition-all">
                                                        <div class="aspect-[2/3] overflow-hidden relative">
                                                            <span class="absolute top-1 right-1 z-10 <?= $color_pill ?> text-white font-comic px-1 text-[8px] border border-black uppercase">
                                                                <?= $tipos_label[$ol['tipo']] ?? '' ?>
                                                            </span>
                                                            <?php if (!empty($ol['portada'])): ?>
                                                                <img src="../<?= htmlspecialchars($ol['portada']) ?>" alt="<?= htmlspecialchars($ol['titulo']) ?>" class="w-full h-full object-cover">
                                                            <?php else: ?>
                                                                <div class="w-full h-full bg-zinc-300"></div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <p class="text-black text-[9px] font-bold uppercase truncate p-1 text-center" title="<?= htmlspecialchars($ol['titulo']) ?>"><?= htmlspecialchars($ol['titulo']) ?></p>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </details>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- BOTONES DE CONTROL -->
    <?php if ($obra1 || $obra2): ?>
    <div class="flex flex-wrap items-center justify-center gap-4 mt-8 fade-up delay-2">
        <?php if ($obra1 && $obra2): ?>
            <a href="<?= 'comparar.php?id1=' . $id2 . '&id2=' . $id1 ?>" class="bg-yellow-500 text-black font-comic text-lg uppercase tracking-widest px-5 py-2 border-4 border-black shadow-[5px_5px_0_0_black] hover:bg-yellow-400 active:translate-x-1 active:translate-y-1 active:shadow-none transition-all">
                ⇄ Intercambiar
            </a>
        <?php endif; ?>
        <a href="comparar.php" class="bg-black text-white font-comic text-lg uppercase tracking-widest px-5 py-2 border-4 border-rose-800 shadow-[5px_5px_0_0_#9f1239] hover:bg-neutral-900 active:translate-x-1 active:translate-y-1 active:shadow-none transition-all">
            ✕ Limpiar todo
        </a>
    </div>
    <?php endif; ?>

    <!-- ESPERANDO SELECCIÓN -->
    <?php if (!$obra1 || !$obra2): ?>
    <div class="mt-12 bg-neutral-900 border-4 border-dashed border-zinc-700 p-10 text-center fade-up delay-2">
        <p class="font-comic text-3xl text-zinc-500 uppercase tracking-widest">
            <?php if (!$obra1 && !$obra2): ?>
                Elige dos obras para comenzar
            <?php else: ?>
                Falta 1 obra...
            <?php endif; ?>
        </p>
        <p class="text-zinc-400 mt-2 text-sm">La comparativa aparecerá aquí cuando ambas obras estén seleccionadas.</p>
    </div>
    <?php else: ?>

    <!-- COMPARATIVA -->
    <?php
        // Calcular ganadores por métrica
        $g_anno = ganador((int)$obra1['anno_lanzamiento'], (int)$obra2['anno_lanzamiento']);
        $g_total = ganador($obra1['total_resenas'], $obra2['total_resenas']);
        $g_media = ganador($obra1['media_puntuacion'], $obra2['media_puntuacion']);
        $g_listas = ganador($obra1['num_listas'], $obra2['num_listas']);

        // Score "global" simplificado: cuenta de victorias en métricas numéricas
        $puntos1 = ($g_anno === 1) + ($g_total === 1) + ($g_media === 1) + ($g_listas === 1);
        $puntos2 = ($g_anno === 2) + ($g_total === 2) + ($g_media === 2) + ($g_listas === 2);

        $autores1 = !empty($obra1['autores']) ? implode(', ', array_map(fn($a) => trim($a['nombre'] . ' ' . $a['apellidos']), $obra1['autores'])) : '—';
        $autores2 = !empty($obra2['autores']) ? implode(', ', array_map(fn($a) => trim($a['nombre'] . ' ' . $a['apellidos']), $obra2['autores'])) : '—';

        // Filas de la comparativa: [etiqueta, valor1, valor2, ganador (0=ninguno), tipo]
        $filas = [
            ['Tipo', $tipos_label[$obra1['tipo']] ?? '—', $tipos_label[$obra2['tipo']] ?? '—', 0, 'text'],
            ['Género', $obra1['genero'] ?: '—', $obra2['genero'] ?: '—', 0, 'text'],
            ['Editorial', $obra1['nombre_editorial'] ?: '—', $obra2['nombre_editorial'] ?: '—', 0, 'text'],
            ['Año de lanzamiento', $obra1['anno_lanzamiento'] ?: '—', $obra2['anno_lanzamiento'] ?: '—', $g_anno, 'numero'],
            ['Autor(es)', $autores1, $autores2, 0, 'text'],
            ['Total reseñas', $obra1['total_resenas'], $obra2['total_resenas'], $g_total, 'numero'],
            ['Media de puntuación', $obra1['media_puntuacion'] > 0 ? number_format($obra1['media_puntuacion'], 1) . ' ★' : '—', $obra2['media_puntuacion'] > 0 ? number_format($obra2['media_puntuacion'], 1) . ' ★' : '—', $g_media, 'estrella'],
            ['En tus favoritos', $obra1['es_favorito'] ? '♥ Sí' : 'No', $obra2['es_favorito'] ? '♥ Sí' : 'No', 0, 'fav'],
            ['En tus listas', $obra1['num_listas'] . (!empty($obra1['nombres_listas']) ? ' (' . htmlspecialchars(implode(', ', $obra1['nombres_listas'])) . ')' : ''), $obra2['num_listas'] . (!empty($obra2['nombres_listas']) ? ' (' . htmlspecialchars(implode(', ', $obra2['nombres_listas'])) . ')' : ''), $g_listas, 'numero'],
        ];
    ?>

    <section class="mt-12 fade-up delay-2">
        <h2 class="font-comic text-3xl md:text-4xl text-white uppercase tracking-widest mb-6 text-center bg-rose-800 px-6 py-2 border-4 border-black shadow-[6px_6px_0_0_black] inline-block rotate-[-1deg]">
            ⚔ Cara a cara
        </h2>

        <!-- Marcador global -->
        <div class="grid grid-cols-3 gap-4 items-center mb-6">
            <div class="text-center bg-neutral-900 border-4 border-black p-4 shadow-[6px_6px_0_0_black] <?= $puntos1 > $puntos2 ? 'bg-rose-900' : '' ?>">
                <p class="font-comic text-xs uppercase tracking-widest text-zinc-400">Marcador</p>
                <p class="font-comic text-5xl md:text-6xl text-yellow-500 leading-none mt-1"><?= $puntos1 ?></p>
                <p class="font-comic text-sm uppercase truncate text-white mt-1" title="<?= htmlspecialchars($obra1['titulo']) ?>"><?= htmlspecialchars($obra1['titulo']) ?></p>
            </div>
            <div class="text-center">
                <span class="font-comic text-3xl text-yellow-500 uppercase tracking-widest drop-shadow-[3px_3px_0_black]">⚔</span>
                <p class="text-[10px] mt-3 text-zinc-500 font-bold uppercase tracking-widest">Basado en métricas numéricas</p>
            </div>
            <div class="text-center bg-neutral-900 border-4 border-black p-4 shadow-[6px_6px_0_0_black] <?= $puntos2 > $puntos1 ? 'bg-rose-900' : '' ?>">
                <p class="font-comic text-xs uppercase tracking-widest text-zinc-400">Marcador</p>
                <p class="font-comic text-5xl md:text-6xl text-yellow-500 leading-none mt-1"><?= $puntos2 ?></p>
                <p class="font-comic text-sm uppercase truncate text-white mt-1" title="<?= htmlspecialchars($obra2['titulo']) ?>"><?= htmlspecialchars($obra2['titulo']) ?></p>
            </div>
        </div>

        <!-- Tabla comparativa -->
        <div class="bg-neutral-900 border-4 border-black shadow-[8px_8px_0_0_black] overflow-hidden">
            <?php foreach ($filas as $i => $fila):
                [$label, $v1, $v2, $g, $tipo] = $fila;
                $bg = $i % 2 === 0 ? 'bg-neutral-900' : 'bg-black/40';
            ?>
                <div class="grid grid-cols-1 md:grid-cols-[1fr_180px_1fr] gap-0 border-b-2 border-black <?= $bg ?>">
                    <!-- Valor 1 -->
                    <div class="p-4 md:text-right border-b-2 md:border-b-0 md:border-r-2 border-black">
                        <span class="md:hidden font-comic text-[10px] uppercase tracking-widest text-zinc-500 block mb-1">Obra #1</span>
                        <span class="<?= $g === 1 ? 'winner-glow inline-block bg-yellow-500 text-black px-3 py-1 border-2 border-black font-bold' : 'text-white' ?> text-sm md:text-base font-bold">
                            <?= is_int($v1) || is_float($v1) ? $v1 : $v1 ?>
                        </span>
                    </div>
                    <!-- Etiqueta -->
                    <div class="p-3 md:p-4 bg-rose-800 text-center border-b-2 md:border-b-0 md:border-r-2 border-black flex items-center justify-center">
                        <span class="font-comic text-base md:text-lg uppercase tracking-widest text-white"><?= htmlspecialchars($label) ?></span>
                    </div>
                    <!-- Valor 2 -->
                    <div class="p-4 md:text-left">
                        <span class="md:hidden font-comic text-[10px] uppercase tracking-widest text-zinc-500 block mb-1">Obra #2</span>
                        <span class="<?= $g === 2 ? 'winner-glow inline-block bg-yellow-500 text-black px-3 py-1 border-2 border-black font-bold' : 'text-white' ?> text-sm md:text-base font-bold">
                            <?= is_int($v2) || is_float($v2) ? $v2 : $v2 ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Sinopsis -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <?php foreach ([1 => $obra1, 2 => $obra2] as $slot => $o): ?>
                <div class="bg-neutral-900 border-4 border-black shadow-[6px_6px_0_0_black] p-5">
                    <h3 class="font-comic text-xl uppercase tracking-widest text-yellow-500 border-b-2 border-zinc-700 pb-2 mb-3">
                        Sinopsis · <?= htmlspecialchars($o['titulo']) ?>
                    </h3>
                    <p class="text-zinc-300 text-sm leading-relaxed">
                        <?= !empty($o['descripcion']) ? nl2br(htmlspecialchars($o['descripcion'])) : '<span class="italic text-zinc-500">Sin descripción.</span>' ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</main>

<?php include "../assets/footer.php"?>

<!-- Dropdowns de resultados (fuera de cualquier stacking context para quedar siempre encima) -->
<div data-resultados="1" class="fixed bg-white border-4 border-black shadow-[6px_6px_0_0_black] hidden flex-col max-h-[320px] overflow-y-auto" style="z-index: 9999;"></div>
<div data-resultados="2" class="fixed bg-white border-4 border-black shadow-[6px_6px_0_0_black] hidden flex-col max-h-[320px] overflow-y-auto" style="z-index: 9999;"></div>

<script src="../Js/FuncionesComparador.js"></script>

</body>
</html>

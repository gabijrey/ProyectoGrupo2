<?php
session_start();

if (!isset($_SESSION['nombre'])) {
    header("Location: ../sesion/login.php");
    exit();
}

require __DIR__ . "/../sesion/conexion_pdo.php";
require __DIR__ . "/../sesion/premium_helper.php";

$nombre_usuario = $_SESSION['nombre'];

// Determinar plan actual del usuario
$plan_actual = null; // null | 'premium' | 'autor'
$rol_actual  = 0;
try {
    $stmt = $_conexion->prepare("SELECT rol FROM usuario WHERE nombre = :n");
    $stmt->execute(['n' => $nombre_usuario]);
    $rol_actual = (int)($stmt->fetchColumn() ?: 0);
    if ($rol_actual === 1) $plan_actual = 'premium';
    elseif ($rol_actual === 2) $plan_actual = 'autor';
} catch (PDOException $e) {}

// Suscripción activa (si existe) para mostrar la fecha
$suscripcion_actual = null;
try {
    $stmt = $_conexion->prepare("
        SELECT metodo_pago, fecha_inicio
        FROM suscripcion
        WHERE nombre_usuario = :n AND estado = 1 AND fecha_cancelacion IS NULL
        ORDER BY fecha_inicio DESC LIMIT 1
    ");
    $stmt->execute(['n' => $nombre_usuario]);
    $suscripcion_actual = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (PDOException $e) {}

$planes = [
    'premium' => [
        'nombre'      => 'Premium',
        'precio'      => 15.99,
        'color'       => 'rose-800',
        'color_text'  => 'text-rose-500',
        'descripcion' => 'Para los lectores que viven y respiran ilustración.',
        'ventajas'    => [
            'Insignia ★ Verificado en tu perfil',
            'Tus reseñas pesan más en la nota global',
            'Acceso al Escáner de códigos de barras',
            'Sin anuncios en la plataforma',
            'Prioridad en moderación y soporte',
        ],
    ],
    'autor' => [
        'nombre'      => 'Autor',
        'precio'      => 18.99,
        'color'       => 'yellow-500',
        'color_text'  => 'text-yellow-500',
        'descripcion' => 'Para creadores que quieren llegar a más lectores.',
        'ventajas'    => [
            'Insignia ★ Verificado de Autor',
            'Tus obras aparecen en el carrusel destacado',
            'Tus reseñas y comentarios pesan más',
            'Acceso al Escáner de códigos de barras',
            'Estadísticas avanzadas de tus obras (próximamente)',
            'Sin anuncios en la plataforma',
        ],
    ],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comiclook | Planes y precios</title>
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
        .plan-card { transition: transform 0.2s, box-shadow 0.2s; }
        .plan-card:hover { transform: translate(-4px, -4px); }
        .pago-tab.active {
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
    <div class="text-center mb-12 fade-up">
        <h1 class="font-comic text-5xl md:text-7xl text-white uppercase drop-shadow-[6px_6px_0_black] italic">
            HAZTE PARTE <span class="text-rose-800">DEL CLUB</span>
        </h1>
        <p class="mt-4 font-bold uppercase tracking-widest text-zinc-400 text-sm max-w-2xl mx-auto">
            Apoya el proyecto y desbloquea ventajas exclusivas. Pago anual, sin permanencia.
        </p>

        <?php if ($plan_actual): ?>
            <div class="mt-6 inline-flex items-center gap-3 bg-yellow-500 text-black border-4 border-black px-4 py-2 shadow-[5px_5px_0_0_black] font-bold uppercase tracking-widest text-sm">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.39 4.84L20 7.27l-3.86 3.77L17.27 17 12 14.27 6.73 17l1.13-5.96L4 7.27l5.61-.43L12 2z"/></svg>
                Plan actual: <?= htmlspecialchars($planes[$plan_actual]['nombre']) ?>
                <?php if ($suscripcion_actual): ?>
                    <span class="opacity-70 hidden md:inline">· desde <?= date('d/m/Y', strtotime($suscripcion_actual['fecha_inicio'])) ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tarjetas de planes -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8 items-start">

        <!-- Plan Gratis -->
        <div class="plan-card fade-up bg-neutral-900 border-4 border-black shadow-[8px_8px_0_0_black] p-6 flex flex-col gap-4 relative">
            <div class="absolute -top-3 -left-3 bg-zinc-700 text-white font-comic px-3 py-1 border-2 border-black text-xs uppercase tracking-widest shadow-[2px_2px_0_0_black]">
                Plan inicial
            </div>
            <h2 class="font-comic text-4xl uppercase text-white tracking-wider mt-2">Gratis</h2>
            <p class="text-zinc-400 text-sm font-bold">El plan con el que te has registrado.</p>
            <div class="flex items-baseline gap-1 my-2">
                <span class="font-comic text-5xl text-white">0€</span>
                <span class="text-zinc-500 text-sm font-bold">/ siempre</span>
            </div>
            <ul class="text-sm text-zinc-300 flex flex-col gap-2 leading-relaxed flex-1">
                <li class="flex items-start gap-2"><span class="text-green-500 font-bold flex-shrink-0">✓</span><span>Crear reseñas, listas y favoritos</span></li>
                <li class="flex items-start gap-2"><span class="text-green-500 font-bold flex-shrink-0">✓</span><span>Comparador de obras</span></li>
                <li class="flex items-start gap-2"><span class="text-zinc-600 font-bold flex-shrink-0">✗</span><span class="text-zinc-500">Verificado e insignia</span></li>
                <li class="flex items-start gap-2"><span class="text-zinc-600 font-bold flex-shrink-0">✗</span><span class="text-zinc-500">Escáner de códigos de barras</span></li>
                <li class="flex items-start gap-2"><span class="text-zinc-600 font-bold flex-shrink-0">✗</span><span class="text-zinc-500">Mayor peso en reseñas</span></li>
            </ul>
            <?php if ($rol_actual === 0): ?>
                <span class="block text-center bg-black border-2 border-zinc-700 text-zinc-400 font-comic uppercase tracking-widest py-2 text-sm">Tu plan actual</span>
            <?php else: ?>
                <span class="block text-center bg-black border-2 border-zinc-700 text-zinc-500 font-comic uppercase tracking-widest py-2 text-sm">Plan básico</span>
            <?php endif; ?>
        </div>

        <?php foreach (['premium', 'autor'] as $i => $key):
            $p = $planes[$key];
            $color = $p['color'];
            $delay = $i === 0 ? 'delay-1' : 'delay-2';
            $es_actual = ($plan_actual === $key);
        ?>
        <div class="plan-card fade-up <?= $delay ?> bg-neutral-900 border-4 border-<?= $color ?> shadow-[8px_8px_0_0_<?= $key === 'premium' ? '#9f1239' : '#eab308' ?>] p-6 flex flex-col gap-4 relative <?= $key === 'autor' ? 'md:scale-[1.02]' : '' ?>">
            <div class="absolute -top-3 -left-3 bg-<?= $color ?> text-<?= $key === 'autor' ? 'black' : 'white' ?> font-comic px-3 py-1 border-2 border-black text-xs uppercase tracking-widest shadow-[2px_2px_0_0_black]">
                <?= $key === 'autor' ? 'Recomendado para autores' : 'Más popular' ?>
            </div>
            <?php if ($es_actual): ?>
                <div class="absolute -top-3 right-3 bg-green-500 text-black font-comic px-3 py-1 border-2 border-black text-xs uppercase tracking-widest shadow-[2px_2px_0_0_black]">
                    ★ Activo
                </div>
            <?php endif; ?>

            <h2 class="font-comic text-4xl uppercase <?= $p['color_text'] ?> tracking-wider mt-2"><?= htmlspecialchars($p['nombre']) ?></h2>
            <p class="text-zinc-400 text-sm font-bold"><?= htmlspecialchars($p['descripcion']) ?></p>
            <div class="flex items-baseline gap-1 my-2">
                <span class="font-comic text-5xl text-white"><?= number_format($p['precio'], 2, ',', '.') ?>€</span>
                <span class="text-zinc-500 text-sm font-bold">/ año</span>
            </div>
            <ul class="text-sm text-zinc-200 flex flex-col gap-2 leading-relaxed flex-1">
                <?php foreach ($p['ventajas'] as $v): ?>
                    <li class="flex items-start gap-2">
                        <span class="text-green-500 font-bold flex-shrink-0">✓</span>
                        <span><?= htmlspecialchars($v) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($es_actual): ?>
                <span class="block text-center bg-green-500 text-black border-4 border-black font-comic uppercase tracking-widest py-2 text-sm shadow-[4px_4px_0_0_black]">
                    Suscripción activa
                </span>
            <?php else: ?>
                <button type="button"
                        data-plan="<?= $key ?>"
                        data-precio="<?= $p['precio'] ?>"
                        class="btn-elegir-plan w-full bg-<?= $color ?> <?= $key === 'autor' ? 'text-black' : 'text-white' ?> font-comic text-xl uppercase tracking-widest py-3 border-4 border-black shadow-[6px_6px_0_0_black] hover:scale-[1.02] active:translate-x-1 active:translate-y-1 active:shadow-none transition-all">
                    <?= $plan_actual ? 'Cambiar a ' . $p['nombre'] : 'Elegir ' . $p['nombre'] ?>
                </button>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Aviso -->
    <p class="text-center text-zinc-500 text-xs mt-10 max-w-2xl mx-auto leading-relaxed">
        Los pagos son simulados en este entorno de prueba (TFG). En producción se procesarán a través de pasarelas seguras. Puedes cancelar tu suscripción en cualquier momento desde "Mi cuenta".
    </p>

</main>

<!-- MODAL DE PAGO -->
<div id="modal-pago" class="fixed inset-0 bg-black/90 z-[200] hidden items-center justify-center p-4">
    <div class="bg-neutral-900 border-4 border-black shadow-[10px_10px_0_0_black] w-full max-w-2xl max-h-[90vh] overflow-y-auto relative">

        <button type="button" id="cerrar-modal-pago" class="absolute top-4 right-6 text-zinc-500 font-comic text-2xl hover:text-rose-500 transition-colors z-10">
            X
        </button>

        <div class="bg-rose-800 text-white px-6 py-4 border-b-4 border-black">
            <h2 class="font-comic text-3xl uppercase tracking-widest">Suscripción <span id="modal-plan-nombre">Premium</span></h2>
            <p class="text-xs uppercase tracking-widest text-rose-100 mt-1">
                <span id="modal-precio">15,99€</span> / año · Sin permanencia
            </p>
        </div>

        <div class="p-6 flex flex-col gap-5">

            <!-- Tabs métodos -->
            <div>
                <p class="font-comic text-xs uppercase tracking-widest text-zinc-400 mb-2">Método de pago</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <button type="button" data-metodo="paypal" class="pago-tab active font-comic uppercase tracking-widest text-xs py-3 px-2 bg-neutral-800 text-zinc-300 border-2 border-black hover:bg-rose-900 transition-colors">
                        PayPal
                    </button>
                    <button type="button" data-metodo="tarjeta" class="pago-tab font-comic uppercase tracking-widest text-xs py-3 px-2 bg-neutral-800 text-zinc-300 border-2 border-black hover:bg-rose-900 transition-colors">
                        Tarjeta
                    </button>
                    <button type="button" data-metodo="bizum" class="pago-tab font-comic uppercase tracking-widest text-xs py-3 px-2 bg-neutral-800 text-zinc-300 border-2 border-black hover:bg-rose-900 transition-colors">
                        Bizum
                    </button>
                    <button type="button" data-metodo="applepay" class="pago-tab font-comic uppercase tracking-widest text-xs py-3 px-2 bg-neutral-800 text-zinc-300 border-2 border-black hover:bg-rose-900 transition-colors">
                        Apple Pay
                    </button>
                </div>
            </div>

            <!-- Mensaje de error -->
            <div id="error-pago" class="hidden bg-rose-800 text-white border-4 border-black p-3 font-bold text-sm uppercase"></div>

            <!-- Panel PayPal -->
            <div data-panel="paypal" class="panel-pago flex flex-col gap-4">
                <div class="bg-black border-4 border-zinc-700 p-5 text-center">
                    <p class="font-comic text-yellow-500 text-2xl uppercase tracking-widest mb-2">PayPal</p>
                    <p class="text-zinc-300 text-sm">Serás redirigido a PayPal para autorizar el pago. Confirma con tu cuenta para activar la suscripción.</p>
                </div>
                <button type="button" data-confirmar="paypal" class="btn-confirmar bg-[#003087] text-white font-comic text-xl uppercase tracking-widest py-3 border-4 border-black shadow-[6px_6px_0_0_black] hover:bg-[#001d54] active:translate-x-1 active:translate-y-1 active:shadow-none transition-all">
                    Pagar con PayPal
                </button>
            </div>

            <!-- Panel Tarjeta -->
            <div data-panel="tarjeta" class="panel-pago hidden flex-col gap-3">
                <div>
                    <label class="font-comic text-xs uppercase tracking-widest text-zinc-400 mb-1 block">Titular</label>
                    <input type="text" id="card-titular" placeholder="Nombre y apellidos" class="w-full bg-black border-4 border-zinc-700 focus:border-yellow-500 focus:outline-none px-4 py-2 text-white transition-colors">
                </div>
                <div>
                    <label class="font-comic text-xs uppercase tracking-widest text-zinc-400 mb-1 block">Número de tarjeta</label>
                    <input type="text" id="card-numero" inputmode="numeric" maxlength="23" placeholder="0000 0000 0000 0000" class="w-full bg-black border-4 border-zinc-700 focus:border-yellow-500 focus:outline-none px-4 py-2 text-white font-mono tracking-wider transition-colors">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-comic text-xs uppercase tracking-widest text-zinc-400 mb-1 block">Caducidad</label>
                        <input type="text" id="card-cad" maxlength="5" placeholder="MM/AA" class="w-full bg-black border-4 border-zinc-700 focus:border-yellow-500 focus:outline-none px-4 py-2 text-white font-mono tracking-wider transition-colors">
                    </div>
                    <div>
                        <label class="font-comic text-xs uppercase tracking-widest text-zinc-400 mb-1 block">CVV</label>
                        <input type="password" id="card-cvv" maxlength="4" placeholder="•••" class="w-full bg-black border-4 border-zinc-700 focus:border-yellow-500 focus:outline-none px-4 py-2 text-white font-mono tracking-wider transition-colors">
                    </div>
                </div>
                <button type="button" data-confirmar="tarjeta" class="btn-confirmar mt-2 bg-rose-800 text-white font-comic text-xl uppercase tracking-widest py-3 border-4 border-black shadow-[6px_6px_0_0_black] hover:bg-rose-900 active:translate-x-1 active:translate-y-1 active:shadow-none transition-all">
                    Pagar 15,99€
                </button>
            </div>

            <!-- Panel Bizum -->
            <div data-panel="bizum" class="panel-pago hidden flex-col gap-3">
                <div class="bg-black border-4 border-zinc-700 p-5 text-center">
                    <p class="font-comic text-blue-400 text-2xl uppercase tracking-widest mb-2">Bizum</p>
                    <p class="text-zinc-300 text-sm">Introduce tu número de móvil asociado a Bizum. Recibirás una notificación en tu app de banca para confirmar el cargo.</p>
                </div>
                <div>
                    <label class="font-comic text-xs uppercase tracking-widest text-zinc-400 mb-1 block">Número de móvil</label>
                    <input type="tel" id="bizum-telefono" inputmode="numeric" placeholder="+34 600 000 000" class="w-full bg-black border-4 border-zinc-700 focus:border-blue-500 focus:outline-none px-4 py-2 text-white font-mono tracking-wider transition-colors">
                </div>
                <button type="button" data-confirmar="bizum" class="btn-confirmar mt-2 bg-blue-500 text-white font-comic text-xl uppercase tracking-widest py-3 border-4 border-black shadow-[6px_6px_0_0_black] hover:bg-blue-600 active:translate-x-1 active:translate-y-1 active:shadow-none transition-all">
                    Solicitar pago Bizum
                </button>
            </div>

            <!-- Panel Apple Pay -->
            <div data-panel="applepay" class="panel-pago hidden flex-col gap-3">
                <div class="bg-black border-4 border-zinc-700 p-6 text-center">
                    <p class="font-comic text-white text-2xl uppercase tracking-widest mb-2"> Pay</p>
                    <p class="text-zinc-300 text-sm">Confirma el pago con Face ID, Touch ID o tu código en un dispositivo compatible. Solo aparece esta opción si tu navegador o dispositivo lo soporta.</p>
                </div>
                <button type="button" data-confirmar="applepay" class="btn-confirmar bg-black text-white font-comic text-xl uppercase tracking-widest py-3 border-4 border-white shadow-[6px_6px_0_0_white] hover:bg-neutral-900 active:translate-x-1 active:translate-y-1 active:shadow-none transition-all">
                     Pagar con Apple Pay
                </button>
            </div>

            <p class="text-[10px] text-zinc-500 text-center leading-relaxed">
                Pago seguro. Al continuar aceptas los términos y la política de privacidad de ComicLook.
            </p>
        </div>
    </div>
</div>

<!-- SIMULACIÓN PAYPAL -->
<div id="modal-paypal" class="fixed inset-0 z-[220] hidden flex-col bg-[#f5f7fa] font-sans">
    <!-- Barra de navegación falsa del navegador -->
    <div class="bg-[#e5e7eb] px-4 py-2 flex items-center gap-3 border-b border-[#cbd5e1]">
        <div class="flex gap-1.5">
            <span class="w-3 h-3 rounded-full bg-[#ff5f57]"></span>
            <span class="w-3 h-3 rounded-full bg-[#febc2e]"></span>
            <span class="w-3 h-3 rounded-full bg-[#28c840]"></span>
        </div>
        <div class="flex-1 bg-white rounded-md px-3 py-1 text-xs text-[#475569] flex items-center gap-2 border border-[#cbd5e1]">
            <svg class="w-3.5 h-3.5 text-green-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1a4 4 0 0 0-4 4v3H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10a2 2 0 0 0-2-2h-2V5a4 4 0 0 0-4-4zm-2 7V5a2 2 0 1 1 4 0v3h-4z"/></svg>
            <span>paypal.com/checkoutnow</span>
        </div>
        <button type="button" id="paypal-cerrar-tab" class="text-xs text-[#475569] hover:text-black font-bold">✕</button>
    </div>

    <!-- Contenido -->
    <div class="flex-1 overflow-y-auto">
        <!-- Header PayPal -->
        <div class="bg-white border-b border-[#cbd5e1] px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="font-bold text-2xl tracking-tight" style="font-family: 'Helvetica Neue', Arial, sans-serif;">
                    <span style="color:#003087;">Pay</span><span style="color:#009cde;">Pal</span>
                </span>
            </div>
            <div class="text-right text-xs text-[#475569]">
                <p class="font-semibold">Pago seguro</p>
                <p>SSL cifrado · 256 bits</p>
            </div>
        </div>

        <div class="max-w-md mx-auto px-6 py-8">
            <!-- Resumen del pago -->
            <div class="bg-white rounded-lg shadow-sm border border-[#e2e8f0] p-5 mb-6">
                <p class="text-xs text-[#64748b] uppercase tracking-wider font-semibold">Pagas a</p>
                <p class="font-bold text-[#1e293b] text-lg">COMICLOOK SL</p>
                <hr class="my-3 border-[#e2e8f0]">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-[#475569]">Suscripción anual <span id="paypal-plan">Premium</span></span>
                    <span class="font-bold text-[#1e293b]" id="paypal-precio">15,99 €</span>
                </div>
                <div class="flex justify-between items-center mt-2 pt-2 border-t border-[#e2e8f0]">
                    <span class="font-bold text-[#1e293b]">Total</span>
                    <span class="font-bold text-[#1e293b] text-lg" id="paypal-total">15,99 €</span>
                </div>
            </div>

            <!-- Paso 1: Login -->
            <div id="paypal-paso-login" class="bg-white rounded-lg shadow-sm border border-[#e2e8f0] p-6">
                <h2 class="font-bold text-xl text-[#1e293b] mb-1">Inicia sesión</h2>
                <p class="text-sm text-[#64748b] mb-5">Inicia sesión para completar el pago de forma segura.</p>
                <div class="flex flex-col gap-3">
                    <input type="email" id="paypal-email" value="usuario@comiclook.com" class="w-full border border-[#cbd5e1] rounded-md px-3 py-3 text-sm text-[#1e293b] focus:border-[#0070ba] focus:outline-none focus:ring-2 focus:ring-[#0070ba]/20" placeholder="Email o número de móvil">
                    <input type="password" id="paypal-pass" value="••••••••" class="w-full border border-[#cbd5e1] rounded-md px-3 py-3 text-sm text-[#1e293b] focus:border-[#0070ba] focus:outline-none focus:ring-2 focus:ring-[#0070ba]/20" placeholder="Contraseña">
                </div>
                <button type="button" id="paypal-btn-login" class="w-full mt-5 bg-[#0070ba] text-white font-bold py-3 rounded-full hover:bg-[#005ea6] transition-colors text-sm">
                    Iniciar sesión
                </button>
                <p class="text-center text-[#0070ba] text-xs font-semibold mt-3 cursor-pointer hover:underline">¿Has olvidado tu correo o contraseña?</p>
                <hr class="my-5 border-[#e2e8f0]">
                <button type="button" class="w-full bg-white text-[#0070ba] font-bold py-3 rounded-full border-2 border-[#0070ba] text-sm hover:bg-[#f1f5f9] transition-colors">
                    Crear cuenta
                </button>
            </div>

            <!-- Paso 2: Confirmar -->
            <div id="paypal-paso-confirmar" class="hidden bg-white rounded-lg shadow-sm border border-[#e2e8f0] p-6">
                <div class="flex items-center gap-3 mb-5 pb-4 border-b border-[#e2e8f0]">
                    <div class="w-10 h-10 rounded-full bg-[#0070ba] text-white flex items-center justify-center font-bold">U</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-[#1e293b] truncate" id="paypal-email-confirmado">usuario@comiclook.com</p>
                        <p class="text-xs text-[#64748b]">Cuenta verificada</p>
                    </div>
                </div>

                <p class="text-xs text-[#64748b] uppercase tracking-wider font-semibold mb-2">Método de pago</p>
                <div class="border border-[#e2e8f0] rounded-md p-3 flex items-center gap-3 mb-5">
                    <div class="w-10 h-7 bg-gradient-to-br from-[#fbbf24] to-[#f59e0b] rounded text-white text-[10px] font-bold flex items-center justify-center">VISA</div>
                    <span class="text-sm text-[#1e293b] font-medium">Visa terminada en ••42</span>
                </div>

                <button type="button" id="paypal-btn-pagar" class="w-full bg-[#FFC439] text-[#1e293b] font-bold py-3 rounded-full hover:bg-[#f0b429] transition-colors text-sm shadow-sm">
                    Pagar ahora
                </button>
                <button type="button" id="paypal-btn-cancelar" class="w-full mt-3 text-[#0070ba] font-semibold py-2 text-sm hover:underline">
                    Cancelar y volver
                </button>
            </div>

            <!-- Paso 3: Procesando -->
            <div id="paypal-paso-procesando" class="hidden bg-white rounded-lg shadow-sm border border-[#e2e8f0] p-10 text-center">
                <div class="inline-block w-12 h-12 border-4 border-[#0070ba] border-t-transparent rounded-full animate-spin mb-4"></div>
                <p class="text-[#1e293b] font-semibold">Procesando tu pago...</p>
                <p class="text-xs text-[#64748b] mt-1">No cierres esta ventana.</p>
            </div>

            <p class="text-[10px] text-[#94a3b8] text-center mt-6 leading-relaxed">
                Política · Términos · Comentarios · Ayuda · Contáctanos<br>
                © 1999-<?= date('Y') ?> PayPal. Todos los derechos reservados.
            </p>
        </div>
    </div>
</div>

<!-- SIMULACIÓN APPLE PAY -->
<div id="modal-applepay" class="fixed inset-0 z-[220] hidden items-end md:items-center justify-center bg-black/70 backdrop-blur-md p-0 md:p-6">
    <div class="bg-[#1c1c1e] w-full md:max-w-sm text-white rounded-t-3xl md:rounded-3xl overflow-hidden shadow-2xl" style="font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Helvetica Neue', sans-serif;">

        <!-- Indicador superior -->
        <div class="flex justify-center pt-2 pb-1">
            <div class="w-10 h-1 bg-zinc-600 rounded-full"></div>
        </div>

        <!-- Cabecera -->
        <div class="px-5 py-3 flex items-center justify-between border-b border-zinc-800">
            <span class="text-xs text-zinc-400">Cancelar</span>
            <span class="text-2xl font-semibold tracking-tight"> Pay</span>
            <span class="text-xs text-zinc-400 opacity-0">Cancelar</span>
        </div>

        <!-- Tarjeta -->
        <div class="p-5 border-b border-zinc-800">
            <div class="bg-gradient-to-br from-[#2d2d2f] to-[#1c1c1e] rounded-2xl p-4 border border-zinc-700 shadow-inner">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex flex-col gap-1">
                        <span class="text-[10px] text-zinc-500 uppercase tracking-widest">Banco Demo</span>
                        <span class="text-xs text-zinc-400">Débito</span>
                    </div>
                    <svg class="w-8 h-8 text-zinc-400" viewBox="0 0 24 24" fill="currentColor"><path d="M2 7h20v3H2zm0 5h20v5a2 2 0 01-2 2H4a2 2 0 01-2-2v-5z"/><path d="M4 5a2 2 0 00-2 2h20a2 2 0 00-2-2H4z"/></svg>
                </div>
                <p class="font-mono tracking-widest text-base">•••• 4242</p>
            </div>
        </div>

        <!-- Resumen del pago -->
        <div class="px-5 py-4 border-b border-zinc-800 space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-zinc-400">Pagar a</span>
                <span class="font-semibold">ComicLook</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-zinc-400">Suscripción</span>
                <span id="applepay-plan" class="font-semibold">Premium · anual</span>
            </div>
            <div class="flex justify-between text-base pt-2 mt-2 border-t border-zinc-800">
                <span class="font-semibold">Total</span>
                <span id="applepay-precio" class="font-semibold">15,99 €</span>
            </div>
        </div>

        <!-- Face ID -->
        <div class="px-5 pt-6 pb-8 text-center">
            <div id="applepay-faceid-wrapper" class="relative inline-flex flex-col items-center">
                <div id="applepay-faceid-icon" class="w-16 h-16 border-2 border-white rounded-xl flex items-center justify-center transition-all duration-300">
                    <svg class="w-10 h-10 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 7V5a2 2 0 012-2h2M16 3h2a2 2 0 012 2v2M20 17v2a2 2 0 01-2 2h-2M8 21H6a2 2 0 01-2-2v-2"/>
                        <circle cx="9" cy="10" r="0.5" fill="currentColor"/>
                        <circle cx="15" cy="10" r="0.5" fill="currentColor"/>
                        <path d="M12 11v3M10 16h4"/>
                    </svg>
                </div>
                <p id="applepay-faceid-texto" class="mt-3 text-sm text-zinc-300">Toca para pagar con Face ID</p>
            </div>

            <button type="button" id="applepay-btn-confirmar" class="mt-6 w-full bg-white text-black font-semibold py-3.5 rounded-xl text-sm hover:bg-zinc-100 transition-colors flex items-center justify-center gap-2">
                <span></span>
                <span>Pagar con Apple Pay</span>
            </button>
            <button type="button" id="applepay-btn-cancelar" class="mt-3 text-zinc-400 text-sm font-medium hover:text-white transition-colors">
                Cancelar
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes faceid-scan {
        0%, 100% { box-shadow: 0 0 0 0 rgba(52, 199, 89, 0); border-color: #fff; }
        50%      { box-shadow: 0 0 0 8px rgba(52, 199, 89, 0.2); border-color: #34c759; }
    }
    .faceid-scanning {
        animation: faceid-scan 1s ease-in-out infinite;
    }
    .faceid-success {
        border-color: #34c759 !important;
        background: #34c75920;
    }
</style>

<!-- MODAL DE ÉXITO -->
<div id="modal-exito" class="fixed inset-0 bg-black/95 z-[230] hidden items-center justify-center p-4">
    <div class="bg-neutral-900 border-4 border-yellow-500 shadow-[10px_10px_0_0_#eab308] w-full max-w-md p-8 text-center fade-up">
        <div class="font-comic text-7xl text-yellow-500 mb-4">★</div>
        <h2 class="font-comic text-3xl uppercase text-white mb-2">¡Bienvenido al club!</h2>
        <p id="exito-mensaje" class="text-zinc-300 text-sm mb-6">Tu suscripción está activa.</p>
        <a id="exito-cta" href="../sesion/user.php" class="inline-block bg-rose-800 text-white font-comic text-lg uppercase tracking-widest px-6 py-3 border-4 border-black shadow-[5px_5px_0_0_black] hover:bg-rose-900 active:translate-x-1 active:translate-y-1 active:shadow-none transition-all">
            Ir a mi cuenta →
        </a>
    </div>
</div>

<?php include "../assets/footer.php"?>

<script src="../Js/FuncionesPricing.js"></script>

</body>
</html>

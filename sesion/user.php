<?php
session_start();
require "conexion_pdo.php"; 
if (!isset($_SESSION['nombre'])) {
    header("Location: sesion/login.php");
    exit;
}
$usuario = $_SESSION['nombre'];
$errores = [];
$exito = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmtActual = $_conexion->prepare("SELECT * FROM usuario WHERE nombre = ?");
    $stmtActual->execute([$usuario]);
    $userActual = $stmtActual->fetch(PDO::FETCH_ASSOC);
    if (!$userActual) {
        $errores[] = "Usuario no encontrado.";
    } else {
        $bio = trim($_POST['bio'] ?? '');
        if (strlen($bio) > 1000) {
            $errores[] = "Has superado el maximo de caracteres de la biografia!";
        }
        $pass        = $_POST['pass'] ?? '';
        $passConfirm = $_POST['pass_confirm'] ?? '';
        $nuevaHash   = null;
        if ($pass !== '') {
            if (strlen($pass) < 6) {
                $errores[] = "La contraseña debe tener al menos 6 caracteres.";
            } elseif ($pass !== $passConfirm) {
                $errores[] = "Las contraseñas no coinciden.";
            } else {
                $nuevaHash = password_hash($pass, PASSWORD_DEFAULT);
            }
        }
        $rutaFoto = $userActual['img_perfil'];
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array($ext, $permitidas)) {
                $errores[] = "Formato de imagen no permitido.";
            } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
                $errores[] = "La imagen no puede superar 2MB.";
            } else {
                $dirFotos = '../imagen/perfiles/';
                if (!is_dir($dirFotos)) {
                    mkdir($dirFotos, 0755, true);
                }
                $nombreFoto = 'perfil_' . $userActual['nombre'] . '_' . time() . '.' . $ext;
                $rutaDestino = $dirFotos . $nombreFoto;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
                    if (!empty($userActual['img_perfil']) && file_exists('../' . $userActual['img_perfil'])) {
                        unlink('../' . $userActual['img_perfil']);
                    }
                    $rutaFoto = 'imagen/perfiles/' . $nombreFoto;
                } else {
                    $errores[] = "Error al subir la imagen.";
                }
            }
        }
        if (empty($errores)) {
            if ($nuevaHash) {
                $sql = "UPDATE usuario SET bio = ?, contrasena = ?, img_perfil = ? WHERE nombre = ?";
                $params = [$bio, $nuevaHash, $rutaFoto, $userActual['nombre']];
            } else {
                $sql = "UPDATE usuario SET bio = ?, img_perfil = ? WHERE nombre = ?";
                $params = [$bio, $rutaFoto, $userActual['nombre']];
            }
            $stmtUpdate = $_conexion->prepare($sql);
            if ($stmtUpdate->execute($params)) {
                $exito = "¡Perfil actualizado correctamente!";
            } else {
                $errores[] = "Error al guardar los cambios en la base de datos.";
            }
        }
    }
}
$stmt = $_conexion->prepare("SELECT * FROM usuario WHERE nombre = ?");
$stmt->execute([$usuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: sesion/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control | ComicLook</title>
    <link rel="icon" href="../comiclook_icon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../Js/ValidacionObra.js"></script>
    <script src="../Js/FuncionesPerfil.js" defer></script>
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
<body class="text-white min-h-screen">

    <div class="flex flex-col md:flex-row min-h-screen">
        <?php include '../assets/sidebar_dashboard.php'; ?>

        <main class="flex-1 p-6 md:p-12 overflow-y-auto">
            <h1 class="font-comic text-5xl md:text-6xl uppercase italic drop-shadow-[4px_4px_0_black] mb-10 text-white">Ajustes de Cuenta</h1>

            <?php if ($exito): ?>
                <div class="bg-green-500 text-black font-bold uppercase p-4 border-4 border-black shadow-[6px_6px_0_0_black] mb-8">
                    <?= htmlspecialchars($exito) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errores)): ?>
                <div class="bg-rose-800 text-white font-bold uppercase p-4 border-4 border-black shadow-[6px_6px_0_0_black] mb-8">
                    <?php foreach ($errores as $err): ?>
                        <p class="mb-1">⚠ <?= htmlspecialchars($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                
                <div class="lg:col-span-2 bg-yellow-500 text-black border-8 border-black p-8 shadow-[12px_12px_0_0_black]">
                    <form action="user.php" method="POST" enctype="multipart/form-data" class="space-y-8">
                        
                        <div class="flex flex-col md:flex-row items-center gap-8 border-b-4 border-black pb-8">
                            <div class="relative w-40 h-40 bg-neutral-800 border-4 border-black shadow-[6px_6px_0_0_black] overflow-hidden group cursor-pointer" 
                                onclick="document.getElementById('foto-input').click()">
                                <img id="img-perfil" 
                                    src="../<?= htmlspecialchars($user['img_perfil'] ?: 'imagen/perfiles/perfil__1774513912.png') ?>" 
                                    alt="Foto de perfil" 
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                                <div class="hidden flex-col items-center justify-center w-full h-full bg-neutral-800 text-neutral-600">
                                    <svg class="w-16 h-16 drop-shadow-[2px_2px_0_black]" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-comic text-xs uppercase mt-1">¿Quién eres?</span>
                                </div>
                                <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-10 transition-opacity"></div>
                            </div>
                            <div class="flex-1 space-y-3">
                                <label class="font-comic text-2xl uppercase italic">Cambiar foto de perfil</label>
                                <input id="foto-input" type="file" name="foto" accept="image/*" class="hidden">
                                <button type="button" onclick="document.getElementById('foto-input').click()" 
                                        class="w-full bg-black text-white font-bold uppercase py-3 border-4 border-black hover:bg-neutral-800 transition-all active:translate-y-1">
                                    Seleccionar archivo
                                </button>
                                <p class="text-[10px] font-black uppercase tracking-widest">JPG, PNG, WEBP · MÁX. 2MB</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="bio-textarea" class="font-comic text-2xl uppercase italic">Biografía</label>
                            <textarea name="bio" id="bio-textarea" rows="4" maxlength="1200" 
                                      class="w-full border-4 border-black p-4 font-bold focus:outline-none focus:bg-white transition-colors text-black placeholder-neutral-600"
                                      placeholder="CUÉNTA TU HISTORIA..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t-4 border-black">
                            <div class="space-y-2">
                                <label class="font-bold uppercase text-xs">Nueva Contraseña</label>
                                <input type="password" name="pass" placeholder="Escribe tu nueva contraseña" 
                                       class="w-full border-4 border-black p-3 font-bold focus:outline-none focus:bg-white text-black placeholder-neutral-600">
                            </div>
                            <div class="space-y-2">
                                <label class="font-bold uppercase text-xs">Confirmar Contraseña</label>
                                <input type="password" name="pass_confirm" 
                                       class="w-full border-4 border-black p-3 font-bold focus:outline-none focus:bg-white text-black">
                            </div>
                        </div>

                        <button type="submit" 
                                class="w-full bg-rose-800 text-white font-comic text-3xl py-4 border-4 border-black shadow-[8px_8px_0_0_black] hover:bg-rose-900 transition-all hover:scale-105 active:translate-y-1 active:shadow-none uppercase italic">
                            ¡GUARDAR CAMBIOS!
                        </button>
                    </form>
                </div>

                <div class="space-y-8">
                    <h2 class="font-comic text-4xl uppercase italic drop-shadow-[2px_2px_0_black]">Tus reseñas</h2>
                    <div class="space-y-6">
                        <?php
                        $sql_res = "SELECT r.*, o.titulo, o.portada 
                                    FROM resena r 
                                    JOIN obra o ON r.id_obra = o.id 
                                    WHERE r.nombre_usuario = ? 
                                    ORDER BY r.fecha_public DESC LIMIT 3";
                        $stmt_res = $_conexion->prepare($sql_res);
                        $stmt_res->execute([$user['nombre']]);
                        $resenas = $stmt_res->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($resenas)){ ?>
                            <div class="bg-black text-white p-6 border-4 border-black shadow-[6px_6px_0_0_white] italic uppercase font-bold text-xs text-center">
                                AÚN NO HAS ESCRITO NADA... ¡VE AL CATÁLOGO!
                            </div>
                        <?php }else{
                            foreach ($resenas as $resena){ ?>
                                <div class="flex gap-4 p-4 bg-white text-black border-4 border-black shadow-[8px_8px_0_0_black] hover:rotate-1 transition-transform">
                                    <img src="../<?= htmlspecialchars($resena['portada']) ?>" 
                                         class="w-16 h-24 object-cover border-2 border-black" 
                                         alt="<?= htmlspecialchars($resena['titulo']) ?>">
                                    <div class="min-w-0">
                                        <h4 class="font-black text-sm uppercase truncate mb-1"><?= htmlspecialchars($resena['titulo']) ?></h4>
                                        <div class="bg-yellow-500 text-black font-bold px-2 inline-block border-2 border-black text-[10px] italic mb-1">
                                            ★ <?= htmlspecialchars($resena['puntuacion']) ?>/5
                                        </div>
                                        <p class="text-[10px] font-bold text-neutral-600 uppercase leading-tight line-clamp-3 italic">
                                            "<?= htmlspecialchars($resena['comentario']) ?>"
                                        </p>
                                    </div>
                                </div>
                            <?php };
                        }; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
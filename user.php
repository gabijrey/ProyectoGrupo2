<?php
session_start();
require "sesion/conexion_pdo.php"; 
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
                $dirFotos = 'imagen/perfiles/';
                if (!is_dir($dirFotos)) {
                    mkdir($dirFotos, 0755, true);
                }
                $nombreFoto = 'perfil_' . $userActual['nombre'] . '_' . time() . '.' . $ext;
                $rutaDestino = $dirFotos . $nombreFoto;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
                    if (!empty($userActual['img_perfil']) && file_exists($userActual['img_perfil'])) {
                        unlink($userActual['img_perfil']);
                    }
                    $rutaFoto = $rutaDestino;
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
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="Js/FuncionesPerfil.js" defer></script>
</head>
<body class="bg-zinc-950 text-white font-sans">

    <div class="flex min-h-screen">
        <aside class="w-64 bg-zinc-900 border-r border-zinc-800 flex flex-col">
            <div class="p-6">
                <img src="logos/logoLight.webp" class="h-8 w-auto" alt="ComicLook">
            </div>
            
            <nav class="flex-1 px-4 space-y-2">
                <a href="user.php" class="flex items-center gap-3 bg-rose-800 text-white px-4 py-3 rounded-xl font-bold">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Mi Perfil
                </a>
                <a href="" class="flex items-center gap-3 text-zinc-400 hover:text-white hover:bg-zinc-800 px-4 py-3 rounded-xl transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/><path d="m9 12 2 2 4-4"/></svg>
                    Mis Reseñas
                </a>
                <a href="index.php" class="flex items-center gap-3 text-zinc-400 hover:text-white hover:bg-zinc-800 px-4 py-3 rounded-xl transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Volver a Inicio
                </a>
            </nav>

            <div class="p-4 border-t border-zinc-800">
                <a href="sesion/logout.php" class="flex items-center gap-3 text-rose-500 hover:bg-rose-500/10 px-4 py-3 rounded-xl transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    Cerrar Sesión
                </a>
            </div>
        </aside>

        <main class="flex-1 p-8">
            <h1 class="text-3xl font-bold mb-8">Ajustes de Cuenta</h1>
            <!-- ─── MENSAJES DE FEEDBACK ─── -->
            <?php if ($exito): ?>
                <div class="mb-6 flex items-center gap-3 bg-green-900/40 border border-green-700 text-green-400 px-5 py-4 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <?= htmlspecialchars($exito) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errores)): ?>
                <div class="mb-6 bg-rose-900/40 border border-rose-700 text-rose-400 px-5 py-4 rounded-xl space-y-1">
                    <?php foreach ($errores as $err): ?>
                        <p class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <?= htmlspecialchars($err) ?>
                        </p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <form action="user.php" method="POST" enctype="multipart/form-data" class="bg-zinc-900 p-8 rounded-2xl border border-zinc-800 space-y-6">
                        <div class="flex items-center gap-6 pb-6 border-b border-zinc-800">
                            <div class="w-24 h-24 bg-zinc-800 rounded-full overflow-hidden border-2 border-rose-800 cursor-pointer hover:opacity-80 transition-opacity">
                                <img id="img-perfil" src="<?= htmlspecialchars($user['img_perfil'] ?: 'imagen/perfiles/perfil__1774513912.png') ?>" class="w-full h-full object-cover" alt="Foto de perfil">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2 uppercase tracking-wide opacity-70">Cambiar foto de perfil</label>
                                <input id="foto-input" type="file" name="foto" accept="image/*" class="hidden">
                                <button type="button" class="text-xs bg-rose-800/20 text-rose-400 border border-rose-800/30 px-4 py-2 rounded-full hover:bg-rose-800/30 transition-all font-bold">
                                    Seleccionar archivo
                                </button>
                                <p class="text-xs text-zinc-600 mt-1">JPG, PNG, WEBP · Máx. 2MB</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Biografía</label>
                            <textarea name="bio" id="bio-textarea" rows="4" maxlength="1200" class="w-full bg-zinc-950 border border-zinc-800 rounded-lg p-3 focus:border-rose-500 outline-none transition-colors resize-none"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Nueva Contraseña</label>
                                <input type="password" name="pass" placeholder="Dejar en blanco para mantener" class="w-full bg-zinc-950 border border-zinc-800 rounded-lg p-3 focus:border-rose-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Confirmar Contraseña</label>
                                <input type="password" name="pass_confirm" class="w-full bg-zinc-950 border border-zinc-800 rounded-lg p-3 focus:border-rose-500 outline-none">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-rose-800 hover:bg-rose-700 py-3 rounded-lg font-bold transition-colors">
                            Guardar Cambios
                        </button>
                    </form>
                </div>
                <div class="bg-zinc-900 p-6 rounded-2xl border border-zinc-800 h-fit">
                    <h2 class="text-xl font-bold mb-6">Tus últimas reseñas</h2>
                    <div class="space-y-4">
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
                            <p class="text-zinc-500 text-sm">Aún no has escrito ninguna reseña.</p>
                        <?php }else{
                            foreach ($resenas as $resena){ ?>
                                <div class="flex gap-4 p-3 bg-zinc-950 rounded-xl border border-zinc-800">
                                    <img src="img/portadas/<?= htmlspecialchars($resena['portada']) ?>" class="w-12 h-16 object-cover rounded" alt="<?= htmlspecialchars($resena['titulo']) ?>">
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-bold truncate"><?= htmlspecialchars($resena['titulo']) ?></h4>
                                        <div class="text-rose-500 text-xs">★ <?= htmlspecialchars($resena['puntuacion']) ?>/5</div>
                                        <p class="text-[10px] text-zinc-500 mt-1 line-clamp-2"><?= htmlspecialchars($resena['comentario']) ?></p>
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

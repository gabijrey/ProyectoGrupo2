<?php
session_start();

if (!isset($_SESSION["nombre"])) {
    header("Location: login.php");
    exit();
}

$nombre = $_SESSION["nombre"];
require "conexion_pdo.php";

$mensaje_error = "";
$consulta = "";

//Crear nueva lista
if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["crear_lista"])) {
    $titulo = trim($_POST["titulo"]);
    $descripcion = trim($_POST["descripcion"]);
    $privacidad = (isset($_POST["privacidad"]) && $_POST["privacidad"] === "1") ? 1 : 0;

    if(!empty($titulo)) {
        try {
            $consulta = "INSERT INTO lista (titulo, descripcion, privacidad, nombre_usuario, fecha_creacion) VALUES (:titulo, :descripcion, :privacidad, :usuario, NOW())";
            $stmt = $_conexion->prepare($consulta);
            $stmt->execute([
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'privacidad' => $privacidad,
                'usuario' => $nombre
            ]);
            //Recargar pagina para no reenviar formulario
            header ("Location: listas.php?status=creada");
            exit();

        }
        catch(PDOException $e) {
            $mensaje_error = "Error al crear la lista en la BBDD: ".$e->getMessage();
        }
    }
    else {
        $mensaje_error = "El titulo no puede estar vacío.";
    }
}

//Mostrar las listas que ya tiene creadas el usuario
try {
    $consulta = "SELECT * FROM lista WHERE nombre_usuario = :usuario ORDER BY fecha_creacion DESC";
    $stmtListas = $_conexion->prepare($consulta);
    $stmtListas->execute(['usuario' => $nombre]);
    $mis_listas = $stmtListas->fetchAll(PDO::FETCH_ASSOC);
}
catch(PDOException $e) {
    $mis_listas = [];
}


//Eliminar lista
if(isset($_GET["eliminar_id"])) {
    $id_borrar = (int)$_GET["eliminar_id"];

    //Verificar que el usuario este logueado
    $stmtCheck = $_conexion->prepare("SELECT nombre_usuario FROM lista WHERE id = :id");
    $stmtCheck->execute(['id' => $id_borrar]);
    $propietario = $stmtCheck->fetchColumn();

    if($propietario === $nombre) {
        //Borrar relacion en tabla intermedia
        $stmtRel = $_conexion->prepare("DELETE FROM lista_obra WHERE id_lista = :id");
        $stmtRel->execute(['id' => $id_borrar]);

        //Borrar en tabla lista
        $stmtDel = $_conexion->prepare("DELETE FROM lista WHERE id = :id");
        $stmtDel->execute(['id' => $id_borrar]);

        header("Location: listas.php?status=borrada");
        exit();
    }
};

//Editar lista
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["guardar_cambios_lista"])) {
    $id_edit = (int)$_POST["id_lista_edit"];
    $n_titulo = trim($_POST["nuevo_titulo"]);
    $n_desc = trim($_POST["nueva_descripcion"]);
    $n_priv = (int)$_POST["nueva_privacidad"];
    // Verificamos que la lista pertenezca al usuario antes de hacer el UPDATE
    $stmtCheckEdit = $_conexion->prepare("SELECT nombre_usuario FROM lista WHERE id = :id");
    $stmtCheckEdit->execute(['id' => $id_edit]);
    
    if ($stmtCheckEdit->fetchColumn() === $nombre) {
        $sql_upd = "UPDATE lista SET titulo = :t, descripcion = :d, privacidad = :p WHERE id = :id";
        $stmt_upd = $_conexion->prepare($sql_upd);
        $stmt_upd->execute([
            't' => $n_titulo,
            'd' => $n_desc,
            'p' => $n_priv,
            'id' => $id_edit
        ]);
        header("Location: listas.php?status=editada");
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comiclook | Mis Listas</title>
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
    
    <main class="flex-grow p-8 max-w-7xl mx-auto w-full grid grid-cols-1 md:grid-cols-3 gap-10">
        <!-- COLUMNA IZQUIERDA: Formulario -->
        <aside class="md:col-span-1">
            <h1 class="font-comic text-5xl mb-6 text-white uppercase drop-shadow-[4px_4px_0_black] italic">
                MIS <span class="text-rose-800">LISTAS</span>
            </h1>
            <?php if (!empty($mensaje_error)) { ?>
                <div class="bg-rose-800 border-4 border-black p-3 mb-4 text-white font-bold text-sm uppercase">
                    <?= $mensaje_error ?>
                </div>
            <?php } ?>
            <?php if (isset($_GET['status']) && $_GET['status'] == 'creada') { ?>
                <div class="bg-yellow-500 border-4 border-black p-3 mb-4 text-black font-bold text-sm uppercase">
                    ¡Lista creada con éxito!
                </div>
            <?php } ?>
            <form method="POST" action="listas.php" class="bg-neutral-900 border-4 border-black p-6 shadow-[8px_8px_0_0_black] flex flex-col gap-5 sticky top-24">
                <h2 class="font-comic text-2xl text-yellow-500 uppercase border-b-4 border-black pb-2">Crear Nueva Lista</h2>
                
                <div>
                    <label class="block font-bold text-xs uppercase tracking-widest text-zinc-400 mb-1">Título</label>
                    <input type="text" name="titulo" required placeholder="Ej: Mis lecturas de verano..." class="w-full bg-black border-4 border-zinc-700 focus:border-yellow-500 focus:outline-none px-4 py-2 text-white transition-colors">
                </div>
                <div>
                    <label class="block font-bold text-xs uppercase tracking-widest text-zinc-400 mb-1">Descripción</label>
                    <textarea name="descripcion" rows="3" placeholder="¿De qué va esta lista?" class="w-full bg-black border-4 border-zinc-700 focus:border-yellow-500 focus:outline-none px-4 py-2 text-white resize-none transition-colors"></textarea>
                </div>
                <div>
                    <label class="block font-bold text-xs uppercase tracking-widest text-zinc-400 mb-3">Privacidad</label>
                    <div class="flex flex-col gap-3">
                        <!-- Botón Radio: Pública -->
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" name="privacidad" value="0" checked class="hidden peer">
                            <div class="w-5 h-5 border-2 border-zinc-500 peer-checked:border-blue-500 peer-checked:bg-blue-500 rounded-full flex items-center justify-center transition-all shadow-[2px_2px_0_0_black]"></div>
                            <span class="text-zinc-400 peer-checked:text-blue-500 font-bold uppercase transition-colors flex items-center gap-2">
                                <!-- Icono Planeta -->
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path><path d="M2 12h20"></path></svg>
                                Pública
                            </span>
                        </label>
                        
                        <!-- Botón Radio: Privada -->
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" name="privacidad" value="1" class="hidden peer">
                            <div class="w-5 h-5 border-2 border-zinc-500 peer-checked:border-rose-600 peer-checked:bg-rose-600 rounded-full flex items-center justify-center transition-all shadow-[2px_2px_0_0_black]"></div>
                            <span class="text-zinc-400 peer-checked:text-rose-500 font-bold uppercase transition-colors flex items-center gap-2">
                                <!-- Icono Candado -->
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                Privada
                            </span>
                        </label>
                    </div>
                </div>
                <button type="submit" name="crear_lista" class="mt-4 bg-rose-800 text-white font-comic text-xl uppercase tracking-widest px-4 py-3 border-4 border-black shadow-[6px_6px_0_0_black] hover:bg-rose-900 active:translate-y-1 active:translate-x-1 active:shadow-none transition-all">
                    Añadir Lista +
                </button>
            </form>
        </aside>
        <!-- COLUMNA DERECHA: Galería de listas -->
        <section class="md:col-span-2">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <?php foreach($mis_listas as $lista) {
                    // Obtener hasta 4 portadas de esta lista
                    $stmtPortadas = $_conexion->prepare("
                        SELECT o.portada 
                        FROM lista_obra lo 
                        JOIN obra o ON lo.id_obra = o.id 
                        WHERE lo.id_lista = :id_lista 
                        LIMIT 4
                    ");
                    $stmtPortadas->execute(['id_lista' => $lista['id']]);
                    $portadas = $stmtPortadas->fetchAll(PDO::FETCH_COLUMN);
                ?>
                    
                    <!-- Tarjeta de lista -->
                    <a href="ver_lista.php?id=<?= $lista['id'] ?>" class="bg-neutral-900 border-4 border-white shadow-[6px_6px_0_0_black] p-5 hover:-translate-y-1 hover:-translate-x-1 hover:shadow-[10px_10px_0_0_#9f1239] transition-all flex flex-col justify-between aspect-square group">
                        
                        <div>
                            <div class="flex justify-between items-start mb-3 border-b-4 border-white pb-3">
                                <h3 class="font-comic text-3xl uppercase text-white leading-tight group-hover:text-yellow-400 transition-colors"><?= htmlspecialchars($lista['titulo']) ?></h3>
                                
                                <!-- Icono de Privacidad Condicional -->
                                <?php if($lista['privacidad'] == 1) { ?>
                                    <svg class="w-8 h-8 text-rose-800 flex-shrink-0 drop-shadow-[2px_2px_0_black]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="black" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                <?php } else { ?>
                                    <svg class="w-8 h-8 text-blue-500 flex-shrink-0 drop-shadow-[2px_2px_0_black]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="black" stroke-width="1.5"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path><path d="M2 12h20"></path></svg>
                                <?php } ?>
                            </div>
                            <p class="text-zinc-600 text-sm font-bold line-clamp-4 leading-relaxed">
                                <?= htmlspecialchars($lista['descripcion']) ?: 'Sin descripción.' ?>
                            </p>
                            <!-- Collage de Portadas -->
                    <div class="mt-4 grid grid-cols-2 grid-rows-2 gap-1 bg-black border-2 border-black overflow-hidden h-40 shadow-[3px_3px_0_0_black]">
                                <?php if(!empty($portadas)) { 
                                    foreach($portadas as $p) { ?>
                                        <img src="../<?= htmlspecialchars($p) ?>" class="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-opacity grayscale group-hover:grayscale-0">
                                    <?php } 
                                    // Si hay menos de 4, rellenamos con huecos negros para que no se descuadre la rejilla
                                    for($i=0; $i < (4 - count($portadas)); $i++) {
                                        echo '<div class="bg-neutral-800 w-full h-full"></div>';
                                    }
                                } else { ?>
                                    <!-- Si la lista está vacía, mostramos un mensaje sutil -->
                                    <div class="col-span-2 row-span-2 flex flex-col items-center justify-center bg-neutral-800/50 h-full border-2 border-dashed border-zinc-700">
                                        <svg class="w-8 h-8 text-zinc-600 mb-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="font-comic text-[10px] text-zinc-500 uppercase tracking-widest">Lista vacía</span>
                                    </div>
                                <?php } ?>
                            </div>
                                </div>
                        <div class="mt-4 pt-3 flex justify-between items-center text-xs font-bold uppercase text-zinc-500">
                            <div class="flex items-center gap-1">
                                <span class="bg-yellow-400 text-black border-2 border-black px-2 py-1 shadow-[2px_2px_0_0_black]"><?= date('d/m/Y', strtotime($lista['fecha_creacion'])) ?></span>
                             <!-- BOTÓN DE ACCIONES -->
                            <div class="relative">
                                <button onclick="toggleMenuLista(event, <?= $lista['id'] ?>)" class="text-zinc-500 hover:text-white p-1 transition-colors">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                    </svg>
                                </button>
                                <!-- Menú Desplegable (Hidden por defecto) -->
                                <div id="menu-lista-<?= $lista['id'] ?>" class="absolute right-0 mt-2 w-32 bg-neutral-900 border-4 border-black shadow-[4px_4px_0_0_black] z-50 hidden">
                                    <button onclick="abrirModalEditar(event, <?= $lista['id'] ?>, '<?= addslashes(htmlspecialchars($lista['titulo'])) ?>', '<?= addslashes(htmlspecialchars($lista['descripcion'])) ?>', <?= $lista['privacidad'] ?>)" class="w-full text-left px-4 py-2 text-xs font-bold text-white uppercase hover:bg-rose-800 transition-colors">Editar</button>
                                    <button onclick="eliminarLista(event, <?= $lista['id'] ?>)" class="w-full text-left px-4 py-2 text-xs font-bold uppercase text-rose-500 hover:bg-rose-800 hover:text-white border-t-2 border-black transition-colors">Eliminar</button>
                                </div>
                            </div>
                            </div>
                            
                            <span class="bg-black text-white px-3 py-1 border-2 border-transparent group-hover:bg-rose-800 transition-colors">Ver →</span>
                        </div>
                    </a>
                <?php } ?>
                
                <?php if(empty($mis_listas)) { ?>
                    <div class="col-span-full bg-neutral-900 border-4 border-black p-10 text-center shadow-[8px_8px_0_0_black]">
                        <p class="font-comic text-3xl text-zinc-500 uppercase tracking-widest">Aún no hay listas</p>
                        <p class="text-zinc-400 font-bold mt-2">Crea tu primera lista usando el formulario de la izquierda.</p>
                    </div>
                <?php } ?>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <?php include '../assets/footer.php'?>
</body>
</html>
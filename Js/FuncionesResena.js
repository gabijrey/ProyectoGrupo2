function toggleMenuResena(id) {
    // 1. Buscamos el menú específico
    const menu = document.getElementById('menu-resena-' + id);

    // 2. (Opcional) Cerramos todos los demás menús primero para que no se amontonen
    document.querySelectorAll('[id^="menu-resena-"]').forEach(m => {
        if (m !== menu) m.classList.add('hidden');
    });
    // 3. Togleamos el nuestro
    menu.classList.toggle('hidden');
}
// 4. (Extra) Cerrar si el usuario hace clic fuera del menú
window.onclick = function (event) {
    if (!event.target.matches('button') && !event.target.closest('.relative')) {
        document.querySelectorAll('[id^="menu-resena-"]').forEach(m => m.classList.add('hidden'));
    }
}

function mensajeConfirmacion(url) {
    //Crear elementos
    const cajaModal = document.createElement('div');
    cajaModal.className = "bg-yellow-500 border-8 border-black p-8 shadow-[12px_12px_0_0_black] text-center max-w-sm w-full";

    const overlay = document.createElement('div');
    overlay.className = "fixed inset-0 bg-black/90 z-[100] flex items-center justify-center p-4";

    const contenedorBotones = document.createElement('div');
    contenedorBotones.className = "flex gap-4 justify-center mt-6";

    const titulo = document.createElement('p');
    titulo.className = "font-comic text-3xl text-black mb-8 uppercase italic";
    titulo.textContent = "¿SEGURO QUE QUIERES BORRAR ESTA RESEÑA?";

    const botonSi = document.createElement('button');
    botonSi.textContent = "BORRAR";
    botonSi.className = "bg-rose-800 text-white font-comic px-6 py-2 border-4 border-black shadow-[4px_4px_0_0_black] hover:bg-rose-900 transition-all uppercase";
    botonSi.onclick = () => window.location.href = url;

    const botonNo = document.createElement('button');
    botonNo.textContent = "CANCELAR";
    botonNo.className = "bg-black text-white font-comic px-6 py-2 border-4 border-black shadow-[4px_4px_0_0_black] hover:bg-zinc-800 transition-all uppercase";
    botonNo.onclick = () => overlay.remove();


    //Ensamblar toda la cajetilla de confirmacion
    contenedorBotones.appendChild(botonSi);
    contenedorBotones.appendChild(botonNo);
    cajaModal.appendChild(titulo);
    cajaModal.appendChild(contenedorBotones);
    overlay.appendChild(cajaModal);
    document.body.appendChild(overlay);
}
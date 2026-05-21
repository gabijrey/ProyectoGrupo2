const buscador = document.getElementById("buscador-obras");
const contenedorResultados = document.getElementById("resultados-busqueda");
const heroWriting = document.getElementById("hero-writing");
const heroPunch = document.getElementById("hero-punch");

let lastLength = 0;
let heroTimeout;
let debounceTimer;

const hideHeroes = () => {
  heroWriting.classList.remove("hero-active-writing");
  heroPunch.classList.remove("hero-active-punch");
};

buscador.addEventListener("input", (e) => {
  clearTimeout(debounceTimer);
  clearTimeout(heroTimeout);

  const query = e.target.value.trim();
  const currentLength = e.target.value.length;

  // Lógica de Héroes
  if (currentLength > lastLength) {
    // Escribiendo (Más caracteres)
    heroPunch.classList.remove("hero-active-punch");
    // Forzar reinicio de animación
    heroWriting.classList.remove("hero-active-writing");
    void heroWriting.offsetWidth; // Trigger reflow
    heroWriting.classList.add("hero-active-writing");
  } else if (currentLength < lastLength) {
    // Borrando (Menos caracteres)
    heroWriting.classList.remove("hero-active-writing");
    // Forzar reinicio de animación
    heroPunch.classList.remove("hero-active-punch");
    void heroPunch.offsetWidth; // Trigger reflow
    heroPunch.classList.add("hero-active-punch");
  }

  lastLength = currentLength;

  // Ocultar héroes tras 1.2 segundos sin escribir
  heroTimeout = setTimeout(hideHeroes, 1200);

  if (query.length < 2) {
    contenedorResultados.innerHTML = "";
    contenedorResultados.classList.add("hidden");
    contenedorResultados.classList.remove("flex");
    return;
  }

  debounceTimer = setTimeout(() => {
    fetch(`buscar_obras.php?q=${encodeURIComponent(query)}`)
      .then((res) => res.json())
      .then((data) => {
        contenedorResultados.innerHTML = "";
        if (data.length === 0) {
          contenedorResultados.innerHTML =
            '<div class="p-4 text-center font-bold text-black font-comic text-xl">NO SE ENCONTRARON OBRAS. ¡SIGUE BUSCANDO!</div>';
          contenedorResultados.classList.remove("hidden");
          contenedorResultados.classList.add("flex");
          return;
        }

        data.forEach((obra) => {
          const tipoTexto =
            obra.tipo == 0 ? "Cómic" : obra.tipo == 1 ? "Manga" : "Libro";
          const a = document.createElement("a");
          a.href = `webcontent/obra.php?id=${obra.id}`;
          a.className =
            "flex items-center gap-4 p-3 border-b-4 border-black hover:bg-yellow-400 transition-colors cursor-pointer group";

          a.innerHTML = `
                                <img src="${obra.portada}" alt="${obra.titulo}" class="w-12 h-16 object-cover border-2 border-black group-hover:scale-110 transition-transform">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-black uppercase text-lg truncate" title="${obra.titulo}">${obra.titulo}</h3>
                                    <span class="text-[10px] font-black bg-rose-800 text-white px-2 py-1 uppercase border-2 border-black inline-block mt-1">${tipoTexto}</span>
                                </div>
                                <div class="text-black font-bold opacity-0 group-hover:opacity-100 transition-opacity">
                                    VER ➔
                                </div>
                            `;
          contenedorResultados.appendChild(a);
        });

        contenedorResultados.classList.remove("hidden");
        contenedorResultados.classList.add("flex");
      })
      .catch((err) => {
        console.error("Error buscando obras:", err);
      });
  }, 300);
});

// Ocultar resultados al hacer click fuera
document.addEventListener("click", (e) => {
  if (
    !buscador.contains(e.target) &&
    !contenedorResultados.contains(e.target)
  ) {
    contenedorResultados.classList.add("hidden");
    contenedorResultados.classList.remove("flex");
  }
});

// Mostrar de nuevo al hacer click en el input si hay valor
buscador.addEventListener("focus", () => {
  if (
    buscador.value.trim().length >= 2 &&
    contenedorResultados.children.length > 0
  ) {
    contenedorResultados.classList.remove("hidden");
    contenedorResultados.classList.add("flex");
  }
});

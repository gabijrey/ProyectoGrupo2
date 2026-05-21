/* ----------------------------- COMPARADOR DE OBRAS ----------------------------- */

// Helpers para construir URLs conservando el otro slot
function paramsActuales() {
  const url = new URL(window.location.href);
  return {
    id1: url.searchParams.get("id1"),
    id2: url.searchParams.get("id2"),
  };
}

function urlParaSlot(slot, idObra) {
  const actual = paramsActuales();
  const params = new URLSearchParams();
  if (slot === 1) {
    params.set("id1", idObra);
    if (actual.id2) params.set("id2", actual.id2);
  } else {
    if (actual.id1) params.set("id1", actual.id1);
    params.set("id2", idObra);
  }
  return "comparar.php?" + params.toString();
}

/* --- Tabs --- */
document.querySelectorAll(".tab-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    const slot = btn.dataset.slot;
    const tab = btn.dataset.tab;

    document
      .querySelectorAll(`.tab-btn[data-slot="${slot}"]`)
      .forEach((b) => b.classList.remove("active"));
    btn.classList.add("active");

    document
      .querySelectorAll(`.tab-panel[data-slot="${slot}"]`)
      .forEach((p) => p.classList.add("hidden"));
    const panel = document.querySelector(
      `.tab-panel[data-slot="${slot}"][data-panel="${tab}"]`
    );
    if (panel) panel.classList.remove("hidden");

    // Si cambias de tab, oculta el dropdown de resultados de ese slot
    const drop = document.querySelector(`[data-resultados="${slot}"]`);
    if (drop) {
      drop.classList.add("hidden");
      drop.classList.remove("flex");
    }
  });
});

/* --- Posicionado del dropdown anclado al input --- */
function posicionarDropdown(input, dropdown) {
  const rect = input.getBoundingClientRect();
  dropdown.style.top = rect.bottom + 8 + "px";
  dropdown.style.left = rect.left + "px";
  dropdown.style.width = rect.width + "px";
}

/* --- Buscadores con autocompletado por slot --- */
document.querySelectorAll("[data-buscador]").forEach((input) => {
  const slot = parseInt(input.dataset.buscador, 10);
  const resultados = document.querySelector(`[data-resultados="${slot}"]`);
  if (!resultados) return;
  let debounceTimer;

  const mostrar = () => {
    posicionarDropdown(input, resultados);
    resultados.classList.remove("hidden");
    resultados.classList.add("flex");
  };
  const ocultar = () => {
    resultados.classList.add("hidden");
    resultados.classList.remove("flex");
  };

  input.addEventListener("input", (e) => {
    clearTimeout(debounceTimer);
    const query = e.target.value.trim();

    if (query.length < 2) {
      ocultar();
      return;
    }

    debounceTimer = setTimeout(() => {
      fetch(`../buscar_obras.php?q=${encodeURIComponent(query)}`)
        .then((res) => res.json())
        .then((data) => {
          resultados.innerHTML = "";

          if (!Array.isArray(data) || data.length === 0) {
            resultados.innerHTML =
              '<div class="p-4 text-center font-bold text-black font-comic text-base">SIN RESULTADOS</div>';
            mostrar();
            return;
          }

          data.forEach((obra) => {
            const tipoTexto =
              obra.tipo == 0 || obra.tipo == 3
                ? "Cómic"
                : obra.tipo == 1
                ? "Manga"
                : "Libro";

            const a = document.createElement("a");
            a.href = urlParaSlot(slot, obra.id);
            a.className =
              "flex items-center gap-3 p-2 border-b-2 border-black hover:bg-yellow-400 transition-colors cursor-pointer group";
            a.innerHTML = `
              <img src="../${obra.portada}" alt="" class="w-10 h-14 object-cover border-2 border-black group-hover:scale-110 transition-transform">
              <div class="flex-1 min-w-0">
                <h3 class="font-bold text-black uppercase text-sm truncate" title="${obra.titulo}">${obra.titulo}</h3>
                <span class="text-[9px] font-black bg-rose-800 text-white px-1.5 py-0.5 uppercase border border-black inline-block mt-1">${tipoTexto}</span>
              </div>
              <div class="text-black font-bold text-xs opacity-0 group-hover:opacity-100 transition-opacity">→</div>
            `;
            resultados.appendChild(a);
          });

          mostrar();
        })
        .catch((err) => {
          console.error("Error buscando obras:", err);
        });
    }, 250);
  });

  input.addEventListener("focus", () => {
    if (input.value.trim().length >= 2 && resultados.children.length > 0) {
      mostrar();
    }
  });

  // Reposicionar mientras está visible si el usuario hace scroll/resize
  window.addEventListener("scroll", () => {
    if (!resultados.classList.contains("hidden")) {
      posicionarDropdown(input, resultados);
    }
  }, { passive: true });
  window.addEventListener("resize", () => {
    if (!resultados.classList.contains("hidden")) {
      posicionarDropdown(input, resultados);
    }
  });

  // Cerrar al hacer click fuera del input o del dropdown
  document.addEventListener("click", (e) => {
    if (resultados.classList.contains("hidden")) return;
    if (input.contains(e.target) || resultados.contains(e.target)) return;
    ocultar();
  });
});

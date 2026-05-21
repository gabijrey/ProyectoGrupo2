/**-----------------------------------AÑADIR A FAVORITOS------------------------------- */
//Corazoncito de obras favoritas
function toggleFavorito(idObra) {
  //Variables necesarias
  const url = "../sesion/profile/toggle_favoritos.php";
  const iconoCorazon = document.getElementById("heart-icon");

  fetch(url, {
    method: "POST",
    headers: {
      "Content-type": "application/json",
    },
    body: JSON.stringify({
      id_obra: idObra,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      // console.log("Respuesta de PHP:", data);

      if (data.success) {
        //Si todo va bien cambiamos el color del corazon
        if (data.action === "added") {
          iconoCorazon.setAttribute("fill", "#e11d48");
          iconoCorazon.classList.add("scale-110");
          setTimeout(() => iconoCorazon.classList.remove("scale-110"), 150);
        } else if (data.action === "removed") {
          //Sino pues dejamos vacio el corazon
          iconoCorazon.setAttribute("fill", "none");
        }
      } else {
        console.error("Error en el servidor:", data.error);
      }
    })
    .catch((error) => {
      console.error("Error en la conexion", error);
    });
}

/**-------------------------------- AÑADIR A LISTAS ------------------------- */

//Mostrar ocultar menu de listas
function toggleModalListas() {
  const modal = document.getElementById("modal-listas");
  modal.classList.toggle("hidden");
}

//Cerrar el menu si se clica fuera
document.addEventListener("click", function (e) {
  const modal = document.getElementById("modal-listas");
  if (
    modal &&
    !modal.classList.contains("hidden") &&
    !e.target.closest("#contenedor-listas")
  ) {
    modal.classList.add("hidden");
  }
});

//Guardar obra en la lista
function addObraToLista(idObra, idLista) {
  const url = "../sesion/profile/add_to_lista.php";
  fetch(url, {
    method: "POST",
    headers: {
      "Content-type": "application/json",
    },
    body: JSON.stringify({
      id_obra: idObra,
      id_lista: idLista,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        mostrarNotificacion("¡Añadido correctamente a la lista!");
        toggleModalListas();
      } else {
        mostrarNotificacion("Error: " + data.error, "error");
      }
    })
    .catch((error) => {
      console.error("Error en la conexion", error);
    });
}

//Abrir modal para crear listas desde obra.php
function abrirModalCrearLista() {
  document.getElementById("modal-listas").classList.add("hidden");
  const modalCrear = document.getElementById("modal-crear-lista");

  modalCrear.classList.remove("hidden");
  modalCrear.classList.add("flex");
}

//Cerrar modal de crear listas
function cerrarModalCrearLista() {
  const modalCrear = document.getElementById("modal-crear-lista");

  modalCrear.classList.add("hidden");
  modalCrear.classList.remove("flex");
}

/* ----------------------------------FUNCIONALIDADES EXTRA----------------------------- */

//Crear notificacion dinámica de añadido
function mostrarNotificacion(mensaje, tipo = "exito") {
  const popUpAdded = document.createElement("div");

  popUpAdded.className =
    "fixed top-24 right-5 border-4 border-black font-comic text-xl uppercase tracking-widest px-6 py-4 shadow-[6px_6px_0_0_black] z-[9999] transition-all duration-300 transform translate-x-full opacity-0 flex flex-col items-end gap-2";

  if (tipo === "exito") {
    popUpAdded.classList.add("bg-yellow-500", "text-black");
  } else {
    popUpAdded.classList.add("bg-rose-800", "text-white");
  }

  const textoMensaje = document.createElement("span");
  textoMensaje.textContent = mensaje;
  popUpAdded.appendChild(textoMensaje);

  if (tipo === "exito") {
    const enlaceListas = document.createElement("a");
    enlaceListas.href = "../sesion/listas.php";
    enlaceListas.textContent = "Ir a listas →";
    enlaceListas.className =
      "bg-rose-800 text-white font-sans text-[11px] font-black uppercase tracking-widest px-3 py-1.5 border-2 border-black shadow-[2px_2px_0_0_black] hover:bg-rose-900 hover:-translate-y-0.5 hover:-translate-x-0.5 hover:shadow-[4px_4px_0_0_black] transition-all cursor-pointer";

    popUpAdded.appendChild(enlaceListas);
  }
  document.body.appendChild(popUpAdded);
  //mini-Delay para la animacion al aparecer
  requestAnimationFrame(() => {
    popUpAdded.classList.remove("translate-x-full", "opacity-0");
  });

  //Delay para que desaparezca la cajetilla
  setTimeout(() => {
    popUpAdded.classList.add("translate-x-full", "opacity-0");
    setTimeout(() => {
      popUpAdded.remove();
    }, 300);
  }, 2500);
}

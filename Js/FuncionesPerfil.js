document.addEventListener("DOMContentLoaded", () => {
  const inputFoto = document.getElementById("foto-input"); // El input real que está oculto.
  const imageField = document.getElementById("img-perfil"); // la imagen que se muestra

  if (inputFoto) {
    // Al cambiar el archivo seleccionado (Vista previa)
    inputFoto.addEventListener("change", (event) => {
      // se activa justo después de seleccionar un archivo del ordenador
      if (event.target.files && event.target.files[0] && imageField) {
        // Comprobación de que hay seleccionado un archivo
        const reader = new FileReader(); // Este objeto es un lector, que permite ver archivos del pc y ser entendidos por el navegador
        reader.onload = (e) => {
          // cuando se termina de leer el archivo se pone como nueva SRC de la imagen.
          imageField.src = e.target.result;

          // Asegurarnos de que la imagen sea visible (por si estaba oculta por error)
          imageField.style.display = "block";
          // Ocultar el div del icono gris (el placeholder)
          if (imageField.nextElementSibling) {
            imageField.nextElementSibling.style.display = "none";
          }
        };
        reader.readAsDataURL(event.target.files[0]); // Entrega archivo al lector para pasar al formato Data url
      }
    });
  }

  // Contador de la maravillosa biografia :D --------------------------------------------------------------------------------------
  const bioTextarea = document.getElementById("bio-textarea"); // pillo el textarea
  if (bioTextarea) {
    // 1. Crear el elemento contador
    const counter = document.createElement("div"); // creo el contador
    counter.id = "bio-counter"; // pongo id al contador
    counter.className =
      "text-right text-xs font-bold mt-1 transition-colors text-zinc-500"; // Le doy estilos //
    counter.textContent = "0 / 1000"; // pongo texto inicial //

    // 2. Insertarlo justo después del textarea
    bioTextarea.after(counter);

    // 3. Función para actualizar el contador
    const updateCounter = () => {
      const count = bioTextarea.value.length;
      counter.textContent = `${count} / 1000`; // aqui pongo la cantidad exacta de caracteres (a tiempo real) //

      if (count > 1000) {
        // filtro de los carácteres //
        counter.classList.remove("text-green-500", "text-zinc-500");
        counter.classList.add("text-rose-500"); // Esto es rojito //
      } else if (count > 0) {
        counter.classList.remove("text-rose-500", "text-zinc-500");
        counter.classList.add("text-green-500"); // Esto es verdecito //
      } else {
        counter.classList.remove("text-rose-500", "text-green-500");
        counter.classList.add("text-zinc-500"); // Esto es en gris rarete //
      }
    };

    // 4. Inicializar y escuchar cambios
    updateCounter();
    bioTextarea.addEventListener("input", updateCounter); // se actualiza cada vez que escribes algo...
  }
});

/* ABRIR MODALES */
function toggleMenuLista(event, id) {
  event.preventDefault();
  event.stopPropagation();

  //Localizar menu de la lista
  const menu = document.getElementById("menu-lista-" + id);

  //Cerrar posibles menus abiertos
  document.querySelectorAll('[id^="menu-lista-"]').forEach((m) => {
    if (m !== menu) m.classList.add("hidden");
  });

  menu.classList.toggle("hidden");
}
// Cerrar si se hace click fuera
window.addEventListener("click", function (e) {
  if (!e.target.matches("button")) {
    document
      .querySelectorAll('[id^="menu-lista-"]')
      .forEach((m) => m.classList.add("hidden"));
  }
});

/*--------------------------------ELIMINAR COSITAS---------------------------------- */

function crearElementosEliminar() {
  const cajaModal = document.createElement("div");
  cajaModal.className =
    "bg-yellow-500 border-8 border-black p-8 shadow-[12px_12px_0_0_black] text-center max-w-sm w-full";

  const overlay = document.createElement("div");
  overlay.className =
    "fixed inset-0 bg-black/90 z-[100] flex items-center justify-center p-4";

  const contenedorBotones = document.createElement("div");
  contenedorBotones.className = "flex gap-4 justify-center mt-6";

  const titulo = document.createElement("p");
  titulo.className = "font-comic text-3xl text-black mb-8 uppercase italic";

  const botonSi = document.createElement("button");
  botonSi.className =
    "bg-rose-800 text-white font-comic px-6 py-2 border-4 border-black shadow-[4px_4px_0_0_black] hover:bg-rose-900 transition-all uppercase";

  const botonNo = document.createElement("button");
  botonNo.className =
    "bg-black text-white font-comic px-6 py-2 border-4 border-black shadow-[4px_4px_0_0_black] hover:bg-rose-900 transition-all uppercase";

  // Devolvemos un objeto con todos los elementos para usarlos fuera
  return { overlay, cajaModal, titulo, contenedorBotones, botonSi, botonNo };
}

//Eliminar obra de la lista
function eliminarDeLista(e, idLista, idObra) {
  e.preventDefault();
  const url = `ver_lista.php?id=${idLista}&eliminar_obra=${idObra}`;

  // Usamos el constructor
  const modal = crearElementosEliminar();
  modal.titulo.textContent =
    "¿SEGURO QUE QUIERES QUITAR ESTA OBRA DE LA LISTA?";
  modal.botonSi.textContent = "QUITAR";
  modal.botonNo.textContent = "CANCELAR";
  modal.botonSi.onclick = () => (window.location.href = url);
  modal.botonNo.onclick = () => modal.overlay.remove();

  // Ensamblamos (puedes copiar este bloque de tu otra funcion)
  modal.contenedorBotones.appendChild(modal.botonSi);
  modal.contenedorBotones.appendChild(modal.botonNo);
  modal.cajaModal.appendChild(modal.titulo);
  modal.cajaModal.appendChild(modal.contenedorBotones);
  modal.overlay.appendChild(modal.cajaModal);
  document.body.appendChild(modal.overlay);
}

//Eliminar lista completa
function eliminarLista(e, id) {
  e.preventDefault();
  e.stopPropagation();

  //Llamar a la funcion para crear elementos
  const modal = crearElementosEliminar();

  modal.titulo.textContent = "¿BORRAR ESTA LISTA Y TODO SU CONTENIDO?";
  modal.botonSi.textContent = "BORRAR";
  modal.botonNo.textContent = "CANCELAR";

  modal.botonSi.onclick = () =>
    (window.location.href = `listas.php?eliminar_id=${id}`);
  modal.botonNo.onclick = () => modal.overlay.remove();

  //Ensamblar todo
  modal.contenedorBotones.appendChild(modal.botonSi);
  modal.contenedorBotones.appendChild(modal.botonNo);
  modal.cajaModal.appendChild(modal.titulo);
  modal.cajaModal.appendChild(modal.contenedorBotones);
  modal.overlay.appendChild(modal.cajaModal);

  document.body.appendChild(modal.overlay);
}

/*-------------------------------- EDITAR COSITAS ------------------------------ */

function abrirModalEditar(e, id, tituloActual, descActual, privActual) {
  e.preventDefault();
  e.stopPropagation();

  // 1. Overlay y Formulario
  const overlay = document.createElement("div");
  overlay.className =
    "fixed inset-0 bg-black/90 z-[100] flex items-center justify-center p-4";
  const form = document.createElement("form");
  form.method = "POST";
  form.action = "listas.php";
  form.className =
    "bg-neutral-900 border-8 border-black p-8 shadow-[12px_12px_0_0_black] w-full max-w-lg flex flex-col gap-5";

  // 2. Título del Modal
  const h2 = document.createElement("h2");
  h2.className =
    "font-comic text-4xl text-yellow-500 uppercase border-b-4 border-black pb-2 italic";
  h2.textContent = "Editar Lista";
  form.appendChild(h2);

  // 3. Input Oculto (ID)
  const inputId = document.createElement("input");
  inputId.type = "hidden";
  inputId.name = "id_lista_edit";
  inputId.value = id;
  form.appendChild(inputId);

  // 4. Campo: Título
  const divTitulo = document.createElement("div");
  const labelT = document.createElement("label");
  labelT.className =
    "block font-bold text-xs uppercase tracking-widest text-zinc-400 mb-1";
  labelT.textContent = "Título";
  const inputT = document.createElement("input");
  inputT.type = "text";
  inputT.name = "nuevo_titulo";
  inputT.required = true;
  inputT.value = tituloActual;
  inputT.className =
    "w-full bg-black border-4 border-zinc-700 focus:border-yellow-500 focus:outline-none px-4 py-2 text-white";
  divTitulo.appendChild(labelT);
  divTitulo.appendChild(inputT);
  form.appendChild(divTitulo);

  // 5. Campo: Descripción
  const divDesc = document.createElement("div");
  const labelD = document.createElement("label");
  labelD.className =
    "block font-bold text-xs uppercase tracking-widest text-zinc-400 mb-1";
  labelD.textContent = "Descripción";
  const textareaD = document.createElement("textarea");
  textareaD.name = "nueva_descripcion";
  textareaD.rows = 3;
  textareaD.value = descActual;
  textareaD.className =
    "w-full bg-black border-4 border-zinc-700 focus:border-yellow-500 focus:outline-none px-4 py-2 text-white resize-none";
  divDesc.appendChild(labelD);
  divDesc.appendChild(textareaD);
  form.appendChild(divDesc);

  // 6. Campo: Privacidad (Select)
  const divPriv = document.createElement("div");
  const labelP = document.createElement("label");
  labelP.className =
    "block font-bold text-xs uppercase tracking-widest text-zinc-400 mb-3";
  labelP.textContent = "Privacidad";
  divPriv.appendChild(labelP);
  const contenedorRadios = document.createElement("div");
  contenedorRadios.className = "flex flex-row gap-8"; // Uno al lado del otro con separación
  // Función auxiliar para no repetir código de los radios
  const crearRadioPrivacidad = (
    valor,
    texto,
    iconoSVG,
    colorPeer,
    esChequeado,
  ) => {
    const label = document.createElement("label");
    label.className = "flex items-center gap-3 cursor-pointer group";
    const input = document.createElement("input");
    input.type = "radio";
    input.name = "nueva_privacidad";
    input.value = valor;
    input.checked = esChequeado;
    input.className = "hidden peer";
    const circulo = document.createElement("div");
    circulo.className = `w-5 h-5 border-2 border-zinc-500 peer-checked:border-${colorPeer} peer-checked:bg-${colorPeer} rounded-full flex items-center justify-center transition-all shadow-[2px_2px_0_0_black]`;
    const span = document.createElement("span");
    span.className = `text-zinc-400 peer-checked:text-${colorPeer} font-bold uppercase transition-colors flex items-center gap-2 text-xs`;
    span.innerHTML = iconoSVG + texto;
    label.appendChild(input);
    label.appendChild(circulo);
    label.appendChild(span);
    return label;
  };
  // SVGs de los iconos (Planeta y Candado)
  const svgPlaneta =
    '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path><path d="M2 12h20"></path></svg>';
  const svgCandado =
    '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>';
  const radioPub = crearRadioPrivacidad(
    "0",
    "Pública",
    svgPlaneta,
    "blue-500",
    privActual == 0,
  );
  const radioPriv = crearRadioPrivacidad(
    "1",
    "Privada",
    svgCandado,
    "rose-600",
    privActual == 1,
  );
  contenedorRadios.appendChild(radioPub);
  contenedorRadios.appendChild(radioPriv);
  divPriv.appendChild(contenedorRadios);
  form.appendChild(divPriv);

  // 7. Contenedor de Botones
  const divBtns = document.createElement("div");
  divBtns.className = "flex gap-4 justify-end mt-4";
  const btnCancel = document.createElement("button");
  btnCancel.type = "button";
  btnCancel.className =
    "bg-black text-white font-comic px-6 py-2 border-4 border-black shadow-[4px_4px_0_0_black] hover:bg-zinc-800 transition-all uppercase";
  btnCancel.textContent = "Cancelar";
  btnCancel.onclick = () => overlay.remove();
  const btnSave = document.createElement("button");
  btnSave.type = "submit";
  btnSave.name = "guardar_cambios_lista";
  btnSave.className =
    "bg-rose-800 text-white font-comic px-6 py-2 border-4 border-black shadow-[4px_4px_0_0_black] hover:bg-rose-900 transition-all uppercase text-sm";
  btnSave.textContent = "Guardar";
  divBtns.appendChild(btnCancel);
  divBtns.appendChild(btnSave);
  form.appendChild(divBtns);
  // 8. Ensamblado final
  overlay.appendChild(form);
  document.body.appendChild(overlay);
}

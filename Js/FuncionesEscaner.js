/* ----------------------------- ESCÁNER DE CÓDIGOS DE BARRAS ----------------------------- */

const video = document.getElementById("scanner-video");
const btnIniciar = document.getElementById("btn-iniciar");
const btnDetener = document.getElementById("btn-detener");
const selectCamara = document.getElementById("select-camara");
const statusMsg = document.getElementById("status-msg");
const ultimoCodigoBox = document.getElementById("ultimo-codigo");
const ultimoCodigoValor = document.getElementById("ultimo-codigo-valor");
const resultadoBox = document.getElementById("resultado-obra");
const formManual = document.getElementById("form-manual");
const inputManual = document.getElementById("codigo-manual");

if (!window.ZXing) {
  setStatus("No se ha podido cargar la librería del lector. Comprueba tu conexión.", "error");
}

const codeReader = window.ZXing ? new ZXing.BrowserMultiFormatReader() : null;
let escaneandoConId = null;
let ultimoCodigoBuscado = null;
let ultimoCodigoTimestamp = 0;

function setStatus(texto, tipo = "info") {
  if (!statusMsg) return;
  statusMsg.textContent = texto;
  statusMsg.classList.remove("text-zinc-300", "text-rose-500", "text-yellow-500", "text-green-500");
  if (tipo === "error") statusMsg.classList.add("text-rose-500");
  else if (tipo === "warning") statusMsg.classList.add("text-yellow-500");
  else if (tipo === "ok") statusMsg.classList.add("text-green-500");
  else statusMsg.classList.add("text-zinc-300");
}

async function listarCamaras() {
  if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
    setStatus("Tu navegador no soporta acceso a cámaras.", "error");
    return [];
  }
  try {
    const dispositivos = await navigator.mediaDevices.enumerateDevices();
    const camaras = dispositivos.filter((d) => d.kind === "videoinput");

    selectCamara.innerHTML = "";
    if (camaras.length === 0) {
      const opt = document.createElement("option");
      opt.value = "";
      opt.textContent = "Sin cámaras disponibles";
      selectCamara.appendChild(opt);
      return [];
    }
    camaras.forEach((d, i) => {
      const opt = document.createElement("option");
      opt.value = d.deviceId;
      opt.textContent = d.label || `Cámara ${i + 1}`;
      selectCamara.appendChild(opt);
    });
    // Preferir cámara trasera si hay opciones
    const trasera = camaras.find((d) =>
      /back|rear|environment|trasera/i.test(d.label || "")
    );
    if (trasera) selectCamara.value = trasera.deviceId;
    return camaras;
  } catch (e) {
    console.error(e);
    setStatus("No se pueden listar las cámaras: " + e.message, "error");
    return [];
  }
}

async function iniciarEscaneo() {
  if (!codeReader) return;
  try {
    setStatus("Pidiendo permiso a la cámara...", "warning");
    
    const constraints = {
      video: selectCamara.value
        ? { deviceId: { exact: selectCamara.value } }
        : { facingMode: { ideal: "environment" } },
    };

    btnIniciar.disabled = true;
    btnDetener.disabled = false;

    const ctrl = await codeReader.decodeFromConstraints(constraints, video, (resultado, err) => {
      if (resultado) {
        const codigo = resultado.getText();
        const ahora = Date.now();
        if (codigo === ultimoCodigoBuscado && ahora - ultimoCodigoTimestamp < 3000) return;
        ultimoCodigoBuscado = codigo;
        ultimoCodigoTimestamp = ahora;
        manejarCodigoLeido(codigo);
      }
    });
    escaneandoConId = ctrl;
    setStatus("Escaneando... apunta al código de barras.", "ok");

    // Una vez concedido el permiso, los labels de las cámaras ya son visibles → refrescar lista
    listarCamaras();
  } catch (e) {
    console.error(e);
    btnIniciar.disabled = false;
    btnDetener.disabled = true;
    if (e.name === "NotAllowedError") {
      setStatus("Permiso de cámara denegado. Habilítalo en los ajustes del navegador.", "error");
    } else if (e.name === "NotFoundError") {
      setStatus("No se ha encontrado ninguna cámara en este dispositivo.", "error");
    } else if (location.protocol !== "https:" && location.hostname !== "localhost") {
      setStatus("La cámara solo funciona en HTTPS o en localhost.", "error");
    } else {
      setStatus("Error al iniciar la cámara: " + e.message, "error");
    }
  }
}

function detenerEscaneo() {
  if (codeReader) codeReader.reset();
  if (video && video.srcObject) {
    video.srcObject.getTracks().forEach((t) => t.stop());
    video.srcObject = null;
  }
  btnIniciar.disabled = false;
  btnDetener.disabled = true;
  setStatus("Cámara detenida.", "info");
}



async function manejarCodigoLeido(codigo) {
  ultimoCodigoBox.classList.remove("hidden");
  ultimoCodigoValor.textContent = codigo;
  setStatus("Código detectado, buscando obra...", "warning");

  if (!/^\d{6,14}$/.test(codigo)) {
    setStatus("Código '" + codigo + "' no válido (debe ser numérico).", "error");
    return;
  }

  try {
    const res = await fetch("../sesion/profile/buscar_por_codigo.php?codigo=" + encodeURIComponent(codigo));
    const data = await res.json();

    if (data.success && data.obra) {
      pintarObraEncontrada(data.obra);
      setStatus("¡Obra encontrada! Redirigiendo...", "ok");
      detenerEscaneo();
      setTimeout(() => {
        window.location.href = "obra.php?id=" + data.obra.id;
      }, 1200);
    } else {
      resultadoBox.classList.add("hidden");
      setStatus(data.error || "No se ha encontrado ninguna obra con ese código.", "error");
    }
  } catch (e) {
    console.error(e);
    setStatus("Error al consultar el servidor.", "error");
  }
}

function pintarObraEncontrada(obra) {
  const tipos = { 0: "Cómic", 1: "Manga", 2: "Libro", 3: "Cómic" };
  const tipoTxt = tipos[obra.tipo] || "Obra";
  resultadoBox.classList.remove("hidden");
  resultadoBox.innerHTML = `
    <img src="../${obra.portada || ""}" alt="" class="w-16 h-24 object-cover border-2 border-black flex-shrink-0">
    <div class="flex-1 min-w-0">
      <span class="font-comic text-[10px] uppercase tracking-widest text-yellow-500 block">${tipoTxt}</span>
      <p class="font-comic text-base uppercase text-white truncate" title="${obra.titulo}">${obra.titulo}</p>
      <p class="text-[10px] text-zinc-400 mt-1 truncate">${obra.nombre_editorial || ""} · ${obra.anno_lanzamiento || ""}</p>
    </div>
  `;
}

if (btnIniciar) btnIniciar.addEventListener("click", () => iniciarEscaneo());

if (btnDetener) btnDetener.addEventListener("click", detenerEscaneo);

if (selectCamara) selectCamara.addEventListener("change", () => {
  if (!btnDetener.disabled) {
    detenerEscaneo();
    iniciarEscaneo();
  }
});

if (formManual) formManual.addEventListener("submit", (e) => {
  e.preventDefault();
  const codigo = (inputManual.value || "").trim();
  if (codigo) manejarCodigoLeido(codigo);
});

// Al cargar, dejamos el selector vacío. Tras conceder permiso a la cámara
// (al pulsar "Iniciar"), iniciarEscaneo() refresca la lista con los labels reales.

// Detener si el usuario sale de la página
window.addEventListener("beforeunload", detenerEscaneo);

/* --------------------------- PRICING / PAGO --------------------------- */

const modalPago = document.getElementById("modal-pago");
const modalExito = document.getElementById("modal-exito");
const modalPaypal = document.getElementById("modal-paypal");
const modalApplepay = document.getElementById("modal-applepay");
const errorBox = document.getElementById("error-pago");
const modalPlanNombre = document.getElementById("modal-plan-nombre");
const modalPrecio = document.getElementById("modal-precio");
const cerrarBtn = document.getElementById("cerrar-modal-pago");

let planSeleccionado = null;
let precioSeleccionado = 0;

const PLANES = {
  premium: { nombre: "Premium", precio: 15.99 },
  autor:   { nombre: "Autor",   precio: 18.99 },
};

function formatearPrecio(p) {
  return p.toFixed(2).replace(".", ",") + " €";
}

function abrirModalPlan(plan) {
  if (!PLANES[plan]) return;
  planSeleccionado = plan;
  precioSeleccionado = PLANES[plan].precio;

  modalPlanNombre.textContent = PLANES[plan].nombre;
  const precioStr = formatearPrecio(precioSeleccionado);
  modalPrecio.textContent = precioStr;

  document.querySelectorAll('[data-confirmar="tarjeta"]').forEach((b) => {
    b.textContent = "Pagar " + precioStr;
  });

  cambiarTab("paypal");
  ocultarError();
  modalPago.classList.remove("hidden");
  modalPago.classList.add("flex");
  document.body.style.overflow = "hidden";
}

function cerrarModalPago() {
  modalPago.classList.add("hidden");
  modalPago.classList.remove("flex");
  document.body.style.overflow = "";
}

function cambiarTab(metodo) {
  document.querySelectorAll(".pago-tab").forEach((b) => {
    b.classList.toggle("active", b.dataset.metodo === metodo);
  });
  document.querySelectorAll(".panel-pago").forEach((p) => {
    if (p.dataset.panel === metodo) {
      p.classList.remove("hidden");
      p.classList.add("flex");
    } else {
      p.classList.add("hidden");
      p.classList.remove("flex");
    }
  });
  ocultarError();
}

function mostrarError(msg) {
  errorBox.textContent = msg;
  errorBox.classList.remove("hidden");
}
function ocultarError() {
  errorBox.classList.add("hidden");
}

/* ----------- Listeners ----------- */
document.querySelectorAll(".btn-elegir-plan").forEach((btn) => {
  btn.addEventListener("click", () => abrirModalPlan(btn.dataset.plan));
});
cerrarBtn.addEventListener("click", cerrarModalPago);
modalPago.addEventListener("click", (e) => {
  if (e.target === modalPago) cerrarModalPago();
});
document.querySelectorAll(".pago-tab").forEach((btn) => {
  btn.addEventListener("click", () => cambiarTab(btn.dataset.metodo));
});

/* Máscaras tarjeta */
const cardNumero = document.getElementById("card-numero");
const cardCad = document.getElementById("card-cad");
if (cardNumero) {
  cardNumero.addEventListener("input", () => {
    let v = cardNumero.value.replace(/\D/g, "").slice(0, 19);
    cardNumero.value = v.replace(/(.{4})/g, "$1 ").trim();
  });
}
if (cardCad) {
  cardCad.addEventListener("input", () => {
    let v = cardCad.value.replace(/\D/g, "").slice(0, 4);
    if (v.length > 2) v = v.slice(0, 2) + "/" + v.slice(2);
    cardCad.value = v;
  });
}

/* Botones de confirmación dentro del modal */
document.querySelectorAll(".btn-confirmar").forEach((btn) => {
  btn.addEventListener("click", () => onConfirmar(btn.dataset.confirmar, btn));
});

function onConfirmar(metodo, btn) {
  ocultarError();
  if (!planSeleccionado) {
    mostrarError("Selecciona un plan primero.");
    return;
  }
  // PayPal y Apple Pay abren su pantalla simulada antes del pago real
  if (metodo === "paypal")   return abrirSimulacionPaypal();
  if (metodo === "applepay") return abrirSimulacionApplepay();
  // Tarjeta y Bizum siguen el flujo directo
  procesarPagoReal(metodo, btn);
}

/* --------------------------------------------------------------------- */
/*                       SIMULACIÓN PAYPAL                                */
/* --------------------------------------------------------------------- */
const paypalPasoLogin     = document.getElementById("paypal-paso-login");
const paypalPasoConfirmar = document.getElementById("paypal-paso-confirmar");
const paypalPasoProcesando= document.getElementById("paypal-paso-procesando");
const paypalEmail         = document.getElementById("paypal-email");
const paypalEmailConfirmado = document.getElementById("paypal-email-confirmado");
const paypalPlan          = document.getElementById("paypal-plan");
const paypalPrecio        = document.getElementById("paypal-precio");
const paypalTotal         = document.getElementById("paypal-total");

function abrirSimulacionPaypal() {
  paypalPlan.textContent = PLANES[planSeleccionado].nombre;
  paypalPrecio.textContent = formatearPrecio(precioSeleccionado);
  paypalTotal.textContent  = formatearPrecio(precioSeleccionado);

  // Reset al paso 1
  paypalPasoLogin.classList.remove("hidden");
  paypalPasoConfirmar.classList.add("hidden");
  paypalPasoProcesando.classList.add("hidden");

  modalPaypal.classList.remove("hidden");
  modalPaypal.classList.add("flex");
  document.body.style.overflow = "hidden";
}

function cerrarSimulacionPaypal() {
  modalPaypal.classList.add("hidden");
  modalPaypal.classList.remove("flex");
  document.body.style.overflow = "hidden"; // el modal de pago sigue abierto
}

document.getElementById("paypal-btn-login").addEventListener("click", () => {
  const email = (paypalEmail.value || "").trim();
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    paypalEmail.classList.add("border-red-500");
    return;
  }
  paypalEmail.classList.remove("border-red-500");
  paypalEmailConfirmado.textContent = email;
  paypalPasoLogin.classList.add("hidden");
  paypalPasoConfirmar.classList.remove("hidden");
});

document.getElementById("paypal-btn-cancelar").addEventListener("click", cerrarSimulacionPaypal);
document.getElementById("paypal-cerrar-tab").addEventListener("click", cerrarSimulacionPaypal);

document.getElementById("paypal-btn-pagar").addEventListener("click", async () => {
  paypalPasoConfirmar.classList.add("hidden");
  paypalPasoProcesando.classList.remove("hidden");

  // Esperar un poco para que se vea la animación, después llamar al backend
  await sleep(1500);
  const ok = await procesarPagoReal("paypal");
  if (!ok) {
    // Volver al paso de confirmar para reintentar
    paypalPasoProcesando.classList.add("hidden");
    paypalPasoConfirmar.classList.remove("hidden");
  } else {
    // El pago fue OK: cerrar la simulación. El modal de éxito ya está visible.
    modalPaypal.classList.add("hidden");
    modalPaypal.classList.remove("flex");
  }
});

/* --------------------------------------------------------------------- */
/*                       SIMULACIÓN APPLE PAY                             */
/* --------------------------------------------------------------------- */
const applepayPlan      = document.getElementById("applepay-plan");
const applepayPrecio    = document.getElementById("applepay-precio");
const applepayFaceIcon  = document.getElementById("applepay-faceid-icon");
const applepayFaceTexto = document.getElementById("applepay-faceid-texto");
const applepayBtnConf   = document.getElementById("applepay-btn-confirmar");

function abrirSimulacionApplepay() {
  applepayPlan.textContent = `${PLANES[planSeleccionado].nombre} · anual`;
  applepayPrecio.textContent = formatearPrecio(precioSeleccionado);

  // Reset estado Face ID
  applepayFaceIcon.classList.remove("faceid-scanning", "faceid-success");
  applepayFaceTexto.textContent = "Toca para pagar con Face ID";
  applepayBtnConf.disabled = false;

  modalApplepay.classList.remove("hidden");
  modalApplepay.classList.add("flex");
  document.body.style.overflow = "hidden";
}

function cerrarSimulacionApplepay() {
  modalApplepay.classList.add("hidden");
  modalApplepay.classList.remove("flex");
  document.body.style.overflow = "hidden"; // sigue abierto el modal de pago
}

document.getElementById("applepay-btn-cancelar").addEventListener("click", cerrarSimulacionApplepay);

applepayBtnConf.addEventListener("click", async () => {
  applepayBtnConf.disabled = true;

  // Fase 1: escaneando Face ID
  applepayFaceIcon.classList.add("faceid-scanning");
  applepayFaceTexto.textContent = "Mira al sensor...";
  await sleep(1500);

  // Fase 2: autenticado
  applepayFaceIcon.classList.remove("faceid-scanning");
  applepayFaceIcon.classList.add("faceid-success");
  applepayFaceTexto.textContent = "Autenticado ✓";
  await sleep(700);

  // Fase 3: procesando pago
  applepayFaceTexto.textContent = "Procesando pago...";
  const ok = await procesarPagoReal("applepay");
  if (!ok) {
    // Reset para reintentar
    applepayFaceIcon.classList.remove("faceid-success");
    applepayFaceTexto.textContent = "Pago rechazado. Vuelve a intentarlo.";
    applepayBtnConf.disabled = false;
  } else {
    applepayFaceTexto.textContent = "¡Pago completado!";
    await sleep(400);
    modalApplepay.classList.add("hidden");
    modalApplepay.classList.remove("flex");
  }
});

/* --------------------------------------------------------------------- */
/*                       LLAMADA REAL AL BACKEND                          */
/* --------------------------------------------------------------------- */
async function procesarPagoReal(metodo, btnTarjetaOBizum) {
  const payload = { plan: planSeleccionado, metodo_pago: metodo };

  if (metodo === "tarjeta") {
    const numero = (document.getElementById("card-numero").value || "").replace(/\s/g, "");
    const cvv     = document.getElementById("card-cvv").value;
    const cad     = document.getElementById("card-cad").value;
    const titular = document.getElementById("card-titular").value.trim();

    if (titular === "") { mostrarError("Indica el titular de la tarjeta."); return false; }
    if (!/^\d{13,19}$/.test(numero)) { mostrarError("Número de tarjeta inválido."); return false; }
    if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(cad)) { mostrarError("Fecha de caducidad inválida (MM/AA)."); return false; }
    if (!/^\d{3,4}$/.test(cvv)) { mostrarError("CVV inválido."); return false; }

    payload.numero = numero; payload.cvv = cvv; payload.cad = cad; payload.titular = titular;
  }
  if (metodo === "bizum") {
    const tel = (document.getElementById("bizum-telefono").value || "").replace(/\s/g, "");
    if (!/^(\+?34)?\d{9}$/.test(tel)) { mostrarError("Número de móvil inválido."); return false; }
    payload.telefono = tel;
  }

  // Para tarjeta/bizum: deshabilitar el botón del modal
  let textoOrig = null;
  if (btnTarjetaOBizum) {
    textoOrig = btnTarjetaOBizum.textContent;
    btnTarjetaOBizum.disabled = true;
    btnTarjetaOBizum.textContent = "Procesando...";
  }

  try {
    const res = await fetch("../sesion/profile/procesar_pago.php", {
      method: "POST",
      headers: { "Content-type": "application/json" },
      body: JSON.stringify(payload),
    });
    const data = await res.json();

    if (data.success) {
      cerrarModalPago();
      const mensaje = document.getElementById("exito-mensaje");
      mensaje.innerHTML = `Tu suscripción <strong class="text-yellow-500">${data.plan_nombre}</strong> está activa. ¡Bienvenido!`;
      modalExito.classList.remove("hidden");
      modalExito.classList.add("flex");
      setTimeout(() => { window.location.href = "pricing.php"; }, 2500);
      return true;
    } else {
      mostrarError(data.error || "No se ha podido procesar el pago.");
      if (btnTarjetaOBizum) { btnTarjetaOBizum.disabled = false; btnTarjetaOBizum.textContent = textoOrig; }
      return false;
    }
  } catch (e) {
    console.error(e);
    mostrarError("Error de conexión con el servidor.");
    if (btnTarjetaOBizum) { btnTarjetaOBizum.disabled = false; btnTarjetaOBizum.textContent = textoOrig; }
    return false;
  }
}

function sleep(ms) { return new Promise((r) => setTimeout(r, ms)); }

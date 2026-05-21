window.onload = function () {
  let BotonEnviar = document.getElementById("BotonEnviar");
  let BotonReseteo = document.getElementById("BotonReseteo");
  let Formu = document.getElementById("Formulario");
  let errores;
  let contenedor;

  let usuario = document.getElementById("usuario");
  let contrasena = document.getElementById("contrasena");

  BotonReseteo.onclick = function () {
    for (let i = 0; i < Formu.elements.length; i++) {
      //Meter en un if para que no pille los input que son botones
      if (
        Formu.elements[i].type === "text" ||
        Formu.elements[i].type === "password"
      ) {
        Formu.elements[i].value = "";
        Formu.elements[i].style.border = "";
      }
    }

    //Para que se limpien los mensajes de error cuando el usuario pulse reset
    let errorMsg = document.getElementsByClassName("FE");
    for (let error of errorMsg) {
      error.textContent = "";
    }

    if (contenedor) {
      document.body.removeChild(contenedor);
      contenedor = null;
    }
  };

  // Voy a meter aqui directamente todas las expresiones:
  // -------------------------------------------------------- //

  /** VERSION 2 */
  function validarDatos() {
    //Expresion anti-scripts
    const antiScripts = /<script\b[^>]*>|on\w+\s*=/gi;

    errores = false;

    // aqui borro el contenedor del fallo //

    if (contenedor) {
      document.body.removeChild(contenedor);
      contenedor = null;
    }

    // Filtros principales //
    usuario.value = usuario.value.trim(); // quitar espacios al usuario
    contrasena.value = contrasena.value.trim(); // quitar espacios a la contraseña

    if (antiScripts.test(usuario.value)) {
      errores = true;
    }
    if (antiScripts.test(contrasena.value)) {
      errores = true;
    }
    if (usuario.value == "" || contrasena.value == "") {
      errores = true;
    }

    //Comprueba que no hay errores y valida todo
    if (!errores) {
        
      return true;
    } else {
      return false;
    }
  }

  //Enviar el formulario pulsando enter
  Formu.addEventListener("keydown", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      if (validarDatos()) {
        Formu.submit();
      } else {
      contenedor = document.createElement("div");
      contenedor.textContent = "Usuario o contraseña no válidos";
      contenedor.style.color = "red";
      contenedor.style.borderRadius = "2px";
      contenedor.style.border = "solid red 1px";
      document.body.appendChild(contenedor);
      }
    }
  });

  //Enviar el formulario haciendo click
  BotonEnviar.addEventListener("click", function (e) {
    e.preventDefault();
    if (validarDatos()) {
      Formu.submit();
    } else {
      contenedor = document.createElement("div");
      contenedor.textContent = "Usuario o contraseña no válidos";
      contenedor.style.color = "red";
      contenedor.style.borderRadius = "2px";
      contenedor.style.border = "solid red 1px";
      document.body.appendChild(contenedor);
    }
  });
};

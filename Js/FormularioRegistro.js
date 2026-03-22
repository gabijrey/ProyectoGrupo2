window.onload = function () {
  let BotonEnviar = document.getElementById("BotonEnviar");
  let BotonReseteo = document.getElementById("BotonReseteo");
  let Formu = document.getElementById("Formulario");
  let mensajeError = document.getElementsByClassName("FE");
  let errores;
  let contenedor;



  let Fnombre = Formu.elements[0];
  let Femail = Formu.elements[1];
  let Fcontra = Formu.elements[2];

  BotonReseteo.onclick = function () {
    for (let i = 0; i < 3; i++) {
      Formu.elements[i].value = "";
      Formu.elements[i].style.border = "";
    }
    if (contenedor) {
      document.body.removeChild(contenedor);
      contenedor = null;
    }
  };

  BotonEnviar.onclick = function (e) {

    e.preventDefault();

    if (contenedor) {
        document.body.removeChild(contenedor);
        contenedor = null; // Muy importante para que JS sepa que ya no existe
    }

    for (let i = 0; i < 3; i++) {
      Formu.elements[i].style.border = "";
    }

    errores = "";
    
    mensajeError[0].textContent = "";
    mensajeError[1].textContent = "";
    mensajeError[2].textContent = "";

    

    //Primero quito los espacios...//

    Formu.elements[0].value = Fnombre.value.trim();
    Formu.elements[1].value = Femail.value.trim();
    Formu.elements[2].value = Fcontra.value.trim();



    //Expresión anti Scripts//

    const antiScripts = /<script\b[^>]*>|on\w+\s*=/gi;

    // Expresión de validación de correos //

    const ValidaEmail = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    // Expresión de validación de contraseña //

    const ValidaContraseña = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).*$/;

    /* Explicación de la expresión: 

    - Minimo una minuscula.
    - Minimo una mayuscula.
    - Que contenga un numero.
    - Que contenga algún simbolo.

    */
    // Filtros para los nombrecitos c: //

    // Filtro para nombre vacío //

    if (Fnombre.value == "") {
      Fnombre.style.border = "5px solid red";
      errores += "El Campo Nombre no puede quedar vacío \n";

      mensajeError[0].style.color = "red";
      mensajeError[0].textContent += "El campo Nombre no puede quedar vacío \n";
      Fnombre.focus();

    }

    // Filtro para que el nombre tenga como muy poco 5 caracteres //
    if (Fnombre.value.length < 3) {
        Fnombre.style.border = "5px solid red";
        errores += "El Campo Nombre tiene que tener al menos 3 caracteres \n";

        mensajeError[0].style.color = "red";
        mensajeError[0].textContent += "El Campo Nombre tiene que tener al menos 3 caracteres \n";
        Fnombre.focus();
    }
    if (antiScripts.test(Fnombre.value)) {
        Fnombre.style.border = "5px solid red";
        errores += "Caracteres no validos para el campo Nombre... \n";

        mensajeError[0].style.color = "red";
        mensajeError[0].textContent += "Carácteres no validos para el campo Nombre \n";
        Fnombre.focus();
    }

    // Filtros para el emailsito //

    // Email vacio //

    if (Femail.value == "") {
         Femail.style.border = "5px solid red";
      errores += "El Campo Email no puede quedar vacío \n";

      mensajeError[1].style.color = "red";
      mensajeError[1].textContent += "El campo Email no puede quedar vacío \n";
      Femail.focus();
    }

    // valida si el email tiene el formato bueno //

    if(!ValidaEmail.test(Femail.value)){
        errores += "El campo email no tiene el formato correcto \n";
        mensajeError[1].style.color = "red";
      mensajeError[1].textContent += "Este campo no tiene el formato correcto \n";
      Femail.focus();
    }

    // Hacer validación por si acaso hay algun script //

    if (antiScripts.test(Femail.value)) {
        Femail.style.border = "5px solid red";
        errores += "Caracteres no validos para el campo Email...";

         mensajeError[1].style.color = "red";
      mensajeError[1].textContent += "Caracteres no validos para el campo Email... \n";
      Femail.focus();
    }

    // Filtros para la super contraseña UwU!! //

    // filtro contraseña vacía //

    if (Fcontra.value == "") {
      Fcontra.style.border = "5px solid red";
      errores += "El campo contraseña no puede quedar vacio... \n";

      mensajeError[2].style.color = "red";
      mensajeError[2].textContent += "El campo contraseña no puede quedar vacío... \n";
      Fcontra.focus();
    }

    // filtro longitud de contraseña //

    if (Fcontra.value.length < 9) {
      Fcontra.style.border = "5px solid red";
      errores += "El campo contraseña tiene que tener como minimo 9 caracteres... \n";

      mensajeError[2].style.color = "red";
      mensajeError[2].textContent += "El campo contraseña tiene que tener como minimo 9 caracteres... \n";
      Fcontra.focus();
    }

    // filtro de validación scripts... //

    if (antiScripts.test(Fcontra.value)) {
        Fcontra.style.border = "5px solid red";
        errores += "Caracteres no validos para el campo contraseña... \n";

         mensajeError[2].style.color = "red";
      mensajeError[2].textContent += "Caracteres no validos para el campo contraseña... \n";
      Fcontra.focus();
    }

    if (!ValidaContraseña.test(Fcontra.value)) {
        Fcontra.style.border = "5px solid red";
        errores += "Tu contraseña debe contener al menos, 1 minuscula, 1 mayuscula, 1 numero y un simbolo... \n";

         mensajeError[2].style.color = "red";
      mensajeError[2].textContent += "Tu contraseña debe contener al menos, 1 minuscula, 1 mayuscula, 1 numero y un simbolo... \n";
      Fcontra.focus();
    }

    if (errores != "") {
        contenedor = document.createElement("div");
        contenedor.textContent = errores;
        contenedor.style.whiteSpace = "pre-line"; // esto de aqui he mirado que es para que se respeten los saltos de linea c:
        contenedor.style.color = "red";
        contenedor.style.borderRadius = "20px";
        contenedor.style.border = "solid red 5px";
        document.body.appendChild(contenedor);

    }else{
      Formu.submit();
    }
  };
};

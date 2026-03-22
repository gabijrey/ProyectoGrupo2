window.onload = function(){
  let BotonEnviar = document.getElementById("BotonEnviar");
  let BotonReseteo = document.getElementById("BotonReseteo");
  let Formu = document.getElementById("Formulario");
  let errores;
  let contenedor;

    BotonReseteo.onclick = function () {
    for (let i = 0; i < 2; i++) { 
      Formu.elements[i].value = "";
      Formu.elements[i].style.border = "";
    }
    if(contenedor){
      document.body.removeChild(contenedor);
      contenedor = null;
    }
  };

  // Voy a meter aqui directamente todas las expresiones:
  // -------------------------------------------------------- //


    //Expresión anti Scripts//

    const antiScripts = /<script\b[^>]*>|on\w+\s*=/gi;

    BotonEnviar.onclick = function(e){
        errores = false;
        e.preventDefault();
        
        // aqui borro el contenedor del fallo //

        if (contenedor) {
            document.body.removeChild(contenedor);
            contenedor = null;
        }
        
        // Filtros principales //

    Formu.elements[0].value = Formu.elements[0].value.trim(); // quitar espacios al nombre
    Formu.elements[1].value = Formu.elements[1].value.trim(); // quitar espacios a la contraseña

        if (antiScripts.test(Formu.elements[0].value)) {
            errores = true;
        }
        if (antiScripts.test(Formu.elements[1].value)){
            errores = true;
        }
        if (Formu.elements[0].value == "" || Formu.elements[1].value == ""){
            errores = true;
        }

        if (errores) {
        contenedor = document.createElement("div");
        contenedor.textContent = "Usuario o contraseña no válidos";
        contenedor.style.color = "red";
        contenedor.style.borderRadius = "20px";
        contenedor.style.border = "solid red 5px";
        document.body.appendChild(contenedor);
        }else{
            Formu.submit();
        }

    }
}
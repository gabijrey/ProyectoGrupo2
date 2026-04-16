window.onload = function () {
  // Como no hemos modificado obra.php para añadir IDs, buscamos los elementos
  // a través de selectores CSS (por sus atributos o tipos) y los IDs que ya existían.
  let Formu = document.querySelector('form[action^="obra.php"]');
  let textarea = document.getElementById("resena_texto");
  let BotonEnviar = document.querySelector('button[type="submit"]');
  let errores = "";
  let contenedor;


  // este chorro es un montonazo de palabras relacionadas a la muerte para intentar hacer un filtro en las reseñas, y no hacer spoilers...

  const palabrasSpoilerMuerte = [
    // Directas
    "muerte", "morir", "muere", "murió", "murio", "muerto", "muerta",
    "fallecer", "falleció", "fallecio", "difunto", "cadáver", "cadaver", "deceso",

    // Verbos / acciones
    "matar", "mató", "mato", "asesinar", "asesinato", "ejecutado",
    "eliminar", "liquidar", "sacrificar", "aniquilar",

    // Frases comunes
    "muere al final", "lo mata", "la mata", "lo asesina", "la asesina",
    "final trágico", "final tragico", "no sobrevive", "cae en batalla", "pierde la vida",

    // Eufemismos
    "pasa a mejor vida", "ya no está", "ya no esta", "desaparece para siempre",
    "descansa en paz", "rip", "se nos fue", "deja de existir",

    // Ficción / cómics
    "muere en combate", "lo destruyen", "la destruyen", "lo eliminan", "la eliminan",
    "lo borran", "la borran", "lo desintegran", "la desintegran",
    "muere en el arco", "sacrificio final",

    // Variaciones / errores / leet
    "murioooo", "murrio", "muert", "mu3re", "m4t4r"
  ];

  if (!BotonEnviar || !Formu || !textarea) return;

  BotonEnviar.onclick = function (e) {
    e.preventDefault();

    if (contenedor) {
      document.body.removeChild(contenedor);
      contenedor = null;
    }

    textarea.style.border = "";
    errores = "";

    // Primero quito los espacios iniciales/finales...
    textarea.value = textarea.value.trim();

    // Expresión anti Scripts
    const antiScripts = /<script\b[^>]*>|on\w+\s*=/gi;

    // --- Filtros para la reseña --- //

    // 1. Filtro para reseña vacía
    if (textarea.value === "") {
      textarea.style.border = "5px solid red";
      errores += "El campo de la opinión no puede quedar vacío.\n";
      textarea.focus();
    }

    // 2. Filtro anti scripts uwu
    if (antiScripts.test(textarea.value)) {
      textarea.style.border = "5px solid red";
      errores += "Caracteres no válidos detectados en tu opinión.\n";
      textarea.focus();
    }

    // 3. Filtro de Spoiler (Muerte) anti pendejos
    let textoResena = textarea.value.toLowerCase();
    let contieneSpoiler = palabrasSpoilerMuerte.some((palabra) => textoResena.includes(palabra));

    if (contieneSpoiler) {
      textarea.style.border = "5px solid red";
      errores += "¡Por favor, no hagas spoilers! Evita hablar de muertes o finales drásticos.\n";
      textarea.focus();
    }

    // --- Mostrar errores o enviar --- //
    if (errores !== "") {
      contenedor = document.createElement("div");
      
      // Estilos brutales de Comiclook usando las clases de Tailwind de tu proyecto
      contenedor.className = "fixed top-10 left-1/2 transform -translate-x-1/2 z-[9999] bg-neutral-900 border-4 border-black shadow-[8px_8px_0_0_#9f1239] p-5 w-11/12 max-w-md transition-all";
      
      // Estructura HTML para que parezca una tarjeta de la web real
      contenedor.innerHTML = `
        <div class="flex items-center justify-between mb-3 border-b-2 border-zinc-700 pb-2">
            <span class="font-comic text-2xl text-rose-600 uppercase tracking-widest drop-shadow-[2px_2px_0_black]">Cuidado Spoileador!!</span>
            <button onclick="this.closest('div').parentElement.remove()" class="text-zinc-400 hover:text-white font-black text-xl leading-none transition-colors">X</button>
        </div>
        <div class="text-white text-sm font-bold uppercase tracking-wider whitespace-pre-line leading-relaxed border-l-4 border-rose-800 pl-3">
            ${errores}
        </div>
      `;

      document.body.appendChild(contenedor);

      // Esto es como una cuenta atrás para que desaparezca el mensajito en 5 segundos UwU
      setTimeout(() => {
        if (contenedor && document.body.contains(contenedor)) {
          document.body.removeChild(contenedor);
          contenedor = null;
        }
      }, 5000);
    } else {
      Formu.submit();
    }
  };
};

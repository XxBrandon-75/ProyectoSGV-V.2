let catalogosDB = null;

const {
  camposEditables,
  puedeModificar,
  esAdmin,
  esPropioUsuario,
  datosUsuario,
  rolUsuarioActual,
  idUsuarioActual,
  catCiudades,
  catEstados,
} = window.perfilConfig || {};

document.addEventListener("DOMContentLoaded", () => {
  cargarCatalogos();

  // Navegación entre secciones (solo si existen elementos con data-section)
  const menuItems = document.querySelectorAll(".perfil-menu li[data-section]");
  if (menuItems.length > 0) {
    menuItems.forEach((item) => {
      item.addEventListener("click", function () {
        // Remover clase activa
        document
          .querySelectorAll(".perfil-menu li")
          .forEach((li) => li.classList.remove("activo"));
        document
          .querySelectorAll(".seccion-contenido")
          .forEach((sec) => sec.classList.remove("activa"));

        // Agregar clase activa
        this.classList.add("activo");
        const seccion = this.getAttribute("data-section");
        const seccionElement = document.getElementById(`seccion-${seccion}`);
        if (seccionElement) {
          seccionElement.classList.add("activa");
        }
      });
    });
  }

  const modalEditar = document.getElementById("modal-editar");
  if (modalEditar) {
    modalEditar.addEventListener("click", function (event) {
      if (event.target === modalEditar) {
        cerrarModal();
      }
    });
  }
});

async function cargarCatalogos() {
  try {
    const response = await fetch(
      "controllers/catalogoController.php?action=obtenerTodos"
    );
    const data = await response.json();
    if (data.success) {
      catalogosDB = data.catalogos;
    } else {
      console.error("Error en la respuesta de catálogos:", data);
    }
  } catch (error) {
    console.error("Error al cargar catálogos:", error);
  }
}

// Variable para almacenar los valores originales del formulario
let valoresOriginales = {};

async function editarSeccion(seccion, event) {
  const modal = document.getElementById("modal-editar");
  const camposContainer = document.getElementById("campos-editar");
  const modalTitulo = document.getElementById("modal-titulo");

  // Lógica especial para foto de perfil
  if (seccion === "foto_perfil") {
    modalTitulo.textContent = "Cambiar foto de perfil";
    camposContainer.innerHTML = `
      <div class="foto-perfil-requisitos">
        <div class="requisitos-header">
          <i class="fa-solid fa-circle-info"></i>
          <h4>Requisitos de la fotografía</h4>
        </div>
        <ul class="requisitos-lista">
          <li><i class="fa-solid fa-check"></i> Fondo blanco o neutro</li>
          <li><i class="fa-solid fa-check"></i> Mirando al frente</li>
          <li><i class="fa-solid fa-check"></i> Sin accesorios (gorras, lentes oscuros, etc.)</li>
          <li><i class="fa-solid fa-check"></i> Formato: JPG, PNG o WEBP</li>
          <li><i class="fa-solid fa-check"></i> Tamaño máximo: 2MB</li>
          <li><i class="fa-solid fa-check"></i> Dimensiones mínimas: 400x480 píxeles</li>
        </ul>
      </div>
      
      <div class="foto-perfil-preview-container">
        <div class="preview-header">
          <i class="fa-solid fa-image"></i>
          <span>Vista previa</span>
        </div>
        <div class="preview-foto-wrapper">
          <img id=\"preview-foto-perfil\" 
               src=\"${datosUsuario.FotoPerfil || ""}\" 
               alt=\"Previsualización\" 
               class=\"preview-foto-img\">
          <div class="preview-placeholder" id="preview-placeholder">
            <i class="fa-solid fa-user"></i>
            <p>Selecciona una foto para ver la previsualización</p>
          </div>
        </div>
      </div>

      <div class=\"form-group\">
        <label for=\"edit-foto_perfil\">
          <i class="fa-solid fa-upload"></i> Seleccionar fotografía
        </label>
        <input type=\"file\" 
               id=\"edit-foto_perfil\" 
               name=\"foto_perfil\" 
               accept=\"image/jpeg,image/jpg,image/png,image/webp\" 
               required>
        <input type=\"hidden\" name=\"seccion\" value=\"foto_perfil\">
      </div>
    `;
    modal.style.display = "flex";

    // Previsualización JS
    const input = document.getElementById("edit-foto_perfil");
    const preview = document.getElementById("preview-foto-perfil");
    const placeholder = document.getElementById("preview-placeholder");

    input.addEventListener("change", function (event) {
      if (input.files && input.files[0]) {
        const file = input.files[0];

        // Validar tamaño
        if (file.size > 2 * 1024 * 1024) {
          alert("La imagen no debe superar los 2MB");
          input.value = "";
          return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
          preview.src = e.target.result;
          preview.style.display = "block";
          if (placeholder) placeholder.style.display = "none";
        };
        reader.readAsDataURL(file);
      }
    });

    // Mostrar foto actual o placeholder
    if (datosUsuario.FotoPerfil) {
      preview.style.display = "block";
      if (placeholder) placeholder.style.display = "none";
    } else {
      preview.style.display = "none";
      if (placeholder) placeholder.style.display = "flex";
    }

    return;
  }

  // Obtener configuración de la sección
  const config = seccionesConfig[seccion];
  if (!config) {
    console.error(`No se encontró configuración para la sección: ${seccion}`);
    return;
  }

  // Mostrar modal inmediatamente con loader
  modalTitulo.textContent = config.titulo;
  camposContainer.innerHTML =
    '<div class="loading"><i class="fa-solid fa-spinner fa-spin"></i><p>Cargando información...</p></div>';
  modal.style.display = "flex";

  try {
    // Asegurarse de que los catálogos estén cargados
    if (!catalogosDB) {
      await cargarCatalogos();
    }

    // Pequeño delay para que se vea el loader (similar a verDetallesVoluntario)
    await new Promise((resolve) => setTimeout(resolve, 300));

    // Limpiar el contenedor
    camposContainer.innerHTML = "";

    // Reiniciar valores originales
    valoresOriginales = {};

    // Agregar campo oculto con la sección que se está editando
    const seccionInput = document.createElement("input");
    seccionInput.type = "hidden";
    seccionInput.name = "seccion";
    seccionInput.value = seccion;
    camposContainer.appendChild(seccionInput);

    // Generar campos dinámicamente
    config.campos.forEach((campo) => {
      const valorActual = datosUsuario[campo.nombre] || "";

      // Guardar valor original
      valoresOriginales[campo.nombre] = valorActual;

      const div = document.createElement("div");
      div.className = "form-group";
      const label = document.createElement("label");
      label.textContent = campo.label;
      label.setAttribute("for", `edit-${campo.nombre}`);

      let input;
      if (campo.tipo === "textarea") {
        input = document.createElement("textarea");
        input.rows = 3;
        input.value = valorActual;
      } else if (campo.tipo === "select") {
        input = document.createElement("select");

        // Obtener opciones dinámicamente
        let opciones = obtenerOpcionesCampo(campo);

        // Aplicar filtro si existe
        if (
          campo.filtroOpciones &&
          typeof campo.filtroOpciones === "function"
        ) {
          opciones = campo.filtroOpciones(opciones);
        }

        // Agregar opciones al select
        if (opciones && Array.isArray(opciones)) {
          opciones.forEach((opcion) => {
            const option = document.createElement("option");
            option.value = opcion.value;
            option.textContent = opcion.label;
            if (opcion.value == valorActual) {
              option.selected = true;
            }
            input.appendChild(option);
          });
        }
      } else {
        input = document.createElement("input");
        input.type = campo.tipo;
        input.value = valorActual;
      }

      input.id = `edit-${campo.nombre}`;
      input.name = campo.nombre;

      // Aplicar validaciones
      if (campo.required) {
        input.required = true;
      }
      if (campo.maxlength) {
        input.maxLength = campo.maxlength;
      }
      if (campo.minlength) {
        input.minLength = campo.minlength;
      }
      if (campo.pattern) {
        input.pattern = campo.pattern;
      }
      if (campo.title) {
        input.title = campo.title;
      }
      if (campo.placeholder) {
        input.placeholder = campo.placeholder;
      }

      // Para campos de teléfono, agregar validación en tiempo real
      if (campo.tipo === "tel") {
        input.addEventListener("input", function (e) {
          // Solo permitir números
          this.value = this.value.replace(/[^0-9]/g, "");
        });
      }

      // Para CURP, convertir a mayúsculas
      if (campo.nombre === "CURP") {
        input.addEventListener("input", function (e) {
          this.value = this.value.toUpperCase();
        });
      }

      // Para códigos postales, solo números
      if (campo.nombre === "CodigoPostal") {
        input.addEventListener("input", function (e) {
          this.value = this.value.replace(/[^0-9]/g, "");
        });
      }

      // Verificar permisos
      const seccionesLibres = ["direccion", "emergencia"];
      const puedeEditar =
        seccionesLibres.includes(seccion) ||
        camposEditables.includes(campo.nombre);

      if (!puedeEditar) {
        input.disabled = true;
        input.title = "No tienes permisos para editar este campo";
        div.classList.add("campo-bloqueado");
      }

      div.appendChild(label);
      div.appendChild(input);
      camposContainer.appendChild(div);
    });

    // Los campos ya están cargados, el modal ya está visible
  } catch (error) {
    console.error("Error al editar sección:", error);
    camposContainer.innerHTML = `<div class="error-mensaje"><i class="fa-solid fa-exclamation-circle"></i><p>Error al cargar los datos. Por favor, intenta de nuevo.</p></div>`;
  }
}

/**
 * Obtiene las opciones para un campo de tipo select
 * @param {Object} campo - Configuración del campo
 * @returns {Array} Array de opciones {value, label}
 */
function obtenerOpcionesCampo(campo) {
  // Si tiene opciones estáticas, usarlas
  if (campo.opcionesEstaticas) {
    return campo.opcionesEstaticas;
  }

  // Si tiene catalogoSource, obtener del catálogo correspondiente
  if (campo.catalogoSource && campo.catalogoKey) {
    const catalogo =
      campo.catalogoSource === "catalogosDB"
        ? catalogosDB?.[campo.catalogoKey]
        : window.perfilConfig?.[campo.catalogoSource];

    if (catalogo && Array.isArray(catalogo)) {
      return catalogo.map((item) => ({
        value: item[campo.valueField],
        label: item[campo.labelField],
      }));
    }
  }

  // Si usa catEstados o catCiudades directamente (compatibilidad)
  if (campo.catalogoSource === "catEstados" && catEstados) {
    return catEstados.map((e) => ({
      value: e.EstadoID,
      label: e.Nombre,
    }));
  }

  if (campo.catalogoSource === "catCiudades" && catCiudades) {
    return catCiudades.map((c) => ({
      value: c.CiudadID,
      label: c.Nombre,
    }));
  }

  return [];
}

// Función para cerrar modal
function cerrarModal() {
  document.getElementById("modal-editar").style.display = "none";
}

// Función para solicitar actualización de datos
function solicitarActualizacion(seccion) {
  const secciones = {
    personal: "Información Personal",
    tutor: "Información del Tutor",
    contacto: "Información de Contacto",
    emergencia: "Contacto de Emergencia",
    direccion: "Dirección",
    profesional: "Información Profesional",
    medica: "Información Médica",
    voluntariado: "Información de Voluntariado",
  };

  const nombreSeccion = secciones[seccion] || seccion;

  const mensaje = prompt(
    `Solicitar actualización de ${nombreSeccion}\n\n` +
      `Por favor, describe qué información necesitas actualizar:`
  );

  if (mensaje === null || mensaje.trim() === "") {
    return; // Usuario canceló o no escribió nada
  }

  // Enviar solicitud al servidor
  fetch("controllers/perfilController.php?action=solicitarActualizacion", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      voluntarioID: datosUsuario.VoluntarioID,
      seccion: seccion,
      nombreSeccion: nombreSeccion,
      mensaje: mensaje.trim(),
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert(
          "Solicitud enviada correctamente. Un coordinador o administrador la revisará pronto."
        );
      } else {
        alert("Error: " + (data.message || "No se pudo enviar la solicitud"));
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error al enviar la solicitud. Por favor, intenta de nuevo.");
    });
}

// Función para guardar cambios
function guardarCambios(event) {
  event.preventDefault();
  const formData = new FormData(event.target);
  // Si es foto_perfil, enviar a cambiarFotoPerfil sin validar cambios
  if (formData.get("seccion") === "foto_perfil") {
    fetch("index.php?controller=home&action=cambiarFotoPerfil", {
      method: "POST",
      body: formData,
    })
      .then((resp) => resp.json())
      .then((data) => {
        if (data.success) {
          // Actualizar la imagen de perfil en el DOM sin recargar
          if (data.url) {
            // Agregar timestamp para evitar caché del navegador
            const urlConTimestamp = data.url + "?t=" + new Date().getTime();
            document
              .querySelectorAll("#img-perfil-preview, #preview-foto-perfil")
              .forEach((img) => {
                img.src = urlConTimestamp;
                img.style.display = "inline-block";
              });
          }
          cerrarModal();
          alert(data.message);
        } else {
          alert(data.message || "Error al subir la foto");
        }
      })
      .catch(() => {
        alert("Error al subir la foto");
      });
    return;
  }
  // Verificar si hay cambios reales
  let hayCambios = false;

  for (const [campo, valorOriginal] of Object.entries(valoresOriginales)) {
    const valorActual = formData.get(campo) || "";
    // Comparar valores (considerando que pueden ser strings vacíos)
    if (valorActual !== valorOriginal) {
      hayCambios = true;
      break;
    }
  }

  // Si no hay cambios, mostrar mensaje y cerrar modal
  if (!hayCambios) {
    alert("No se detectaron cambios en tu información");
    cerrarModal();
    return;
  }

  // Agregar el ID del voluntario que se está editando
  formData.append("voluntarioID", datosUsuario.VoluntarioID);

  fetch("controllers/perfilController.php?action=actualizarDatos", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      // Primero verificamos si la respuesta es JSON válida
      const contentType = response.headers.get("content-type");
      if (!contentType || !contentType.includes("application/json")) {
        // Si no es JSON, obtenemos el texto para ver el error
        return response.text().then((text) => {
          console.error("Respuesta no es JSON:", text);
          throw new Error(
            "El servidor devolvió HTML en lugar de JSON. Revisa la consola para ver el error."
          );
        });
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        alert("Datos actualizados correctamente");
        location.reload(); // Recargar para mostrar cambios
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error al actualizar datos: " + error.message);
    });
}

function cambiarFotoPerfil(input) {
  if (input.files && input.files[0]) {
    const file = input.files[0];

    // Validar tipo de archivo
    if (!file.type.match("image.*")) {
      alert("Por favor selecciona una imagen válida");
      return;
    }

    // Validar tamaño (máximo 5MB)
    if (file.size > 5 * 1024 * 1024) {
      alert("La imagen no debe superar los 5MB");
      return;
    }

    // Previsualizar imagen
    const reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById("preview-foto-perfil").src = e.target.result;
    };
    reader.readAsDataURL(file);

    // Subir imagen al servidor
    const formData = new FormData();
    formData.append("foto_perfil", file);
    formData.append(
      "csrf_token",
      document.querySelector('input[name="csrf_token"]').value
    );

    fetch("controllers/perfilController.php?action=cambiarFoto", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          mostrarNotificacion(
            "Foto de perfil actualizada correctamente",
            "success"
          );
        } else {
          mostrarNotificacion(
            data.message || "Error al actualizar la foto",
            "error"
          );
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        mostrarNotificacion("Error al actualizar la foto de perfil", "error");
      });
  }
}

function mostrarNotificacion(mensaje, tipo) {
  const notif = document.createElement("div");
  notif.style.cssText = `
    position: fixed;
    top: 100px;
    right: 20px;
    padding: 1.5rem 2rem;
    background: ${tipo === "success" ? "#28a745" : "#dc3545"};
    color: white;
    border-radius: 8px;
    font-size: 1.4rem;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    z-index: 10000;
    animation: slideIn 0.3s ease;
  `;
  notif.innerHTML = `<i class="fa-solid fa-${
    tipo === "success" ? "check" : "exclamation"
  }-circle"></i> ${mensaje}`;

  document.body.appendChild(notif);

  setTimeout(() => {
    notif.style.animation = "slideOut 0.3s ease";
    setTimeout(() => notif.remove(), 300);
  }, 3000);
}

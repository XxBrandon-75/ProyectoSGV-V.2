document.addEventListener("DOMContentLoaded", () => {
  const btnAgregar = document.getElementById("btn-agregar");
  const panel = document.getElementById("formulario-panel");
  const btnCerrar = document.getElementById("btn-cerrar");
  const form = document.getElementById("form-especialidad");
  const lista = document.getElementById("lista-especialidades");

  // Detectar si hay un parámetro CURP en la URL (modo administrador)
  const urlParams = new URLSearchParams(window.location.search);
  const curpVoluntario = urlParams.get("curp");

  // Si hay CURP, ocultar el botón de agregar (modo solo lectura)
  if (curpVoluntario && btnAgregar) {
    btnAgregar.style.display = "none";
    // Agregar un mensaje indicando que se están viendo las especialidades de otro voluntario
    const titulo = document.querySelector(".especialidades-voluntario h1");
    if (titulo) {
      titulo.innerHTML = `<i class="fa-solid fa-graduation-cap"></i> Especialidades del Voluntario (${curpVoluntario})`;
    }
  }

  // Cargar especialidades al iniciar
  cargarEspecialidades();

  // Abrir panel de formulario
  if (btnAgregar) {
    btnAgregar.addEventListener("click", () => {
      form.reset();
      panel.classList.add("active");
    });
  }

  // Cerrar panel
  if (btnCerrar) {
    btnCerrar.addEventListener("click", () => {
      panel.classList.remove("active");
      form.reset();
    });
  }

  // Enviar formulario
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      // Validar que sea PDF
      const archivoInput = document.getElementById("archivo");
      if (archivoInput.files.length > 0) {
        const archivo = archivoInput.files[0];
        const extension = archivo.name.split(".").pop().toLowerCase();

        if (extension !== "pdf") {
          mostrarNotificacion(
            "Solo se permiten archivos en formato PDF.",
            "error"
          );
          return;
        }

        if (archivo.size > 5 * 1024 * 1024) {
          mostrarNotificacion("El archivo no debe superar los 5MB.", "error");
          return;
        }
      }

      const formData = new FormData(form);
      const btnGuardar = form.querySelector(".btn-guardar");

      // Deshabilitar botón mientras se procesa
      btnGuardar.disabled = true;
      btnGuardar.innerHTML =
        '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';

      try {
        const response = await fetch(
          "index.php?controller=especialidades&action=agregarEspecialidad",
          {
            method: "POST",
            body: formData,
          }
        );

        const result = await response.json();

        if (result.success) {
          mostrarNotificacion(result.message, "success");
          panel.classList.remove("active");
          form.reset();
          cargarEspecialidades(); // Recargar lista
        } else {
          mostrarNotificacion(
            result.message || "Error al guardar la especialidad",
            "error"
          );
        }
      } catch (error) {
        console.error("Error:", error);
        mostrarNotificacion("Error de conexión. Intenta de nuevo.", "error");
      } finally {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML =
          '<i class="fa-solid fa-save"></i> Guardar Especialidad';
      }
    });
  }

  /**
   * Cargar especialidades del voluntario
   */
  async function cargarEspecialidades() {
    lista.innerHTML =
      '<div class="cargando-especialidades"><i class="fa-solid fa-spinner fa-spin"></i> Cargando especialidades...</div>';

    try {
      // Si hay CURP en la URL, agregar como parámetro
      const urlParams = new URLSearchParams(window.location.search);
      const curpVoluntario = urlParams.get("curp");

      let url =
        "index.php?controller=especialidades&action=obtenerEspecialidades";
      if (curpVoluntario) {
        url += `&curp=${encodeURIComponent(curpVoluntario)}`;
      }

      const response = await fetch(url);
      const result = await response.json();

      if (result.success) {
        renderEspecialidades(result.data, curpVoluntario);
      } else {
        lista.innerHTML = `<p class="sin-cursos">Error al cargar especialidades: ${result.message}</p>`;
      }
    } catch (error) {
      console.error("Error:", error);
      lista.innerHTML =
        '<p class="sin-cursos">Error de conexión. Intenta recargar la página.</p>';
    }
  }

  /**
   * Renderizar tarjetas de especialidades
   */
  function renderEspecialidades(especialidades, curpVoluntario) {
    lista.innerHTML = "";

    if (!especialidades || especialidades.length === 0) {
      if (curpVoluntario) {
        lista.innerHTML = `<p class="sin-cursos">Este voluntario no ha registrado ninguna especialidad.</p>`;
      } else {
        lista.innerHTML = `<p class="sin-cursos">No has registrado ninguna especialidad. Haz clic en "Agregar Nueva Especialidad" para empezar.</p>`;
      }
      return;
    }

    especialidades.forEach((esp) => {
      const card = document.createElement("div");
      card.classList.add("especialidad-card");

      // Determinar clase de estatus
      let estatusClass = "estatus-pendiente";
      let estatusIcono = "fa-clock";

      if (esp["Estatus de la Especialidad"] === "Aprobado") {
        estatusClass = "estatus-aprobado";
        estatusIcono = "fa-check-circle";
      } else if (esp["Estatus de la Especialidad"] === "Rechazado") {
        estatusClass = "estatus-rechazado";
        estatusIcono = "fa-times-circle";
      }

      card.innerHTML = `
                <div class="especialidad-header">
                    <h3><i class="fa-solid fa-medal"></i> ${
                      esp["Nombre Especialidad"]
                    }</h3>
                    <span class="estatus-badge ${estatusClass}">
                        <i class="fa-solid ${estatusIcono}"></i> ${
        esp["Estatus de la Especialidad"]
      }
                    </span>
                </div>
                
                <div class="especialidad-body">
                    <div class="info-item">
                        <strong><i class="fa-solid fa-file-alt"></i> Documento Adjunto:</strong>
                        <span>${esp["NombreArchivo"] || "Sin documento"}</span>
                    </div>
                    ${
                      esp["Descripcion"]
                        ? `
                    <div class="info-item">
                        <strong><i class="fa-solid fa-align-left"></i> Descripción:</strong>
                        <span>${esp["Descripcion"]}</span>
                    </div>
                    `
                        : ""
                    }
                </div>
            `;

      lista.appendChild(card);
    });
  }

  /**
   * Mostrar notificaciones
   */
  function mostrarNotificacion(mensaje, tipo = "info") {
    // Crear elemento de notificación
    const notif = document.createElement("div");
    notif.className = `notificacion notif-${tipo}`;

    let icono = "fa-info-circle";
    if (tipo === "success") icono = "fa-check-circle";
    if (tipo === "error") icono = "fa-exclamation-circle";

    notif.innerHTML = `
            <i class="fa-solid ${icono}"></i>
            <span>${mensaje}</span>
        `;

    document.body.appendChild(notif);

    // Mostrar con animación
    setTimeout(() => notif.classList.add("show"), 10);

    // Ocultar después de 4 segundos
    setTimeout(() => {
      notif.classList.remove("show");
      setTimeout(() => notif.remove(), 300);
    }, 4000);
  }
});

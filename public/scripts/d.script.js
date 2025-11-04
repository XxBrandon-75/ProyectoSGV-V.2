document.addEventListener("DOMContentLoaded", () => {
  const btnAgregarNueva = document.getElementById("btn-agregar-nueva");
  const formPanel = document.getElementById("formulario-panel");
  const btnCancelar = document.getElementById("btn-cancelar");
  const formDocumento = document.getElementById("form-documento");
  const listaDocumentos = document.getElementById("lista-documentos");

  // Cargar documentos al iniciar
  cargarDocumentos();

  // Mostrar formulario para nuevo documento (solo admin)
  if (btnAgregarNueva) {
    btnAgregarNueva.addEventListener("click", () => {
      formDocumento.reset();
      // Asegurar que el campo de plantilla esté visible si es admin
      if (typeof togglePlantillaField === "function") {
        togglePlantillaField("Plantilla");
      }
      formPanel.classList.add("active");
    });
  }

  // Cancelar formulario
  if (btnCancelar) {
    btnCancelar.addEventListener("click", () => {
      formPanel.classList.remove("active");
    });
  }

  // Cerrar formulario con ESC
  document.addEventListener("keydown", (e) => {
    if (
      e.key === "Escape" &&
      formPanel &&
      formPanel.classList.contains("active")
    ) {
      formPanel.classList.remove("active");
    }
  });

  // Enviar formulario de documento
  if (formDocumento) {
    formDocumento.addEventListener("submit", async (e) => {
      e.preventDefault();

      const formData = new FormData(formDocumento);
      const tipoVer = formData.get("tipoVer");
      const archivoInput = formDocumento.querySelector('input[type="file"]');
      const archivo = archivoInput ? archivoInput.files[0] : null;

      // Validar según el contexto
      if (ES_ADMIN) {
        // Admin: solo requiere archivo si elige "Plantilla"
        if (tipoVer === "Plantilla" && !archivo) {
          alert("Debes seleccionar un archivo de plantilla.");
          return;
        }
        // Si es "Solo Subir", NO requiere archivo (se omite validación)
      } else {
        // Voluntario: siempre requiere archivo
        if (!archivo) {
          alert("Por favor selecciona un archivo.");
          return;
        }
      }

      // Validar tipo de archivo si hay archivo
      if (archivo) {
        const tiposPermitidos = ["application/pdf"];
        if (!tiposPermitidos.includes(archivo.type)) {
          alert("Solo se permiten archivos PDF.");
          return;
        }

        // Validar tamaño (10MB)
        if (archivo.size > 10 * 1024 * 1024) {
          alert("El archivo no debe superar los 10MB.");
          return;
        }
      }

      try {
        const response = await fetch(
          "index.php?controller=documentacion&action=subirDocumento",
          {
            method: "POST",
            body: formData,
          }
        );

        const result = await response.json();

        if (result.success) {
          alert(result.message || "Documento guardado correctamente");
          formPanel.classList.remove("active");
          formDocumento.reset();
          cargarDocumentos();
        } else {
          alert(result.message || "Error al guardar el documento");
        }
      } catch (error) {
        console.error("Error:", error);
        alert("Error al guardar el documento. Por favor intenta de nuevo.");
      }
    });
  }

  // Función para cargar documentos
  async function cargarDocumentos() {
    try {
      listaDocumentos.innerHTML =
        '<div class="cargando-documentos"><i class="fa-solid fa-spinner fa-spin"></i><p>Cargando documentos...</p></div>';

      // Construir URL con filtro de voluntario si aplica
      let url = "index.php?controller=documentacion&action=obtenerDocumentos";
      if (
        typeof VOLUNTARIO_FILTRO_ID !== "undefined" &&
        VOLUNTARIO_FILTRO_ID !== null
      ) {
        url += `&voluntarioId=${VOLUNTARIO_FILTRO_ID}`;
      }

      const response = await fetch(url);
      const data = await response.json();

      if (data.success && data.documentos && data.documentos.length > 0) {
        renderizarDocumentos(data.documentos);
      } else {
        listaDocumentos.innerHTML = `
                    <div class="sin-documentos">
                        <i class="fa-solid fa-folder-open"></i>
                        <p>${
                          ES_ADMIN
                            ? "No hay documentos definidos. Agrega el primer tipo de documento."
                            : "No hay documentos disponibles."
                        }</p>
                    </div>
                `;
      }
    } catch (error) {
      console.error("Error al cargar documentos:", error);
      listaDocumentos.innerHTML =
        '<div class="sin-documentos"><p>Error al cargar documentos</p></div>';
    }
  }

  // Función para renderizar documentos
  function renderizarDocumentos(documentos) {
    listaDocumentos.innerHTML = documentos
      .map((doc) => {
        // Determinar tipo y estatus
        const tipoDocumento = doc.TipoDocumento || "Solo Subir";
        const tieneArchivo =
          doc.VoluntarioDocumentoID !== null &&
          doc.VoluntarioDocumentoID !== undefined;
        const estatus = tieneArchivo
          ? (doc.Estatus || "pendiente").toLowerCase()
          : "disponible";

        let accionesHTML = "";
        let descripcionHTML = "";

        if (ES_ADMIN) {
          // Vista Admin
          if (tieneArchivo) {
            // Hay archivo subido por voluntario
            const fechaSubida = doc.FechaSubida
              ? new Date(doc.FechaSubida).toLocaleDateString("es-MX")
              : "N/A";

            descripcionHTML = `
                        <div class="documento-info">
                            <div class="info-item">
                                <i class="fa-solid fa-user"></i>
                                <span><strong>Voluntario:</strong> ${
                                  doc.NombreVoluntario || "N/A"
                                }</span>
                            </div>
                            <div class="info-item">
                                <i class="fa-solid fa-calendar"></i>
                                <span><strong>Subido:</strong> ${fechaSubida}</span>
                            </div>
                            <div class="info-item">
                                <i class="fa-solid fa-file"></i>
                                <span><strong>Archivo:</strong> ${
                                  doc.ArchivoSubido || "N/A"
                                }</span>
                            </div>
                        </div>
                    `;

            if (estatus === "pendiente") {
              accionesHTML = `
                            <button class="btn-accion btn-visualizar" onclick="visualizarDocumento(${doc.VoluntarioDocumentoID})">
                                <i class="fa-solid fa-eye"></i> Ver
                            </button>
                            <button class="btn-accion btn-aprobar" onclick="aprobarDocumento(${doc.VoluntarioDocumentoID})">
                                <i class="fa-solid fa-check"></i> Aprobar
                            </button>
                            <button class="btn-accion btn-rechazar" onclick="rechazarDocumento(${doc.VoluntarioDocumentoID})">
                                <i class="fa-solid fa-times"></i> Rechazar
                            </button>
                        `;
            } else {
              accionesHTML = `
                            <button class="btn-accion btn-visualizar" onclick="visualizarDocumento(${doc.VoluntarioDocumentoID})">
                                <i class="fa-solid fa-eye"></i> Ver
                            </button>
                            <button class="btn-accion btn-eliminar" onclick="eliminarDocumento(${doc.VoluntarioDocumentoID})">
                                <i class="fa-solid fa-trash"></i> Eliminar
                            </button>
                        `;
            }
          } else {
            // Tipo de documento definido, sin archivos subidos
            descripcionHTML = `
                        <div class="documento-info">
                            <div class="info-item">
                                <i class="fa-solid fa-info-circle"></i>
                                <span>${
                                  tipoDocumento === "Plantilla"
                                    ? "Plantilla disponible para descargar"
                                    : "Los voluntarios pueden subir este documento"
                                }</span>
                            </div>
                        </div>
                    `;

            if (tipoDocumento === "Plantilla") {
              accionesHTML = `
                            <button class="btn-accion btn-descargar" onclick="descargarPlantilla(${doc.DocumentoID})">
                                <i class="fa-solid fa-download"></i> Descargar Plantilla
                            </button>
                            <button class="btn-accion btn-eliminar" onclick="eliminarTipoDocumento(${doc.DocumentoID})">
                                <i class="fa-solid fa-trash"></i> Eliminar Tipo
                            </button>
                        `;
            } else {
              accionesHTML = `
                            <button class="btn-accion btn-eliminar" onclick="eliminarTipoDocumento(${doc.DocumentoID})">
                                <i class="fa-solid fa-trash"></i> Eliminar Tipo
                            </button>
                        `;
            }
          }
        } else {
          // Vista Voluntario
          if (tieneArchivo) {
            // Ya subió este documento
            const fechaSubida = doc.FechaSubida
              ? new Date(doc.FechaSubida).toLocaleDateString("es-MX")
              : "N/A";

            descripcionHTML = `
                        <div class="documento-info">
                            <div class="info-item">
                                <i class="fa-solid fa-calendar"></i>
                                <span><strong>Subido:</strong> ${fechaSubida}</span>
                            </div>
                            <div class="info-item">
                                <i class="fa-solid fa-file"></i>
                                <span><strong>Archivo:</strong> ${
                                  doc.ArchivoSubido || "N/A"
                                }</span>
                            </div>
                        </div>
                    `;

            accionesHTML = `
                        <button class="btn-accion btn-visualizar" onclick="visualizarDocumento(${doc.VoluntarioDocumentoID})">
                            <i class="fa-solid fa-eye"></i> Ver Mi Documento
                        </button>
                    `;
          } else {
            // Necesita subir este documento
            descripcionHTML = `
                        <div class="documento-info">
                            <div class="info-item">
                                <i class="fa-solid fa-exclamation-triangle" style="color: #ffc107;"></i>
                                <span>Este documento es requerido. ${
                                  tipoDocumento === "Plantilla"
                                    ? "Descarga la plantilla, llénala y súbela."
                                    : "Súbelo cuando lo tengas listo."
                                }</span>
                            </div>
                        </div>
                    `;

            if (tipoDocumento === "Plantilla") {
              accionesHTML = `
                            <button class="btn-accion btn-descargar" onclick="descargarPlantilla(${doc.DocumentoID})">
                                <i class="fa-solid fa-download"></i> Descargar Plantilla
                            </button>
                            <button class="btn-accion btn-aprobar" onclick="mostrarFormularioSubir('${doc.NombreArchivo}')">
                                <i class="fa-solid fa-upload"></i> Subir Documento
                            </button>
                        `;
            } else {
              accionesHTML = `
                            <button class="btn-accion btn-aprobar" onclick="mostrarFormularioSubir('${doc.NombreArchivo}')">
                                <i class="fa-solid fa-upload"></i> Subir Documento
                            </button>
                        `;
            }
          }
        }

        const estatusTexto =
          estatus === "disponible"
            ? "Pendiente"
            : estatus === "pendiente"
            ? "En Revisión"
            : estatus === "aprobado" || estatus === "validado"
            ? "Aprobado"
            : estatus === "rechazado"
            ? "Rechazado"
            : estatus;

        return `
                <div class="documento-card ${estatus}">
                    <div class="documento-header ${estatus}">
                        <h3 class="documento-titulo">
                            <i class="fa-solid ${
                              tieneArchivo ? "fa-file-check" : "fa-file-lines"
                            }"></i>
                            ${doc.NombreArchivo}
                        </h3>
                        <span class="documento-estatus">${estatusTexto}</span>
                    </div>
                    <div class="documento-body">
                        ${descripcionHTML}
                        <div class="documento-acciones">
                            ${accionesHTML}
                        </div>
                    </div>
                </div>
            `;
      })
      .join("");
  }

  // Mostrar formulario para subir documento (voluntarios)
  window.mostrarFormularioSubir = function (nombreDocumento) {
    const nombreDocField =
      document.getElementById("nombreDocumento") ||
      document.querySelector('[name="nombreDocumento"]');
    if (nombreDocField) {
      nombreDocField.value = nombreDocumento;
    }
    formPanel.classList.add("active");
  };

  // Descargar plantilla de documento (del catálogo)
  window.descargarPlantilla = async function (documentoId) {
    try {
      window.location.href = `index.php?controller=documentacion&action=descargarPlantilla&id=${documentoId}`;
    } catch (error) {
      console.error("Error:", error);
      alert("Error al descargar la plantilla");
    }
  };

  // Descargar documento subido por voluntario
  window.descargarDocumento = async function (VoluntarioDocumentoID) {
    try {
      window.location.href = `index.php?controller=documentacion&action=descargarDocumento&id=${VoluntarioDocumentoID}`;
    } catch (error) {
      console.error("Error:", error);
      alert("Error al descargar el documento");
    }
  };

  // Visualizar documento subido por voluntario
  window.visualizarDocumento = async function (VoluntarioDocumentoID) {
    try {
      window.open(
        `index.php?controller=documentacion&action=verDocumento&id=${VoluntarioDocumentoID}`,
        "_blank"
      );
    } catch (error) {
      console.error("Error:", error);
      alert("Error al visualizar el documento");
    }
  };

  // Aprobar documento de voluntario
  window.aprobarDocumento = async function (VoluntarioDocumentoID) {
    if (!confirm("¿Estás seguro de aprobar este documento?")) return;

    try {
      const response = await fetch(
        "index.php?controller=documentacion&action=aprobarDocumento",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `documentoId=${VoluntarioDocumentoID}`,
        }
      );

      const result = await response.json();

      if (result.success) {
        alert("Documento aprobado correctamente");
        cargarDocumentos();
      } else {
        alert(result.message || "Error al aprobar el documento");
      }
    } catch (error) {
      console.error("Error:", error);
      alert("Error al aprobar el documento");
    }
  };

  // Rechazar documento de voluntario
  window.rechazarDocumento = async function (VoluntarioDocumentoID) {
    const motivo = prompt("Ingresa el motivo del rechazo:");
    if (!motivo) return;

    try {
      const response = await fetch(
        "index.php?controller=documentacion&action=rechazarDocumento",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `documentoId=${VoluntarioDocumentoID}&motivo=${encodeURIComponent(
            motivo
          )}`,
        }
      );

      const result = await response.json();

      if (result.success) {
        alert("Documento rechazado");
        cargarDocumentos();
      } else {
        alert(result.message || "Error al rechazar el documento");
      }
    } catch (error) {
      console.error("Error:", error);
      alert("Error al rechazar el documento");
    }
  };

  // Eliminar documento subido por voluntario
  window.eliminarDocumento = async function (VoluntarioDocumentoID) {
    if (!confirm("¿Estás seguro de eliminar este documento?")) return;

    try {
      const response = await fetch(
        "index.php?controller=documentacion&action=eliminarDocumento",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `documentoId=${VoluntarioDocumentoID}`,
        }
      );

      const result = await response.json();

      if (result.success) {
        alert("Documento eliminado");
        cargarDocumentos();
      } else {
        alert(result.message || "Error al eliminar el documento");
      }
    } catch (error) {
      console.error("Error:", error);
      alert("Error al eliminar el documento");
    }
  };

  // Eliminar tipo de documento del catálogo (solo admin)
  window.eliminarTipoDocumento = async function (documentoId) {
    if (
      !confirm(
        "¿Estás seguro de eliminar este tipo de documento del catálogo? Esto eliminará también todos los documentos subidos por los voluntarios."
      )
    )
      return;

    try {
      const response = await fetch(
        "index.php?controller=documentacion&action=eliminarTipoDocumento",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `documentoId=${documentoId}`,
        }
      );

      const result = await response.json();

      if (result.success) {
        alert("Tipo de documento eliminado del catálogo");
        cargarDocumentos();
      } else {
        alert(result.message || "Error al eliminar el tipo de documento");
      }
    } catch (error) {
      console.error("Error:", error);
      alert("Error al eliminar el tipo de documento");
    }
  };
});

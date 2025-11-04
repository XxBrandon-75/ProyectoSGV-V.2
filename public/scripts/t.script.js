document.addEventListener("DOMContentLoaded", () => {
  const btnAgregar = document.getElementById("btn-agregar");
  const panel = document.getElementById("formulario-panel");
  const btnCerrar = document.getElementById("btn-cerrar");
  const form = document.getElementById("form-tramite");
  const lista = document.getElementById("lista-tramites");
  const formTitulo = document.getElementById("form-titulo");
  const indexEditar = document.getElementById("index-editar");
  const adminPanel = document.querySelector(".admin-panel");

  const listaRequerimientos = document.getElementById("lista-requerimientos");
  const nuevoLabel = document.getElementById("nuevo-label");
  const nuevoTipo = document.getElementById("nuevo-tipo");
  const btnAddReq = document.getElementById("btn-add-requerimiento");

  let tramites = [];
  let requerimientosTemp = [];
  let formAbierto = null;

  // ⚠️ ELIMINADO EL USO DE LOCALSTORAGE - Ahora todo viene de la BD

  // Crear botón toggle y overlay para móvil
  if (adminPanel) {
    const toggleBtn = document.createElement("button");
    toggleBtn.className = "admin-toggle";
    toggleBtn.innerHTML = '<i class="fa-solid fa-gear"></i>';
    toggleBtn.setAttribute("aria-label", "Toggle admin panel");
    document.body.appendChild(toggleBtn);

    const overlay = document.createElement("div");
    overlay.className = "admin-overlay";
    document.body.appendChild(overlay);

    toggleBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      const isActive = adminPanel.classList.toggle("active");
      toggleBtn.classList.toggle("active");
      overlay.classList.toggle("active");

      if (isActive) {
        document.body.style.overflow = "hidden";
      } else {
        document.body.style.overflow = "";
      }
    });

    overlay.addEventListener("click", () => {
      adminPanel.classList.remove("active");
      toggleBtn.classList.remove("active");
      overlay.classList.remove("active");
      document.body.style.overflow = "";
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && adminPanel.classList.contains("active")) {
        adminPanel.classList.remove("active");
        toggleBtn.classList.remove("active");
        overlay.classList.remove("active");
        document.body.style.overflow = "";
      }
    });
  }

  function renderRequerimientosTemp() {
    listaRequerimientos.innerHTML = "";
    if (requerimientosTemp.length === 0) {
      listaRequerimientos.innerHTML =
        '<p style="text-align: center; color: #999; font-size: 1.3rem; padding: 1rem;">No hay requerimientos agregados</p>';
      return;
    }
    requerimientosTemp.forEach((req, index) => {
      agregarRequerimientoAlDOM(req, index);
    });
  }

  function agregarRequerimientoAlDOM(req, index) {
    const div = document.createElement("div");
    div.classList.add("requerimiento-item");

    const spanContainer = document.createElement("span");
    spanContainer.classList.add("requerimiento-display");

    // ✅ CORREGIDO: Solo Texto y Archivo (sin Número)
    const tipoTexto = { text: "Texto", file: "Archivo" }[req.tipo] || req.tipo;
    spanContainer.innerHTML = `<i class="fa-solid fa-grip-vertical" style="color: #999; margin-right: 0.5rem;"></i> ${req.label} <span style="color: var(--secondary-color); font-size: 1.2rem;">(${tipoTexto})</span>`;

    const btnEliminarReq = document.createElement("button");
    btnEliminarReq.innerHTML = '<i class="fa-solid fa-times"></i>';
    btnEliminarReq.type = "button";
    btnEliminarReq.classList.add("btn-eliminar-req");
    btnEliminarReq.title = "Eliminar requerimiento";

    btnEliminarReq.addEventListener("click", () => {
      requerimientosTemp.splice(index, 1);
      renderRequerimientosTemp();
      mostrarNotificacion("Requerimiento eliminado", "info");
    });

    div.append(spanContainer, btnEliminarReq);
    listaRequerimientos.appendChild(div);

    div.style.opacity = "0";
    div.style.transform = "translateX(-10px)";
    setTimeout(() => {
      div.style.transition = "all 0.3s ease";
      div.style.opacity = "1";
      div.style.transform = "translateX(0)";
    }, index * 30);

    spanContainer.addEventListener("click", () => {
      const inputLabel = document.createElement("input");
      inputLabel.type = "text";
      inputLabel.value = req.label;
      inputLabel.classList.add("req-input-edit");
      inputLabel.placeholder = "Nombre del campo";

      const selectTipo = document.createElement("select");
      selectTipo.classList.add("req-select-edit");

      const tipos = [
        { val: "text", text: "Texto" },
        { val: "file", text: "Archivo" },
      ];
      tipos.forEach((t) => {
        const opt = document.createElement("option");
        opt.value = t.val;
        opt.textContent = t.text;
        if (t.val === req.tipo) opt.selected = true;
        selectTipo.appendChild(opt);
      });

      div.replaceChild(inputLabel, spanContainer);
      div.insertBefore(selectTipo, inputLabel.nextSibling);
      btnEliminarReq.style.display = "none";

      const handleSave = () => {
        setTimeout(() => {
          if (
            document.activeElement !== inputLabel &&
            document.activeElement !== selectTipo
          ) {
            const newLabel = inputLabel.value.trim();
            const newTipo = selectTipo.value;

            if (newLabel) {
              requerimientosTemp[index].label = newLabel;
              requerimientosTemp[index].tipo = newTipo;
              renderRequerimientosTemp();
              mostrarNotificacion("Requerimiento actualizado", "success");
            } else {
              renderRequerimientosTemp();
            }
          }
        }, 10);
      };

      inputLabel.addEventListener("blur", handleSave);
      selectTipo.addEventListener("blur", handleSave);

      inputLabel.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
          e.preventDefault();
          const newLabel = inputLabel.value.trim();
          if (newLabel) {
            requerimientosTemp[index].label = newLabel;
            requerimientosTemp[index].tipo = selectTipo.value;
            renderRequerimientosTemp();
            mostrarNotificacion("Requerimiento actualizado", "success");
          }
        }
      });

      inputLabel.focus();
      inputLabel.select();
    });
  }

  if (btnAgregar) {
    btnAgregar.addEventListener("click", () => {
      form.reset();
      indexEditar.value = "";
      formTitulo.textContent = "Agregar nuevo trámite";
      requerimientosTemp = [];
      renderRequerimientosTemp();
      panel.classList.add("active");

      if (adminPanel && adminPanel.classList.contains("active")) {
        adminPanel.classList.remove("active");
        document.querySelector(".admin-toggle")?.classList.remove("active");
        document.querySelector(".admin-overlay")?.classList.remove("active");
        document.body.style.overflow = "";
      }
    });
  }

  if (btnCerrar) {
    btnCerrar.addEventListener("click", () => {
      panel.classList.remove("active");
    });
  }

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && panel && panel.classList.contains("active")) {
      panel.classList.remove("active");
    }
  });

  if (btnAddReq) {
    btnAddReq.addEventListener("click", () => {
      const label = nuevoLabel.value.trim();
      const tipo = nuevoTipo.value;
      if (!label) {
        mostrarNotificacion(
          "Por favor ingresa un nombre para el campo",
          "error"
        );
        nuevoLabel.focus();
        return;
      }

      const req = { label, tipo };
      requerimientosTemp.push(req);
      renderRequerimientosTemp();
      nuevoLabel.value = "";
      nuevoLabel.focus();
      mostrarNotificacion("Requerimiento agregado", "success");
    });

    nuevoLabel.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        btnAddReq.click();
      }
    });
  }

  // ✅ NUEVO: Guardar trámite en la base de datos
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      if (requerimientosTemp.length === 0) {
        mostrarNotificacion("Agrega al menos un requerimiento", "error");
        return;
      }

      // Determinar si es edición o creación
      const esEdicion = indexEditar.value !== "";
      const accion = esEdicion ? "modificar_tramite" : "guardar_tramite";

      // Preparar los datos para enviar al backend
      const formData = new FormData();
      formData.append("nombre_tramite", form.nombre.value);
      formData.append("descripcion_tramite", form.descripcion.value);

      // Si es edición, agregar el ID del trámite
      if (esEdicion) {
        formData.append("tipo_tramite_id", indexEditar.value);
      }

      // Agregar requerimientos como arrays
      requerimientosTemp.forEach((req, i) => {
        formData.append(`req_nombre[]`, req.label);

        // ✅ MAPEAR: text -> texto, file -> Archivo
        const tipoDato = req.tipo === "file" ? "Archivo" : "texto";
        formData.append(`req_tipodato[]`, tipoDato);

        // Por ahora, NombreDocumento y TipoDocumento pueden ser null
        formData.append(`req_docnombre[]`, "");
        formData.append(`req_tipodoc[]`, "");
      });

      try {
        // ✅ Enviar al controlador PHP
        const response = await fetch(`?action=${accion}`, {
          method: "POST",
          body: formData,
        });

        // Verificar si la respuesta es JSON válido
        const textResponse = await response.text();

        let resultado;
        try {
          resultado = JSON.parse(textResponse);
        } catch (e) {
          console.error("Error al parsear JSON:", e);
          throw new Error(
            "La respuesta del servidor no es JSON válida: " +
              textResponse.substring(0, 200)
          );
        }

        if (resultado.Estatus === "Éxito") {
          const mensaje = esEdicion
            ? "Trámite actualizado exitosamente"
            : "Trámite guardado exitosamente";
          mostrarNotificacion(mensaje, "success");
          panel.classList.remove("active");
          form.reset();
          requerimientosTemp = [];
          indexEditar.value = ""; // Limpiar el índice de edición
          // Recargar trámites desde la BD
          await cargarTramites();
        } else {
          mostrarNotificacion(`Error: ${resultado.Mensaje}`, "error");
        }
      } catch (error) {
        console.error("Error completo:", error);
        mostrarNotificacion(
          `Error al guardar el trámite: ${error.message}`,
          "error"
        );
      }
    });
  }

  // ✅ NUEVO: Cargar trámites desde la base de datos
  async function cargarTramites(forzarRecarga = false) {
    try {
      // Si forzamos recarga O no hay trámites iniciales, cargar desde API
      if (
        forzarRecarga ||
        typeof TRAMITES_INICIALES === "undefined" ||
        TRAMITES_INICIALES.length === 0
      ) {
        const response = await fetch("?action=ver_tramites");
        tramites = await response.json();
        renderTramites();
        return;
      }

      // Si no, usar trámites iniciales solo la primera vez
      tramites = TRAMITES_INICIALES;
      renderTramites();
    } catch (error) {
      console.error("Error al cargar trámites:", error);
      mostrarNotificacion("Error al cargar trámites", "error");
    }
  }

  function renderTramites() {
    lista.innerHTML = "";
    const listaCompletados = document.getElementById(
      "lista-tramites-completados"
    );
    if (listaCompletados) {
      listaCompletados.innerHTML = "";
    }

    if (tramites.length === 0) {
      lista.innerHTML = `
        <div class="sin-tramites">
          <i class="fa-solid fa-clipboard-list" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
          <p>No hay trámites registrados.</p>
        </div>`;
      return;
    }

    // ✅ FILTRAR DUPLICADOS: Agrupar por TramiteID y quedarse con el más reciente
    const tramitesUnicos = new Map();
    const prioridad = {
      aprobado: 5,
      solicitado: 4,
      "en revisión": 4,
      pendiente: 4,
      rechazado: 3,
      "disponible para solicitar": 1,
    };

    tramites.forEach((t) => {
      const tramiteID = t.Id || t.TramiteID || t.TipoTramiteID;
      const estatus = (t.Estatus || "Disponible para solicitar").toLowerCase();

      // Obtener prioridad del estatus
      let prioridadActual = 1;
      for (const [key, value] of Object.entries(prioridad)) {
        if (estatus.includes(key)) {
          prioridadActual = value;
          break;
        }
      }

      // Si ya existe este trámite, comparar prioridades
      if (tramitesUnicos.has(tramiteID)) {
        const existente = tramitesUnicos.get(tramiteID);
        const estatusExistente = (existente.Estatus || "").toLowerCase();
        let prioridadExistente = 1;

        for (const [key, value] of Object.entries(prioridad)) {
          if (estatusExistente.includes(key)) {
            prioridadExistente = value;
            break;
          }
        }

        // Solo reemplazar si el nuevo tiene mayor prioridad
        if (prioridadActual > prioridadExistente) {
          tramitesUnicos.set(tramiteID, t);
        }
      } else {
        tramitesUnicos.set(tramiteID, t);
      }
    });

    // Convertir el Map a array
    const tramitesFiltrados = Array.from(tramitesUnicos.values());

    // Separar trámites en activos y completados
    const tramitesActivos = [];
    const tramitesCompletados = [];

    tramitesFiltrados.forEach((t) => {
      const estatus = t.Estatus || "Disponible para solicitar";

      if (estatus.toLowerCase().includes("aprobado")) {
        tramitesCompletados.push(t);
      } else {
        tramitesActivos.push(t);
      }
    });

    // Renderizar trámites activos
    if (tramitesActivos.length === 0) {
      lista.innerHTML = `
        <div class="sin-tramites">
          <i class="fa-solid fa-clipboard-list" style="font-size: 3rem; color: #ccc; margin-bottom: 0.5rem;"></i>
          <p>No hay trámites disponibles.</p>
        </div>`;
    } else {
      tramitesActivos.forEach((t, i) => {
        const card = crearTarjetaTramite(t, i, false);
        lista.appendChild(card);
      });
    }

    // Renderizar trámites completados
    if (listaCompletados) {
      if (tramitesCompletados.length === 0) {
        listaCompletados.innerHTML = `
          <div class="sin-tramites">
            <i class="fa-solid fa-circle-check" style="font-size: 3rem; color: #ccc; margin-bottom: 0.5rem;"></i>
            <p>No tienes trámites completados.</p>
          </div>`;
      } else {
        tramitesCompletados.forEach((t, i) => {
          const card = crearTarjetaTramite(t, i, true);
          listaCompletados.appendChild(card);
        });
      }
    }

    // Asignar eventos
    document
      .querySelectorAll(".btn-solicitar")
      .forEach((b) => (b.onclick = toggleFormulario));

    if (typeof CAN_EDIT_CARDS !== "undefined" && CAN_EDIT_CARDS) {
      document
        .querySelectorAll(".btn-eliminar")
        .forEach((b) => (b.onclick = eliminar));
      document
        .querySelectorAll(".btn-editar")
        .forEach((b) => (b.onclick = editar));
    }
  }

  function crearTarjetaTramite(t, i, esCompletado) {
    const card = document.createElement("div");
    card.classList.add("tramite-card");
    if (esCompletado) {
      card.classList.add("tramite-completado");
    }

    let adminButtons = "";

    if (typeof CAN_EDIT_CARDS !== "undefined" && CAN_EDIT_CARDS) {
      adminButtons = `
        <div class="acciones-admin">
          <button class="btn-editar" data-id="${
            t.Id || t.TramiteID || t.TipoTramiteID
          }" title="Editar trámite">
            <i class="fa-solid fa-pen"></i> Editar
          </button>
          <button class="btn-eliminar" data-id="${
            t.Id || t.TramiteID || t.TipoTramiteID
          }" title="Eliminar trámite">
            <i class="fa-solid fa-trash"></i> Eliminar
          </button>
        </div>
      `;
    }

    // Obtener datos del trámite del SP
    const nombre = t.NombreTramite || t["Nombre del tramite"] || t.Nombre || "";
    const descripcion = t.Descripcion || "";
    const tramiteID = t.TramiteID || t.Id || t.TipoTramiteID;
    const estatus = t.Estatus || "Disponible para solicitar";

    // Determinar clase de estatus y si puede solicitar
    let estatusClass = "estatus-disponible";
    let puedeSOLICITAR = true;
    let estatusIcon = "fa-circle-check";
    let esRechazado = false;

    if (estatus.toLowerCase().includes("aprobado")) {
      estatusClass = "estatus-aprobado";
      estatusIcon = "fa-circle-check";
      puedeSOLICITAR = false;
    } else if (
      estatus.toLowerCase().includes("pendiente") ||
      estatus.toLowerCase().includes("revisión") ||
      estatus.toLowerCase().includes("solicitado")
    ) {
      estatusClass = "estatus-pendiente";
      estatusIcon = "fa-clock";
      puedeSOLICITAR = false;
    } else if (estatus.toLowerCase().includes("rechazado")) {
      estatusClass = "estatus-rechazado";
      estatusIcon = "fa-circle-xmark";
      puedeSOLICITAR = true;
      esRechazado = true;
    } else if (estatus.toLowerCase().includes("disponible")) {
      estatusClass = "estatus-disponible";
      estatusIcon = "fa-paper-plane";
      puedeSOLICITAR = true;
    }

    // Botón de solicitar solo si está disponible o rechazado (no para aprobados)
    let botonSolicitar = "";
    if (puedeSOLICITAR && !esCompletado) {
      botonSolicitar = `
        <button class="btn-solicitar" data-id="${tramiteID}" data-es-rechazado="${esRechazado}" title="Solicitar trámite">
          <i class="fa-solid fa-file-signature"></i> ${
            esRechazado ? "Reenviar" : "Solicitar"
          }
        </button>
      `;
    }

    card.innerHTML = `
      <div class="tramite-header">
        <div class="tramite-info">
          <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
            <h3><i class="fa-solid fa-file-pen"></i> ${nombre}</h3>
            <span class="badge-estatus ${estatusClass}">
              <i class="fa-solid ${estatusIcon}"></i> ${estatus}
            </span>
          </div>
          <p><i class="fa-solid fa-align-left"></i> <strong>Descripción:</strong> ${descripcion}</p>
        </div>
        <div class="tramite-actions">
          ${adminButtons}
          ${botonSolicitar}
        </div>
      </div>
      ${
        !esCompletado
          ? `<div class="form-solicitud-wrapper" id="form-wrapper-${i}">
          <div class="form-solicitud-content">
            <h3><i class="fa-solid fa-clipboard-list"></i> Completar solicitud</h3>
            <form class="form-solicitud" id="form-solicitud-${i}" data-tramite-id="${tramiteID}">
            </form>
          </div>
        </div>`
          : ""
      }
    `;

    card.style.opacity = "0";
    card.style.transform = "translateY(20px)";

    setTimeout(() => {
      card.style.transition = "all 0.4s ease";
      card.style.opacity = "1";
      card.style.transform = "translateY(0)";
    }, i * 100);

    return card;
  }

  async function toggleFormulario(e) {
    const btn = e.target.closest("button");
    const tramiteID = btn.dataset.id;
    const i = Array.from(document.querySelectorAll(".btn-solicitar")).indexOf(
      btn
    );
    const wrapper = document.getElementById(`form-wrapper-${i}`);
    const formSolicitud = document.getElementById(`form-solicitud-${i}`);

    if (formAbierto !== null && formAbierto !== i) {
      const prevWrapper = document.getElementById(
        `form-wrapper-${formAbierto}`
      );
      const prevBtn = document.querySelector(
        `.btn-solicitar[data-id="${formAbierto}"]`
      );
      if (prevWrapper) prevWrapper.classList.remove("active");
      if (prevBtn) prevBtn.classList.remove("active");
    }

    const isActive = wrapper.classList.toggle("active");
    btn.classList.toggle("active");

    if (isActive) {
      formAbierto = i;

      try {
        // Cargar requerimientos desde la BD
        const response = await fetch(
          `?action=ver_requerimientos&id=${tramiteID}`
        );
        const textResponse = await response.text();

        let requerimientos;
        try {
          requerimientos = JSON.parse(textResponse);
        } catch (e) {
          console.error("Error al parsear requerimientos:", e);
          throw new Error("No se pudieron cargar los requerimientos");
        }

        if (!requerimientos || requerimientos.length === 0) {
          throw new Error("Este trámite no tiene requerimientos configurados");
        }

        formSolicitud.innerHTML = "";

        requerimientos.forEach((req, idx) => {
          const fieldGroup = document.createElement("div");
          fieldGroup.style.opacity = "0";
          fieldGroup.style.transform = "translateY(10px)";

          // ✅ CORREGIDO: Mapeo correcto de nombres de columnas
          const nombreReq =
            req["Nombre de los requerimientos"] ||
            req["Nombre"] ||
            req["NombreRequerimiento"] ||
            "";
          const tipoDato = req["Tipos de datos"] || req["TipoDato"] || "text";

          const label = document.createElement("label");
          label.innerHTML = `<i class="fa-solid fa-chevron-right" style="font-size: 1.2rem; color: var(--secondary-color);"></i> ${nombreReq}`;

          let input;

          // Normalizar el tipo de dato
          const tipoNormalizado = tipoDato.toLowerCase();

          if (tipoNormalizado === "archivo" || tipoNormalizado === "file") {
            input = document.createElement("input");
            input.type = "file";
          } else {
            input = document.createElement("input");
            input.type = "text";
          }

          input.required = true;
          input.name = nombreReq.toLowerCase().replace(/\s+/g, "_");
          input.placeholder = `Ingresa ${nombreReq.toLowerCase()}`;
          input.dataset.nombreOriginal = nombreReq; // Guardar nombre original

          fieldGroup.appendChild(label);
          fieldGroup.appendChild(input);
          formSolicitud.appendChild(fieldGroup);

          setTimeout(() => {
            fieldGroup.style.transition = "all 0.3s ease";
            fieldGroup.style.opacity = "1";
            fieldGroup.style.transform = "translateY(0)";
          }, idx * 50);
        });

        const btnEnviar = document.createElement("button");
        btnEnviar.type = "submit";
        btnEnviar.innerHTML =
          '<i class="fa-solid fa-paper-plane"></i> Enviar solicitud';
        formSolicitud.appendChild(btnEnviar);

        formSolicitud.onsubmit = async (e) => {
          e.preventDefault();

          try {
            const formDataInicio = new FormData();

            if (typeof VOLUNTARIO_ID === "undefined" || VOLUNTARIO_ID === 0) {
              throw new Error(
                "No se pudo identificar al usuario. Por favor, recarga la página."
              );
            }

            // ✅ NUEVO: Verificar si es un reenvío de trámite rechazado
            const esRechazado = btn.dataset.esRechazado === "true";

            formDataInicio.append("voluntarioID", VOLUNTARIO_ID);
            formDataInicio.append("tipoTramiteID", tramiteID);
            formDataInicio.append(
              "observaciones",
              esRechazado
                ? "Reenvío de solicitud rechazada"
                : "Solicitud desde el formulario web"
            );

            const responseInicio = await fetch("?action=iniciar_solicitud", {
              method: "POST",
              body: formDataInicio,
            });

            const resultadoInicio = await responseInicio.json();

            if (resultadoInicio.Estatus !== "Éxito") {
              throw new Error(resultadoInicio.Mensaje);
            }

            const solicitudID = resultadoInicio.SolicitudID;

            // PASO 2: Guardar los datos del formulario
            const responseDetalles = await fetch(
              `?action=obtener_datos_solicitud&solicitudID=${solicitudID}`
            );
            const datosSolicitud = await responseDetalles.json();

            const formDataGuardar = new FormData();

            datosSolicitud.forEach((dato, idx) => {
              const nombreReq =
                dato.NombreRequerimiento ||
                dato["Nombre de los requerimientos"] ||
                dato.Nombre ||
                "";
              const inputName = nombreReq.toLowerCase().replace(/\s+/g, "_");
              const input = formSolicitud.querySelector(
                `[name="${inputName}"]`
              );

              if (input) {
                formDataGuardar.append(
                  `DatoSolicitudID[]`,
                  dato.DatoSolicitudID
                );

                if (input.type === "file" && input.files.length > 0) {
                  // TODO: Implementar subida de archivos
                  formDataGuardar.append(`DatoTexto[]`, "");
                  formDataGuardar.append(`DatoNumero[]`, "");
                  formDataGuardar.append(`DatoFecha[]`, "");
                  formDataGuardar.append(
                    `NombreArchivo[]`,
                    input.files[0].name
                  );
                  formDataGuardar.append(
                    `RutaArchivo[]`,
                    "/uploads/" + input.files[0].name
                  );
                } else {
                  formDataGuardar.append(`DatoTexto[]`, input.value || "");
                  formDataGuardar.append(`DatoNumero[]`, "");
                  formDataGuardar.append(`DatoFecha[]`, "");
                  formDataGuardar.append(`NombreArchivo[]`, "");
                  formDataGuardar.append(`RutaArchivo[]`, "");
                }
              }
            });

            formDataGuardar.append("nuevoEstatus", "En Revisión");

            const responseGuardar = await fetch("?action=guardar_solicitud", {
              method: "POST",
              body: formDataGuardar,
            });

            const resultadoGuardar = await responseGuardar.json();

            if (resultadoGuardar.Estatus === "Éxito") {
              const mensajeExito = esRechazado
                ? "Solicitud reenviada correctamente. El estatus se ha actualizado a 'En Revisión'."
                : "Solicitud enviada correctamente";
              mostrarNotificacion(mensajeExito, "success");
              wrapper.classList.remove("active");
              btn.classList.remove("active");
              formAbierto = null;
              formSolicitud.reset();

              setTimeout(async () => {
                await cargarTramites(true); // Forzar recarga desde BD
              }, 1000);
            } else {
              throw new Error(resultadoGuardar.Mensaje);
            }
          } catch (error) {
            console.error("Error al guardar solicitud:", error);
            mostrarNotificacion(
              `Error al enviar solicitud: ${error.message}`,
              "error"
            );
          }
        };

        setTimeout(() => {
          wrapper.scrollIntoView({ behavior: "smooth", block: "nearest" });
        }, 100);
      } catch (error) {
        console.error("Error al cargar requerimientos:", error);
        mostrarNotificacion("Error al cargar el formulario", "error");
      }
    } else {
      formAbierto = null;
    }
  }

  function editar(e) {
    if (!form || !panel || !formTitulo || !listaRequerimientos) return;

    const tramiteID = e.target.closest("button").dataset.id;

    // Buscar el trámite en el array
    const tramite = tramites.find((t) => {
      const id = t.Id || t.TipoTramiteID;
      return id == tramiteID;
    });

    if (!tramite) {
      mostrarNotificacion("No se encontró el trámite", "error");
      return;
    }

    // Llenar el formulario con los datos del trámite
    indexEditar.value = tramiteID; // Guardar el ID para edición
    formTitulo.textContent = "Editar trámite";
    form.nombre.value = tramite["Nombre del tramite"] || tramite.Nombre || "";
    form.descripcion.value = tramite.Descripcion || "";

    // Las fechas no están en el SP actual, así que las dejamos vacías
    form.fecha_inicio.value = "";
    form.fecha_corte.value = "";

    // Cargar requerimientos existentes
    requerimientosTemp = [];

    // Hacer petición para obtener los requerimientos
    fetch(`?action=ver_requerimientos&id=${tramiteID}`)
      .then((response) => response.json())
      .then((requerimientos) => {
        requerimientos.forEach((req) => {
          const nombreReq =
            req["Nombre de los requerimientos"] ||
            req["Nombre"] ||
            req["NombreRequerimiento"] ||
            "";
          const tipoDato = req["Tipos de datos"] || req["TipoDato"] || "texto";

          // Mapear el tipo de dato
          let tipoMapeado = "text";
          if (
            tipoDato.toLowerCase() === "archivo" ||
            tipoDato.toLowerCase() === "file"
          ) {
            tipoMapeado = "file";
          }

          requerimientosTemp.push({
            label: nombreReq,
            tipo: tipoMapeado,
          });
        });

        renderRequerimientosTemp();
        panel.classList.add("active");

        if (adminPanel && adminPanel.classList.contains("active")) {
          adminPanel.classList.remove("active");
          document.querySelector(".admin-toggle")?.classList.remove("active");
          document.querySelector(".admin-overlay")?.classList.remove("active");
          document.body.style.overflow = "";
        }
      })
      .catch((error) => {
        console.error("Error al cargar requerimientos:", error);
        mostrarNotificacion("Error al cargar los datos del trámite", "error");
      });
  }

  function eliminar(e) {
    const tramiteID = e.target.closest("button").dataset.id;

    // Buscar el trámite para mostrar su nombre en la confirmación
    const tramite = tramites.find((t) => {
      const id = t.Id || t.TipoTramiteID;
      return id == tramiteID;
    });

    const nombreTramite = tramite
      ? tramite["Nombre del tramite"] || tramite.Nombre
      : "este trámite";

    if (
      !confirm(
        `¿Estás seguro de dar de baja "${nombreTramite}"?\n\nEsto lo marcará como inactivo y no será visible para los usuarios.`
      )
    ) {
      return;
    }

    // Enviar petición para eliminar
    const formData = new FormData();
    formData.append("tipo_tramite_id", tramiteID);

    fetch("?action=eliminar_tramite", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((resultado) => {
        if (resultado.Estatus === "Éxito") {
          mostrarNotificacion("Trámite dado de baja correctamente", "success");
          // Recargar trámites
          cargarTramites();
        } else {
          mostrarNotificacion(`Error: ${resultado.Mensaje}`, "error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        mostrarNotificacion("Error al dar de baja el trámite", "error");
      });
  }

  function mostrarNotificacion(mensaje, tipo = "success") {
    const colores = {
      success: "#28a745",
      error: "#dc3545",
      info: "#17a2b8",
    };

    const iconos = {
      success: "fa-check-circle",
      error: "fa-times-circle",
      info: "fa-info-circle",
    };

    const notif = document.createElement("div");
    notif.style.cssText = `
      position: fixed;
      top: 9rem;
      right: 2rem;
      background: ${colores[tipo]};
      color: white;
      padding: 1.5rem 2rem;
      border-radius: 0.8rem;
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
      z-index: 10000;
      font-size: 1.5rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 1rem;
      animation: slideIn 0.3s ease;
      max-width: 400px;
    `;

    notif.innerHTML = `<i class="fa-solid ${iconos[tipo]}"></i> ${mensaje}`;
    document.body.appendChild(notif);

    setTimeout(() => {
      notif.style.animation = "slideOut 0.3s ease";
      setTimeout(() => notif.remove(), 300);
    }, 3000);
  }

  // ✅ CARGAR TRÁMITES AL INICIAR
  cargarTramites();
});

const style = document.createElement("style");
style.textContent = `
  @keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
  }
  @keyframes slideOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
  }
`;
document.head.appendChild(style);

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

  // YA NO HAY LOCAL STORAGE :(
  //PERO YA JALA CON LA BD :)
  
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
      listaRequerimientos.innerHTML = '<p style="text-align: center; color: #999; font-size: 1.3rem; padding: 1rem;">No hay requerimientos agregados</p>';
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
    const tipoTexto = {text: 'Texto', file: 'Archivo'}[req.tipo] || req.tipo;
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
      const inputLabel = document.createElement('input');
      inputLabel.type = 'text';
      inputLabel.value = req.label;
      inputLabel.classList.add('req-input-edit');
      inputLabel.placeholder = "Nombre del campo";

      const selectTipo = document.createElement('select');
      selectTipo.classList.add('req-select-edit');
      
      // ✅ CORREGIDO: Solo Texto y Archivo
      const tipos = [
        {val: 'text', text: 'Texto'}, 
        {val: 'file', text: 'Archivo'}
      ];
      tipos.forEach(t => {
        const opt = document.createElement('option');
        opt.value = t.val;
        opt.textContent = t.text;
        if (t.val === req.tipo) opt.selected = true;
        selectTipo.appendChild(opt);
      });

      div.replaceChild(inputLabel, spanContainer);
      div.insertBefore(selectTipo, inputLabel.nextSibling);
      btnEliminarReq.style.display = 'none';

      const handleSave = () => {
        setTimeout(() => {
          if (document.activeElement !== inputLabel && document.activeElement !== selectTipo) {
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

      inputLabel.addEventListener('blur', handleSave);
      selectTipo.addEventListener('blur', handleSave); 
      
      inputLabel.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
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
        mostrarNotificacion("Por favor ingresa un nombre para el campo", "error");
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

  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      if (requerimientosTemp.length === 0) {
        mostrarNotificacion("Agrega al menos un requerimiento", "error");
        return;
      }

      const formData = new FormData();
      formData.append('nombre_tramite', form.nombre.value);
      formData.append('descripcion_tramite', form.descripcion.value);
      
      requerimientosTemp.forEach((req, i) => {
        formData.append(`req_nombre[]`, req.label);
        
        const tipoDato = req.tipo === 'file' ? 'Archivo' : 'texto';
        formData.append(`req_tipodato[]`, tipoDato);
        
        formData.append(`req_docnombre[]`, '');
        formData.append(`req_tipodoc[]`, '');
      });

      try {
        const response = await fetch('?action=guardar_tramite', {
          method: 'POST',
          body: formData
        });

        const textResponse = await response.text();
        console.log('Respuesta del servidor:', textResponse);
        
        let resultado;
        try {
          resultado = JSON.parse(textResponse);
        } catch (e) {
          console.error('Error al parsear JSON:', e);
          throw new Error('La respuesta del servidor no es JSON válida: ' + textResponse.substring(0, 200));
        }

        if (resultado.Estatus === 'Éxito') {
          mostrarNotificacion("Trámite guardado exitosamente", "success");
          panel.classList.remove("active");
          form.reset();
          requerimientosTemp = [];

          await cargarTramites();
        } else {
          mostrarNotificacion(`Error: ${resultado.Mensaje}`, "error");
        }

      } catch (error) {
        console.error('Error completo:', error);
        mostrarNotificacion(`Error al guardar el trámite: ${error.message}`, "error");
      }
    });
  }

  async function cargarTramites() {
    try {
      // Si ya tenemos trámites iniciales, usarlos
      if (typeof TRAMITES_INICIALES !== 'undefined' && TRAMITES_INICIALES.length > 0) {
        tramites = TRAMITES_INICIALES;
        renderTramites();
        return;
      }
      
      // Si no, cargar desde la API
      const response = await fetch('?action=ver_tramites');
      tramites = await response.json();
      renderTramites();
    } catch (error) {
      console.error('Error al cargar trámites:', error);
      mostrarNotificacion("Error al cargar trámites", "error");
    }
  }

  function renderTramites() {
    lista.innerHTML = "";
    if (tramites.length === 0) {
      lista.innerHTML = `
        <div class="sin-tramites">
          <i class="fa-solid fa-clipboard-list" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
          <p>No hay trámites registrados.</p>
        </div>`;
      return;
    }

    tramites.forEach((t, i) => {
      const card = document.createElement("div");
      card.classList.add("tramite-card");

      let adminButtons = '';

      if (typeof CAN_EDIT_CARDS !== 'undefined' && CAN_EDIT_CARDS) {
        adminButtons = `
          <div class="acciones-admin">
            <button class="btn-editar" data-id="${t.Id || t.TipoTramiteID}" title="Editar trámite">
              <i class="fa-solid fa-pen"></i> Editar
            </button>
            <button class="btn-eliminar" data-id="${t.Id || t.TipoTramiteID}" title="Eliminar trámite">
              <i class="fa-solid fa-trash"></i> Eliminar
            </button>
          </div>
        `;
      }

      // ✅ CORREGIDO: Usar nombres de columnas correctos de la BD
      const nombre = t['Nombre del tramite'] || t.Nombre || '';
      const descripcion = t.Descripcion || '';
      const tramiteID = t.Id || t.TipoTramiteID;

      card.innerHTML = `
        <div class="tramite-header">
          <div class="tramite-info">
            <h3><i class="fa-solid fa-file-pen"></i> ${nombre}</h3>
            <p><i class="fa-solid fa-align-left"></i> <strong>Descripción:</strong> ${descripcion}</p>
          </div>
          <div class="tramite-actions">
            ${adminButtons}
            <button class="btn-solicitar" data-id="${tramiteID}" title="Solicitar trámite">
              <i class="fa-solid fa-file-signature"></i> Solicitar
            </button>
          </div>
        </div>
        <div class="form-solicitud-wrapper" id="form-wrapper-${i}">
          <div class="form-solicitud-content">
            <h3><i class="fa-solid fa-clipboard-list"></i> Completar solicitud</h3>
            <form class="form-solicitud" id="form-solicitud-${i}" data-tramite-id="${tramiteID}">
            </form>
          </div>
        </div>
      `;
      
      card.style.opacity = "0";
      card.style.transform = "translateY(20px)";
      lista.appendChild(card);
      
      setTimeout(() => {
        card.style.transition = "all 0.4s ease";
        card.style.opacity = "1";
        card.style.transform = "translateY(0)";
      }, i * 50);
    });

    document.querySelectorAll(".btn-solicitar").forEach(b => b.onclick = toggleFormulario);

    if (typeof CAN_EDIT_CARDS !== 'undefined' && CAN_EDIT_CARDS) {
      document.querySelectorAll(".btn-eliminar").forEach(b => b.onclick = eliminar);
      document.querySelectorAll(".btn-editar").forEach(b => b.onclick = editar);
    }
  }

  async function toggleFormulario(e) {
    const btn = e.target.closest("button");
    const tramiteID = btn.dataset.id;
    const i = Array.from(document.querySelectorAll(".btn-solicitar")).indexOf(btn);
    const wrapper = document.getElementById(`form-wrapper-${i}`);
    const formSolicitud = document.getElementById(`form-solicitud-${i}`);

    if (formAbierto !== null && formAbierto !== i) {
      const prevWrapper = document.getElementById(`form-wrapper-${formAbierto}`);
      const prevBtn = document.querySelector(`.btn-solicitar[data-id="${formAbierto}"]`);
      if (prevWrapper) prevWrapper.classList.remove("active");
      if (prevBtn) prevBtn.classList.remove("active");
    }

    const isActive = wrapper.classList.toggle("active");
    btn.classList.toggle("active");

    if (isActive) {
      formAbierto = i;
      
      try {
        // Cargar requerimientos desde la BD
        const response = await fetch(`?action=ver_requerimientos&id=${tramiteID}`);
        const textResponse = await response.text();
        
        console.log('Requerimientos raw:', textResponse);
        
        let requerimientos;
        try {
          requerimientos = JSON.parse(textResponse);
        } catch (e) {
          console.error('Error al parsear requerimientos:', e);
          throw new Error('No se pudieron cargar los requerimientos');
        }
        
        if (!requerimientos || requerimientos.length === 0) {
          throw new Error('Este trámite no tiene requerimientos configurados');
        }

        formSolicitud.innerHTML = "";
        
        requerimientos.forEach((req, idx) => {
          const fieldGroup = document.createElement("div");
          fieldGroup.style.opacity = "0";
          fieldGroup.style.transform = "translateY(10px)";
          
          // ✅ CORREGIDO: Mapeo correcto de nombres de columnas
          const nombreReq = req['Nombre de los requerimientos'] || req['Nombre'] || req['NombreRequerimiento'] || '';
          const tipoDato = req['Tipos de datos'] || req['TipoDato'] || 'text';
          
          const label = document.createElement("label");
          label.innerHTML = `<i class="fa-solid fa-chevron-right" style="font-size: 1.2rem; color: var(--secondary-color);"></i> ${nombreReq}`;
          
          let input;
          
          // Normalizar el tipo de dato
          const tipoNormalizado = tipoDato.toLowerCase();
          
          if (tipoNormalizado === 'archivo' || tipoNormalizado === 'file') {
            input = document.createElement("input");
            input.type = "file";
          } else {
            input = document.createElement("input");
            input.type = "text";
          }
          
          input.required = true;
          input.name = nombreReq.toLowerCase().replace(/\s+/g, '_');
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
        btnEnviar.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Enviar solicitud';
        formSolicitud.appendChild(btnEnviar);

        formSolicitud.onsubmit = async (e) => {
          e.preventDefault();
          
          try {
            // PASO 1: Iniciar la solicitud (esto crea los registros vacíos)
            const formDataInicio = new FormData();
            
            if (typeof VOLUNTARIO_ID === 'undefined' || VOLUNTARIO_ID === 0) {
              throw new Error('No se pudo identificar al usuario. Por favor, recarga la página.');
            }
            
            formDataInicio.append('voluntarioID', VOLUNTARIO_ID);
            formDataInicio.append('tipoTramiteID', tramiteID);
            formDataInicio.append('observaciones', 'Solicitud desde el formulario web');
            
            const responseInicio = await fetch('?action=iniciar_solicitud', {
              method: 'POST',
              body: formDataInicio
            });
            
            const resultadoInicio = await responseInicio.json();
            
            if (resultadoInicio.Estatus !== 'Éxito') {
              throw new Error(resultadoInicio.Mensaje);
            }
            
            const solicitudID = resultadoInicio.SolicitudID;
            

            const responseDetalles = await fetch(`?action=obtener_datos_solicitud&solicitudID=${solicitudID}`);
            const datosSolicitud = await responseDetalles.json();
            
            // Preparar los datos para guardar
            const formDataGuardar = new FormData();
            
            datosSolicitud.forEach((dato, idx) => {

              const nombreReq = dato.NombreRequerimiento || dato['Nombre de los requerimientos'] || dato.Nombre || '';
              const inputName = nombreReq.toLowerCase().replace(/\s+/g, '_');
              const input = formSolicitud.querySelector(`[name="${inputName}"]`);
              
              if (input) {
                formDataGuardar.append(`DatoSolicitudID[]`, dato.DatoSolicitudID);
                
                if (input.type === 'file' && input.files.length > 0) {
                  // TODO: Implementar subida de archivos
                  formDataGuardar.append(`DatoTexto[]`, '');
                  formDataGuardar.append(`DatoNumero[]`, '');
                  formDataGuardar.append(`DatoFecha[]`, '');
                  formDataGuardar.append(`NombreArchivo[]`, input.files[0].name);
                  formDataGuardar.append(`RutaArchivo[]`, '/uploads/' + input.files[0].name);
                } else {
                  formDataGuardar.append(`DatoTexto[]`, input.value || '');
                  formDataGuardar.append(`DatoNumero[]`, '');
                  formDataGuardar.append(`DatoFecha[]`, '');
                  formDataGuardar.append(`NombreArchivo[]`, '');
                  formDataGuardar.append(`RutaArchivo[]`, '');
                }
              }
            });
            
            formDataGuardar.append('nuevoEstatus', 'En Revisión');
            
            const responseGuardar = await fetch('?action=guardar_solicitud', {
              method: 'POST',
              body: formDataGuardar
            });
            
            const resultadoGuardar = await responseGuardar.json();
            
            if (resultadoGuardar.Estatus === 'Éxito') {
              mostrarNotificacion(`Solicitud enviada correctamente`, "success");
              wrapper.classList.remove("active");
              btn.classList.remove("active");
              formAbierto = null;
              formSolicitud.reset();
            } else {
              throw new Error(resultadoGuardar.Mensaje);
            }
            
          } catch (error) {
            console.error('Error al guardar solicitud:', error);
            mostrarNotificacion(`Error al enviar solicitud: ${error.message}`, "error");
          }
        };

        setTimeout(() => {
          wrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 100);

      } catch (error) {
        console.error('Error al cargar requerimientos:', error);
        mostrarNotificacion("Error al cargar el formulario", "error");
      }
    } else {
      formAbierto = null;
    }
  }

  //CONFIESO QUE SIEMPRE ME HA GUSTADO LA MUCHACHA DE LENTES, BNO NO LA CONOCES PERO ANDABA ALLA EN LA TIENDA PERO LA NETA TABA BONITA
  //POR CIERTO NO HAY PROCEDURE PARA ESTA LINEA POR LO TANTO HACE FALTA DE IMPLEMENTAR 
  function editar(e) {
    // TODO: Implementar edición de trámites
    mostrarNotificacion("Función de edición en desarrollo", "info");
  }


  //FALTA IMPLEMENTAR NO HAY PROCEDURE PÁ QUIEN LO VEA PA
  function eliminar(e) {
    // TODO: Implementar eliminación de trámites
    mostrarNotificacion("Función de eliminación en desarrollo", "info");
  }

  function mostrarNotificacion(mensaje, tipo = "success") {
    const colores = {
      success: "#28a745",
      error: "#dc3545",
      info: "#17a2b8"
    };
    
    const iconos = {
      success: "fa-check-circle",
      error: "fa-times-circle",
      info: "fa-info-circle"
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

const style = document.createElement('style');
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
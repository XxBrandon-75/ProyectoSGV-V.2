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

  let tramites = JSON.parse(localStorage.getItem("tramites")) || [];
  let requerimientosTemp = [];
  let formAbierto = null;

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
    
    const tipoTexto = {text: 'Texto', number: 'Número', file: 'Archivo'}[req.tipo] || req.tipo;
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

    // Animación de entrada
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
      const tipos = [
        {val: 'text', text: 'Texto'}, 
        {val: 'number', text: 'Número'}, 
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
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      if (requerimientosTemp.length === 0) {
        mostrarNotificacion("Agrega al menos un requerimiento", "error");
        return;
      }

      const data = {
        nombre: form.nombre.value,
        descripcion: form.descripcion.value,
        inicio: form.fecha_inicio.value,
        corte: form.fecha_corte.value,
        requerimientos: requerimientosTemp
      };

      const index = indexEditar.value;
      if (index === "") {
        tramites.push(data);
        mostrarNotificacion("Trámite agregado exitosamente", "success");
      } else {
        tramites[index] = data;
        mostrarNotificacion("Trámite actualizado exitosamente", "success");
      }

      localStorage.setItem("tramites", JSON.stringify(tramites));
      renderTramites();
      panel.classList.remove("active");
      form.reset();
    });
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
            <button class="btn-editar" data-i="${i}" title="Editar trámite">
              <i class="fa-solid fa-pen"></i> Editar
            </button>
            <button class="btn-eliminar" data-i="${i}" title="Eliminar trámite">
              <i class="fa-solid fa-trash"></i> Eliminar
            </button>
          </div>
        `;
      }

      card.innerHTML = `
        <div class="tramite-header">
          <div class="tramite-info">
            <h3><i class="fa-solid fa-file-pen"></i> ${t.nombre}</h3>
            <p><i class="fa-solid fa-align-left"></i> <strong>Descripción:</strong> ${t.descripcion}</p>
            <p><i class="fa-solid fa-calendar-check"></i> <strong>Inicio:</strong> ${t.inicio} | <i class="fa-solid fa-calendar-xmark"></i> <strong>Corte:</strong> ${t.corte}</p>
          </div>
          <div class="tramite-actions">
            ${adminButtons}
            <button class="btn-solicitar" data-i="${i}" title="Solicitar trámite">
              <i class="fa-solid fa-file-signature"></i> Solicitar
            </button>
          </div>
        </div>
        <div class="form-solicitud-wrapper" id="form-wrapper-${i}">
          <div class="form-solicitud-content">
            <h3><i class="fa-solid fa-clipboard-list"></i> Completar solicitud</h3>
            <form class="form-solicitud" id="form-solicitud-${i}">
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

  function toggleFormulario(e) {
    const btn = e.target.closest("button");
    const i = btn.dataset.i;
    const t = tramites[i];
    const wrapper = document.getElementById(`form-wrapper-${i}`);
    const formSolicitud = document.getElementById(`form-solicitud-${i}`);

    if (formAbierto !== null && formAbierto !== i) {
      const prevWrapper = document.getElementById(`form-wrapper-${formAbierto}`);
      const prevBtn = document.querySelector(`.btn-solicitar[data-i="${formAbierto}"]`);
      if (prevWrapper) prevWrapper.classList.remove("active");
      if (prevBtn) prevBtn.classList.remove("active");
    }

    const isActive = wrapper.classList.toggle("active");
    btn.classList.toggle("active");

    if (isActive) {
      formAbierto = i;
      
      formSolicitud.innerHTML = "";
      
      t.requerimientos.forEach((req, idx) => {
        const fieldGroup = document.createElement("div");
        fieldGroup.style.opacity = "0";
        fieldGroup.style.transform = "translateY(10px)";
        
        const label = document.createElement("label");
        label.innerHTML = `<i class="fa-solid fa-chevron-right" style="font-size: 1.2rem; color: var(--secondary-color);"></i> ${req.label}`;
        
        let input;
        if (req.tipo === "file") {
          input = document.createElement("input");
          input.type = "file";
        } else if (req.tipo === "number") {
          input = document.createElement("input");
          input.type = "number";
        } else {
          input = document.createElement("input");
          input.type = "text";
        }
        
        input.required = true;
        input.name = req.label.toLowerCase().replace(/\s+/g, '_');
        input.placeholder = `Ingresa ${req.label.toLowerCase()}`;
        
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

      formSolicitud.onsubmit = (e) => {
        e.preventDefault();
        mostrarNotificacion(`Solicitud enviada correctamente para: ${t.nombre}`, "success");
        wrapper.classList.remove("active");
        btn.classList.remove("active");
        formAbierto = null;
        formSolicitud.reset();
      };

      setTimeout(() => {
        wrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }, 100);
    } else {
      formAbierto = null;
    }
  }

  function editar(e) {
    if (!form || !panel || !formTitulo || !listaRequerimientos) return;

    const i = e.target.closest("button").dataset.i;
    const t = tramites[i];
    indexEditar.value = i;
    formTitulo.textContent = "Editar trámite";
    form.nombre.value = t.nombre;
    form.descripcion.value = t.descripcion;
    form.fecha_inicio.value = t.inicio;
    form.fecha_corte.value = t.corte;

    requerimientosTemp = [...t.requerimientos];
    renderRequerimientosTemp();
    panel.classList.add("active");
    
    if (adminPanel && adminPanel.classList.contains("active")) {
      adminPanel.classList.remove("active");
      document.querySelector(".admin-toggle")?.classList.remove("active");
      document.querySelector(".admin-overlay")?.classList.remove("active");
      document.body.style.overflow = "";
    }
  }

  function eliminar(e) {
    const i = e.target.closest("button").dataset.i;
    const t = tramites[i];
    
    if (confirm(`¿Eliminar el trámite "${t.nombre}"?`)) {
      tramites.splice(i, 1);
      localStorage.setItem("tramites", JSON.stringify(tramites));
      renderTramites();
      mostrarNotificacion("Trámite eliminado correctamente", "error");
    }
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

  renderTramites();
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
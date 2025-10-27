document.addEventListener("DOMContentLoaded", () => {
  const btnAgregar = document.getElementById("btn-agregar");
  const panel = document.getElementById("formulario-panel");
  const btnCerrar = document.getElementById("btn-cerrar");
  const form = document.getElementById("form-tramite");
  const lista = document.getElementById("lista-tramites");
  const formTitulo = document.getElementById("form-titulo");
  const indexEditar = document.getElementById("index-editar");

  const listaRequerimientos = document.getElementById("lista-requerimientos");
  const nuevoLabel = document.getElementById("nuevo-label");
  const nuevoTipo = document.getElementById("nuevo-tipo");
  const btnAddReq = document.getElementById("btn-add-requerimiento");

  let tramites = JSON.parse(localStorage.getItem("tramites")) || [];
  let requerimientosTemp = [];
  let formAbierto = null; // Track which form is currently open

  // Función para renderizar los requerimientos temporales
  function renderRequerimientosTemp() {
    listaRequerimientos.innerHTML = "";
    requerimientosTemp.forEach((req, index) => {
      agregarRequerimientoAlDOM(req, index);
    });
  }

  // Función para agregar un requerimiento al DOM con edición
  function agregarRequerimientoAlDOM(req, index) {
    const div = document.createElement("div");
    div.classList.add("requerimiento-item");
    
    // Contenedor de visualización
    const spanContainer = document.createElement("span"); 
    spanContainer.classList.add("requerimiento-display");
    spanContainer.textContent = `${req.label} (${req.tipo})`;
    
    // Botón de eliminar
    const btnEliminarReq = document.createElement("button");
    btnEliminarReq.textContent = "x";
    btnEliminarReq.type = "button";
    btnEliminarReq.classList.add("btn-eliminar-req");
    
    // Evento para eliminar
    btnEliminarReq.addEventListener("click", () => {
      requerimientosTemp.splice(index, 1);
      renderRequerimientosTemp(); 
    });

    div.append(spanContainer, btnEliminarReq);
    listaRequerimientos.appendChild(div);

    // Click en el span para editar
    spanContainer.addEventListener("click", () => {
      // Crear elementos de edición
      const inputLabel = document.createElement('input');
      inputLabel.type = 'text';
      inputLabel.value = req.label;
      inputLabel.classList.add('req-input-edit');

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

            requerimientosTemp[index].label = newLabel;
            requerimientosTemp[index].tipo = newTipo;
            
            renderRequerimientosTemp();
          }
        }, 10);
      };

      inputLabel.addEventListener('blur', handleSave);
      selectTipo.addEventListener('blur', handleSave); 
      
      inputLabel.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          const newLabel = inputLabel.value.trim();
          const newTipo = selectTipo.value;
          requerimientosTemp[index].label = newLabel;
          requerimientosTemp[index].tipo = newTipo;
          renderRequerimientosTemp();
        }
      });

      inputLabel.focus();
    });
  }

  if(btnAgregar){
    btnAgregar.addEventListener("click", () => {
      form.reset();
      indexEditar.value = "";
      formTitulo.textContent = "Agregar nuevo trámite";
      listaRequerimientos.innerHTML = "";
      requerimientosTemp = [];
      panel.classList.add("active");
    });
  }

  if(btnCerrar){
    btnCerrar.addEventListener("click", () => {
      panel.classList.remove("active");
    });
  }

  if(btnAddReq){
    btnAddReq.addEventListener("click", () => {
      const label = nuevoLabel.value.trim();
      const tipo = nuevoTipo.value;
      if (!label) return;

      const req = { label, tipo };
      requerimientosTemp.push(req);

      renderRequerimientosTemp();

      nuevoLabel.value = "";
    });
  }

  if(form){
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      const data = {
        nombre: form.nombre.value,
        descripcion: form.descripcion.value,
        inicio: form.fecha_inicio.value,
        corte: form.fecha_corte.value,
        requerimientos: requerimientosTemp
      };

      const index = indexEditar.value;
      if (index === "") tramites.push(data);
      else tramites[index] = data;

      localStorage.setItem("tramites", JSON.stringify(tramites));
      renderTramites();
      panel.classList.remove("active");
      form.reset();
    });
  }

  function renderTramites() {
    lista.innerHTML = "";
    if (tramites.length === 0) {
      lista.innerHTML = `<p class="sin-tramites">No hay trámites registrados.</p>`;
      return;
    }

    tramites.forEach((t, i) => {
      const card = document.createElement("div");
      card.classList.add("tramite-card");

      let adminButtons = '';

      if(typeof CAN_EDIT_CARDS !== 'undefined' && CAN_EDIT_CARDS){
        adminButtons = `
          <div class="acciones-admin">
            <button class="btn-editar" data-i="${i}">
              <i class="fa-solid fa-pen"></i> Editar
            </button>
            <button class="btn-eliminar" data-i="${i}">
              <i class="fa-solid fa-trash"></i> Eliminar
            </button>
          </div>
        `;
      }

      card.innerHTML = `
        <div class="tramite-header">
          <div class="tramite-info">
            <h3><i class="fa-solid fa-file-pen"></i> ${t.nombre}</h3>
            <p><strong>Descripción:</strong> ${t.descripcion}</p>
            <p><strong>Inicio:</strong> ${t.inicio} | <strong>Corte:</strong> ${t.corte}</p>
          </div>
          <div class="tramite-actions">
            ${adminButtons}
            <button class="btn-solicitar" data-i="${i}">
              <i class="fa-solid fa-file-signature"></i> Solicitar
            </button>
          </div>
        </div>
        <div class="form-solicitud-wrapper" id="form-wrapper-${i}">
          <div class="form-solicitud-content">
            <h3><i class="fa-solid fa-clipboard-list"></i> Completar solicitud</h3>
            <form class="form-solicitud" id="form-solicitud-${i}">
              <!-- Los campos se generarán dinámicamente -->
            </form>
          </div>
        </div>
      `;
      
      lista.appendChild(card);
    });

    // Add event listeners
    document.querySelectorAll(".btn-solicitar").forEach(b => b.onclick = toggleFormulario);

    if(typeof CAN_EDIT_CARDS !== 'undefined' && CAN_EDIT_CARDS){
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

    // Close any other open form
    if (formAbierto !== null && formAbierto !== i) {
      const prevWrapper = document.getElementById(`form-wrapper-${formAbierto}`);
      const prevBtn = document.querySelector(`.btn-solicitar[data-i="${formAbierto}"]`);
      if (prevWrapper) prevWrapper.classList.remove("active");
      if (prevBtn) prevBtn.classList.remove("active");
    }

    // Toggle current form
    const isActive = wrapper.classList.toggle("active");
    btn.classList.toggle("active");

    if (isActive) {
      formAbierto = i;
      
      // Generate form fields
      formSolicitud.innerHTML = "";
      
      t.requerimientos.forEach(req => {
        const fieldGroup = document.createElement("div");
        
        const label = document.createElement("label");
        label.textContent = req.label;
        
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
        
        fieldGroup.appendChild(label);
        fieldGroup.appendChild(input);
        formSolicitud.appendChild(fieldGroup);
      });

      const btnEnviar = document.createElement("button");
      btnEnviar.type = "submit";
      btnEnviar.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Enviar solicitud';
      formSolicitud.appendChild(btnEnviar);

      // Add submit handler
      formSolicitud.onsubmit = (e) => {
        e.preventDefault();
        alert("Solicitud enviada correctamente para: " + t.nombre);
        wrapper.classList.remove("active");
        btn.classList.remove("active");
        formAbierto = null;
        formSolicitud.reset();
      };

      // Scroll to form
      setTimeout(() => {
        wrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }, 100);
    } else {
      formAbierto = null;
    }
  }

  function editar(e) {
    if(!form || !panel || !formTitulo || !listaRequerimientos) return;

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
  }

  function eliminar(e) {
    const i = e.target.closest("button").dataset.i;
    if (confirm("¿Eliminar este trámite?")) {
      tramites.splice(i, 1);
      localStorage.setItem("tramites", JSON.stringify(tramites));
      renderTramites();
    }
  }

  renderTramites();
});
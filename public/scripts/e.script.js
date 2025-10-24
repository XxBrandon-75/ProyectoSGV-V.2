document.addEventListener("DOMContentLoaded", () => {
  const btnAgregar = document.getElementById("btn-agregar");
  const panel = document.getElementById("formulario-panel");
  const btnCerrar = document.getElementById("btn-cerrar");
  const form = document.getElementById("form-especialidad");
  const lista = document.getElementById("lista-especialidades");
  const formTitulo = document.getElementById("form-titulo");
  const indexEditar = document.getElementById("index-editar");
  const inputImagen = document.getElementById("imagen");
  let imagenTemp = "";

  const listaRequerimientos = document.getElementById("lista-requerimientos");
  const nuevoLabel = document.getElementById("nuevo-label");
  const nuevoTipo = document.getElementById("nuevo-tipo");
  const btnAddReq = document.getElementById("btn-add-requerimiento");

  const modal = document.getElementById("modal-inscripcion");
  const cerrarModal = document.getElementById("cerrar-modal");
  const formInscripcion = document.getElementById("form-inscripcion");

  let especialidades = JSON.parse(localStorage.getItem("especialidades")) || [];
  let requerimientosTemp = [];

  function renderRequerimientosTemp() {
    listaRequerimientos.innerHTML = "";
    requerimientosTemp.forEach((req, index) => {
        agregarRequerimientoAlDOM(req, index);
    });
  }

  function agregarRequerimientoAlDOM(req, index) {
    const div = document.createElement("div");
    div.classList.add("requerimiento-item");
    
    // 1. Variable corregida y usada como contenedor de visualización
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

    spanContainer.addEventListener("click", () => {
        // Crear elementos de edición (Input para Label y Select para Tipo)
        const inputLabel = document.createElement('input');
        inputLabel.type = 'text';
        inputLabel.value = req.label;
        inputLabel.classList.add('req-input-edit');

        const selectTipo = document.createElement('select');
        selectTipo.classList.add('req-select-edit');
        const tipos = [{val: 'text', text: 'Texto'}, {val: 'number', text: 'Número'}, {val: 'file', text: 'Archivo'}];
        tipos.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.val;
            opt.textContent = t.text;
            if (t.val === req.tipo) opt.selected = true;
            selectTipo.appendChild(opt);
        });

        div.replaceChild(inputLabel, spanContainer); // Reemplaza el span por el input
        div.insertBefore(selectTipo, inputLabel.nextSibling); // Inserta el select después del input

        btnEliminarReq.style.display = 'none';

const handleSave = () => {
            // Agregamos un pequeño retraso para permitir que el foco cambie 
            // del input al select, o viceversa, sin que se re-renderice.
            setTimeout(() => {
                // Comprobamos si el foco NO está en ninguno de los dos campos de edición
                if (document.activeElement !== inputLabel && document.activeElement !== selectTipo) {
                    const newLabel = inputLabel.value.trim();
                    const newTipo = selectTipo.value;

                    // Actualizar el array temporal
                    requerimientosTemp[index].label = newLabel;
                    requerimientosTemp[index].tipo = newTipo;
                    
                    // Re-renderizar
                    renderRequerimientosTemp();
                }
            }, 10); // 10ms es suficiente para que el navegador resuelva el cambio de foco
        };

        // Removemos el listener 'mousedown' problemático
        
        // El guardado ocurre al perder el foco en inputLabel
        inputLabel.addEventListener('blur', handleSave);
        
        // El guardado ocurre al perder el foco en selectTipo
        selectTipo.addEventListener('blur', handleSave); 
        
        // Opcional: Permitir guardar con Enter en el input de etiqueta
        inputLabel.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                // Guardar inmediatamente sin esperar el blur
                const newLabel = inputLabel.value.trim();
                const newTipo = selectTipo.value;
                requerimientosTemp[index].label = newLabel;
                requerimientosTemp[index].tipo = newTipo;
                renderRequerimientosTemp();
            }
        });

        // Enfocar el input de la etiqueta al inicio de la edición
        inputLabel.focus();
    });
  }

  function renderRequerimientosTemp() {
    listaRequerimientos.innerHTML = "";
    requerimientosTemp.forEach((req, index) => {
      agregarRequerimientoAlDOM(req, index);
    });
  }

  if(btnAgregar){
    btnAgregar.addEventListener("click", () => {
      form.reset();
      indexEditar.value = "";
      formTitulo.textContent = "Agregar nueva especialidad";
      listaRequerimientos.innerHTML = "";
      requerimientosTemp = [];
      panel.classList.add("active");
    });
  }
  if(btnCerrar){
    btnCerrar.addEventListener("click", () => {
      panel.classList.remove("active");
      form.reset();
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
        apertura: form.fecha_apertura.value,
        cierre: form.fecha_cierre.value,
        requerimientos: requerimientosTemp,
        imagen: imagenTemp || (especialidades[indexEditar.value]?.imagen ?? "")
      };

      const index = indexEditar.value;
      if (index === "") especialidades.push(data);
      else especialidades[index] = data;

      localStorage.setItem("especialidades", JSON.stringify(especialidades));
      renderEspecialidades();
      panel.classList.remove("active");
      form.reset();
    });
  }

  if (inputImagen) {
  inputImagen.addEventListener("change", (e) => {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = () => {
        imagenTemp = reader.result; // Guarda la imagen en base64 temporalmente
      };
      reader.readAsDataURL(file);
    }
  });
}

// abrir / cerrar modal con funciones (mejor para mantener todo consistente)
function abrirModal() {
  modal.style.display = "flex";
}

function cerrarModalFn() {
  modal.style.display = "none";
}

if (cerrarModal) {
  cerrarModal.addEventListener("click", (e) => {
    e.preventDefault(); // evita que se envíe el formulario
    cerrarModalFn();
  });
}

  
  function renderEspecialidades() {
    lista.innerHTML = "";
    if (especialidades.length === 0) {
      lista.innerHTML = `<p class="sin-cursos">No hay especialidades registradas.</p>`;
      return;
    }

    especialidades.forEach((esp, i) => {
      const card = document.createElement("div");
      card.classList.add("curso-card");

      let adminButtons = '';

      if(typeof CAN_EDIT_CARDS !== 'undefined' && CAN_EDIT_CARDS){
        adminButtons= `
          <button class="btn-editar" data-i="${i}"><i class = "fa-solid fa-pen"></i> Editar</button>
          <button class="btn-eliminar" data-i="${i}"><i class = "fa-solid fa-trash"></i> Eliminar</button>
        `;
      }
      const imgSrc = esp.imagen && esp.imagen !== "" 
      ? esp.imagen 
      : "https://www.fundacionaleatica.org/wp-content/uploads/2023/11/405057247_297858453219975_5321687550568629282_n.jpg";
      card.innerHTML = `
      <div class="card-imagen">
        <img src="${imgSrc}" alt="${esp.nombre}">
      </div>
      <div class="card-info">
        <h3><i class="fa-solid fa-hands-holding-child"></i>${esp.nombre}</h3>
        <p><strong>Descripción:</strong> ${esp.descripcion}</p>
        <p><strong>Apertura:</strong> ${esp.apertura}</p>
        <p><strong>Cierre:</strong> ${esp.cierre}</p>
        <div class="acciones-card">
          ${adminButtons}
          <button class="btn-inscribirse" data-i="${i}">
            <i class="fa-solid fa-user-plus"></i> Inscribirse
          </button>
        </div>
      </div>
    `;
      lista.appendChild(card);
    });
    document.querySelectorAll(".btn-inscribirse").forEach(b => b.onclick = e => inscribirse(e));

    if(typeof CAN_EDIT_CARDS !== 'undefined' && CAN_EDIT_CARDS){
        document.querySelectorAll(".btn-eliminar").forEach(b => b.onclick = e => eliminar(e));
        document.querySelectorAll(".btn-editar").forEach(b => b.onclick = e => editar(e));
    }
  }

  function editar(e) {
    if(!form || !panel || !formTitulo || !listaRequerimientos) return;

    const i = e.target.closest("button").dataset.i;
    const esp = especialidades[i];
    indexEditar.value = i;
    formTitulo.textContent = "Editar especialidad";
    form.nombre.value = esp.nombre;
    form.descripcion.value = esp.descripcion;
    form.fecha_apertura.value = esp.apertura;
    form.fecha_cierre.value = esp.cierre;

    requerimientosTemp = [...esp.requerimientos];
    
    renderRequerimientosTemp();

    imagenTemp = esp.imagen || "";
    panel.classList.add("active");
  }

  function eliminar(e) {
    const i = e.target.closest("button").dataset.i;
    if (confirm("¿Eliminar especialidad?")) {
      especialidades.splice(i, 1);
      localStorage.setItem("especialidades", JSON.stringify(especialidades));
      renderEspecialidades();
    }
  }

  
  function inscribirse(e) {
    const i = e.target.closest("button").dataset.i;
    const esp = especialidades[i];

    document.getElementById("titulo-inscripcion").textContent = esp.nombre;
    document.getElementById("descripcion-inscripcion").textContent = esp.descripcion;
    document.getElementById("inicio-inscripcion").textContent = esp.apertura;
    document.getElementById("cierre-inscripcion").textContent = esp.cierre;

    formInscripcion.innerHTML = "";
    esp.requerimientos.forEach(req => {
      const label = document.createElement("label");
      label.textContent = req.label;
      const input = document.createElement("input");
      input.type = req.tipo;
      input.required = true;
      formInscripcion.append(label, input);
    });

    const btnEnviar = document.createElement("button");
    btnEnviar.textContent = "Enviar inscripción";
    formInscripcion.appendChild(btnEnviar);

    abrirModal();
  }
  renderEspecialidades();
});

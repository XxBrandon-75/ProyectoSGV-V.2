document.addEventListener("DOMContentLoaded", () => {
  const btnAgregar = document.getElementById("btn-agregar");
  const panel = document.getElementById("formulario-panel");
  const btnCerrar = document.getElementById("btn-cerrar");
  const form = document.getElementById("form-especialidad");
  const lista = document.getElementById("lista-especialidades");
  const formTitulo = document.getElementById("form-titulo");
  const indexEditar = document.getElementById("index-editar");

  const listaRequerimientos = document.getElementById("lista-requerimientos");
  const nuevoLabel = document.getElementById("nuevo-label");
  const nuevoTipo = document.getElementById("nuevo-tipo");
  const btnAddReq = document.getElementById("btn-add-requerimiento");

  const modal = document.getElementById("modal-inscripcion");
  const cerrarModal = document.getElementById("cerrar-modal");
  const formInscripcion = document.getElementById("form-inscripcion");

  let especialidades = JSON.parse(localStorage.getItem("especialidades")) || [];
  let requerimientosTemp = [];

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

      const div = document.createElement("div");
      div.textContent = `${label} (${tipo})`;
      listaRequerimientos.appendChild(div);

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
        requerimientos: requerimientosTemp
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
      card.innerHTML = `
        <h3><i class="fa-solid fa-hands-holding-child"></i>${esp.nombre}</h3>
        <p><strong>Descripción:</strong> ${esp.descripcion}</p>
        <p><strong>Apertura:</strong> ${esp.apertura}</p>
        <p><strong>Cierre:</strong> ${esp.cierre}</p>
        <div class="acciones-card">
          ${adminButtons}
          <button class="btn-inscribirse" data-i="${i}"><i class="fa-solid fa-user-plus"></i> Inscribirse</button>
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
    listaRequerimientos.innerHTML = "";
    esp.requerimientos.forEach(r => {
      const div = document.createElement("div");
      div.textContent = `${r.label} (${r.tipo})`;
      listaRequerimientos.appendChild(div);
    });

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

    modal.style.display = "flex";
  }

  cerrarModal.onclick = () => (modal.style.display = "none");
  modal.onclick = e => { if (e.target === modal) modal.style.display = "none"; };

  renderEspecialidades();
});

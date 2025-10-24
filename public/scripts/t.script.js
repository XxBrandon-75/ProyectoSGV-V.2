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

  const modal = document.getElementById("modal-solicitud");
  const cerrarModal = document.getElementById("cerrar-modal");
  const formSolicitud = document.getElementById("form-solicitud");

  let tramites = JSON.parse(localStorage.getItem("tramites")) || [];
  let requerimientosTemp = [];

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

    requerimientosTemp.push({ label, tipo });

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

      let adminButtons='';

      if(typeof CAN_EDIT_CARDS !== 'undefined' && CAN_EDIT_CARDS){
        adminButtons=`
          <button class="btn-editar" data-i="${i}"><i class="fa-solid fa-pen"></i> Editar</button>
          <button class="btn-eliminar" data-i="${i}"><i class="fa-solid fa-trash"></i> Eliminar</button>
        `;
      }
      card.innerHTML = `
        <h3><i class="fa-solid fa-file-pen"></i> ${t.nombre}</h3>
        <p><strong>Descripción:</strong> ${t.descripcion}</p>
        <p><strong>Inicio:</strong> ${t.inicio}</p>
        <p><strong>Corte:</strong> ${t.corte}</p>
        <div class="acciones-card">
          ${adminButtons}
          <button class="btn-solicitar" data-i="${i}"><i class="fa-solid fa-file-signature"></i> Solicitar</button>
        </div>
      `;
      lista.appendChild(card);
    });
    document.querySelectorAll(".btn-solicitar").forEach(b => b.onclick = solicitar);

    if(typeof CAN_EDIT_CARDS !== 'undefined' && CAN_EDIT_CARDS){
      document.querySelectorAll(".btn-eliminar").forEach(b => b.onclick = eliminar);
      document.querySelectorAll(".btn-editar").forEach(b => b.onclick = editar);
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
    listaRequerimientos.innerHTML = "";
    t.requerimientos.forEach(r => {
      const div = document.createElement("div");
      div.textContent = `${r.label} (${r.tipo})`;
      listaRequerimientos.appendChild(div);
    });

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

  function solicitar(e) {
    const i = e.target.closest("button").dataset.i;
    const t = tramites[i];

    document.getElementById("titulo-solicitud").textContent = t.nombre;
    document.getElementById("descripcion-solicitud").textContent = t.descripcion;
    document.getElementById("inicio-solicitud").textContent = t.inicio;
    document.getElementById("corte-solicitud").textContent = t.corte;

    formSolicitud.innerHTML = "";
    t.requerimientos.forEach(req => {
      const label = document.createElement("label");
      label.textContent = req.label;
      const input = document.createElement("input");
      input.type = req.tipo;
      input.required = true;
      formSolicitud.append(label, input);
    });

    const btnEnviar = document.createElement("button");
    btnEnviar.textContent = "Enviar solicitud";
    formSolicitud.appendChild(btnEnviar);

    modal.style.display = "flex";
  }

  cerrarModal.onclick = () => (modal.style.display = "none");
  modal.onclick = (e) => { if (e.target === modal) modal.style.display = "none"; };

  renderTramites();
});

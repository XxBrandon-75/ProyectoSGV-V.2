document.addEventListener("DOMContentLoaded", () => {
document.addEventListener("DOMContentLoaded", () => {
  const perfilContenido = document.getElementById("perfil-contenido");
  const rolUsuario = "coordinador"; // Cambia a "coordinador" o "voluntario" para probar

  const usuario = {
    nombre: "Alejandro Morales",
    apellidos: "Gómez López",
    status: "Activo",
    matricula: "ADM-001",
    edad: 32,
    nacimiento: "1993-09-22",
    nacionalidad: "Mexicana",
    curp: "MGLA930922HDFPMN08",
    domicilio: "Av. Universidad #45, CDMX",
    sangre: "A+",
    especialidad: "Gestión de Voluntariados",
  };

  const coordinadores = [
    { nombre: "Carlos Herrera", zona: "Norte", voluntarios: ["Lucía Ramírez", "Juan Pérez"] },
    { nombre: "Andrea Soto", zona: "Sur", voluntarios: ["David Torres", "Ana López"] }
  ];

  renderPerfil();

  document.querySelectorAll(".perfil-menu li").forEach(li => {
    li.addEventListener("click", () => {
      document.querySelectorAll(".perfil-menu li").forEach(e => e.classList.remove("activo"));
      li.classList.add("activo");
      const section = li.dataset.section;

      if (section === "perfil") renderPerfil();
      if (section === "solicitudes") renderSolicitudes();
      if (section === "cargo") renderCargo();
      if (section === "coordinadores") renderCoordinadores();
    });
  });

  function renderPerfil() {
    perfilContenido.innerHTML = `
      <h2 style="color:var(--color);margin-bottom:1rem;">Mi perfil</h2>
      <form class="perfil-formulario">
        <div><label>Nombre</label><input value="${usuario.nombre}" readonly></div>
        <div><label>Apellidos</label><input value="${usuario.apellidos}" readonly></div>
        <div><label>Status</label><input value="${usuario.status}" readonly></div>
        <div><label>Identificación</label><input value="${usuario.matricula}" readonly></div>
        <div><label>Edad</label><input value="${usuario.edad}" readonly></div>
        <div><label>Fecha de nacimiento</label><input value="${usuario.nacimiento}" readonly></div>
        <div><label>Nacionalidad</label><input value="${usuario.nacionalidad}" readonly></div>
        <div><label>CURP</label><input value="${usuario.curp}" readonly></div>
        <div><label>Domicilio</label><input value="${usuario.domicilio}" readonly></div>
        <div><label>Tipo de sangre</label><input value="${usuario.sangre}" readonly></div>
        <div><label>Especialidad</label><input value="${usuario.especialidad}" readonly></div>
      </form>
      <button id="btn-editar" class="btn-editar">Editar perfil</button>
    `;

    document.getElementById("btn-editar").addEventListener("click", abrirModal);
  }

  function renderSolicitudes() {
    perfilContenido.innerHTML = `
      <div class="solicitudes-lista">
        <h2>Mis solicitudes</h2>
        <ul>
          <li>Trámite de acreditación - En proceso</li>
          <li>Especialidad en Apoyo Médico - Aprobado</li>
        </ul>
      </div>
    `;
  }

  function renderCargo() {
    perfilContenido.innerHTML = `
    <div class="cargo-lista">
      <h2>Voluntariado a mi cargo</h2>
      <input type="text" id="buscar-voluntario" placeholder="Buscar voluntario..." class="input-busqueda"/>
      <ul id="lista-voluntarios">
        <li>Lucía Ramírez - Apoyo Comunitario</li>
        <li>Juan Pérez - Primeros Auxilios</li>
        <li>Roberto Díaz - Logística</li>
        <li>Daniela Soto - Atención Médica</li>
      </ul>
    </div>
    `;

      const input = document.getElementById("buscar-voluntario");
  input.addEventListener("input", () => {
    const filtro = input.value.toLowerCase();
    document.querySelectorAll("#lista-voluntarios li").forEach(li => {
      li.style.display = li.textContent.toLowerCase().includes(filtro) ? "block" : "none";
    });
  });

}

  function renderCoordinadores() {
  perfilContenido.innerHTML = `
    <div class="coordinadores-lista">
      <h2>Coordinadores</h2>
      <input type="text" id="buscar-coordinador" placeholder="Buscar coordinador..." class="input-busqueda"/>
      <div id="lista-coordinadores">
        ${coordinadores.map(c => `
          <div class="coordinador-card">
            <h3>${c.nombre} (${c.zona})</h3>
            <ul>${c.voluntarios.map(v => `<li>${v}</li>`).join('')}</ul>
          </div>
        `).join('')}
      </div>
    </div>
    `;
        const input = document.getElementById("buscar-coordinador");
  input.addEventListener("input", () => {
    const filtro = input.value.toLowerCase();
    document.querySelectorAll(".coordinador-card").forEach(card => {
      card.style.display = card.textContent.toLowerCase().includes(filtro) ? "block" : "none";
    });
  });
  }

 
  const modal = document.getElementById("modal-editar");
  const cerrarModal = document.getElementById("cerrar-modal");
  const formEditar = document.getElementById("form-editar");

  function abrirModal() {
    modal.style.display = "flex";
    document.getElementById("edit-nombre").value = usuario.nombre;
    document.getElementById("edit-apellidos").value = usuario.apellidos;
    document.getElementById("edit-domicilio").value = usuario.domicilio;
    document.getElementById("edit-sangre").value = usuario.sangre;
  }

  cerrarModal.addEventListener("click", () => modal.style.display = "none");
  modal.addEventListener("click", e => {
    if (e.target === modal) modal.style.display = "none";
  });

  formEditar.addEventListener("submit", (e) => {
    e.preventDefault();
    usuario.nombre = document.getElementById("edit-nombre").value;
    usuario.apellidos = document.getElementById("edit-apellidos").value;
    usuario.domicilio = document.getElementById("edit-domicilio").value;
    usuario.sangre = document.getElementById("edit-sangre").value;
    modal.style.display = "none";
    renderPerfil();
  });
});
});
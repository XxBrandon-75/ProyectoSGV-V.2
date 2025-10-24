document.addEventListener("DOMContentLoaded", () => {
  const contenedor = document.getElementById("notificaciones-container");

  // Simulamos notificaciones guardadas en localStorage
  const notificaciones = JSON.parse(localStorage.getItem("notificaciones")) || [
    {
      titulo: "Trámite aprobado",
      cuerpo: "El trámite para Solicitar credencial de voluntariado ha sido aprobado.",
      linkTexto: "Ver apartado",
      link: "tramites.html"
    },
    {
      titulo: "Nueva especialidad disponible",
      cuerpo: "Se ha habilitado la especialidad 'Apoyo en emergencias'.",
      linkTexto: "Ir a especialidades",
      link: "especialidades.html"
    },
    {
      titulo: "Documento actualizado",
      cuerpo: "Se ha añadido el nuevo reglamento de seguridad y operación.",
      linkTexto: "Ver documento",
      link: "documentacion.html"
    }
  ];

  function renderNotificaciones() {
    contenedor.innerHTML = "";
    if (notificaciones.length === 0) {
      contenedor.innerHTML = `<p style="text-align:center;font-size:1.6rem;">No tienes notificaciones pendientes.</p>`;
      return;
    }

    notificaciones.forEach(n => {
      const card = document.createElement("div");
      card.classList.add("notificacion-card");
      card.innerHTML = `
        <h3><i class="fa-solid fa-bell"></i> ${n.titulo}</h3>
        <p>${n.cuerpo}</p>
        <a href="${n.link}">${n.linkTexto}</a>
      `;
      contenedor.appendChild(card);
    });
  }

  renderNotificaciones();
});

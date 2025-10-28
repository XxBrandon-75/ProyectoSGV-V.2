document.addEventListener("DOMContentLoaded", () => {
  // Funcionalidad de filtros
  const filtrosBtns = document.querySelectorAll(".filtro-btn");

  filtrosBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      // Remover clase activa de todos los botones
      filtrosBtns.forEach((b) => b.classList.remove("activo"));
      // Agregar clase activa al botón clickeado
      btn.classList.add("activo");

      const filtro = btn.getAttribute("data-filtro");
      aplicarFiltro(filtro);
    });
  });

  function aplicarFiltro(filtro) {
    console.log("Filtro aplicado:", filtro);
    // TODO: Implementar lógica de filtrado cuando se conecte con backend
    // Por ahora solo muestra un mensaje en consola
  }
});

// Funciones para las acciones de notificaciones
function aprobarVoluntario(id) {
  // Mostrar confirmación
  if (confirm("¿Estás seguro de aprobar este voluntario?")) {
    // TODO: Implementar llamada AJAX para aprobar
    console.log("Aprobando voluntario:", id);

    // Animación de éxito (temporal)
    const card = document.querySelector(`.notificacion-card[data-id="${id}"]`);
    if (card) {
      card.style.background = "#d4edda";
      card.style.borderLeftColor = "#28a745";

      setTimeout(() => {
        card.style.opacity = "0";
        card.style.transform = "translateX(100%)";

        setTimeout(() => {
          card.remove();
          actualizarContadores();
        }, 300);
      }, 1000);
    }

    // Mensaje de éxito
    mostrarMensaje("Voluntario aprobado exitosamente", "success");
  }
}

function rechazarVoluntario(id) {
  const motivo = prompt("¿Por qué rechazas esta solicitud? (opcional)");

  if (motivo !== null) {
    // TODO: Implementar llamada AJAX para rechazar
    console.log("Rechazando voluntario:", id, "Motivo:", motivo);

    // Animación de rechazo (temporal)
    const card = document.querySelector(`.notificacion-card[data-id="${id}"]`);
    if (card) {
      card.style.background = "#f8d7da";
      card.style.borderLeftColor = "#dc3545";

      setTimeout(() => {
        card.style.opacity = "0";
        card.style.transform = "translateX(-100%)";

        setTimeout(() => {
          card.remove();
          actualizarContadores();
        }, 300);
      }, 1000);
    }

    // Mensaje de rechazo
    mostrarMensaje("Solicitud rechazada", "error");
  }
}

function verDetalles(id) {
  // TODO: Abrir modal con detalles completos del voluntario
  console.log("Viendo detalles del voluntario:", id);
  alert("Por implementar cuando se conecte con el backend");
}

function actualizarContadores() {
  // Actualizar el badge de pendientes
  const pendientesCards = document.querySelectorAll(
    ".notificacion-card.pendiente"
  );
  const badgeContador = document.querySelector(".badge-contador");
  const badgePendientes = document.querySelector(".badge-pendientes");

  if (badgeContador) {
    badgeContador.textContent = pendientesCards.length;
    if (pendientesCards.length === 0) {
      badgeContador.style.display = "none";
    }
  }

  if (badgePendientes) {
    badgePendientes.textContent = pendientesCards.length;
    if (pendientesCards.length === 0) {
      // Mostrar mensaje de que no hay pendientes
      const seccionPendientes = document.querySelector(".pendientes-seccion");
      if (seccionPendientes) {
        seccionPendientes.innerHTML = `
          <h3 class="seccion-titulo">
            <i class="fa-solid fa-check-circle"></i> 
            No hay voluntarios pendientes de aprobación
          </h3>
          <div class="mensaje-vacio">
            <i class="fa-solid fa-user-check"></i>
            <p>Todos los voluntarios han sido revisados.</p>
          </div>
        `;
      }
    }
  }
}

function mostrarMensaje(mensaje, tipo) {
  // Crear elemento de mensaje temporal
  const mensajeDiv = document.createElement("div");
  mensajeDiv.style.cssText = `
    position: fixed;
    top: 100px;
    right: 20px;
    padding: 1.5rem 2rem;
    background: ${tipo === "success" ? "#4caf50" : "#f44336"};
    color: white;
    border-radius: 8px;
    font-size: 1.4rem;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    z-index: 10000;
    animation: slideIn 0.3s ease;
  `;
  mensajeDiv.innerHTML = `
    <i class="fa-solid fa-${
      tipo === "success" ? "check" : "exclamation"
    }-circle"></i> 
    ${mensaje}
  `;

  document.body.appendChild(mensajeDiv);

  // Remover después de 3 segundos
  setTimeout(() => {
    mensajeDiv.style.animation = "slideOut 0.3s ease";
    setTimeout(() => mensajeDiv.remove(), 300);
  }, 3000);
}

// Agregar estilos de animación
const style = document.createElement("style");
style.textContent = `
  @keyframes slideIn {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
  
  @keyframes slideOut {
    from {
      transform: translateX(0);
      opacity: 1;
    }
    to {
      transform: translateX(100%);
      opacity: 0;
    }
  }
  
  .mensaje-vacio {
    text-align: center;
    padding: 4rem 2rem;
    color: #666;
  }
  
  .mensaje-vacio i {
    font-size: 6rem;
    color: #ccc;
    margin-bottom: 2rem;
  }
  
  .mensaje-vacio p {
    font-size: 1.6rem;
    margin: 0;
  }
`;
document.head.appendChild(style);

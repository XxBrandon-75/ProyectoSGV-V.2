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
    // Llamada AJAX para aprobar
    fetch("controllers/NotificacionesAjaxController.php?action=aprobar", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ voluntarioId: id }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Animación de éxito
          const card = document.querySelector(
            `.notificacion-card[data-id="${id}"]`
          );
          if (card) {
            card.style.background = "#d4edda";
            card.style.borderLeftColor = "#28a745";

            setTimeout(() => {
              card.style.opacity = "0";
              card.style.transform = "translateX(100%)";

              setTimeout(() => {
                card.remove();
                actualizarContadores(data.totalPendientes);
              }, 300);
            }, 1000);
          }

          // Mensaje de éxito
          mostrarMensaje(data.message, "success");
        } else {
          mostrarMensaje(data.message || "Error al aprobar", "error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        mostrarMensaje("Error de conexión con el servidor", "error");
      });
  }
}

function rechazarVoluntario(id) {
  // El motivo ahora es OBLIGATORIO según el procedimiento almacenado
  const motivo = prompt("¿Por qué rechazas esta solicitud? (obligatorio)");

  // Validar que el motivo no esté vacío
  if (motivo === null) {
    return; // Usuario canceló
  }
  
  if (motivo.trim() === '') {
    mostrarMensaje("El motivo del rechazo es obligatorio", "error");
    return;
  }

  if (motivo) {
    // Llamada AJAX para rechazar
    fetch("controllers/NotificacionesAjaxController.php?action=rechazar", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ voluntarioId: id, motivo: motivo }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Animación de rechazo
          const card = document.querySelector(
            `.notificacion-card[data-id="${id}"]`
          );
          if (card) {
            card.style.background = "#f8d7da";
            card.style.borderLeftColor = "#dc3545";

            setTimeout(() => {
              card.style.opacity = "0";
              card.style.transform = "translateX(-100%)";

              setTimeout(() => {
                card.remove();
                actualizarContadores(data.totalPendientes);
              }, 300);
            }, 1000);
          }

          // Mensaje de rechazo
          mostrarMensaje(data.message, "error");
        } else {
          mostrarMensaje(data.message || "Error al rechazar", "error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        mostrarMensaje("Error de conexión con el servidor", "error");
      });
  }
}

function verDetalles(id) {
  // Llamada AJAX para obtener detalles
  fetch(`controllers/NotificacionesAjaxController.php?action=detalles&id=${id}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Mostrar modal con los detalles
        mostrarModalDetalles(data.data);
      } else {
        mostrarMensaje(data.message || "Error al obtener detalles", "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarMensaje("Error de conexión con el servidor", "error");
    });
}

function mostrarModalDetalles(voluntario) {
  // Crear modal con los detalles del voluntario
  const modalHTML = `
    <div class="modal-overlay" id="modal-detalles">
      <div class="modal-contenido">
        <div class="modal-header">
          <h3><i class="fa-solid fa-user"></i> Detalles del Voluntario</h3>
          <button class="modal-cerrar" onclick="cerrarModal()">
            <i class="fa-solid fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <div class="detalle-row">
            <strong>Nombre completo:</strong>
            <span>${voluntario.Nombres} ${voluntario.ApellidoPaterno} ${
    voluntario.ApellidoMaterno || ""
  }</span>
          </div>
          <div class="detalle-row">
            <strong>Email:</strong>
            <span>${voluntario.Email}</span>
          </div>
          <div class="detalle-row">
            <strong>Teléfono:</strong>
            <span>${voluntario.Telefono || "No proporcionado"}</span>
          </div>
          <div class="detalle-row">
            <strong>Delegación:</strong>
            <span>${voluntario.DelegacionNombre || "Sin asignar"}</span>
          </div>
          <div class="detalle-row">
            <strong>Área:</strong>
            <span>${voluntario.AreaNombre || "Sin asignar"}</span>
          </div>
          <div class="detalle-row">
            <strong>Estatus:</strong>
            <span class="badge-estatus">${voluntario.EstatusNombre}</span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-accion btn-aprobar" onclick="aprobarVoluntario(${
            voluntario.VoluntarioID
          }); cerrarModal();">
            <i class="fa-solid fa-check"></i> Aprobar
          </button>
          <button class="btn-accion btn-rechazar" onclick="rechazarVoluntario(${
            voluntario.VoluntarioID
          }); cerrarModal();">
            <i class="fa-solid fa-times"></i> Rechazar
          </button>
          <button class="btn-accion" onclick="cerrarModal()" style="background: #6c757d;">
            <i class="fa-solid fa-arrow-left"></i> Cerrar
          </button>
        </div>
      </div>
    </div>
  `;

  // Insertar modal en el DOM
  document.body.insertAdjacentHTML("beforeend", modalHTML);

  // Agregar event listener para cerrar al hacer clic fuera
  document.getElementById("modal-detalles").addEventListener("click", (e) => {
    if (e.target.id === "modal-detalles") {
      cerrarModal();
    }
  });
}

function cerrarModal() {
  const modal = document.getElementById("modal-detalles");
  if (modal) {
    modal.style.opacity = "0";
    setTimeout(() => modal.remove(), 300);
  }
}

function actualizarContadores(totalPendientes) {
  // Actualizar el badge de pendientes
  const badgeContador = document.querySelector(".badge-contador");
  const badgePendientes = document.querySelector(".badge-pendientes");

  if (badgeContador) {
    badgeContador.textContent = totalPendientes;
    if (totalPendientes === 0) {
      badgeContador.style.display = "none";
    }
  }

  if (badgePendientes) {
    badgePendientes.textContent = totalPendientes;
  }

  // Si no hay más pendientes, mostrar mensaje
  const pendientesCards = document.querySelectorAll(
    ".notificacion-card.pendiente"
  );
  if (pendientesCards.length === 0) {
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
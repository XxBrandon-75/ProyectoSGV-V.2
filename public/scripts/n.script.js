document.addEventListener("DOMContentLoaded", () => {
  // IMPORTANTE: Al cargar, asegurarse que todas las secciones estén visibles
  const voluntariosSeccion = document.querySelector(".voluntarios-seccion");
  const tramitesSeccion = document.querySelector(".tramites-seccion");
  const generalesSeccion = document.querySelector(".generales-seccion");
  
  // Mostrar todas las secciones al inicio
  if (voluntariosSeccion) voluntariosSeccion.style.display = "block";
  if (tramitesSeccion) tramitesSeccion.style.display = "block";
  if (generalesSeccion) generalesSeccion.style.display = "block";

  // Funcionalidad de filtros
  const filtrosBtns = document.querySelectorAll(".filtro-btn");

  filtrosBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      filtrosBtns.forEach((b) => b.classList.remove("activo"));
      btn.classList.add("activo");

      const filtro = btn.getAttribute("data-filtro");
      aplicarFiltro(filtro);
    });
  });

  function aplicarFiltro(filtro) {
    // PRIMERO: Mostrar todas las secciones
    if (voluntariosSeccion) voluntariosSeccion.style.display = "block";
    if (tramitesSeccion) tramitesSeccion.style.display = "block";
    if (generalesSeccion) generalesSeccion.style.display = "block";

    // SEGUNDO: Ocultar según el filtro seleccionado
    switch (filtro) {
      case "todas":
        // Ya están todas mostradas, no hacer nada
        break;
      case "voluntarios":
        if (tramitesSeccion) tramitesSeccion.style.display = "none";
        if (generalesSeccion) generalesSeccion.style.display = "none";
        break;
      case "tramites":
        if (voluntariosSeccion) voluntariosSeccion.style.display = "none";
        if (generalesSeccion) generalesSeccion.style.display = "none";
        break;
      case "leidas":
        if (voluntariosSeccion) voluntariosSeccion.style.display = "none";
        if (tramitesSeccion) tramitesSeccion.style.display = "none";
        break;
    }
  }
});

// ====================================================================
// FUNCIONES PARA VOLUNTARIOS
// ====================================================================

function aprobarVoluntario(id) {
  if (confirm("¿Estás seguro de aprobar este voluntario?")) {
    fetch("controllers/NotificacionesAjaxController.php?action=aprobar", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ voluntarioId: id }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          const card = document.querySelector(`.notificacion-card.pendiente[data-id="${id}"]`);
          if (card) {
            card.style.background = "#d4edda";
            card.style.borderLeftColor = "#28a745";

            setTimeout(() => {
              card.style.opacity = "0";
              card.style.transform = "translateX(100%)";
              setTimeout(() => {
                card.remove();
                actualizarContadores(data.totalPendientes, null);
                actualizarBadgeHeaderExterno();
              }, 300);
            }, 1000);
          }
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
  const motivo = prompt("¿Por qué rechazas esta solicitud? (obligatorio)");

  if (motivo === null) return;
  
  if (motivo.trim() === '') {
    mostrarMensaje("El motivo del rechazo es obligatorio", "error");
    return;
  }

  fetch("controllers/NotificacionesAjaxController.php?action=rechazar", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ voluntarioId: id, motivo: motivo }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const card = document.querySelector(`.notificacion-card.pendiente[data-id="${id}"]`);
        if (card) {
          card.style.background = "#f8d7da";
          card.style.borderLeftColor = "#dc3545";

          setTimeout(() => {
            card.style.opacity = "0";
            card.style.transform = "translateX(-100%)";
            setTimeout(() => {
              card.remove();
              actualizarContadores(data.totalPendientes, null);
              actualizarBadgeHeaderExterno();
            }, 300);
          }, 1000);
        }
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

function verDetalles(id) {
  fetch(`controllers/NotificacionesAjaxController.php?action=detalles&id=${id}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
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
            <span>${voluntario.Nombres} ${voluntario.ApellidoPaterno} ${voluntario.ApellidoMaterno || ""}</span>
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
          <button class="btn-accion btn-aprobar" onclick="aprobarVoluntario(${voluntario.VoluntarioID}); cerrarModal();">
            <i class="fa-solid fa-check"></i> Aprobar
          </button>
          <button class="btn-accion btn-rechazar" onclick="rechazarVoluntario(${voluntario.VoluntarioID}); cerrarModal();">
            <i class="fa-solid fa-times"></i> Rechazar
          </button>
          <button class="btn-accion" onclick="cerrarModal()" style="background: #6c757d;">
            <i class="fa-solid fa-arrow-left"></i> Cerrar
          </button>
        </div>
      </div>
    </div>
  `;

  document.body.insertAdjacentHTML("beforeend", modalHTML);
  document.getElementById("modal-detalles").addEventListener("click", (e) => {
    if (e.target.id === "modal-detalles") cerrarModal();
  });
}

// ====================================================================
// NUEVAS FUNCIONES PARA TRÁMITES
// ====================================================================

function aprobarTramite(id) {
  if (confirm("¿Estás seguro de aprobar este trámite?")) {
    fetch("controllers/NotificacionesAjaxController.php?action=aprobar-tramite", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ solicitudId: id }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          const card = document.querySelector(`.tramite-card[data-id="${id}"]`);
          if (card) {
            card.style.background = "#d4edda";
            card.style.borderLeftColor = "#28a745";

            setTimeout(() => {
              card.style.opacity = "0";
              card.style.transform = "translateX(100%)";
              setTimeout(() => {
                card.remove();
                actualizarContadores(null, data.totalTramites);
                actualizarBadgeHeaderExterno();
              }, 300);
            }, 1000);
          }
          mostrarMensaje(data.message, "success");
        } else {
          mostrarMensaje(data.message || "Error al aprobar trámite", "error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        mostrarMensaje("Error de conexión con el servidor", "error");
      });
  }
}

function confirmarAprobacionTramite(id) {
  const observaciones = document.getElementById("observaciones-aprobacion").value || "Trámite aprobado";
  const numeroCredencial = document.getElementById("numero-credencial").value || null;
  const vigenciaCredencial = document.getElementById("vigencia-credencial").value || null;

  fetch("controllers/NotificacionesAjaxController.php?action=aprobar-tramite", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ 
      solicitudId: id, 
      observaciones: observaciones,
      numeroCredencial: numeroCredencial,
      vigenciaCredencial: vigenciaCredencial
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      cerrarModal();
      if (data.success) {
        const card = document.querySelector(`.tramite-card[data-id="${id}"]`);
        if (card) {
          card.style.background = "#d4edda";
          card.style.borderLeftColor = "#28a745";

          setTimeout(() => {
            card.style.opacity = "0";
            card.style.transform = "translateX(100%)";
            setTimeout(() => {
              card.remove();
              actualizarContadores(null, data.totalTramites);
              actualizarBadgeHeaderExterno();
            }, 300);
          }, 1000);
        }
        mostrarMensaje(data.message, "success");
      } else {
        mostrarMensaje(data.message || "Error al aprobar trámite", "error");
      }
    })
    .catch((error) => {
      cerrarModal();
      console.error("Error:", error);
      mostrarMensaje("Error de conexión con el servidor", "error");
    });
}

  function rechazarTramite(id) {
  if (confirm("¿Estás seguro de rechazar este trámite?")) {
    fetch("controllers/NotificacionesAjaxController.php?action=rechazar-tramite", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ solicitudId: id }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          const card = document.querySelector(`.tramite-card[data-id="${id}"]`);
          if (card) {
            card.style.background = "#f8d7da";
            card.style.borderLeftColor = "#dc3545";

            setTimeout(() => {
              card.style.opacity = "0";
              card.style.transform = "translateX(-100%)";
              setTimeout(() => {
                card.remove();
                actualizarContadores(null, data.totalTramites);
                actualizarBadgeHeaderExterno();
              }, 300);
            }, 1000);
          }
          mostrarMensaje(data.message, "error");
        } else {
          mostrarMensaje(data.message || "Error al rechazar trámite", "error");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        mostrarMensaje("Error de conexión con el servidor", "error");
      });
  }
}

function verDetallesTramite(id) {
  fetch(`controllers/NotificacionesAjaxController.php?action=detalles-tramite&id=${id}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        mostrarModalDetallesTramite(data.data, id);
      } else {
        mostrarMensaje(data.message || "Error al obtener detalles", "error");
      }
    })
}

  function mostrarModalDetallesTramite(tramite) {
      let modalHTML = `
          <div class="modal-overlay" id="modal-detalles">
              <div class="modal-contenido">
                  <div class="modal-header">
                      <h3><i class="fa-solid fa-file-contract"></i> Detalles del Trámite</h3>
                      <button class="modal-cerrar" onclick="cerrarModal()">
                          <i class="fa-solid fa-times"></i>
                      </button>
                  </div>
                  <div class="modal-body">
                      <h4>Requerimientos</h4>
                      <div class="requerimientos-lista">
      `;

      tramite.requerimientos.forEach(req => {
          modalHTML += `
              <div class="requerimiento-item">
                  <strong>${req.NombreRequerimiento}</strong>
                  <div class="requerimiento-dato">
          `;

          if (req.TipoDato === 'Texto') {
              modalHTML += `<span>${req.DatoTexto || 'No proporcionado'}</span>`;
          } else if (req.TipoDato === 'Número') {
              modalHTML += `<span>${req.DatoNumero || 'No proporcionado'}</span>`;
          } else if (req.TipoDato === 'Fecha') {
              modalHTML += `<span>${req.DatoFecha || 'No proporcionado'}</span>`;
          } else if (req.TipoDato === 'Archivo') {
              if (req.RutaArchivo) {
                  modalHTML += `<a href="${req.RutaArchivo}" target="_blank">${req.NombreArchivo}</a>`;
              } else {
                  modalHTML += `<span>No se ha subido archivo</span>`;
              }
          }

          modalHTML += `
                  </div>
              </div>
          `;
      });

      modalHTML += `
                      </div>
                  </div>
                  <div class="modal-footer">
                      <button class="btn-accion btn-aprobar" onclick="aprobarTramite(${tramite.SolicitudID}); cerrarModal();">
                          <i class="fa-solid fa-check"></i> Aprobar
                      </button>
                      <button class="btn-accion btn-rechazar" onclick="rechazarTramite(${tramite.SolicitudID}); cerrarModal();">
                          <i class="fa-solid fa-times"></i> Rechazar
                      </button>
                      <button class="btn-accion" onclick="cerrarModal()" style="background: #6c757d;">
                          <i class="fa-solid fa-arrow-left"></i> Cerrar
                      </button>
                  </div>
              </div>
          </div>
      `;

      document.body.insertAdjacentHTML("beforeend", modalHTML);
      document.getElementById("modal-detalles").addEventListener("click", (e) => {
          if (e.target.id === "modal-detalles") cerrarModal();
      });
  }
// ====================================================================
// FUNCIONES COMPARTIDAS
// ====================================================================

function cerrarModal() {
  const modals = document.querySelectorAll(".modal-overlay");
  modals.forEach(modal => {
    modal.style.opacity = "0";
    setTimeout(() => modal.remove(), 300);
  });
}

function actualizarContadores(totalPendientes, totalTramites) {
  const filtroBtnTodas = document.querySelector('[data-filtro="todas"] .badge-contador');
  const filtroBtnVoluntarios = document.querySelector('[data-filtro="voluntarios"] .badge-contador');
  const filtroBtnTramites = document.querySelector('[data-filtro="tramites"] .badge-contador');
  
  const badgePendientes = document.querySelector(".badge-pendientes:not(.badge-tramites)");
  const badgeTramites = document.querySelector(".badge-tramites");

  let voluntariosPendientes = totalPendientes;
  let tramitesPendientes = totalTramites;

  if (voluntariosPendientes === null && badgePendientes) {
    voluntariosPendientes = parseInt(badgePendientes.textContent) || 0;
  }
  if (tramitesPendientes === null && badgeTramites) {
    tramitesPendientes = parseInt(badgeTramites.textContent) || 0;
  }

  const totalGeneral = (voluntariosPendientes || 0) + (tramitesPendientes || 0);

  if (filtroBtnTodas) {
    filtroBtnTodas.textContent = totalGeneral;
    filtroBtnTodas.style.display = totalGeneral > 0 ? 'inline-block' : 'none';
  }

  if (filtroBtnVoluntarios && voluntariosPendientes !== null) {
    filtroBtnVoluntarios.textContent = voluntariosPendientes;
    filtroBtnVoluntarios.style.display = voluntariosPendientes > 0 ? 'inline-block' : 'none';
  }

  if (filtroBtnTramites && tramitesPendientes !== null) {
    filtroBtnTramites.textContent = tramitesPendientes;
    filtroBtnTramites.style.display = tramitesPendientes > 0 ? 'inline-block' : 'none';
  }

  if (badgePendientes && voluntariosPendientes !== null) {
    badgePendientes.textContent = voluntariosPendientes;
  }

  if (badgeTramites && tramitesPendientes !== null) {
    badgeTramites.textContent = tramitesPendientes;
  }

  verificarSeccionesVacias();
}

function verificarSeccionesVacias() {
  const voluntariosCards = document.querySelectorAll(".notificacion-card.pendiente");
  if (voluntariosCards.length === 0) {
    const seccionVoluntarios = document.querySelector(".voluntarios-seccion");
    if (seccionVoluntarios) {
      seccionVoluntarios.innerHTML = `
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

  const tramitesCards = document.querySelectorAll(".tramite-card");
  if (tramitesCards.length === 0) {
    const seccionTramites = document.querySelector(".tramites-seccion");
    if (seccionTramites) {
      seccionTramites.innerHTML = `
        <h3 class="seccion-titulo">
          <i class="fa-solid fa-check-circle"></i> 
          No hay trámites pendientes de validación
        </h3>
        <div class="mensaje-vacio">
          <i class="fa-solid fa-file-check"></i>
          <p>Todos los trámites han sido procesados.</p>
        </div>
      `;
    }
  }
}

function actualizarBadgeHeaderExterno() {
  if (typeof window.refrescarContadorNotificaciones === 'function') {
    window.refrescarContadorNotificaciones();
  }
}

function mostrarMensaje(mensaje, tipo) {
  const mensajeDiv = document.createElement("div");
  mensajeDiv.className = "notificacion-toast";
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
    <i class="fa-solid fa-${tipo === "success" ? "check" : "exclamation"}-circle"></i> 
    ${mensaje}
  `;

  document.body.appendChild(mensajeDiv);

  setTimeout(() => {
    mensajeDiv.style.animation = "slideOut 0.3s ease";
    setTimeout(() => mensajeDiv.remove(), 300);
  }, 3000);
}
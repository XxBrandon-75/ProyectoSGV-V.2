document.addEventListener("DOMContentLoaded", () => {
  const btnAgregar = document.getElementById("btn-agregar");
  const panel = document.getElementById("formulario-panel");
  const btnCerrar = document.getElementById("btn-cerrar");
  const form = document.getElementById("form-documento");
  const lista = document.getElementById("lista-documentos");
  const formTitulo = document.getElementById("form-titulo");
  const indexEditar = document.getElementById("index-editar");
  const adminPanel = document.querySelector(".admin-panel");

  let documentos = JSON.parse(localStorage.getItem("documentos")) || [];

  // Crear botón toggle y overlay para móvil
  if (adminPanel) {
    // Crear botón toggle
    const toggleBtn = document.createElement("button");
    toggleBtn.className = "admin-toggle";
    toggleBtn.innerHTML = '<i class="fa-solid fa-gear"></i>';
    toggleBtn.setAttribute("aria-label", "Toggle admin panel");
    document.body.appendChild(toggleBtn);

    // Crear overlay
    const overlay = document.createElement("div");
    overlay.className = "admin-overlay";
    document.body.appendChild(overlay);

    // Toggle panel móvil
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

    // Cerrar al hacer click en overlay
    overlay.addEventListener("click", () => {
      adminPanel.classList.remove("active");
      toggleBtn.classList.remove("active");
      overlay.classList.remove("active");
      document.body.style.overflow = "";
    });

    // Cerrar con ESC
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && adminPanel.classList.contains("active")) {
        adminPanel.classList.remove("active");
        toggleBtn.classList.remove("active");
        overlay.classList.remove("active");
        document.body.style.overflow = "";
      }
    });
  }

  if (btnAgregar) {
    btnAgregar.addEventListener("click", () => {
      form.reset();
      indexEditar.value = "";
      formTitulo.textContent = "Agregar nuevo documento";
      panel.classList.add("active");
      
      // Cerrar panel móvil si está abierto
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

  // Cerrar formulario con ESC
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && panel && panel.classList.contains("active")) {
      panel.classList.remove("active");
    }
  });

  if (form) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      const archivo = form.archivo.files[0];
      if (!archivo) return alert("Por favor selecciona un archivo.");

      const reader = new FileReader();
      reader.onload = function () {
        const data = {
          titulo: form.titulo.value,
          descripcion: form.descripcion.value,
          nombreArchivo: archivo.name,
          tipoArchivo: archivo.type,
          contenido: reader.result
        };

        documentos.push(data);
        localStorage.setItem("documentos", JSON.stringify(documentos));

        renderDocumentos();
        panel.classList.remove("active");
        form.reset();
        
        // Mostrar mensaje de éxito
        mostrarNotificacion("Documento agregado exitosamente", "success");
      };
      reader.readAsDataURL(archivo);
    });
  }

  function getIconoPorTipo(tipoArchivo) {
    if (tipoArchivo.includes('pdf')) return 'fa-file-pdf';
    if (tipoArchivo.includes('word') || tipoArchivo.includes('document')) return 'fa-file-word';
    if (tipoArchivo.includes('excel') || tipoArchivo.includes('sheet')) return 'fa-file-excel';
    if (tipoArchivo.includes('image')) return 'fa-file-image';
    return 'fa-file-alt';
  }

  function renderDocumentos() {
    lista.innerHTML = "";
    if (documentos.length === 0) {
      lista.innerHTML = `
        <div class="sin-documentos">
          <i class="fa-solid fa-folder-open" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
          <p>No hay documentos registrados.</p>
        </div>`;
      return;
    }

    documentos.forEach((doc, i) => {
      const card = document.createElement("div");
      card.classList.add("documento-card");

      const icono = getIconoPorTipo(doc.tipoArchivo);
      
      let adminButtons = '';

      if (typeof CAN_EDIT_CARDS !== 'undefined' && CAN_EDIT_CARDS) {
        adminButtons = `
          <button class="btn-eliminar" data-i="${i}" title="Eliminar documento">
            <i class="fa-solid fa-trash"></i> Eliminar
          </button>
        `;
      }

      card.innerHTML = `
        <div class="documento-card-icon">
          <i class="fa-solid ${icono}"></i>
        </div>
        <h3>${doc.titulo}</h3>
        <p>${doc.descripcion || "Sin descripción"}</p>
        <div class="documento-info">
          <strong><i class="fa-solid fa-file"></i> Archivo:</strong> ${doc.nombreArchivo}
        </div>
        <div class="acciones-card">
          <button class="btn-visualizar" data-i="${i}" title="Visualizar documento">
            <i class="fa-solid fa-eye"></i> Ver
          </button>
          <a href="${doc.contenido}" download="${doc.nombreArchivo}" class="btn-descargar" title="Descargar documento">
            <i class="fa-solid fa-download"></i> Descargar
          </a>
          ${adminButtons}
        </div>
      `;
      
      // Animación de entrada
      card.style.opacity = "0";
      card.style.transform = "translateY(20px)";
      lista.appendChild(card);
      
      setTimeout(() => {
        card.style.transition = "all 0.4s ease";
        card.style.opacity = "1";
        card.style.transform = "translateY(0)";
      }, i * 50);
    });

    // Eventos para visualizar
    document.querySelectorAll(".btn-visualizar").forEach(b => b.onclick = visualizarDocumento);

    if (typeof CAN_EDIT_CARDS !== 'undefined' && CAN_EDIT_CARDS) {
      document.querySelectorAll(".btn-eliminar").forEach(b => b.onclick = eliminar);
    }
  }

  function visualizarDocumento(e) {
    const i = e.target.closest("button").dataset.i;
    const doc = documentos[i];

    let modal = document.getElementById("modal-visualizar");
    if (!modal) {
      modal = document.createElement("div");
      modal.id = "modal-visualizar";
      modal.classList.add("modal-visualizar");
      document.body.appendChild(modal);
    }

    let contenido = '';
    if (doc.tipoArchivo === 'application/pdf') {
      contenido = `<iframe src="${doc.contenido}" type="application/pdf"></iframe>`;
    } else if (doc.tipoArchivo.includes('image')) {
      contenido = `<img src="${doc.contenido}" alt="${doc.titulo}" style="max-width: 100%; height: auto; border-radius: 0.8rem;">`;
    } else {
      contenido = `
        <div style="text-align: center; padding: 4rem; background: white; border-radius: 1rem;">
          <i class="fa-solid fa-file" style="font-size: 6rem; color: var(--color); margin-bottom: 2rem;"></i>
          <h3 style="color: var(--color); margin-bottom: 1rem; font-size: 2rem;">Vista previa no disponible</h3>
          <p style="color: #666; margin-bottom: 2rem; font-size: 1.5rem;">Este tipo de archivo no se puede visualizar en el navegador.</p>
          <a href="${doc.contenido}" download="${doc.nombreArchivo}" class="btn-descargar" style="display: inline-flex;">
            <i class="fa-solid fa-download"></i> Descargar archivo
          </a>
        </div>
      `;
    }

    modal.innerHTML = `
      <div class="modal-contenido">
        <div class="modal-header">
          <h3><i class="fa-solid fa-file-lines"></i> ${doc.titulo}</h3>
          <button class="btn-cerrar-modal" title="Cerrar">
            <i class="fa-solid fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          ${contenido}
        </div>
      </div>
    `;

    modal.classList.add("active");

    const btnCerrarModal = modal.querySelector(".btn-cerrar-modal");
    btnCerrarModal.onclick = () => modal.classList.remove("active");

    modal.onclick = (e) => {
      if (e.target === modal) {
        modal.classList.remove("active");
      }
    };

    document.addEventListener('keydown', function cerrarConEsc(e) {
      if (e.key === 'Escape' && modal.classList.contains('active')) {
        modal.classList.remove("active");
        document.removeEventListener('keydown', cerrarConEsc);
      }
    });
  }

  function eliminar(e) {
    const i = e.target.closest("button").dataset.i;
    const doc = documentos[i];
    
    if (confirm(`¿Eliminar el documento "${doc.titulo}"?`)) {
      documentos.splice(i, 1);
      localStorage.setItem("documentos", JSON.stringify(documentos));
      renderDocumentos();
      mostrarNotificacion("Documento eliminado correctamente", "error");
    }
  }

  function mostrarNotificacion(mensaje, tipo = "success") {
    const notif = document.createElement("div");
    notif.style.cssText = `
      position: fixed;
      top: 9rem;
      right: 2rem;
      background: ${tipo === "success" ? "#28a745" : "#dc3545"};
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
    `;
    
    const icon = tipo === "success" ? "fa-check-circle" : "fa-times-circle";
    notif.innerHTML = `<i class="fa-solid ${icon}"></i> ${mensaje}`;
    
    document.body.appendChild(notif);
    
    setTimeout(() => {
      notif.style.animation = "slideOut 0.3s ease";
      setTimeout(() => notif.remove(), 300);
    }, 3000);
  }

  renderDocumentos();
});

// Animaciones CSS
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
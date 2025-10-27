document.addEventListener("DOMContentLoaded", () => {
  const btnAgregar = document.getElementById("btn-agregar");
  const panel = document.getElementById("formulario-panel");
  const btnCerrar = document.getElementById("btn-cerrar");
  const form = document.getElementById("form-documento");
  const lista = document.getElementById("lista-documentos");
  const formTitulo = document.getElementById("form-titulo");
  const indexEditar = document.getElementById("index-editar");

  let documentos = JSON.parse(localStorage.getItem("documentos")) || [];
 
  if(btnAgregar){
    btnAgregar.addEventListener("click", () => {
      form.reset();
      indexEditar.value = "";
      formTitulo.textContent = "Agregar nuevo documento";
      panel.classList.add("active");
    });
  }

  if(btnCerrar){
    btnCerrar.addEventListener("click", () => {
      panel.classList.remove("active");
    });
  }

  if(form){
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
      };
      reader.readAsDataURL(archivo);
    });
  }

  function getIconoPorTipo(tipoArchivo) {
    if (tipoArchivo.includes('pdf')) return 'fa-file-pdf';
    if (tipoArchivo.includes('word') || tipoArchivo.includes('document')) return 'fa-file-word';
    if (tipoArchivo.includes('excel') || tipoArchivo.includes('sheet')) return 'fa-file-excel';
    return 'fa-file-alt';
  }

  function renderDocumentos() {
    lista.innerHTML = "";
    if (documentos.length === 0) {
      lista.innerHTML = `<p class="sin-documentos">No hay documentos registrados.</p>`;
      return;
    }

    documentos.forEach((doc, i) => {
      const card = document.createElement("div");
      card.classList.add("documento-card");

      const icono = getIconoPorTipo(doc.tipoArchivo);
      
      let adminButtons = '';

      if(typeof CAN_EDIT_CARDS !== 'undefined' && CAN_EDIT_CARDS){
        adminButtons = `
          <button class="btn-eliminar" data-i="${i}">
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
          <strong>Archivo:</strong> ${doc.nombreArchivo}
        </div>
        <div class="acciones-card">
          <button class="btn-visualizar" data-i="${i}">
            <i class="fa-solid fa-eye"></i> Ver
          </button>
          <a href="${doc.contenido}" download="${doc.nombreArchivo}" class="btn-descargar">
            <i class="fa-solid fa-download"></i> Descargar
          </a>
          ${adminButtons}
        </div>
      `;
      lista.appendChild(card);
    });

    // Eventos para visualizar
    document.querySelectorAll(".btn-visualizar").forEach(b => b.onclick = visualizarDocumento);

    if(typeof CAN_EDIT_CARDS !== 'undefined' && CAN_EDIT_CARDS){
      document.querySelectorAll(".btn-eliminar").forEach(b => b.onclick = eliminar);
    }
  }

  function visualizarDocumento(e) {
    const i = e.target.closest("button").dataset.i;
    const doc = documentos[i];

    // Crear modal si no existe
    let modal = document.getElementById("modal-visualizar");
    if (!modal) {
      modal = document.createElement("div");
      modal.id = "modal-visualizar";
      modal.classList.add("modal-visualizar");
      document.body.appendChild(modal);
    }

    // Determinar cómo mostrar el documento
    let contenido = '';
    if (doc.tipoArchivo === 'application/pdf') {
      contenido = `<iframe src="${doc.contenido}" type="application/pdf"></iframe>`;
    } else if (doc.tipoArchivo.includes('image')) {
      contenido = `<img src="${doc.contenido}" alt="${doc.titulo}" style="max-width: 100%; height: auto;">`;
    } else {
      contenido = `
        <div style="text-align: center; padding: 4rem;">
          <i class="fa-solid fa-file" style="font-size: 6rem; color: var(--color); margin-bottom: 2rem;"></i>
          <h3 style="color: var(--color); margin-bottom: 1rem;">Vista previa no disponible</h3>
          <p style="color: #666; margin-bottom: 2rem;">Este tipo de archivo no se puede visualizar en el navegador.</p>
          <a href="${doc.contenido}" download="${doc.nombreArchivo}" class="btn-descargar" style="display: inline-flex;">
            <i class="fa-solid fa-download"></i> Descargar archivo
          </a>
        </div>
      `;
    }

    modal.innerHTML = `
      <div class="modal-contenido">
        <div class="modal-header">
          <h3>${doc.titulo}</h3>
          <button class="btn-cerrar-modal">
            <i class="fa-solid fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          ${contenido}
        </div>
      </div>
    `;

    modal.classList.add("active");

    // Cerrar modal
    const btnCerrarModal = modal.querySelector(".btn-cerrar-modal");
    btnCerrarModal.onclick = () => modal.classList.remove("active");

    // Cerrar al hacer clic fuera del contenido
    modal.onclick = (e) => {
      if (e.target === modal) {
        modal.classList.remove("active");
      }
    };

    // Cerrar con tecla ESC
    document.addEventListener('keydown', function cerrarConEsc(e) {
      if (e.key === 'Escape' && modal.classList.contains('active')) {
        modal.classList.remove("active");
        document.removeEventListener('keydown', cerrarConEsc);
      }
    });
  }

  function eliminar(e) {
    const i = e.target.closest("button").dataset.i;
    if (confirm("¿Eliminar este documento?")) {
      documentos.splice(i, 1);
      localStorage.setItem("documentos", JSON.stringify(documentos));
      renderDocumentos();
    }
  }

  renderDocumentos();
});
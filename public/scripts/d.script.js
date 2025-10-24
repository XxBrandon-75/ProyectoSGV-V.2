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

  function renderDocumentos() {
    lista.innerHTML = "";
    if (documentos.length === 0) {
      lista.innerHTML = `<p class="sin-documentos">No hay documentos registrados.</p>`;
      return;
    }

    documentos.forEach((doc, i) => {
      const card = document.createElement("div");
      card.classList.add("documento-card");

      let adminButtons ='';

      if(typeof CAN_EDIT_CARDS !== 'undefined' && CAN_EDIT_CARDS){
        adminButtons =`
          <button class= "btn-eliminar" data-i="${i}"><i class = "fa-solid fa-trash"></i> Eliminar</button>
        `;
      }
      card.innerHTML = `
        <h3><i class="fa-solid fa-file"></i> ${doc.titulo}</h3>
        <p>${doc.descripcion || "Sin descripción"}</p>
        <p><strong>Archivo:</strong> ${doc.nombreArchivo}</p>
        <div class="acciones-card">
          ${adminButtons}
          <a href="${doc.contenido}" download="${doc.nombreArchivo}" class="btn-descargar"><i class="fa-solid fa-download"></i> Descargar</a>
        </div>
      `;
      lista.appendChild(card);
    });

    if(typeof CAN_EDIT_CARDS !== 'undefined' && CAN_EDIT_CARDS){
      document.querySelectorAll(".btn-eliminar").forEach(b => b.onclick = eliminar);
    }
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

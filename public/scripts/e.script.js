document.addEventListener("DOMContentLoaded", () => {

  const btnAgregar = document.getElementById("btn-agregar"); // Ahora es btn-agregar-nueva
    const panel = document.getElementById("formulario-panel");
    const btnCerrar = document.getElementById("btn-cerrar");
    const form = document.getElementById("form-especialidad");
    const lista = document.getElementById("lista-especialidades");
    const formTitulo = document.getElementById("form-titulo");
    const indexEditar = document.getElementById("index-editar");
    
    const listaRequerimientosForm = document.getElementById("lista-requerimientos-form");

    let registrosVoluntario = JSON.parse(localStorage.getItem("registrosVoluntario")) || [];
    let requerimientosEspecialidadSeleccionada = []; // Requerimientos de la especialidad elegida
    
    // --- Lógica del Voluntario ---

    function cargarRequerimientos(nombreEspecialidad) {
        const especialidadesReales = {
            "Tutorial": [
                { label: "Archivos de la Misión", tipo: "file" },
                { label: "¿Ha completado el curso?", tipo: "select", options: ["Sí", "No"] }
            ],
            "Primeros Auxilios": [
                { label: "Adjuntar Certificado (PDF)", tipo: "file" },
                { label: "Años de experiencia", tipo: "number" }
            ],
            "Rescate": [
                { label: "Adjuntar Licencia de buceo", tipo: "file" }
            ]
        };
        
        const requerimientos = especialidadesReales[nombreEspecialidad] || [];
        requerimientosEspecialidadSeleccionada = requerimientos;
        listaRequerimientosForm.innerHTML = "";

        requerimientos.forEach((req, index) => {
            const label = document.createElement("label");
            label.textContent = req.label;
            
            let input;
            if (req.tipo === "select") {
                input = document.createElement("select");
                req.options.forEach(optText => {
                    const opt = document.createElement("option");
                    opt.value = optText;
                    opt.textContent = optText;
                    input.appendChild(opt);
                });
            } else {
                input = document.createElement("input");
                input.type = req.tipo;
                // Los campos de archivo necesitan un nombre único, pero para este caso de ejemplo, solo se marca como requerido
                input.name = `req_${index}`; 
            }
            input.required = true;
            input.dataset.label = req.label; // Guardar la etiqueta en el elemento para facilitar el envío
            
            listaRequerimientosForm.append(label, input);
        });
      }

    // Evento para cargar requerimientos al seleccionar una especialidad
    const selectEspecialidad = document.getElementById("nombre");
    if(selectEspecialidad) {
        selectEspecialidad.addEventListener("change", (e) => {
            cargarRequerimientos(e.target.value);
        });
    }

    // --- Eventos de UI ---
    if(btnAgregar){
        btnAgregar.addEventListener("click", () => {
            form.reset();
            indexEditar.value = "";
            formTitulo.innerHTML = '<i class="fa-solid fa-list-check"></i> Registrar Nueva Especialidad';
            // Limpiar requerimientos anteriores
            listaRequerimientosForm.innerHTML = ""; 
            requerimientosEspecialidadSeleccionada = [];
            
            // Si es un nuevo registro, forzar la selección inicial si hay un select
            selectEspecialidad.value = "";
            
            panel.classList.add("active");
        });
    }
    
    if(btnCerrar){
        btnCerrar.addEventListener("click", () => {
            panel.classList.remove("active");
            form.reset();
        });
    }

    if(form){
        form.addEventListener("submit", (e) => {
            e.preventDefault();

            const nombreEspecialidad = form.nombre.value;
            const notas = form.descripcion.value;
            
            // Recolectar las respuestas del formulario de requerimientos
            const respuestas = [];
            const reqInputs = listaRequerimientosForm.querySelectorAll('input, select');
            reqInputs.forEach(input => {
                respuestas.push({
                    label: input.dataset.label,
                    valor: input.type === 'file' ? input.files[0]?.name || 'Archivo no adjunto' : input.value
                });
            });

            const data = {
                nombre: nombreEspecialidad,
                notas: notas,
                respuestasRequerimientos: respuestas
            };

            const index = indexEditar.value;
            if (index === "") registrosVoluntario.push(data);
            else registrosVoluntario[index] = data;

            localStorage.setItem("registrosVoluntario", JSON.stringify(registrosVoluntario));
            renderEspecialidades();
            panel.classList.remove("active");
            form.reset();
        });
    }

    // --- Renderizado de Tarjetas ---
    function renderEspecialidades() {
        lista.innerHTML = "";
        if (registrosVoluntario.length === 0) {
            lista.innerHTML = `<p class="sin-cursos">No has registrado ninguna especialidad. Haz clic en "Agregar / Editar Especialidades" para empezar.</p>`;
            return;
        }

        registrosVoluntario.forEach((reg, i) => {
            const card = document.createElement("div");
            card.classList.add("especialidad-card"); // Nuevo nombre de clase
            
            // Renderizar las respuestas de los requerimientos
            const respuestasHTML = reg.respuestasRequerimientos.map(r => 
                `<p><strong>${r.label}:</strong> ${r.valor}</p>`
            ).join('');

            card.innerHTML = `
                <h3><i class="fa-solid fa-medal"></i> ${reg.nombre}</h3>
                <p><strong>Notas Personales:</strong> ${reg.notas || 'Sin notas.'}</p>
                <div class="requerimientos-registrados">
                    <h4>Datos de Experiencia:</h4>
                    ${respuestasHTML}
                </div>
                <div class="card-acciones">
                    <button class="btn-accion-card btn-editar-reg" data-i="${i}">
                        <i class="fa-solid fa-pen"></i> Editar
                    </button>
                    <button class="btn-accion-card btn-eliminar-reg" data-i="${i}">
                        <i class="fa-solid fa-trash"></i> Eliminar
                    </button>
                </div>
            `;
            lista.appendChild(card);
        });

        document.querySelectorAll(".btn-editar-reg").forEach(b => b.onclick = e => editarRegistro(e));
        document.querySelectorAll(".btn-eliminar-reg").forEach(b => b.onclick = e => eliminarRegistro(e));
    }

    function editarRegistro(e) {
        const i = e.target.closest("button").dataset.i;
        const reg = registrosVoluntario[i];
        
        indexEditar.value = i;
        formTitulo.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Editar Registro';
        
        // 1. Seleccionar la especialidad
        form.nombre.value = reg.nombre;
        
        // 2. Cargar los requerimientos y pre-llenar los campos
        cargarRequerimientos(reg.nombre);
        
        form.descripcion.value = reg.notas;
        
        // Pre-llenar las respuestas
        const reqInputs = listaRequerimientosForm.querySelectorAll('input, select');
        reg.respuestasRequerimientos.forEach(res => {
            const input = Array.from(reqInputs).find(i => i.dataset.label === res.label);
            if(input && input.type !== 'file') {
                input.value = res.valor;
            }
        });
        
        panel.classList.add("active");
    }

    function eliminarRegistro(e) {
        const i = e.target.closest("button").dataset.i;
        if (confirm("¿Estás seguro de eliminar este registro de especialidad?")) {
            registrosVoluntario.splice(i, 1);
            localStorage.setItem("registrosVoluntario", JSON.stringify(registrosVoluntario));
            renderEspecialidades();
        }
    }
        
    renderEspecialidades();
});
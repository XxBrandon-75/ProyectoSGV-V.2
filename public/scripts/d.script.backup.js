document.addEventListener("DOMContentLoaded", () => {
    const btnAgregarNueva = document.getElementById("btn-agregar-nueva");
    const formPanel = document.getElementById("formulario-panel");
    const btnCancelar = document.getElementById("btn-cancelar");
    const formDocumento = document.getElementById("form-documento");
    const listaDocumentos = document.getElementById("lista-documentos");

    // Cargar documentos al iniciar
    cargarDocumentos();

    // Mostrar formulario para nuevo documento (solo admin)
    if (btnAgregarNueva) {
        btnAgregarNueva.addEventListener("click", () => {
            formDocumento.reset();
            formPanel.classList.add("active");
        });
    }

    // Cancelar formulario
    if (btnCancelar) {
        btnCancelar.addEventListener("click", () => {
            formPanel.classList.remove("active");
        });
    }

    // Cerrar formulario con ESC
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && formPanel && formPanel.classList.contains("active")) {
            formPanel.classList.remove("active");
        }
    });

    // Enviar formulario de documento
    if (formDocumento) {
        formDocumento.addEventListener("submit", async (e) => {
            e.preventDefault();

            const formData = new FormData(formDocumento);
            const archivo = formDocumento.querySelector('input[type="file"]').files[0];

            if (!archivo) {
                alert("Por favor selecciona un archivo.");
                return;
            }

            // Validar tipo de archivo
            const tiposPermitidos = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (!tiposPermitidos.includes(archivo.type)) {
                alert("Solo se permiten archivos PDF, DOC o DOCX.");
                return;
            }

            // Validar tamaño (10MB)
            if (archivo.size > 10 * 1024 * 1024) {
                alert("El archivo no debe superar los 10MB.");
                return;
            }

            try {
                const response = await fetch('index.php?controller=home&action=subirDocumento', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message || 'Documento subido correctamente');
                    formPanel.classList.remove("active");
                    formDocumento.reset();
                    cargarDocumentos();
                } else {
                    alert(result.message || 'Error al subir el documento');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al subir el documento. Por favor intenta de nuevo.');
            }
        });
    }

    // Función para cargar documentos
    async function cargarDocumentos() {
        try {
            listaDocumentos.innerHTML = '<div class="cargando-documentos"><i class="fa-solid fa-spinner fa-spin"></i><p>Cargando documentos...</p></div>';

            const response = await fetch('index.php?controller=home&action=obtenerDocumentos');
            const data = await response.json();

            if (data.success && data.documentos && data.documentos.length > 0) {
                renderizarDocumentos(data.documentos);
            } else {
                listaDocumentos.innerHTML = `
                    <div class="sin-documentos">
                        <i class="fa-solid fa-folder-open"></i>
                        <p>${ES_ADMIN ? 'No hay documentos cargados. Agrega el primer borrador.' : 'No hay documentos disponibles.'}</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error al cargar documentos:', error);
            listaDocumentos.innerHTML = '<div class="sin-documentos"><p>Error al cargar documentos</p></div>';
        }
    }

    // Función para renderizar documentos
    function renderizarDocumentos(documentos) {
        listaDocumentos.innerHTML = documentos.map(doc => {
            const esBorrador = doc.TipoDocumento === 'Borrador';
            const estatus = esBorrador ? 'borrador' : (doc.Estatus || 'pendiente').toLowerCase();
            
            let accionesHTML = '';
            
            if (ES_ADMIN) {
                // Vista Admin
                if (esBorrador) {
                    accionesHTML = `
                        <button class="btn-accion btn-visualizar" onclick="visualizarDocumento(${doc.DocumentoID})">
                            <i class="fa-solid fa-eye"></i> Ver
                        </button>
                        <button class="btn-accion btn-eliminar" onclick="eliminarDocumento(${doc.DocumentoID})">
                            <i class="fa-solid fa-trash"></i> Eliminar
                        </button>
                    `;
                } else {
                    // Documento subido por voluntario
                    accionesHTML = `
                        <button class="btn-accion btn-visualizar" onclick="visualizarDocumento(${doc.DocumentoID})">
                            <i class="fa-solid fa-eye"></i> Ver
                        </button>
                        <button class="btn-accion btn-aprobar" onclick="aprobarDocumento(${doc.DocumentoID})">
                            <i class="fa-solid fa-check"></i> Aprobar
                        </button>
                        <button class="btn-accion btn-rechazar" onclick="rechazarDocumento(${doc.DocumentoID})">
                            <i class="fa-solid fa-times"></i> Rechazar
                        </button>
                    `;
                }
            } else {
                // Vista Voluntario
                if (esBorrador) {
                    accionesHTML = `
                        <button class="btn-accion btn-descargar" onclick="descargarDocumento(${doc.DocumentoID})">
                            <i class="fa-solid fa-download"></i> Descargar Borrador
                        </button>
                    `;
                } else {
                    accionesHTML = `
                        <button class="btn-accion btn-visualizar" onclick="visualizarDocumento(${doc.DocumentoID})">
                            <i class="fa-solid fa-eye"></i> Ver Documento
                        </button>
                    `;
                }
            }

            const fechaSubida = doc.FechaSubida ? new Date(doc.FechaSubida).toLocaleDateString('es-MX') : 'N/A';
            const subidoPor = doc.NombreSubido || (esBorrador ? 'Administrador' : 'Voluntario');

            return `
                <div class="documento-card ${estatus}">
                    <div class="documento-header ${estatus}">
                        <h3 class="documento-titulo">
                            <i class="fa-solid ${esBorrador ? 'fa-file-pdf' : 'fa-file-check'}"></i>
                            ${doc.NombreArchivo || 'Documento'}
                        </h3>
                        <span class="documento-estatus">${esBorrador ? 'Borrador' : estatus}</span>
                    </div>
                    <div class="documento-body">
                        ${doc.Descripcion ? `<p class="documento-descripcion">${doc.Descripcion}</p>` : ''}
                        <div class="documento-info">
                            <div class="info-item">
                                <i class="fa-solid fa-calendar"></i>
                                <span><strong>Subido:</strong> ${fechaSubida}</span>
                            </div>
                            <div class="info-item">
                                <i class="fa-solid fa-user"></i>
                                <span><strong>Por:</strong> ${subidoPor}</span>
                            </div>
                            ${doc.NombreVoluntario && ES_ADMIN ? `
                            <div class="info-item">
                                <i class="fa-solid fa-id-card"></i>
                                <span><strong>Voluntario:</strong> ${doc.NombreVoluntario}</span>
                            </div>
                            ` : ''}
                        </div>
                        <div class="documento-acciones">
                            ${accionesHTML}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Funciones globales
    window.descargarDocumento = async function(documentoId) {
        try {
            window.location.href = `index.php?controller=home&action=descargarDocumento&id=${documentoId}`;
        } catch (error) {
            console.error('Error:', error);
            alert('Error al descargar el documento');
        }
    };

    window.visualizarDocumento = async function(documentoId) {
        try {
            window.open(`index.php?controller=home&action=verDocumento&id=${documentoId}`, '_blank');
        } catch (error) {
            console.error('Error:', error);
            alert('Error al visualizar el documento');
        }
    };

    window.aprobarDocumento = async function(documentoId) {
        if (!confirm('¿Estás seguro de aprobar este documento?')) return;

        try {
            const response = await fetch('index.php?controller=home&action=aprobarDocumento', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `documentoId=${documentoId}`
            });

            const result = await response.json();

            if (result.success) {
                alert('Documento aprobado correctamente');
                cargarDocumentos();
            } else {
                alert(result.message || 'Error al aprobar el documento');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al aprobar el documento');
        }
    };

    window.rechazarDocumento = async function(documentoId) {
        const motivo = prompt('Ingresa el motivo del rechazo:');
        if (!motivo) return;

        try {
            const response = await fetch('index.php?controller=home&action=rechazarDocumento', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `documentoId=${documentoId}&motivo=${encodeURIComponent(motivo)}`
            });

            const result = await response.json();

            if (result.success) {
                alert('Documento rechazado');
                cargarDocumentos();
            } else {
                alert(result.message || 'Error al rechazar el documento');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al rechazar el documento');
        }
    };

    window.eliminarDocumento = async function(documentoId) {
        if (!confirm('¿Estás seguro de eliminar este borrador?')) return;

        try {
            const response = await fetch('index.php?controller=home&action=eliminarDocumento', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `documentoId=${documentoId}`
            });

            const result = await response.json();

            if (result.success) {
                alert('Documento eliminado');
                cargarDocumentos();
            } else {
                alert(result.message || 'Error al eliminar el documento');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al eliminar el documento');
        }
    };
});

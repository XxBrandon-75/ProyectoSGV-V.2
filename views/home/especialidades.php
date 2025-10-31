<section class="especialidades-voluntario">
    <h1><i class="fa-solid fa-graduation-cap"></i> Mis Especialidades y Áreas de Experiencia</h1>
    <p class="descripcion-pagina">Aquí puedes registrar las especialidades o áreas de experiencia que posees para que la coordinación pueda asignarte tareas relevantes.</p>
    
    <button id="btn-agregar" class="btn-agregar-nueva">
        <i class="fa-solid fa-plus"></i> Agregar / Editar Especialidades
    </button>
    
    <div id="lista-especialidades" class="lista-especialidades">
        <p class="sin-cursos">No has registrado ninguna especialidad. Haz clic en "Agregar / Editar Especialidades" para empezar.</p>
    </div>
</section>

<div id="formulario-panel" class="formulario-panel">
    <form id="form-especialidad" class="formulario">
        <h2 id="form-titulo"><i class="fa-solid fa-list-check"></i> Registrar Nueva Especialidad</h2>
        <input type="hidden" id="index-editar">

        <label for="nombre">Seleccionar Especialidad</label>
        <select id="nombre" required>
            <option value="" disabled selected>Elige una especialidad existente...</option>
            <option value="Tutorial">Tutorial</option>
            <option value="Primeros Auxilios">Primeros Auxilios</option>
            <option value="Rescate">Rescate</option>
            </select>
        
        <label for="descripcion">Notas / Observaciones sobre tu experiencia</label>
        <textarea id="descripcion" rows="3"></textarea> 

        <div class="requerimientos-admin">
            <h3>Evidencia Requerida por la Especialidad</h3>
            <p class="instruccion-req">Llena los campos necesarios para validar tu experiencia.</p>
            <div id="lista-requerimientos-form">
                </div>
        </div>

        <div class="form-buttons">
            <button type="submit" class="btn-guardar">Guardar Especialidad</button>
            <button type="button" id="btn-cerrar" class="btn-cancelar">Cancelar</button>
        </div>
    </form>
</div>

<script>
    const IS_VOLUNTARIO_VIEW = true; 
</script>
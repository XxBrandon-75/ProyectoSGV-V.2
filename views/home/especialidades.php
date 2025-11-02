<section class="especialidades-voluntario">
    <h1><i class="fa-solid fa-graduation-cap"></i> Mis Especialidades y Áreas de Experiencia</h1>
    <p class="descripcion-pagina">Aquí puedes registrar las especialidades o áreas de experiencia que posees para que la coordinación pueda asignarte tareas relevantes.</p>
    
    <button id="btn-agregar" class="btn-agregar-nueva">
        <i class="fa-solid fa-plus"></i> Agregar Nueva Especialidad
    </button>
    
    <div id="lista-especialidades" class="lista-especialidades">
        <div class="cargando-especialidades">
            <i class="fa-solid fa-spinner fa-spin"></i> Cargando especialidades...
        </div>
    </div>
</section>

<!-- Panel lateral para agregar especialidad -->
<div id="formulario-panel" class="formulario-panel">
    <form id="form-especialidad" class="formulario" enctype="multipart/form-data">
        <h2 id="form-titulo"><i class="fa-solid fa-list-check"></i> Registrar Nueva Especialidad</h2>

        <label for="nombre">Seleccionar Especialidad <span style="color: red;">*</span></label>
        <select id="nombre" name="nombreEspecialidad" required>
            <option value="" disabled selected>Elige una especialidad existente...</option>
            <option value="Tutorial">Tutorial</option>
            <option value="Primeros Auxilios">Primeros Auxilios</option>
            <option value="Rescate">Rescate</option>
        </select>
        
        <label for="descripcion">Notas / Observaciones sobre tu experiencia <span style="color: red;">*</span></label>
        <textarea id="descripcion" name="autodescripcion" rows="4" placeholder="Describe brevemente tu experiencia en esta especialidad..." required></textarea> 

        <div class="requerimientos-admin">
            <h3><i class="fa-solid fa-file-arrow-up"></i> Evidencia Requerida</h3>
            <p class="instruccion-req">Adjunta un documento que valide tu especialidad (certificado, diploma, licencia, etc.)</p>
            
            <label for="archivo">Adjuntar Documento <span style="color: red;">*</span></label>
            <input type="file" id="archivo" name="archivo" accept=".pdf,application/pdf" required>
            <small style="color: #666; display: block; margin-top: 0.5rem;">
                Solo se permiten archivos en formato PDF (máx. 5MB)
            </small>
        </div>

        <div class="form-buttons">
            <button type="submit" class="btn-guardar">
                <i class="fa-solid fa-save"></i> Guardar Especialidad
            </button>
            <button type="button" id="btn-cerrar" class="btn-cancelar">
                <i class="fa-solid fa-times"></i> Cancelar
            </button>
        </div>
    </form>
</div>